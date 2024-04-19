<?php
require "../config/Conexion.php";

class Gasto
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idcaja, $idlocal, $descripcion, $monto)
	{
		date_default_timezone_set("America/Lima");

		$sqlActualizarMonto = "UPDATE cajas SET monto = monto - '$monto' WHERE idcaja = '$idcaja'";
		ejecutarConsulta($sqlActualizarMonto);

		$sqlInsertarGasto = "INSERT INTO gastos (idusuario, idcaja, idlocal, descripcion, monto, fecha_hora)
                          VALUES ('$idusuario','$idcaja','$idlocal', '$descripcion', '$monto', SYSDATE())";

		return ejecutarConsulta($sqlInsertarGasto);
	}

	public function verificarMonto($idcaja, $monto)
	{
		$sql = "SELECT monto FROM cajas WHERE idcaja = '$idcaja'";
		$resultado = ejecutarConsulta($sql);

		if ($row = mysqli_fetch_assoc($resultado)) {
			if ($monto > $row['monto']) {
				// El monto es mayor al monto de la caja
				return true;
			}
		}
		// El monto es menor o igual al monto de la caja
		return false;
	}

	public function eliminar($idgasto, $idcaja)
	{
		$sqlSelect = "SELECT monto FROM gastos WHERE idgasto='$idgasto'";
		$resultado = ejecutarConsulta($sqlSelect);

		if ($resultado->num_rows > 0) {
			$row = $resultado->fetch_assoc();
			$montoGasto = $row['monto'];

			$sqlUpdate = "UPDATE cajas SET monto = monto + $montoGasto WHERE idcaja='$idcaja'";
			ejecutarConsulta($sqlUpdate);

			$sqlDelete = "DELETE FROM gastos WHERE idgasto='$idgasto'";
			return ejecutarConsulta($sqlDelete);
		} else {
			return false;
		}
	}

	public function mostrar($idgasto)
	{
		$sql = "SELECT r.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE idgasto='$idgasto'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT r.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  r.monto, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja ORDER BY r.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT r.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  r.monto, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE $param ORDER BY r.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT r.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  r.monto, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE r.idlocal = '$idlocalSession' ORDER BY r.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT r.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  r.monto, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE $param AND r.idlocal = '$idlocalSession' ORDER BY r.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idgasto, titulo FROM gastos ORDER BY idgasto DESC";
		return ejecutarConsulta($sql);
	}
}
