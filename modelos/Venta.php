<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Venta
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
		$error = $this->validarPrecioCompraPrecioVenta($idarticulo, $idservicio, $precio_compra, $precio_venta);
		if ($error) {
			// Si cumple, o sea si es verdadero, no se puede insertar
			$mensajeError = "El precio de venta de uno de los artículos o servicios no puede ser menor al precio de compra.";
		}

		// Si hay un mensaje de error, retornar false y mostrar el mensaje en el script principal
		if ($mensajeError !== "") {
			return $mensajeError;
		}

		// Si no hay errores, continuamos con el registro de la venta
		$sql = "INSERT INTO venta (idusuario,idlocal,idcliente,idcaja,tipo_comprobante,num_comprobante,fecha_hora,impuesto,total_venta,vuelto,comentario_interno,comentario_externo,estado)
		VALUES ('$idusuario','$idlocal','$idcliente','$idcaja','$tipo_comprobante','$num_comprobante',SYSDATE(),'$impuesto','$total_venta','$vuelto','$comentario_interno','$comentario_externo','Iniciado')";
		//return ejecutarConsulta($sql);
		$idventanew = ejecutarConsulta_retornarID($sql);

		$sw = true;
		$num_elementos = 0;

		while ($num_elementos < count($idarticulo)) {
			$sql_detalle = "INSERT INTO detalle_venta(idventa,idarticulo,idservicio,idpersonal,cantidad,precio_venta,descuento) VALUES ('$idventanew','$idarticulo[$num_elementos]','0','$idpersonal[$num_elementos]','$cantidad[$num_elementos]','$precio_venta[$num_elementos]','$descuento[$num_elementos]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$actualizar_art = "UPDATE articulo SET precio_venta='$precio_venta[$num_elementos]' WHERE idarticulo='$idarticulo[$num_elementos]'";
			ejecutarConsulta($actualizar_art) or $sw = false;

			$num_elementos = $num_elementos + 1;
		}

		$num_elementos2 = 0;

		while ($num_elementos2 < count($idservicio)) {
			$sql_detalle = "INSERT INTO detalle_venta(idventa,idarticulo,idservicio,idpersonal,cantidad,precio_venta,descuento) VALUES ('$idventanew','0','$idservicio[$num_elementos2]','$idpersonal[$num_elementos2]','$cantidad[$num_elementos2]','$precio_venta[$num_elementos2]','$descuento[$num_elementos2]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$actualizar_art = "UPDATE servicios SET costo='$precio_venta[$num_elementos2]' WHERE idservicio='$idservicio[$num_elementos2]'";
			ejecutarConsulta($actualizar_art) or $sw = false;

			$num_elementos2 = $num_elementos2 + 1;
		}

		$num_elementos3 = 0;

		while ($num_elementos3 < count($metodo_pago)) {
			$sql_detalle = "INSERT INTO detalle_venta_pagos(idventa,idmetodopago,monto) VALUES ('$idventanew','$metodo_pago[$num_elementos3]','$monto[$num_elementos3]')";
			ejecutarConsulta($sql_detalle) or $sw = false;

			$num_elementos3 = $num_elementos3 + 1;
		}

		return [$sw, $idventanew];
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

	public function validarPrecioCompraPrecioVenta($idarticulo, $idservicio, $precio_compra, $precio_venta)
	{
		$idarticulo_servicio = array_merge($idarticulo, $idservicio);

		for ($i = 0; $i < count($idarticulo_servicio); $i++) {
			if ($precio_venta[$i] < $precio_compra[$i]) {
				return true;
			}
		}
		return false;
	}


	public function verificarNumeroExiste($num_comprobante, $idlocal)
	{
		$sql = "SELECT * FROM venta WHERE num_comprobante = '$num_comprobante' AND idlocal = '$idlocal'";
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

	//Implementamos un método para eliminar categorías
	public function eliminar($idventa)
	{
		$sql = "DELETE FROM venta WHERE idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para listar los registros
	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT v.idventa,DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,v.idcliente,p.nombre AS cliente,v.idcaja, ca.titulo AS caja,al.idlocal,al.titulo AS local,u.idusuario,u.nombre AS usuario, u.cargo AS cargo,v.tipo_comprobante,v.num_comprobante,v.total_venta,v.vuelto,v.comentario_interno,v.comentario_externo,v.impuesto,v.estado FROM venta v LEFT JOIN clientes p ON v.idcliente=p.idcliente LEFT JOIN cajas ca ON v.idcaja=ca.idcaja LEFT JOIN locales al ON v.idlocal = al.idlocal LEFT JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idlocal = '$idlocalSession' AND DATE(v.fecha_hora) >= '$fecha_inicio' AND DATE(v.fecha_hora) <= '$fecha_fin' ORDER by v.idventa DESC";
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
		$sql = "SELECT num_comprobante as last_num_comprobante FROM venta WHERE idlocal = '$idlocal' ORDER BY idventa DESC LIMIT 1";
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
				  l.titulo AS local,
				  c.nombre AS cliente,
				  ca.titulo AS caja,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  v.fecha_hora,
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

		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarDetallesProductoVenta($idventa)
	{
		$sql = "SELECT
				  dv.idventa,
				  dv.idarticulo,a.nombre,
				  dv.cantidad,
				  dv.precio_venta,
				  dv.precio_compra,
				  dv.descuento
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				WHERE dv.idventa='$idventa'";

		return ejecutarConsulta($sql);
	}
}
