<?php
require "../config/Conexion.php";

class Trabajador
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $titulo, $descripcion)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO trabajadores (idusuario, titulo, descripcion, fecha_hora, estado, eliminado)
            VALUES ('$idusuario','$titulo', '$descripcion', SYSDATE(), 'activado','0')";
		return ejecutarConsulta($sql);
	}

	public function verificarNombreExiste($titulo)
	{
		$sql = "SELECT * FROM trabajadores WHERE titulo = '$titulo' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function verificarNombreEditarExiste($titulo, $idtrabajador)
	{
		$sql = "SELECT * FROM trabajadores WHERE titulo = '$titulo' AND idtrabajador != '$idtrabajador' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El titulo ya existe en la tabla
			return true;
		}
		// El titulo no existe en la tabla
		return false;
	}

	public function editar($idtrabajador, $titulo, $descripcion)
	{
		$sql = "UPDATE trabajadores SET titulo='$titulo',descripcion='$descripcion' WHERE idtrabajador='$idtrabajador'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idtrabajador)
	{
		$sql = "UPDATE trabajadores SET estado='desactivado' WHERE idtrabajador='$idtrabajador'";
		return ejecutarConsulta($sql);
	}

	public function activar($idtrabajador)
	{
		$sql = "UPDATE trabajadores SET estado='activado' WHERE idtrabajador='$idtrabajador'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idtrabajador)
	{
		$sql = "UPDATE trabajadores SET eliminado = '1' WHERE idtrabajador='$idtrabajador'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idtrabajador)
	{
		$sql = "SELECT * FROM trabajadores WHERE idtrabajador='$idtrabajador'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarTrabajadores()
	{
		$sql = "SELECT nombre, tipo_documento, num_documento, direccion, telefono, email, 
				CONCAT(DAY(fecha_nac), ' de ', 
				CASE MONTH(fecha_nac)
					WHEN 1 THEN 'Enero'
					WHEN 2 THEN 'Febrero'
					WHEN 3 THEN 'Marzo'
					WHEN 4 THEN 'Abril'
					WHEN 5 THEN 'Mayo'
					WHEN 6 THEN 'Junio'
					WHEN 7 THEN 'Julio'
					WHEN 8 THEN 'Agosto'
					WHEN 9 THEN 'Septiembre'
					WHEN 10 THEN 'Octubre'
					WHEN 11 THEN 'Noviembre'
					WHEN 12 THEN 'Diciembre'
				END, ' del ', YEAR(fecha_nac)) as fecha,
				estado FROM trabajadores WHERE eliminado = '0' ORDER BY idtrabajador DESC";
		return ejecutarConsulta($sql);
	}

	public function listarTrabajadoresPorUsuario($idusuario)
	{
		$sql = "SELECT nombre, tipo_documento, num_documento, direccion, telefono, email, 
				CONCAT(DAY(fecha_nac), ' de ', 
				CASE MONTH(fecha_nac)
					WHEN 1 THEN 'Enero'
					WHEN 2 THEN 'Febrero'
					WHEN 3 THEN 'Marzo'
					WHEN 4 THEN 'Abril'
					WHEN 5 THEN 'Mayo'
					WHEN 6 THEN 'Junio'
					WHEN 7 THEN 'Julio'
					WHEN 8 THEN 'Agosto'
					WHEN 9 THEN 'Septiembre'
					WHEN 10 THEN 'Octubre'
					WHEN 11 THEN 'Noviembre'
					WHEN 12 THEN 'Diciembre'
				END, ' del ', YEAR(fecha_nac)) as fecha,
				estado FROM trabajadores WHERE idusuario = '$idusuario' AND eliminado = '0' ORDER BY idtrabajador DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idusuario)
	{
		$sql = "SELECT m.idtrabajador, u.idusuario, u.nombre as nombre, u.cargo as cargo, m.titulo, m.descripcion, DATE_FORMAT(m.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, m.estado FROM trabajadores m LEFT JOIN usuario u ON m.idusuario = u.idusuario WHERE m.idusuario = '$idusuario' AND m.eliminado = '0' ORDER BY m.idtrabajador DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idtrabajador, titulo FROM trabajadores WHERE estado='activado' AND eliminado = '0' ORDER BY idtrabajador DESC";
		return ejecutarConsulta($sql);
	}
}
