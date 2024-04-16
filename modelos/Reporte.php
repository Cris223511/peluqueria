<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Reporte
{
	/* ======================= REPORTE DE VENTAS ======================= */

	public function listarVentas($condiciones = "")
	{
		$sql = "SELECT DISTINCT
				  v.idventa,
				  DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  v.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  v.idcaja,
				  ca.titulo AS caja,
				  al.idlocal,
				  al.titulo AS local,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  v.vuelto,
				  v.impuesto,
				  v.total_venta,
				  v.estado
				FROM venta v
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas ca ON v.idcaja = ca.idcaja
				LEFT JOIN locales al ON v.idlocal = al.idlocal
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				LEFT JOIN detalle_venta_pagos dvp ON v.idventa = dvp.idventa
				WHERE $condiciones
				ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	public function listarVentasLocal($idlocal, $condiciones = "")
	{
		$sql = "SELECT DISTINCT
				  v.idventa,
				  DATE_FORMAT(v.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  v.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  v.idcaja,
				  ca.titulo AS caja,
				  al.idlocal,
				  al.titulo AS local,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  v.tipo_comprobante,
				  v.num_comprobante,
				  v.vuelto,
				  v.impuesto,
				  v.total_venta,
				  v.estado
				FROM venta v
				LEFT JOIN clientes c ON v.idcliente = c.idcliente
				LEFT JOIN cajas ca ON v.idcaja = ca.idcaja
				LEFT JOIN locales al ON v.idlocal = al.idlocal
				LEFT JOIN usuario u ON v.idusuario = u.idusuario
				LEFT JOIN detalle_venta_pagos dvp ON v.idventa = dvp.idventa
				WHERE v.idlocal = '$idlocal'
				AND $condiciones
				ORDER by v.idventa DESC";
		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE PROFORMAS ======================= */

	public function listarProformas($condiciones = "")
	{
		$sql = "SELECT DISTINCT
				  p.idproforma,
				  DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  p.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  p.idcaja,
				  ca.titulo AS caja,
				  al.idlocal,
				  al.titulo AS local,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  p.tipo_comprobante,
				  p.num_comprobante,
				  p.vuelto,
				  p.impuesto,
				  p.total_venta,
				  p.estado
				FROM proforma p
				LEFT JOIN clientes c ON p.idcliente = c.idcliente
				LEFT JOIN cajas ca ON p.idcaja = ca.idcaja
				LEFT JOIN locales al ON p.idlocal = al.idlocal
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN detalle_proforma_pagos dpp ON p.idproforma = dpp.idproforma
				WHERE $condiciones
				ORDER by p.idproforma DESC";
		return ejecutarConsulta($sql);
	}

	public function listarProformasLocal($idlocal, $condiciones = "")
	{
		$sql = "SELECT DISTINCT
				  p.idproforma,
				  DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  p.idcliente,
				  c.nombre AS cliente,
				  c.tipo_documento AS cliente_tipo_documento,
				  c.num_documento AS cliente_num_documento,
				  c.direccion AS cliente_direccion,
				  p.idcaja,
				  ca.titulo AS caja,
				  al.idlocal,
				  al.titulo AS local,
				  u.idusuario,
				  u.nombre AS usuario,
				  u.cargo AS cargo,
				  p.tipo_comprobante,
				  p.num_comprobante,
				  p.vuelto,
				  p.impuesto,
				  p.total_venta,
				  p.estado
				FROM proforma p
				LEFT JOIN clientes c ON p.idcliente = c.idcliente
				LEFT JOIN cajas ca ON p.idcaja = ca.idcaja
				LEFT JOIN locales al ON p.idlocal = al.idlocal
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN detalle_proforma_pagos dpp ON p.idproforma = dpp.idproforma
				WHERE p.idlocal = '$idlocal'
				AND $condiciones
				ORDER by p.idproforma DESC";
		return ejecutarConsulta($sql);
	}
}
