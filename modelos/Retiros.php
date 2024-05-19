<?php
require "../config/Conexion.php";

class Retiro
{
	public function __construct()
	{
	}

	public function agregar($idusuario, $idcaja, $idlocal, $descripcion, $monto, $monto_caja)
	{
		date_default_timezone_set("America/Lima");

		$sqlActualizarMonto = "UPDATE cajas SET monto_total = monto_total - '$monto' WHERE idcaja = '$idcaja'";
		ejecutarConsulta($sqlActualizarMonto);

		$monto_total = $monto_caja - $monto;

		$sqlInsertarRetiro = "INSERT INTO retiros (idusuario, idcaja, idlocal, descripcion, monto_caja, monto, monto_total, fecha_hora)
                          VALUES ('$idusuario','$idcaja','$idlocal', '$descripcion', '$monto_caja', '$monto', '$monto_total', SYSDATE())";

		return ejecutarConsulta($sqlInsertarRetiro);
	}

	public function verificarMonto($idcaja, $monto)
	{
		$sql = "SELECT monto_total FROM cajas WHERE idcaja = '$idcaja'";
		$resultado = ejecutarConsulta($sql);

		if ($row = mysqli_fetch_assoc($resultado)) {
			if ($monto > $row['monto_total']) {
				// El monto_total es mayor al monto_total de la caja
				return true;
			}
		}
		// El monto_total es menor o igual al monto_total de la caja
		return false;
	}

	public function eliminar($idretiro, $idcaja)
	{
		$sqlSelect = "SELECT monto FROM retiros WHERE idretiro='$idretiro'";
		$resultado = ejecutarConsulta($sqlSelect);

		if ($resultado->num_rows > 0) {
			$row = $resultado->fetch_assoc();
			$montoRetiro = $row['monto'];

			$sqlUpdate = "UPDATE cajas SET monto_total = monto_total + $montoRetiro WHERE idcaja='$idcaja'";
			ejecutarConsulta($sqlUpdate);

			$sqlDelete = "DELETE FROM retiros WHERE idretiro='$idretiro'";
			return ejecutarConsulta($sqlDelete);
		} else {
			return false;
		}
	}

	public function mostrar($idretiro)
	{
		$sql = "SELECT r.idretiro, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.monto_caja, r.monto_total, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM retiros r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE idretiro='$idretiro'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT r.idretiro, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.monto_caja, r.monto_total, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM retiros r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja ORDER BY r.idretiro DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT r.idretiro, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.monto_caja, r.monto_total, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM retiros r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE $param ORDER BY r.idretiro DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT r.idretiro, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.monto_caja, r.monto_total, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM retiros r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE r.idlocal = '$idlocalSession' ORDER BY r.idretiro DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT r.idretiro, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, r.monto, r.monto_caja, r.monto_total, r.descripcion, DATE_FORMAT(r.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM retiros r LEFT JOIN usuario u ON r.idusuario = u.idusuario LEFT JOIN locales l ON r.idlocal=l.idlocal LEFT JOIN cajas c ON r.idcaja = c.idcaja WHERE $param AND r.idlocal = '$idlocalSession' ORDER BY r.idretiro DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idretiro, titulo FROM retiros ORDER BY idretiro DESC";
		return ejecutarConsulta($sql);
	}
}
