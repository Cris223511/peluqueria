<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Proforma
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idlocal, $idcliente, $idcaja, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $idarticulo, $idservicio, $idpersonal, $cantidad, $precio_compra, $precio_venta, $descuento, $metodo_pago, $monto)
	{
		$idarticulo = isset($idarticulo) && $idarticulo !== NULL ? $idarticulo : [];
		$idservicio = isset($idservicio) && $idservicio !== NULL ? $idservicio : [];

		// Inicializar variable de mensaje
		$mensajeError = "";

		// Primero, debemos verificar si hay suficiente stock para cada artículo
		$error = $this->validarStock($idarticulo, $cantidad);
		if ($error) {
			// Si hay un error, no se puede insertar
			$mensajeError = "Una de las cantidades superan al stock normal del artículo o servicio.";
		}

		// Luego verificamos si el subtotal es negativo
		$error = $this->validarSubtotalNegativo($idarticulo, $idservicio, $cantidad, $precio_venta, $descuento);
		if ($error) {
			// Si cumple, o sea si es verdadero, asignamos el mensaje correspondiente
			$mensajeError = "El subtotal de uno de los artículos o servicios no puede ser menor a 0.";
		}

		// Luego verificamos si el precio de venta es menor al precio de compra
		$error = $this->validarPrecioCompraPrecioVenta($idarticulo, $precio_compra, $precio_venta);
		if ($error) {
			// Si cumple, o sea si es verdadero, no se puede insertar
			$mensajeError = "El precio de venta de uno de los artículos o servicios no puede ser menor al precio de compra.";
		}

		// Si hay un mensaje de error, retornar false y mostrar el mensaje en el script principal
		if ($mensajeError !== "") {
			return $mensajeError;
		}

		// Si no hay errores, continuamos con el registro de la proforma
		$sql = "INSERT INTO proforma (idusuario,idlocal,idcliente,idcaja,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_venta,vuelto,comentario_interno,comentario_externo,estado,eliminado)
		VALUES ('$idusuario','$idlocal','$idcliente','$idcaja','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_venta','$vuelto','$comentario_interno','$comentario_externo','Finalizado','0')";
		//return ejecutarConsulta($sql);

		$idproformanew = ejecutarConsulta_retornarID($sql);
		$items = array_merge($idarticulo, $idservicio);

		$sw = true;

		for ($i = 0; $i < count($items); $i++) {
			$esArticulo = $i < count($idarticulo);
			$esServicio = $i >= count($idarticulo);

			$id = $esArticulo && isset($idarticulo[$i]) ? $idarticulo[$i] : 0;
			$idServicio = $esServicio && isset($idservicio[$i - count($idarticulo)]) ? $idservicio[$i - count($idarticulo)] : 0;

			$cantidadItem = $cantidad[$i];
			$idPersonalItem = $idpersonal[$i];
			$precioVentaItem = $precio_venta[$i];
			$descuentoItem = $descuento[$i];

			$sql_detalle = "INSERT INTO detalle_proforma(idproforma,idcaja,idarticulo,idservicio,idpersonal,cantidad,precio_venta,descuento,impuesto,fecha_hora) VALUES ('$idproformanew','$idcaja','$id','$idServicio','$idPersonalItem','$cantidadItem','$precioVentaItem','$descuentoItem','$impuesto',SYSDATE())";

			ejecutarConsulta($sql_detalle) or $sw = false;

			if ($esArticulo && $id != 0) {
				$actualizar_art = "UPDATE articulo SET precio_venta='$precioVentaItem' WHERE idarticulo='$id'";
				ejecutarConsulta($actualizar_art) or $sw = false;
			} elseif ($esServicio && $idServicio != 0) {
				$actualizar_serv = "UPDATE servicios SET costo='$precioVentaItem' WHERE idservicio='$idServicio'";
				ejecutarConsulta($actualizar_serv) or $sw = false;
			}
		}

		$num_elementos = 0;

		while ($num_elementos < count($metodo_pago)) {
			$sql_detalle = "INSERT INTO detalle_proforma_pagos(idproforma,idmetodopago,monto) VALUES ('$idproformanew','$metodo_pago[$num_elementos]','$monto[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		// $sql_actualizar_monto = "UPDATE cajas SET monto = monto + '$total_venta' WHERE idcaja = '$idcaja'";
		// ejecutarConsulta($sql_actualizar_monto);

		return [$sw, $idproformanew];
	}

	public function validarStock($idarticulo, $cantidad)
	{
		for ($i = 0; $i < count($idarticulo); $i++) {
			$sql = "SELECT stock FROM articulo WHERE idarticulo = '$idarticulo[$i]'";
			$res = ejecutarConsultaSimpleFila($sql);
			$stockActual = $res['stock'];
			if ($cantidad[$i] > $stockActual) {
				return true;
			}
		}
		return false;
	}

	public function validarSubtotalNegativo($idarticulo, $idservicio, $cantidad, $precio_venta, $descuento)
	{
		$idarticulo_servicio = array_merge($idarticulo, $idservicio);

		for ($i = 0; $i < count($idarticulo_servicio); $i++) {
			if ((($cantidad[$i] * $precio_venta[$i]) - $descuento[$i]) < 0) {
				return true;
			}
		}
		return false;
	}

	public function validarPrecioCompraPrecioVenta($idarticulo, $precio_compra, $precio_venta)
	{
		for ($i = 0; $i < count($idarticulo); $i++) {
			if ($precio_venta[$i] < $precio_compra[$i]) {
				return true;
			}
		}
		return false;
	}


	public function verificarNumeroExiste($num_comprobante, $idlocal)
	{
		$sql = "SELECT * FROM proforma WHERE num_comprobante = '$num_comprobante' AND idlocal = '$idlocal'";
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

	//Implementamos un método para cambiar el estado de la proforma
	public function cambiarEstado($idproforma, $estado)
	{
		$sql = "UPDATE proforma SET estado='$estado' WHERE idproforma='$idproforma'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para anular la proforma
	public function anular($idproforma)
	{
		$sql = "UPDATE proforma SET estado='Anulado' WHERE idproforma='$idproforma'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para eliminar la proforma
	public function eliminar($idproforma)
	{
		$sql = "UPDATE proforma SET eliminado = '1' WHERE idproforma='$idproforma'";
		return ejecutarConsulta($sql);
	}

	public function listar()
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarEstado($estado)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioEstado($idlocalSession, $estado)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFechaEstado($idlocalSession, $fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT v.idproforma,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,p.tipo_documento AS cliente_tipo_documento,p.num_documento AS cliente_num_documento,p.direccion AS cliente_direccion,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM proforma v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' AND v.estado = '$estado' AND v.eliminado = '0' ORDER by v.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivosPorUsuario($idlocal)
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0'
				UNION
				SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.idlocal='$idlocal' AND c.eliminado='0'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.idlocal='$idlocal' AND l.idusuario <> 0 AND l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'personales' AS tabla, p.idpersonal AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM personales p LEFT JOIN locales l ON p.idlocal = l.idlocal WHERE p.idlocal='$idlocal' AND p.eliminado='0'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.idlocal = '$idlocal' AND a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, '0.00' AS precio_compra, s.costo AS precio_venta, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivos()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0'
				UNION
				SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.eliminado='0'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.idusuario <> 0 AND l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'personales' AS tabla, p.idpersonal AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM personales p LEFT JOIN locales l ON p.idlocal = l.idlocal WHERE p.eliminado='0'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, '0.00' AS precio_compra, s.costo AS precio_venta, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoria($idcategoria)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoriaLocal($idcategoria, $idlocal)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.precio_venta AS precio_venta, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarMetodosDePago()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM metodo_pago m WHERE m.eliminado='0'";
		return ejecutarConsulta($sql);
	}

	public function listarClientes()
	{
		$sql = "SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.eliminado='0'";
		return ejecutarConsulta($sql);
	}

	public function listarClientesLocal($idlocal)
	{
		$sql = "SELECT 'clientes' AS tabla, c.idcliente AS id, c.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, c.tipo_documento AS tipo_documento, c.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, l.titulo AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM clientes c LEFT JOIN locales l ON c.idlocal = l.idlocal WHERE c.idlocal='$idlocal' AND c.eliminado='0'";
		return ejecutarConsulta($sql);
	}

	public function getLastNumComprobante($idlocal)
	{
		$sql = "SELECT num_comprobante as last_num_comprobante FROM proforma WHERE idlocal = '$idlocal' ORDER BY idproforma DESC LIMIT 1";
		return ejecutarConsulta($sql);
	}

	// MOSTRAR LOS DATOS POR VENTA

	public function listarDetallesVenta($idproforma)
	{
		$sql = "SELECT
				  v.idproforma,
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
				FROM proforma v
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				LEFT JOIN locales l ON v.idlocal = l.idlocal
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas ca ON v.idcaja = ca.idcaja
				WHERE v.idproforma = '$idproforma'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductoVenta($idproforma)
	{
		$sql = "SELECT
				  dv.idproforma,
				  dv.idarticulo,
				  dv.idservicio,
				  a.nombre AS articulo,
				  a.codigo AS codigo_articulo,
				  s.titulo AS servicio,
				  s.codigo AS cod_servicio,
				  dv.cantidad,
				  dv.precio_venta,
				  dv.descuento
				FROM detalle_proforma dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dv.idservicio = s.idservicio
				WHERE dv.idproforma='$idproforma'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesMetodosPagoVenta($idproforma)
	{
		$sql = "SELECT
				  dvp.idproforma,
				  dvp.idmetodopago,
				  m.titulo AS metodo_pago,
				  dvp.monto
				FROM detalle_proforma_pagos dvp
				LEFT JOIN metodo_pago m ON dvp.idmetodopago = m.idmetodopago
				WHERE dvp.idproforma='$idproforma'";

		return ejecutarConsulta($sql);
	}
}