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
		$sql = "INSERT INTO cajas (idcaja_cerrada, idusuario, idlocal, titulo, monto, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
            VALUES ('0','$idusuario','$idlocal','$titulo', '$monto', '3', '$descripcion', SYSDATE(), '0000-00-00 00:00:00', 'aperturado','0','0')";
		return ejecutarConsulta($sql);
	}

	public function agregarCajaCerrada($idcaja, $idusuario)
	{
		$sql = "UPDATE cajas SET estado='cerrado' WHERE idcaja='$idcaja'";
		ejecutarConsulta($sql);

		date_default_timezone_set("America/Lima");
		$sql2 = "SELECT * FROM cajas WHERE idcaja = '$idcaja'";
		$resultado = ejecutarConsulta($sql2);

		if ($fila = mysqli_fetch_assoc($resultado)) {
			// Almacena los datos de la fila en variables
			$idcaja_cerrada = $fila['idcaja'];
			$idlocal = $fila['idlocal'];
			$titulo = $fila['titulo'];
			$monto = $fila['monto'];
			$descripcion = $fila['descripcion'];
			$fecha = $fila['fecha_hora'];

			// Inserta los datos en la nueva tabla
			$sql3 = "INSERT INTO cajas_cerradas (idcaja_cerrada, idusuario, idlocal, titulo, monto, contador, descripcion, fecha_hora, fecha_cierre, estado, vendido, eliminado)
						   VALUES ('$idcaja_cerrada', '$idusuario', '$idlocal', '$titulo', '$monto', '3', '$descripcion', '$fecha', SYSDATE(), 'cerrado', '0', '0')";
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
		$sql = "DELETE FROM cajas_cerradas WHERE idcaja = '$idcaja'";
		return ejecutarConsulta($sql);
	}

	public function mostrar($idcaja)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listar()
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorParametro($param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function mostrarCerradas($idcaja)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc, c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' AND idcaja='$idcaja'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarCerradas()
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorParametro($param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuario($idlocalSession)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE c.idlocal = '$idlocalSession' AND c.eliminado = '0' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarCerradasPorUsuarioParametro($idlocalSession, $param)
	{
		$sql = "SELECT c.idcaja, c.idlocal, c.idcaja_cerrada, u.idusuario, u.nombre as nombre, u.cargo as cargo, c.titulo, l.titulo as local, l.local_ruc as local_ruc,  c.monto, c.descripcion, DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha, DATE_FORMAT(c.fecha_cierre, '%d-%m-%Y %H:%i:%s') as fecha_cierre, c.contador, c.vendido, c.estado FROM cajas_cerradas c LEFT JOIN usuario u ON c.idusuario = u.idusuario LEFT JOIN locales l ON c.idlocal=l.idlocal WHERE $param AND c.eliminado = '0' AND c.idlocal = '$idlocalSession' ORDER BY c.idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarActivos()
	{
		$sql = "SELECT idcaja, titulo FROM cajas WHERE estado='aperturado' AND eliminado = '0' ORDER BY idcaja DESC";
		return ejecutarConsulta($sql);
	}

	public function listarDetallesProductosCaja($idcaja, $idcaja_cerrada)
	{
		$sql = "SELECT
				  a.nombre AS articulo,
				  s.titulo AS servicio,
				  a.codigo AS codigo,
				  s.codigo AS codigo_servicio,
				  dv.cantidad AS cantidad,
				  dv.precio_venta AS precio_venta,
				  dv.descuento AS descuento,
				  dv.impuesto AS impuesto
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dv.idservicio = s.idservicio
				LEFT JOIN cajas_cerradas cc ON dv.idcaja = cc.idcaja_cerrada
				WHERE cc.idcaja = '$idcaja' 
				AND cc.idcaja_cerrada = '$idcaja_cerrada'
				AND DATE(dv.fecha_hora) = DATE(cc.fecha_cierre)
				ORDER BY dv.iddetalle_venta ASC";

		return ejecutarConsulta($sql);
	}
}
