<?php
require "../config/Conexion.php";

class Cliente
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email)
	{
		date_default_timezone_set("America/Lima");
		$sql = "INSERT INTO clientes (idusuario, idlocal, nombre, tipo_documento, num_documento, direccion, telefono, email, fecha_hora, estado, eliminado)
            VALUES ('$idusuario','$idlocal','$nombre','$tipo_documento','$num_documento','$direccion','$telefono', '$email', SYSDATE(),'activado','0')";
		return ejecutarConsulta($sql);
	}

	public function verificarDniExiste($num_documento)
	{
		$sql = "SELECT * FROM clientes WHERE num_documento = '$num_documento' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function verificarDniEditarExiste($num_documento, $idcliente)
	{
		$sql = "SELECT * FROM clientes WHERE num_documento = '$num_documento' AND idcliente != '$idcliente' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		if (mysqli_num_rows($resultado) > 0) {
			// El número documento ya existe en la tabla
			return true;
		}
		// El número documento no existe en la tabla
		return false;
	}

	public function editar($idcliente, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email)
	{
		$sql = "UPDATE clientes SET idlocal='$idlocal',nombre='$nombre',tipo_documento='$tipo_documento',num_documento='$num_documento',direccion='$direccion',telefono='$telefono',email='$email' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function desactivar($idcliente)
	{
		$sql = "UPDATE clientes SET estado='desactivado' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function activar($idcliente)
	{
		$sql = "UPDATE clientes SET estado='activado' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function eliminar($idcliente)
	{
		$sql = "UPDATE clientes SET eliminado = '1' WHERE idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcliente)
	{
		$sql = "SELECT * FROM clientes WHERE idcliente='$idcliente'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarClientes()
	{
		$sql = "SELECT c.idcliente, c.nombre, l.titulo AS local, c.tipo_documento, c.num_documento, c.direccion, c.telefono, c.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, c.estado
				FROM clientes c
				LEFT JOIN usuario u ON c.idusuario = u.idusuario
				LEFT JOIN locales l ON c.idlocal = l.idlocal
				WHERE c.eliminado = '0' ORDER BY c.idcliente DESC";
		return ejecutarConsulta($sql);
	}

	public function listarClientesPorUsuario($idlocal_session)
	{
		$sql = "SELECT c.idcliente, c.nombre, l.titulo AS local, c.tipo_documento, c.num_documento, c.direccion, c.telefono, c.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, c.estado
				FROM clientes c
				LEFT JOIN usuario u ON c.idusuario = u.idusuario
				LEFT JOIN locales l ON c.idlocal = l.idlocal
				WHERE c.idlocal = '$idlocal_session' AND c.eliminado = '0' ORDER BY c.idcliente DESC";
		return ejecutarConsulta($sql);
	}
}
