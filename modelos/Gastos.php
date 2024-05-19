<?php
require "../config/Conexion.php";

class Gasto
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

		$sqlInsertarGasto = "INSERT INTO gastos (idusuario, idcaja, idlocal, descripcion, monto_caja, monto, monto_total, fecha_hora)
                          VALUES ('$idusuario','$idcaja','$idlocal', '$descripcion', '$monto_caja', '$monto', '$monto_total', SYSDATE())";

		return ejecutarConsulta($sqlInsertarGasto);
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

	public function eliminar($idgasto, $idcaja)
	{
		$sqlSelect = "SELECT monto FROM gastos WHERE idgasto='$idgasto'";
		$resultado = ejecutarConsulta($sqlSelect);

		if ($resultado->num_rows > 0) {
			$row = $resultado->fetch_assoc();
			$montoGasto = $row['monto'];

			$sqlUpdate = "UPDATE cajas SET monto_total = monto_total + $montoGasto WHERE idcaja='$idcaja'";
			ejecutarConsulta($sqlUpdate);

			$sqlDelete = "DELETE FROM gastos WHERE idgasto='$idgasto'";
			return ejecutarConsulta($sqlDelete);
		} else {
			return false;
		}
	}

	public function mostrar($idgasto)
	{
		$sql = "SELECT g.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja, g.monto, g.monto_caja, g.monto_total, g.descripcion, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos g LEFT JOIN usuario u ON g.idusuario = u.idusuario LEFT JOIN locales l ON g.idlocal=l.idlocal LEFT JOIN cajas c ON g.idcaja = c.idcaja WHERE idgasto='$idgasto'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT g.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  g.monto, g.monto_caja, g.monto_total, g.descripcion, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos g LEFT JOIN usuario u ON g.idusuario = u.idusuario LEFT JOIN locales l ON g.idlocal=l.idlocal LEFT JOIN cajas c ON g.idcaja = c.idcaja ORDER BY g.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT g.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  g.monto, g.monto_caja, g.monto_total, g.descripcion, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos g LEFT JOIN usuario u ON g.idusuario = u.idusuario LEFT JOIN locales l ON g.idlocal=l.idlocal LEFT JOIN cajas c ON g.idcaja = c.idcaja WHERE $param ORDER BY g.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT g.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  g.monto, g.monto_caja, g.monto_total, g.descripcion, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos g LEFT JOIN usuario u ON g.idusuario = u.idusuario LEFT JOIN locales l ON g.idlocal=l.idlocal LEFT JOIN cajas c ON g.idcaja = c.idcaja WHERE g.idlocal = '$idlocalSession' ORDER BY g.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT g.idgasto, u.idusuario, c.idcaja, l.idlocal, c.titulo as caja, u.nombre as nombre, u.cargo as cargo, l.titulo as local, l.local_ruc as local_ruc, c.titulo as caja,  g.monto, g.monto_caja, g.monto_total, g.descripcion, DATE_FORMAT(g.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha FROM gastos g LEFT JOIN usuario u ON g.idusuario = u.idusuario LEFT JOIN locales l ON g.idlocal=l.idlocal LEFT JOIN cajas c ON g.idcaja = c.idcaja WHERE $param AND g.idlocal = '$idlocalSession' ORDER BY g.idgasto DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idgasto, titulo FROM gastos ORDER BY idgasto DESC";
		return ejecutarConsulta($sql);
	}
}
