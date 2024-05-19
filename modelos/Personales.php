<?php
require "../config/Conexion.php";

class Personal
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idlocal, $nombre, $cargo, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO personales (idusuario, idlocal, nombre, cargo, tipo_documento, num_documento, direccion, descripcion, telefono, email, fecha_hora, fecha_hora_comision, estado, eliminado)
            VALUES ('$idusuario','$idlocal','$nombre','$cargo','$tipo_documento','$num_documento','$direccion','$descripcion','$telefono', '$email', SYSDATE(), '0000-00-00 00:00:00', 'activado','0')";
		return ejecutarConsulta($sql);
	}

	public function verificarDniExiste($num_documento)
	{
		$sql = "SELECT * FROM personales WHERE num_documento = '$num_documento' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function verificarDniEditarExiste($num_documento, $idpersonal)
	{
		$sql = "SELECT * FROM personales WHERE num_documento = '$num_documento' AND idpersonal != '$idpersonal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function editar($idpersonal, $idlocal, $nombre, $cargo, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email)
	{
		$sql = "UPDATE personales SET idlocal='$idlocal',nombre='$nombre',cargo='$cargo',tipo_documento='$tipo_documento',num_documento='$num_documento',direccion='$direccion',descripcion='$descripcion',telefono='$telefono',email='$email' WHERE idpersonal='$idpersonal'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idpersonal)
	{
		$sql = "UPDATE personales SET estado='desactivado' WHERE idpersonal='$idpersonal'";
		return ejecutarConsulta($sql);
	}

	public function activar($idpersonal)
	{
		$sql = "UPDATE personales SET estado='activado' WHERE idpersonal='$idpersonal'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idpersonal)
	{
		$sql = "UPDATE personales SET eliminado = '1' WHERE idpersonal='$idpersonal'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idpersonal)
	{
		$sql = "SELECT * FROM personales WHERE idpersonal='$idpersonal'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarPersonales()
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.descripcion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, p.estado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.eliminado = '0' ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPersonalesPorUsuario($idlocal_session)
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.descripcion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, p.estado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.idlocal = '$idlocal_session' AND p.eliminado = '0' ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}
}
