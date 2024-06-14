<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Venta
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idlocal, $idcliente, $idcaja, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $detalles, $idpersonal, $cantidad, $precio_compra, $precio_venta, $comision, $descuento, $metodo_pago, $monto)
	{
		// Inicializar variable de mensaje
		$mensajeError = "";

		// Convertir $detalles a un array si es una cadena JSON
		$detalles = json_decode($detalles, true);

		// Validaciones
		$error = $this->validarStock($detalles, $cantidad);
		if ($error) {
			$mensajeError = "Una de las cantidades superan al stock normal del artículo o servicio.";
		}

		$error = $this->validarSubtotalNegativo($detalles, $cantidad, $precio_venta, $descuento);
		if ($error) {
			$mensajeError = "El subtotal de uno de los artículos o servicios no puede ser menor a 0.";
		}

		$error = $this->validarPrecioCompraPrecioVenta($detalles, $precio_compra, $precio_venta);
		if ($error) {
			$mensajeError = "El precio de venta de uno de los artículos o servicios no puede ser menor al precio de compra.";
		}

		$error = $this->validarArticuloPorLocal($detalles, $idlocal);
		if ($error) {
			$mensajeError = "Uno de los productos no forman parte del local seleccionado.";
		}

		// Si hay un mensaje de error, retornar false y mostrar el mensaje en el script principal
		if ($mensajeError !== "") {
			return $mensajeError;
		}

		// Continuar con el registro de la venta
		$sql = "INSERT INTO venta (idusuario,idlocal,idcliente,idcaja,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_venta,vuelto,comentario_interno,comentario_externo,estado,eliminado)
		VALUES ('$idusuario','$idlocal','$idcliente','$idcaja','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_venta','$vuelto','$comentario_interno','$comentario_externo','Finalizado','0')";
		$idventanew = ejecutarConsulta_retornarID($sql);

		$sw = true;

		foreach ($detalles as $i => $detalle) {
			$esArticulo = strpos($detalle, '_producto') !== false;
			$esServicio = strpos($detalle, '_servicio') !== false;

			$id = str_replace(['_producto', '_servicio'], '', $detalle);

			$cantidadItem = $cantidad[$i];
			$idPersonalItem = $idpersonal[$i];
			$precioVentaItem = $precio_venta[$i];
			$comisionItem = $comision[$i];
			$descuentoItem = $descuento[$i];

			$idArticulo = $esArticulo ? $id : 0;
			$idServicio = $esServicio ? $id : 0;

			$sql_detalle = "INSERT INTO detalle_venta(idventa,idcaja,idarticulo,idservicio,idpersonal,cantidad,precio_venta,descuento,impuesto,fecha_hora) VALUES ('$idventanew','$idcaja','$idArticulo','$idServicio','$idPersonalItem','$cantidadItem','$precioVentaItem','$descuentoItem','$impuesto',SYSDATE())";

			ejecutarConsulta($sql_detalle) or $sw = false;

			if ($idPersonalItem != 0) {
				$sql_actualizar = "UPDATE personales SET fecha_hora_comision = SYSDATE() WHERE idpersonal = '$idPersonalItem'";
				ejecutarConsulta($sql_actualizar);

				$sql_detalle = "INSERT INTO comisiones (idventa, idproforma, idpersonal, idarticulo, idservicio, idcliente, comision, tipo, fecha_hora) VALUES ('$idventanew', 0, '$idPersonalItem', '$idArticulo', '$idServicio', '$idcliente', '$comisionItem', '1', SYSDATE())";
				ejecutarConsulta($sql_detalle) or $sw = false;
			}

			if ($esArticulo && $id != 0) {
				$actualizar_art = "UPDATE articulo SET precio_venta='$precioVentaItem' WHERE idarticulo='$id'";
				ejecutarConsulta($actualizar_art) or $sw = false;
			} elseif ($esServicio && $id != 0) {
				$actualizar_serv = "UPDATE servicios SET costo='$precioVentaItem' WHERE idservicio='$id'";
				ejecutarConsulta($actualizar_serv) or $sw = false;
			}
		}

		$num_elementos = 0;

		while ($num_elementos < count($metodo_pago)) {
			$sql_detalle = "INSERT INTO detalle_venta_pagos(idventa,idmetodopago,monto) VALUES ('$idventanew','$metodo_pago[$num_elementos]','$monto[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		$sql_actualizar_monto = "UPDATE cajas SET monto_total = monto_total + '$total_venta', vendido = '1' WHERE idcaja = '$idcaja'";
		ejecutarConsulta($sql_actualizar_monto);

		return [$sw, $idventanew];
	}

	public function validarStock($detalles, $cantidad)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false;
		});

		foreach ($idarticulos as $indice => $idarticulo) {
			$id = str_replace('_producto', '', $idarticulo);
			$sql = "SELECT stock FROM articulo WHERE idarticulo = '$id'";
			$res = ejecutarConsultaSimpleFila($sql);
			$stockActual = $res['stock'];
			if ($cantidad[$indice] > $stockActual) {
				return true;
			}
		}
		return false;
	}

	public function validarSubtotalNegativo($detalles, $cantidad, $precio_venta, $descuento)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos_servicios = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false || strpos($detalle, '_servicio') !== false;
		});

		foreach ($idarticulos_servicios as $indice => $id_detalle) {
			$tipo = strpos($id_detalle, '_producto') !== false ? '_producto' : '_servicio';
			$id = str_replace($tipo, '', $id_detalle);

			if ((($cantidad[$indice] * $precio_venta[$indice]) - $descuento[$indice]) < 0) {
				return true;
			}
		}
		return false;
	}

	public function validarPrecioCompraPrecioVenta($detalles, $precio_compra, $precio_venta)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false;
		});

		foreach ($idarticulos as $indice => $idarticulo) {
			$id = str_replace('_producto', '', $idarticulo);
			if ($precio_venta[$indice] < $precio_compra[$indice]) {
				return true;
			}
		}
		return false;
	}

	public function validarArticuloPorLocal($detalles, $idlocal)
	{
		if (!is_array($detalles)) {
			$detalles = json_decode($detalles, true);
		}

		$idarticulos = array_filter($detalles, function ($detalle) {
			return strpos($detalle, '_producto') !== false;
		});

		foreach ($idarticulos as $indice => $idarticulo) {
			$id = str_replace('_producto', '', $idarticulo);
			$sql = "SELECT idarticulo FROM articulo WHERE idarticulo = '$id' AND idlocal = '$idlocal'";
			$result = ejecutarConsultaSimpleFila($sql);
			if (!$result) {
				return true;
			}
		}
		return false;
	}

	public function verificarNumeroExiste($num_comprobante, $idlocal)
	{
		$sql = "SELECT * FROM venta WHERE num_comprobante = '$num_comprobante' AND idlocal = '$idlocal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número ya existe en la tabla
			return true;
		}
		// El número no existe en la tabla
		return false;
	}

	public function validarCaja($idlocal)
	{
		$sql = "SELECT idcaja, estado FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementamos un método para cambiar el estado de la venta
	public function cambiarEstado($idventa, $estado)
	{
		$sql = "UPDATE venta SET estado='$estado' WHERE idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para anular la venta
	public function anular($idventa)
	{
		$sql_anular_venta = "UPDATE venta SET estado='Anulado' WHERE idventa='$idventa'";
		ejecutarConsulta($sql_anular_venta);

		$sql_verificar_eliminada = "SELECT eliminado FROM venta WHERE idventa='$idventa' AND eliminado = '0'";
		$resultado_verificar_eliminada = ejecutarConsulta($sql_verificar_eliminada);

		if ($resultado_verificar_eliminada->num_rows > 0) {
			$sql_actualizar_monto = "UPDATE cajas SET monto_total = GREATEST(0, monto_total - (SELECT total_venta FROM venta WHERE idventa = '$idventa')) WHERE idcaja = (SELECT idcaja FROM venta WHERE idventa = '$idventa')";
			ejecutarConsulta($sql_actualizar_monto);
		}

		$sql_eliminar_comisiones = "DELETE FROM comisiones WHERE idventa='$idventa'";
		ejecutarConsulta($sql_eliminar_comisiones);

		return true;
	}

	//Implementamos un método para eliminar la venta
	public function eliminar($idventa)
	{
		$sql_eliminar_venta = "UPDATE venta SET eliminado = '1' WHERE idventa='$idventa'";
		ejecutarConsulta($sql_eliminar_venta);

		$sql_verificar_anulada = "SELECT estado FROM venta WHERE idventa='$idventa' AND estado <> 'Anulado'";
		$resultado_verificar_anulada = ejecutarConsulta($sql_verificar_anulada);

		if ($resultado_verificar_anulada->num_rows > 0) {
			$sql_actualizar_monto = "UPDATE cajas SET monto_total = GREATEST(0, monto_total - (SELECT total_venta FROM venta WHERE idventa = '$idventa')) WHERE idcaja = (SELECT idcaja FROM venta WHERE idventa = '$idventa')";
			ejecutarConsulta($sql_actualizar_monto);
		}

		$sql_eliminar_comisiones = "DELETE FROM comisiones WHERE idventa='$idventa'";
		ejecutarConsulta($sql_eliminar_comisiones);

		return true;
	}

	public function listar()
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarEstado($estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioEstado($idlocalSession, $estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFechaEstado($idlocalSession, $fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivosPorUsuario($idlocal)
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'
				UNION
				SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE (c.idlocal = '$idlocal' OR c.idlocal = 0) AND c.eliminado='0' AND c.estado='activado'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.idlocal='$idlocal' AND l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'personales' AS tabla, p.idpersonal AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM personales p LEFT JOIN locales l ON p.idlocal = l.idlocal WHERE p.idlocal='$idlocal' AND p.eliminado='0' AND p.estado='activado'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.idlocal = '$idlocal' AND a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' AND ca.estado='activado' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.comision AS comision, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, '0.00' AS precio_compra, s.costo AS precio_venta, '0' AS comision, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivos()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'
				UNION
				SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.eliminado='0' AND c.estado='activado'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'personales' AS tabla, p.idpersonal AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM personales p LEFT JOIN locales l ON p.idlocal = l.idlocal WHERE p.eliminado='0' AND p.estado='activado'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS comision, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' AND ca.estado='activado' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.comision AS comision, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, '0.00' AS precio_compra, s.costo AS precio_venta, '0' AS comision, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoria($idcategoria)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.comision AS comision, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoriaLocal($idcategoria, $idlocal)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.comision AS comision, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarMetodosDePago()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function listarClientes()
	{
		$sql = "SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.eliminado='0' AND c.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function listarClientesLocal($idlocal)
	{
		$sql = "SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.idlocal='$idlocal' AND c.eliminado='0' AND c.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function getLastNumComprobante($idlocal)
	{
		$sql = "SELECT MAX(num_comprobante) AS last_num_comprobante FROM venta WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function getCajaLocal($idlocal)
	{
		$sql = "SELECT idcaja FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function verificarCajaLocal($idlocal)
	{
		$sql = "SELECT estado FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	// MOSTRAR LOS DATOS POR VENTA

	public function listarDetallesVenta($idventa)
	{
		$sql = "SELECT
				  v.idventa,
				  v.idusuario,
				  v.idlocal,
				  v.idcliente,
				  v.idcaja,
				  u.nombre AS usuario,
				  u.tipo_documento AS tipo_documento_usuario,
				  u.num_documento AS num_documento_usuario,
				  u.direccion AS direccion_usuario,
				  u.telefono AS telefono_usuario,
				  u.email AS email_usuario,
				  l.titulo AS local,
				  l.local_ruc AS local_ruc,
				  c.nombre AS cliente,
				  c.telefono AS telefono,
				  c.tipo_documento AS tipo_documento,
				  c.num_documento AS num_documento,
				  ca.titulo AS caja,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha_hora,
				  v.impuesto,
				  v.total_venta,
				  v.vuelto,
				  v.comentario_interno,
				  v.comentario_externo,
				  v.estado
				FROM venta v
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				LEFT JOIN locales l ON v.idlocal = l.idlocal
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas ca ON v.idcaja = ca.idcaja
				WHERE v.idventa = '$idventa'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductoVenta($idventa)
	{
		$sql = "SELECT
				  dv.idventa,
				  dv.idarticulo,
				  dv.idservicio,
				  a.nombre AS articulo,
				  a.codigo AS codigo_articulo,
				  s.titulo AS servicio,
				  s.codigo AS cod_servicio,
				  dv.cantidad,
				  dv.precio_venta,
				  dv.descuento
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dv.idservicio = s.idservicio
				WHERE dv.idventa='$idventa'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesMetodosPagoVenta($idventa)
	{
		$sql = "SELECT
				  dvp.idventa,
				  dvp.idmetodopago,
				  m.titulo AS metodo_pago,
				  dvp.monto
				FROM detalle_venta_pagos dvp
				LEFT JOIN metodo_pago m ON dvp.idmetodopago = m.idmetodopago
				WHERE dvp.idventa='$idventa'";

		return ejecutarConsulta($sql);
	}
}
