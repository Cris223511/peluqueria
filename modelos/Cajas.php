<?php
require "../config/Conexion.php";

class Caja
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idlocal, $titulo, $monto, $descripcion)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO cajas (idcaja_cerrada, idusuario, idlocal, titulo, monto, monto_total, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
            VALUES ('0','$idusuario','$idlocal','$titulo', '$monto', '$monto', '3', '$descripcion', SYSDATE(), '0000-00-00 00:00:00', 'aperturado','0','0')";
		return ejecutarConsulta($sql);
	}

	public function agregarCajaCerrada($idcaja, $idusuario)
	{
		$sql = "UPDATE cajas SET estado='cerrado', vendido='0' WHERE idcaja='$idcaja'";
		ejecutarConsulta($sql);

		date_default_timezone_set("America/Lima");
		$sql2 = "SELECT * FROM cajas WHERE idcaja = '$idcaja'";
		$resultado = ejecutarConsulta($sql2);

		if ($fila = mysqli_fetch_assoc($resultado)) {
			// Almacena los datos de la fila en variables
			$idcaja_cerrada = $fila['idcaja'];
			$idlocal = $fila['idlocal'];
			$titulo = $fila['titulo'];
			$monto = $fila['monto'];
			$monto_total = $fila['monto_total'];
			$descripcion = $fila['descripcion'];
			$fecha = $fila['fecha_hora'];

			// Inserta los datos en la nueva tabla
			$sql3 = "INSERT INTO cajas_cerradas (idcaja_cerrada, idusuario, idlocal, titulo, monto, monto_total, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
						   VALUES ('$idcaja_cerrada', '$idusuario', '$idlocal', '$titulo', '$monto', '$monto_total', '3', '$descripcion', '$fecha', SYSDATE(), 'cerrado', '0', '0')";
			return ejecutarConsulta($sql3);
		}
	}

	public function verificarNombreExiste($titulo)
	{
		$sql = "SELECT * FROM cajas WHERE titulo = '$titulo' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function verificarNombreEditarExiste($titulo, $idcaja)
	{
		$sql = "SELECT * FROM cajas WHERE titulo = '$titulo' AND idcaja != '$idcaja' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function validarCaja($idlocal)
	{
		$sql = "SELECT * FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// Hay una caja en el local
			return true;
		}
		// No existe una caja en el local
		return false;
	}

	public function editar($idcaja, $idusuario, $idlocal, $titulo, $monto, $descripcion)
	{
		$sql = "UPDATE cajas SET idusuario='$idusuario',idlocal='$idlocal',titulo='$titulo',monto='$monto',monto_total='$monto',descripcion='$descripcion',contador=contador-1 WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function editarSinMonto($idcaja, $idusuario, $idlocal, $titulo, $descripcion)
	{
		$sql = "UPDATE cajas SET idusuario='$idusuario',idlocal='$idlocal',titulo='$titulo',descripcion='$descripcion' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function aperturar($idcaja)
	{
		$sql = "UPDATE cajas SET estado='aperturado', fecha_hora=SYSDATE(), monto='0.00', monto_total='0.00', contador='3', fecha_cierre='0000-00-00 00:00:00' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idcaja)
	{
		$sql = "UPDATE cajas SET eliminado = '1' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function eliminarCajaCerrada($idcaja)
	{
		$sql = "DELETE FROM cajas_cerradas WHERE idcaja = '$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcaja)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc, c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function mostrarCerradas($idcaja)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc, c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarCerradas()
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorParametro($param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.monto_total, c.monto_total, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idcaja, titulo FROM cajas WHERE estado='aperturado' AND eliminado = '0' ORDER BY idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductosCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT
				  a.idarticulo,
				  s.idservicio,
				  a.nombre AS articulo,
				  s.titulo AS servicio,
				  a.codigo AS codigo,
				  s.codigo AS codigo_servicio,
				  dv.cantidad AS cantidad,
				  dv.precio_venta AS precio_venta,
				  dv.descuento AS descuento,
				  dv.impuesto AS impuesto,
				  DATE_FORMAT(dv.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dv.idservicio = s.idservicio
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja' 
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre
				ORDER BY dv.iddetalle_venta ASC";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesVentasCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT 
				  v.idventa,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  c.nombre AS cliente,
				  c.tipo_documento AS tipo_documento_cliente,
				  c.num_documento AS num_documento_cliente,
				  (
					SELECT SUM(dv2.cantidad)
					FROM detalle_venta dv2
					WHERE dv2.idventa = dv.idventa
				  ) AS cantidad,
				  v.total_venta
				FROM detalle_venta dv
				LEFT JOIN venta v ON dv.idventa = v.idventa
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja'
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre
				GROUP BY v.idventa, v.tipo_comprobante, v.num_comprobante, c.nombre, c.tipo_documento, c.num_documento, v.total_venta;";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesVentasAnuladasCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT 
				  v.idventa,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  c.nombre AS cliente,
				  c.tipo_documento AS tipo_documento_cliente,
				  c.num_documento AS num_documento_cliente,
				  (
				  SELECT SUM(dv2.cantidad)
				  FROM detalle_venta dv2
				  WHERE dv2.idventa = dv.idventa
				  ) AS cantidad,
				  v.total_venta
				FROM detalle_venta dv
				LEFT JOIN venta v ON dv.idventa = v.idventa
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja'
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre
				AND v.estado = 'Anulado'  -- Agregando el filtro por estado anulado
				GROUP BY v.idventa, v.tipo_comprobante, v.num_comprobante, c.nombre, c.tipo_documento, c.num_documento, v.total_venta;";

		return ejecutarConsulta($sql);
	}

	public function listarPrimerayUltimaVentaCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "(SELECT 
					v.idventa,
					v.tipo_comprobante,
					v.num_comprobante,
					c.nombre AS cliente,
					c.tipo_documento AS tipo_documento_cliente,
					c.num_documento AS num_documento_cliente,
					(
						SELECT SUM(dv2.cantidad)
						FROM detalle_venta dv2
						WHERE dv2.idventa = dv.idventa
					) AS cantidad,
					v.total_venta
				FROM detalle_venta dv
				LEFT JOIN venta v ON dv.idventa = v.idventa
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja'
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre
				ORDER BY v.idventa ASC
				LIMIT 1)
					
				UNION ALL
				
				(SELECT 
					v.idventa,
					v.tipo_comprobante,
					v.num_comprobante,
					c.nombre AS cliente,
					c.tipo_documento AS tipo_documento_cliente,
					c.num_documento AS num_documento_cliente,
					(
						SELECT SUM(dv2.cantidad)
						FROM detalle_venta dv2
						WHERE dv2.idventa = dv.idventa
					) AS cantidad,
					v.total_venta
				FROM detalle_venta dv
				LEFT JOIN venta v ON dv.idventa = v.idventa
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja'
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre
				ORDER BY v.idventa DESC
				LIMIT 1)";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesEstadoVentasCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT 
				  COUNT(DISTINCT CASE WHEN v.estado = 'anulado' THEN dv.idventa END) AS anulados,
				  COUNT(DISTINCT dv.idventa) AS emitidos,
				  COUNT(DISTINCT CASE WHEN v.estado NOT IN ('anulado') THEN dv.idventa END) AS validos
				FROM detalle_venta dv
				LEFT JOIN venta v ON dv.idventa = v.idventa
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja'
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND dv.fecha_hora >= cc.fecha_hora
				AND dv.fecha_hora <= cc.fecha_cierre;";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesMetodosPagoCajaCerrada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT
					mp.titulo AS metodo_pago,
					SUM(dvp.monto) AS monto_total,
					v.vuelto AS vuelto,
					v.idventa
				FROM detalle_venta_pagos dvp
				LEFT JOIN metodo_pago mp ON dvp.idmetodopago = mp.idmetodopago
				LEFT JOIN venta v ON dvp.idventa = v.idventa
				WHERE dvp.idventa IN (
					SELECT DISTINCT dv.idventa
					FROM detalle_venta dv
					LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
					WHERE cc.idcaja = '$idcaja'
					AND cc.idcaja_cerrada = '$idcaja_cerrada'
					AND dv.fecha_hora BETWEEN cc.fecha_hora AND cc.fecha_cierre
				)
				GROUP BY mp.titulo, v.vuelto, v.idventa
				ORDER BY dvp.iddetalle_venta_pago ASC;";

		return ejecutarConsulta($sql);
	}

	public function listarDetallesCajaAperturada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT titulo AS caja, monto AS monto, monto_total AS monto_total, DATE_FORMAT(fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha FROM cajas_cerradas WHERE idcaja_cerrada = '$idcaja_cerrada' AND idcaja = '$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function listarDetallesRetirosCajaAperurada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT c.titulo AS caja, r.monto AS monto_retiro, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha 
				FROM retiros r 
				LEFT JOIN cajas c ON r.idcaja = c.idcaja 
				LEFT JOIN cajas_cerradas cc ON c.idcaja = cc.idcaja_cerrada
				WHERE r.idcaja = '$idcaja_cerrada'
				AND cc.idcaja = '$idcaja'
				AND r.fecha_hora >= cc.fecha_hora
				AND r.fecha_hora <= cc.fecha_cierre";
		return ejecutarConsulta($sql);
	}

	public function listarDetallesGastosCajaAperurada($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT c.titulo AS caja, g.monto AS monto_gasto, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha 
				FROM gastos g 
				LEFT JOIN cajas c ON g.idcaja = c.idcaja 
				LEFT JOIN cajas_cerradas cc ON c.idcaja = cc.idcaja_cerrada
				WHERE g.idcaja = '$idcaja_cerrada'
				AND cc.idcaja = '$idcaja'
				AND g.fecha_hora >= cc.fecha_hora
				AND g.fecha_hora <= cc.fecha_cierre";
		return ejecutarConsulta($sql);
	}
}
