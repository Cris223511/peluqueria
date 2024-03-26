<?php
require "../config/Conexion.php";

class Caja
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idlocal, $titulo, $monto, $descripcion)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO cajas (idusuario, idlocal, titulo, monto, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
            VALUES ('$idusuario','$idlocal','$titulo', '$monto', '3', '$descripcion', SYSDATE(), '0000-00-00 00:00:00', 'aperturado','0','0')";
		return ejecutarConsulta($sql);
	}

	public function agregarCajaCerrada($idcaja)
	{
		$sql = "UPDATE cajas SET estado='cerrado' WHERE idcaja='$idcaja'";
		ejecutarConsulta($sql);

		date_default_timezone_set("America/Lima");
		$sql2 = "SELECT * FROM cajas WHERE idcaja = '$idcaja'";
		$resultado = ejecutarConsulta($sql2);

		if ($fila = mysqli_fetch_assoc($resultado)) {
			// Almacena los datos de la fila en variables
			$idusuario = $fila['idusuario'];
			$idlocal = $fila['idlocal'];
			$titulo = $fila['titulo'];
			$monto = $fila['monto'];
			$descripcion = $fila['descripcion'];
			$fecha = $fila['fecha_hora'];

			// Inserta los datos en la nueva tabla
			$sql3 = "INSERT INTO cajas_cerradas (idusuario, idlocal, titulo, monto, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
						   VALUES ('$idusuario', '$idlocal', '$titulo', '$monto', '3', '$descripcion', '$fecha', SYSDATE(), 'cerrado', '0', '0')";
			return ejecutarConsulta($sql3);
		}
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

	public function validarCaja($idlocal)
	{
		$sql = "SELECT * FROM cajas WHERE idlocal = '$idlocal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// Hay una caja en el local
			return true;
		}
		// No existe una caja en el local
		return false;
	}

	public function editar($idcaja, $idusuario, $idlocal, $titulo, $monto, $descripcion)
	{
		$sql = "UPDATE cajas SET idusuario='$idusuario',idlocal='$idlocal',titulo='$titulo',monto='$monto',descripcion='$descripcion',contador=contador-1 WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function editarSinMonto($idcaja, $idusuario, $idlocal, $titulo, $descripcion)
	{
		$sql = "UPDATE cajas SET idusuario='$idusuario',idlocal='$idlocal',titulo='$titulo',descripcion='$descripcion' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function aperturar($idcaja)
	{
		$sql = "UPDATE cajas SET estado='aperturado', fecha_hora=SYSDATE(), monto='0.00', contador='3', fecha_cierre='0000-00-00 00:00:00' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idcaja)
	{
		$sql = "UPDATE cajas SET eliminado = '1' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function eliminarCajaCerrada($idcaja)
	{
		$sql = "UPDATE cajas_cerradas SET eliminado = '1' WHERE idcaja='$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcaja)
	{
		$sql = "SELECT * FROM cajas WHERE idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerrados()
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND c.estado = 'cerrado' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradosPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' AND c.estado = 'cerrado' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradas()
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorParametro($param)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasCerrados()
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND c.estado = 'cerrado' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasCerradosPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' AND c.estado = 'cerrado' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}


	public function listarActivos()
	{
		$sql = "SELECT idcaja, titulo FROM cajas WHERE estado='aperturado' AND eliminado = '0' ORDER BY idcaja DESC";
		return ejecutarConsulta($sql);
	}
}
