<?php
require "../config/Conexion.php";

class LocalExterno
{
	public function __construct() {}

	public function agregar($idusuario, $titulo, $empresa, $local_ruc, $descripcion, $imagen)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO locales (idusuario, titulo, empresa, local_ruc, descripcion, imagen, fecha_hora, estado, eliminado)
            VALUES ('$idusuario','$titulo','$empresa','$local_ruc','$descripcion', '$imagen', SYSDATE(), 'activado', '0')";
		return ejecutarConsulta($sql);
	}

	public function verificarNombreExiste($titulo)
	{
		$sql = "SELECT * FROM locales WHERE titulo = '$titulo' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function verificarNombreEditarExiste($titulo, $idlocal)
	{
		$sql = "SELECT * FROM locales WHERE titulo = '$titulo' AND idlocal != '$idlocal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function editar($idlocal, $titulo, $empresa, $local_ruc, $descripcion, $imagen)
	{
		$sql = "UPDATE locales SET titulo='$titulo',empresa='$empresa',local_ruc='$local_ruc',descripcion='$descripcion',imagen='$imagen' WHERE idlocal='$idlocal'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idlocal)
	{
		$sql = "UPDATE locales SET estado='desactivado' WHERE idlocal='$idlocal'";
		return ejecutarConsulta($sql);
	}

	public function activar($idlocal)
	{
		$sql = "UPDATE locales SET estado='activado' WHERE idlocal='$idlocal'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idlocal)
	{
		$sql = "SELECT * FROM locales WHERE idlocal='$idlocal'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function eliminar($idlocal)
	{
		$sql = "UPDATE locales SET eliminado = '1' WHERE idlocal='$idlocal'";
		return ejecutarConsulta($sql);
	}

	// todos los locales

	public function listar($idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND l.eliminado = '0' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorFecha($idlocal, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND l.eliminado = '0' AND DATE(l.fecha_hora) >= '$fecha_inicio' AND DATE(l.fecha_hora) <= '$fecha_fin' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND u.idlocal = '$idlocal' AND l.eliminado = '0' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioFecha($idlocal, $fecha_inicio, $fecha_fin)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND u.idlocal = '$idlocal' AND l.eliminado = '0' AND DATE(l.fecha_hora) >= '$fecha_inicio' AND DATE(l.fecha_hora) <= '$fecha_fin' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos($idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND l.estado='activado' AND l.eliminado = '0' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivosASC($idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idlocal <> '$idlocal' AND l.estado='activado' AND l.eliminado = '0' ORDER BY l.idlocal ASC";
		return ejecutarConsulta($sql);
	}

	public function listarUsuariosPorLocal($idlocal)
	{
		$sql = "SELECT
					u.idusuario,
					u.idlocal,
					u.nombre,
					l.titulo as local,
					l.empresa,
					l.local_ruc as local_ruc,
					u.tipo_documento,
					u.num_documento,
					u.direccion,
					u.telefono,
					u.email,
					u.cargo,
					u.login,
					u.clave,
					u.imagen,
					u.estado
				FROM usuario u
				LEFT JOIN locales l ON u.idlocal = l.idlocal
				WHERE u.idlocal = '$idlocal' AND u.eliminado = '0' ORDER BY u.idusuario DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioActivos($idusuario, $idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idusuario = '$idusuario' AND l.idlocal <> '$idlocal' AND l.estado='activado' AND l.eliminado = '0' ORDER BY l.idlocal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioActivosASC($idusuario, $idlocal)
	{
		$sql = "SELECT l.idlocal, u.idusuario, u.nombre as nombre, u.cargo as cargo, l.titulo, l.empresa, l.local_ruc, l.descripcion, l.imagen, DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, l.estado FROM locales l LEFT JOIN usuario u ON l.idusuario = u.idusuario WHERE l.idusuario = '$idusuario' AND l.idlocal <> '$idlocal' AND l.estado='activado' AND l.eliminado = '0' ORDER BY l.idlocal ASC";
		return ejecutarConsulta($sql);
	}
}
