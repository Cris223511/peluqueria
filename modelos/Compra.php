<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Compra
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	public function insertar($idusuario, $idlocal, $idproveedor, $tipo_comprobante, $num_comprobante, $impuesto, $total_compra, $vuelto, $comentario_interno, $comentario_externo, $idarticulo, $idservicio, $idpersonal, $cantidad, $precio_compra, $precio_venta, $descuento, $metodo_pago, $monto)
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

		// Si no hay errores, continuamos con el registro de la compra
		$sql = "INSERT INTO compra (idusuario,idlocal,idproveedor,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_compra,vuelto,comentario_interno,comentario_externo,estado,eliminado)
		VALUES ('$idusuario','$idlocal','$idproveedor','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_compra','$vuelto','$comentario_interno','$comentario_externo','Finalizado','0')";
		//return ejecutarConsulta($sql);

		$idcompranew = ejecutarConsulta_retornarID($sql);
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

			$sql_detalle = "INSERT INTO detalle_compra(idcompra,idarticulo,idservicio,idpersonal,cantidad,precio_venta,descuento,impuesto,fecha_hora) VALUES ('$idcompranew','$id','$idServicio','$idPersonalItem','$cantidadItem','$precioVentaItem','$descuentoItem','$impuesto',SYSDATE())";

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
			$sql_detalle = "INSERT INTO detalle_compra_pagos(idcompra,idmetodopago,monto) VALUES ('$idcompranew','$metodo_pago[$num_elementos]','$monto[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		return [$sw, $idcompranew];
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
		$sql = "SELECT * FROM compra WHERE num_comprobante = '$num_comprobante' AND idlocal = '$idlocal'";
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
		$sql = "SELECT 'metodo_pago' AS tabla, m.idmetodopago AS id, m.titulo AS nombre, NULL AS local_ruc, m.imagen AS imagen, NULL AS tipo_documento, NULL AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM metodo_pago m WHERE m.eliminado='0'
				UNION
				SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM proveedores p WHERE p.eliminado='0'
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
				SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS local, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock, NULL AS stock_minimo FROM proveedores p WHERE p.eliminado='0'
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

	public function listarProveedores()
	{
		$sql = "SELECT 'proveedores' AS tabla, p.idproveedor AS id, p.nombre AS nombre, NULL AS local_ruc, NULL AS imagen, p.tipo_documento AS tipo_documento, p.num_documento AS num_documento, NULL AS cantidad, NULL AS marca, NULL AS codigo, NULL AS precio_compra, NULL AS precio_venta, NULL AS stock FROM proveedores p WHERE p.eliminado='0'";
		return ejecutarConsulta($sql);
	}

	public function getLastNumComprobante($idlocal)
	{
		$sql = "SELECT num_comprobante as last_num_comprobante FROM compra WHERE idlocal = '$idlocal' ORDER BY idcompra DESC LIMIT 1";
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
				  dco.precio_venta,
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
