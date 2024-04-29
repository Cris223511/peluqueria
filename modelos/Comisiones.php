<?php
require "../config/Conexion.php";

class Comision
{
	public function __construct()
	{
	}

	public function listarPersonales()
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, p.estado, p.total_comision, p.comisionado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.eliminado = '0' ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPersonalesPorUsuario($idlocal_session)
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, p.estado, p.total_comision, p.comisionado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.idlocal = '$idlocal_session' AND p.eliminado = '0' ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}

	public function mostrarComisionesPersonal($idpersonal)
	{
		$sql = "";
		return ejecutarConsulta($sql);
	}
}
