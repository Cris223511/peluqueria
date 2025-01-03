<?php
require "../config/Conexion.php";

class LocalDisponible
{
	public function __construct() {}

	public function agregar($titulo, $empresa, $local_ruc, $descripcion, $imagen)
	{
		if (empty($imagen))
			$imagen = "default.jpg";

		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO locales (idusuario, titulo, empresa, local_ruc, descripcion, imagen, fecha_hora, estado, eliminado)
            VALUES (0,'$titulo','$empresa','$local_ruc','$descripcion','$imagen', SYSDATE(), 'activado', '0')";
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

	public function asignar($idlocal_asignar, $idusuario_asignar)
	{
		$sql = "UPDATE locales SET idusuario='$idusuario_asignar' WHERE idlocal='$idlocal_asignar'";
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

	public function eliminar($idlocal)
	{
		$sql = "UPDATE locales SET eliminado = '1' WHERE idlocal='$idlocal'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idlocal)
	{
		$sql = "SELECT * FROM locales WHERE idlocal='$idlocal'";
		return ejecutarConsultaSimpleFila($sql);
	}

	// locales disponibles

	public function listarLocalesDisponibles()
	{
		$sql = "SELECT 
				  l.idlocal,
				  u.idusuario,
				  u.nombre AS nombre,
				  u.cargo AS cargo,
				  l.titulo,
				  l.empresa,
				  l.local_ruc,
				  l.descripcion,
				  l.imagen,
				  DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  l.estado
				FROM locales l
				LEFT JOIN usuario u ON l.idlocal = u.idlocal
				WHERE u.idusuario IS NULL AND l.eliminado = '0'
				ORDER BY l.idlocal DESC";

		return ejecutarConsulta($sql);
	}

	public function listarLocalesDisponiblesActivos()
	{
		$sql = "SELECT 
				  l.idlocal,
				  u.idusuario,
				  u.nombre AS nombre,
				  u.cargo AS cargo,
				  l.titulo,
				  l.empresa,
				  l.local_ruc,
				  l.descripcion,
				  l.imagen,
				  DATE_FORMAT(l.fecha_hora, '%d-%m-%Y %H:%i:%s') AS fecha,
				  l.estado
				FROM locales l
				LEFT JOIN usuario u ON l.idlocal = u.idlocal
				WHERE u.idusuario IS NULL AND l.eliminado = '0' AND l.estado = 'activado'
				ORDER BY l.idlocal DESC";

		return ejecutarConsulta($sql);
	}
}
