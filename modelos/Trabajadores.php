<?php
require "../config/Conexion.php";

class Trabajador
{
	public function __construct()
	{
	}

	public function listarUsuariosPorLocal($idlocal)
	{
		$sql = "SELECT
					u.idusuario,
					u.idlocal,
					u.nombre,
					l.titulo as local,
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
}
