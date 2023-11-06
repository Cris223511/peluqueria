<?php
require "../config/Conexion.php";

class Caja
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $titulo, $descripcion)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO cajas (idusuario, titulo, descripcion, fecha_hora, estado, eliminado)
            VALUES ('$idusuario','$titulo', '$descripcion', SYSDATE(), 'activado','0')";
		return ejecutarConsulta($sql);
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

	public function editar($idcaja, $titulo, $descripcion)
	{
		$sql = "UPDATE cajas SET titulo='$titulo',descripcion='$descripcion' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idcaja)
	{
		$sql = "UPDATE cajas SET estado='desactivado' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function activar($idcaja)
	{
		$sql = "UPDATE cajas SET estado='activado' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idcaja)
	{
		$sql = "UPDATE cajas SET eliminado = '1' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcaja)
	{
		$sql = "SELECT * FROM cajas WHERE idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT m.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, m.titulo, m.descripcion, DATE_FORMAT(m.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, m.estado FROM cajas m LEFT JOIN usuario u ON m.idusuario = u.idusuario WHERE m.eliminado = '0' ORDER BY m.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idusuario)
	{
		$sql = "SELECT m.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, m.titulo, m.descripcion, DATE_FORMAT(m.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, m.estado FROM cajas m LEFT JOIN usuario u ON m.idusuario = u.idusuario WHERE m.idusuario = '$idusuario' AND m.eliminado = '0' ORDER BY m.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idcaja, titulo FROM cajas WHERE estado='activado' AND eliminado = '0' ORDER BY idcaja DESC";
		return ejecutarConsulta($sql);
	}
}
