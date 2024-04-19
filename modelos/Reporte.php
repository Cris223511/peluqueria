<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Reporte
{
	/* ======================= REPORTE DE COMPRAS ======================= */

	public function listarCompras($condiciones = "")
	{
		$sql = "SELECT DISTINCT
					  c.idcompra,
					  DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
					  c.idproveedor,
					  p.nombre AS proveedor,
					  p.tipo_documento AS proveedor_tipo_documento,
					  p.num_documento AS proveedor_num_documento,
					  p.direccion AS proveedor_direccion,
					  al.idlocal,
					  al.titulo AS local,
					  u.idusuario,
					  u.nombre AS usuario,
					  u.cargo AS cargo,
					  c.tipo_comprobante,
					  c.num_comprobante,
					  c.vuelto,
					  c.impuesto,
					  c.total_compra,
					  c.estado
					FROM compra c
					LEFT JOIN proveedores p ON c.idproveedor = p.idproveedor
					LEFT JOIN locales al ON c.idlocal = al.idlocal
					LEFT JOIN usuario u ON c.idusuario = u.idusuario
					LEFT JOIN detalle_compra_pagos dvp ON c.idcompra = dvp.idcompra
					WHERE $condiciones
					ORDER by c.idcompra DESC";

		return ejecutarConsulta($sql);
	}

	public function listarComprasLocal($idlocal, $condiciones = "")
	{
		$sql = "SELECT DISTINCT
					  c.idcompra,
					  DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
					  c.idproveedor,
					  p.nombre AS proveedor,
					  p.tipo_documento AS proveedor_tipo_documento,
					  p.num_documento AS proveedor_num_documento,
					  p.direccion AS proveedor_direccion,
					  al.idlocal,
					  al.titulo AS local,
					  u.idusuario,
					  u.nombre AS usuario,
					  u.cargo AS cargo,
					  c.tipo_comprobante,
					  c.num_comprobante,
					  c.vuelto,
					  c.impuesto,
					  c.total_compra,
					  c.estado
					FROM compra c
					LEFT JOIN proveedores p ON c.idproveedor = p.idproveedor
					LEFT JOIN locales al ON c.idlocal = al.idlocal
					LEFT JOIN usuario u ON c.idusuario = u.idusuario
					LEFT JOIN detalle_compra_pagos dvp ON c.idcompra = dvp.idcompra
					WHERE c.idlocal = '$idlocal'
					AND $condiciones
					ORDER by c.idcompra DESC";

		return ejecutarConsulta($sql);
	}

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

	/* ======================= REPORTE DE ARTICULOS MÁS VENDIDOS ======================= */

	public function listarArticulosMasVendidos($condiciones = "")
	{
		$sql = "SELECT
				  a.idarticulo,
				  a.idusuario,
				  a.idmarca,
				  a.idcategoria,
				  COUNT(dv.idarticulo) as cantidad,
				  u.nombre as usuario,
				  u.cargo as cargo,
				  u.cargo,
				  c.titulo as categoria,
				  al.titulo as local,
				  m.titulo as marca,
				  a.codigo,
				  a.codigo_producto,
				  a.nombre,
				  a.stock,
				  a.stock_minimo,
				  a.descripcion,
				  a.imagen,
				  a.precio_compra,
				  a.precio_venta,
				  a.estado
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
				LEFT JOIN locales al ON a.idlocal = al.idlocal
				LEFT JOIN usuario u ON a.idusuario = u.idusuario
				LEFT JOIN marcas m ON a.idmarca = m.idmarca
				WHERE $condiciones
				GROUP BY dv.idarticulo
				ORDER BY cantidad DESC";

		return ejecutarConsulta($sql);
	}

	public function listarArticulosMasVendidosLocal($idlocal, $condiciones = "")
	{
		$sql = "SELECT
				  a.idarticulo,
				  a.idusuario,
				  a.idmarca,
				  a.idcategoria,
				  COUNT(dv.idarticulo) as cantidad,
				  u.nombre as usuario,
				  u.cargo as cargo,
				  u.cargo,
				  c.titulo as categoria,
				  al.titulo as local,
				  m.titulo as marca,
				  a.codigo,
				  a.codigo_producto,
				  a.nombre,
				  a.stock,
				  a.stock_minimo,
				  a.descripcion,
				  a.imagen,
				  a.precio_compra,
				  a.precio_venta,
				  a.estado
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
				LEFT JOIN locales al ON a.idlocal = al.idlocal
				LEFT JOIN usuario u ON a.idusuario = u.idusuario
				LEFT JOIN marcas m ON a.idmarca = m.idmarca
				WHERE a.idlocal = '$idlocal'
				AND $condiciones
				GROUP BY dv.idarticulo
				ORDER BY cantidad DESC";

		return ejecutarConsulta($sql);
	}
}
