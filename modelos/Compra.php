<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Compra
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idlocal, $idproveedor, $tipo_comprobante, $num_comprobante, $impuesto, $total_compra, $vuelto, $comentario_interno, $comentario_externo, $detalles, $cantidad, $precio_compra, $descuento, $metodo_pago, $monto)
	{
		// Inicializar variable de mensaje
		$mensajeError = "";

		// Convertir $detalles a un array si es una cadena JSON
		$detalles = json_decode($detalles, true);

		// // Primero, debemos verificar si hay suficiente stock para cada artículo
		// $error = $this->validarStock($detalles, $cantidad);
		// if ($error) {
		// 	// Si hay un error, no se puede insertar
		// 	$mensajeError = "Una de las cantidades superan al stock normal del artículo o servicio.";
		// }

		$error = $this->validarArticuloPorLocal($detalles, $idlocal);
		if ($error) {
			$mensajeError = "Uno de los artículos no forman parte del local seleccionado.";
		}

		// Si hay un mensaje de error, retornar false y mostrar el mensaje en el script principal
		if ($mensajeError !== "") {
			return $mensajeError;
		}

		// Si no hay errores, continuamos con el registro de la compra
		$sql = "INSERT INTO compra (idusuario,idlocal,idproveedor,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_compra,vuelto,comentario_interno,comentario_externo,estado,eliminado)
		VALUES ('$idusuario','$idlocal','$idproveedor','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_compra','$vuelto','$comentario_interno','$comentario_externo','Finalizado','0')";
		//return ejecutarConsulta($sql);

		$idcompranew = ejecutarConsulta_retornarID($sql);
		$sw = true;

		foreach ($detalles as $i => $detalle) {
			$esArticulo = strpos($detalle, '_producto') !== false;
			$esServicio = strpos($detalle, '_servicio') !== false;

			$id = str_replace(['_producto', '_servicio'], '', $detalle);

			$cantidadItem = $cantidad[$i];
			$precioCompraItem = $precio_compra[$i];
			$descuentoItem = $descuento[$i];

			$idArticulo = $esArticulo ? $id : 0;
			$idServicio = $esServicio ? $id : 0;

			$sql_detalle = "INSERT INTO detalle_compra(idcompra,idarticulo,idservicio,cantidad,precio_compra,descuento,impuesto,fecha_hora) VALUES ('$idcompranew','$idArticulo','$idServicio','$cantidadItem','$precioCompraItem','$descuentoItem','$impuesto',SYSDATE())";

			ejecutarConsulta($sql_detalle) or $sw = false;

			if ($esArticulo && $id != 0) {
				$actualizar_art = "UPDATE articulo SET precio_compra='$precioCompraItem' WHERE idarticulo='$id'";
				ejecutarConsulta($actualizar_art) or $sw = false;
			} elseif ($esServicio && $id != 0) {
				$actualizar_serv = "UPDATE servicios SET costo='$precioCompraItem' WHERE idservicio='$id'";
				ejecutarConsulta($actualizar_serv) or $sw = false;
			}
		}

		$num_elementos = 0;

		while ($num_elementos < count($metodo_pago)) {
			$sql_detalle = "INSERT INTO detalle_compra_pagos(idcompra,idmetodopago,monto) VALUES ('$idcompranew','$metodo_pago[$num_elementos]','$monto[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		return [$sw, $idcompranew];
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
		$sql = "SELECT * FROM compra WHERE num_comprobante = '$num_comprobante' AND idlocal = '$idlocal' AND eliminado = '0'";
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

	//Implementamos un método para cambiar el estado de la compra
	public function cambiarEstado($idcompra, $estado)
	{
		$sql = "UPDATE compra SET estado='$estado' WHERE idcompra='$idcompra'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para anular la compra
	public function anular($idcompra)
	{
		$sql = "UPDATE compra SET estado='Anulado' WHERE idcompra='$idcompra'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para eliminar la compra
	public function eliminar($idcompra)
	{
		$sql = "UPDATE compra SET eliminado = '1' WHERE idcompra='$idcompra'";
		return ejecutarConsulta($sql);
	}

	public function listar()
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarEstado($estado)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.estado = '$estado' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE DATE(co.fecha_hora) >= '$fecha_inicio' AND DATE(co.fecha_hora) <= '$fecha_fin' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE DATE(co.fecha_hora) >= '$fecha_inicio' AND DATE(co.fecha_hora) <= '$fecha_fin' AND co.estado = '$estado' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.idlocal = '$idlocalSession' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioEstado($idlocalSession, $estado)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.idlocal = '$idlocalSession' AND co.estado = '$estado' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.idlocal = '$idlocalSession' AND DATE(co.fecha_hora) >= '$fecha_inicio' AND DATE(co.fecha_hora) <= '$fecha_fin' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFechaEstado($idlocalSession, $fecha_inicio, $fecha_fin, $estado)
	{
		$sql = "SELECT co.idcompra,DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,co.idproveedor,p.nombre AS proveedor,p.tipo_documento AS proveedor_tipo_documento,p.num_documento AS proveedor_num_documento,p.direccion AS proveedor_direccion,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,co.tipo_comprobante,co.num_comprobante,co.total_compra,co.vuelto,co.comentario_interno,co.comentario_externo,co.impuesto,co.estado FROM compra co LEFT JOIN proveedores p ON co.idproveedor=p.idproveedor LEFT JOIN locales al ON co.idlocal = al.idlocal LEFT JOIN usuario u ON co.idusuario=u.idusuario WHERE co.idlocal = '$idlocalSession' AND DATE(co.fecha_hora) >= '$fecha_inicio' AND DATE(co.fecha_hora) <= '$fecha_fin' AND co.estado = '$estado' AND co.eliminado = '0' ORDER by co.idcompra DESC";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivosPorUsuario($idlocal)
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'
				UNION
				SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM proveedores p WHERE p.eliminado='0' AND p.estado='activado'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.idlocal='$idlocal' AND l.idusuario <> 0 AND l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.idlocal = '$idlocal' AND a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' AND ca.estado='activado' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, s.costo AS precio_compra, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarTodosLocalActivos()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'
				UNION
				SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM proveedores p WHERE p.eliminado='0' AND p.estado='activado'
				UNION
				SELECT 'locales' AS tabla, l.idlocal AS id, l.titulo AS nombre, l.local_ruc AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM locales l WHERE l.idusuario <> 0 AND l.estado='activado' AND l.eliminado = '0'
				UNION
				SELECT 'categoria' AS tabla, ca.idcategoria AS id, ca.titulo AS nombre, NULL AS local_ruc, NULL AS imagen, NULL AS tipo_documento, NULL AS num_documento, COUNT(CASE WHEN a.eliminado = '0' THEN a.idcategoria END) AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock, NULL AS stock_minimo FROM categoria ca LEFT JOIN articulo a ON ca.idcategoria = a.idcategoria WHERE ca.eliminado = '0' AND ca.estado='activado' GROUP BY ca.idcategoria, ca.titulo
				UNION
				SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.eliminado = '0'
				UNION
				SELECT 'servicio' AS tabla, s.idservicio AS id, s.titulo AS nombre, NULL AS local_ruc, 'servicios.jpg' AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, 'Servicio' AS marca, NULL AS local, s.codigo AS codigo, s.costo AS precio_compra, '1' AS stock, '1' AS stock_minimo FROM servicios s WHERE s.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoria($idcategoria)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarArticulosPorCategoriaLocal($idcategoria, $idlocal)
	{
		$sql = "SELECT 'articulo' AS tabla, a.idarticulo AS id, a.nombre AS nombre, NULL AS local_ruc, a.imagen AS imagen, NULL AS cantidad, m.titulo AS marca, l.titulo AS local, a.codigo AS codigo, a.precio_compra AS precio_compra, a.stock AS stock, a.stock_minimo AS stock_minimo FROM articulo a LEFT JOIN marcas m ON a.idmarca = m.idmarca LEFT JOIN locales l ON a.idlocal = l.idlocal WHERE a.idlocal = '$idlocal' AND a.idcategoria = '$idcategoria' AND a.eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function listarMetodosDePago()
	{
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS stock FROM metodo_pago m WHERE m.eliminado='0' AND m.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function listarProveedores()
	{
		$sql = "SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS codigo, NULL AS precio_compra, NULL AS stock FROM proveedores p WHERE p.eliminado='0' AND p.estado='activado'";
		return ejecutarConsulta($sql);
	}

	public function getLastNumComprobante($idlocal)
	{
		$sql = "SELECT MAX(num_comprobante) AS last_num_comprobante FROM compra WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	public function getCajaLocal($idlocal)
	{
		$sql = "SELECT idcaja FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		return ejecutarConsulta($sql);
	}

	// MOSTRAR LOS DATOS POR COMPRA

	public function listarDetallesCompra($idcompra)
	{
		$sql = "SELECT
				  co.idcompra,
				  co.idusuario,
				  co.idlocal,
				  co.idproveedor,
				  u.nombre AS usuario,
				  u.tipo_documento AS tipo_documento_usuario,
				  u.num_documento AS num_documento_usuario,
				  u.direccion AS direccion_usuario,
				  u.telefono AS telefono_usuario,
				  u.email AS email_usuario,
				  l.titulo AS local,
				  l.local_ruc AS local_ruc,
				  p.nombre AS proveedor,
				  p.telefono AS telefono,
				  p.tipo_documento AS tipo_documento,
				  p.num_documento AS num_documento,
				  co.tipo_comprobante,
				  co.num_comprobante,
				  DATE_FORMAT(co.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha_hora,
				  co.impuesto,
				  co.total_compra,
				  co.vuelto,
				  co.comentario_interno,
				  co.comentario_externo,
				  co.estado
				FROM compra co
				LEFT JOIN usuario u ON co.idusuario = u.idusuario
				LEFT JOIN locales l ON co.idlocal = l.idlocal
				LEFT JOIN proveedores p ON co.idproveedor = p.idproveedor
				WHERE co.idcompra = '$idcompra'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductoCompra($idcompra)
	{
		$sql = "SELECT
				  dco.idcompra,
				  dco.idarticulo,
				  dco.idservicio,
				  a.nombre AS articulo,
				  a.codigo AS codigo_articulo,
				  s.titulo AS servicio,
				  s.codigo AS cod_servicio,
				  dco.cantidad,
				  dco.precio_compra,
				  dco.descuento
				FROM detalle_compra dco
				LEFT JOIN articulo a ON dco.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dco.idservicio = s.idservicio
				WHERE dco.idcompra='$idcompra'";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesMetodosPagoCompra($idcompra)
	{
		$sql = "SELECT
				  dvp.idcompra,
				  dvp.idmetodopago,
				  m.titulo AS metodo_pago,
				  dvp.monto
				FROM detalle_compra_pagos dvp
				LEFT JOIN metodo_pago m ON dvp.idmetodopago = m.idmetodopago
				WHERE dvp.idcompra='$idcompra'";

		return ejecutarConsulta($sql);
	}
}
