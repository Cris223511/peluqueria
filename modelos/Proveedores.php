<?php
require "../config/Conexion.php";

class Proveedor
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO proveedores (idusuario, nombre, tipo_documento, num_documento, direccion, descripcion, telefono, email, fecha_hora, estado, eliminado)
            VALUES ('$idusuario','$nombre','$tipo_documento','$num_documento','$direccion','$descripcion','$telefono', '$email', SYSDATE(),'activado','0')";
		return ejecutarConsulta_retornarID($sql);
	}

	public function editar($idproveedor, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		$sql = "UPDATE proveedores SET nombre='$nombre',tipo_documento='$tipo_documento',num_documento='$num_documento',direccion='$direccion',descripcion='$descripcion',telefono='$telefono',email='$email' WHERE idproveedor='$idproveedor'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idproveedor)
	{
		$sql = "UPDATE proveedores SET estado='desactivado' WHERE idproveedor='$idproveedor'";
		return ejecutarConsulta($sql);
	}

	public function activar($idproveedor)
	{
		$sql = "UPDATE proveedores SET estado='activado' WHERE idproveedor='$idproveedor'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idproveedor)
	{
		$sql = "UPDATE proveedores SET eliminado = '1' WHERE idproveedor='$idproveedor'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idproveedor)
	{
		$sql = "SELECT * FROM proveedores WHERE idproveedor='$idproveedor'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarProveedores()
	{
		$sql = "SELECT p.idproveedor, p.nombre, p.tipo_documento, p.num_documento, p.direccion, p.descripcion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, p.estado
				FROM proveedores p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				WHERE p.eliminado = '0' AND p.idproveedor <> '0' ORDER BY p.idproveedor DESC";
		return ejecutarConsulta($sql);
	}

	/* ======================= REPORTE DE COMPRAS POR PROVEEDOR ======================= */

	public function listarComprasProveedor($idproveedor)
	{
		$sql = "SELECT
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
				WHERE c.idproveedor = '$idproveedor'
				ORDER by c.idcompra DESC";

		return ejecutarConsulta($sql);
	}

	public function listarComprasProveedorhaLocal($idproveedor, $idlocal)
	{
		$sql = "SELECT
				  c.idcompra,
				  DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  c.idproveedor,
				  p.nombre AS proveedor,
				  p.tipo_documento AS proveedor_tipo_documento,
				  p.num_documento AS proveedor_num_documento,
				  p.direccion AS proveedor_direccion,
				  c.idcaja,
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
				WHERE c.idproveedor = '$idproveedor'
				AND c.idlocal = '$idlocal'
				ORDER by c.idcompra DESC";

		return ejecutarConsulta($sql);
	}
}
