<?php
require "../config/Conexion.php";

class Comision
{
	public function __construct()
	{
	}

	public function insertar($idpersonalUniq, $detalles, $idpersonal, $comision, $tipo)
	{
		$sw = true;
		$totalComision = 0;

		// Eliminar todas las comisiones asociadas al idpersonalUniq
		$sql_eliminar = "DELETE FROM comisiones WHERE idpersonal = '$idpersonalUniq'";
		ejecutarConsulta($sql_eliminar);

		$sql_actualizar = "UPDATE personales SET fecha_hora_comision = SYSDATE() WHERE idpersonal = '$idpersonalUniq'";
		ejecutarConsulta($sql_actualizar);

		// Convertir $detalles de JSON a array
		$detalles = json_decode($detalles, true);

		// Insertar las nuevas comisiones en la tabla comisiones
		foreach ($detalles as $i => $detalle) {
			// Obtener los datos de detalle
			$esArticulo = strpos($detalle, '_producto') !== false;
			$esServicio = strpos($detalle, '_servicio') !== false;
			$id = str_replace(['_producto', '_servicio'], '', $detalle);
			$idPersonalItem = $idpersonal[$i];
			$comisionItem = $comision[$i];
			$tipoItem = $tipo[$i];
			$idArticulo = $esArticulo ? $id : 0;
			$idServicio = $esServicio ? $id : 0;

			// Insertar el detalle en la tabla comisiones
			$sql_detalle = "INSERT INTO comisiones (idpersonal, idarticulo, idservicio, comision, tipo, fecha_hora) VALUES ('$idPersonalItem', '$idArticulo', '$idServicio', '$comisionItem', '$tipoItem', SYSDATE())";
			ejecutarConsulta($sql_detalle) or $sw = false;

			// Actualizar la suma de comisiones
			$totalComision += $comisionItem;
		}

		// Actualizar la columna total_comision del primer idpersonal
		$sql_actualizar = "UPDATE personales SET total_comision = '$totalComision', comisionado = '1' WHERE idpersonal = '$idpersonalUniq'";
		ejecutarConsulta($sql_actualizar) or $sw = false;

		return $sw;
	}


	public function listarPersonales()
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.idlocal, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora_comision, '%d-%m-%Y %H:%i:%s') as fecha, p.estado, p.total_comision, p.comisionado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.eliminado = '0' AND EXISTS (
					SELECT 1
					FROM detalle_venta dv
					LEFT JOIN venta v ON dv.idventa = v.idventa
					WHERE dv.idpersonal = p.idpersonal
					AND v.idlocal = p.idlocal
					AND v.estado <> 'Anulado'
				)
				ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}


	public function listarPersonalesPorUsuario($idlocal_session)
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.idlocal, l.titulo AS local, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora_comision, '%d-%m-%Y %H:%i:%s') as fecha, p.estado, p.total_comision, p.comisionado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.idlocal = '$idlocal_session' AND p.eliminado = '0' AND EXISTS (
					SELECT 1
					FROM detalle_venta dv
					LEFT JOIN venta v ON dv.idventa = v.idventa
					WHERE dv.idpersonal = p.idpersonal
					AND v.idlocal = p.idlocal
					AND v.estado <> 'Anulado'
				)
				ORDER BY p.idpersonal DESC";
		return ejecutarConsulta($sql);
	}


	public function mostrarComisionesPersonal($idpersonal, $idlocal)
	{
		$sql = "SELECT
				  dv.iddetalle_venta,
				  dv.idventa,
				  dv.idcaja,
				  dv.idarticulo,
				  dv.idservicio,
				  a.nombre AS titulo_articulo,
				  s.titulo AS titulo_servicio,
				  dv.idpersonal,
				  dv.cantidad,
				  dv.precio_venta,
				  dv.descuento,
				  dv.impuesto,
				  dv.fecha_hora
				FROM detalle_venta dv
				LEFT JOIN articulo a ON dv.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON dv.idservicio = s.idservicio
				LEFT JOIN venta v ON dv.idventa = v.idventa AND v.estado <> 'Anulado'
				WHERE v.idlocal = '$idlocal' AND dv.idpersonal = '$idpersonal' AND v.eliminado = '0'";

		return ejecutarConsulta($sql);
	}

	public function verComisionesEmpleado($idpersonal)
	{
		$sql = "SELECT
				  c.idarticulo,
				  c.idservicio,
				  c.idpersonal,
				  c.comision,
				  c.tipo,
				  DATE_FORMAT(c.fecha_hora, '%d-%m-%Y %H:%i:%s') as fecha,
				  a.nombre AS nombre_articulo,
				  s.titulo AS nombre_servicio
				FROM comisiones c
				LEFT JOIN articulo a ON c.idarticulo = a.idarticulo
				LEFT JOIN servicios s ON c.idservicio = s.idservicio
				WHERE c.idpersonal = '$idpersonal'";

		return ejecutarConsulta($sql);
	}

	public function verDatosEmpleado($idpersonal)
	{
		$sql = "SELECT p.idpersonal, p.nombre, l.idlocal, l.titulo AS local, l.local_ruc AS local_ruc, p.cargo AS cargo_personal, p.tipo_documento, p.num_documento, p.direccion, p.telefono, p.email, u.idusuario, u.nombre as usuario, u.cargo as cargo,
				DATE_FORMAT(p.fecha_hora_comision, '%d-%m-%Y %H:%i:%s') as fecha, p.estado, p.total_comision, p.comisionado
				FROM personales p
				LEFT JOIN usuario u ON p.idusuario = u.idusuario
				LEFT JOIN locales l ON p.idlocal = l.idlocal
				WHERE p.idpersonal = '$idpersonal'";

		return ejecutarConsultaSimpleFila($sql);
	}
}