<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

// si no está logeado o no tiene ningún cargo...
if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	echo 'No está autorizado para realizar esta acción.';
	exit();
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['reportes'] == 1) {
		require_once "../modelos/Reporte.php";

		$reporte = new Reporte();

		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		switch ($_GET["op"]) {
			case 'listarVentas':
				$parametros = array(
					"v.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "v.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(v.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "v.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "v.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "v.estado = '{$_GET["param6"]}'",
					"param7" => "dvp.idmetodopago = '{$_GET["param7"]}'",
					"param8" => "c.nombre LIKE '%{$_GET["param8"]}%'",
					"param9" => "c.num_documento = '{$_GET["param9"]}'",
					"param10" => "v.num_comprobante = '{$_GET["param10"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarVentas($condiciones) : $reporte->listarVentasLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalPrecioVenta = 0;

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
							break;
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'cajero':
							$cargo_detalle = "Cajero";
							break;
						default:
							break;
					}

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-info" style="color: black !important; margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->cliente,
						"3" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"4" => $reg->local,
						"5" => $reg->caja,
						"6" => $reg->tipo_comprobante,
						"7" => 'N° ' . $reg->num_comprobante,
						"8" => $reg->total_venta,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioVenta += $reg->total_venta;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "",
						"5" => "",
						"6" => "",
						"7" => "<strong>TOTAL</strong>",
						"8" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"9" => "",
						"10" => "",
					);
				}

				$results = array(
					"sEcho" => 1, //Información para el datatables
					"iTotalRecords" => count($data), //enviamos el total registros al datatable
					"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
					"aaData" => $data
				);
				echo json_encode($results);

				break;

			case 'listarProformas':
				$parametros = array(
					"p.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "p.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(p.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "p.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "p.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "p.estado = '{$_GET["param6"]}'",
					"param7" => "dpp.idmetodopago = '{$_GET["param7"]}'",
					"param8" => "c.nombre LIKE '%{$_GET["param8"]}%'",
					"param9" => "c.num_documento = '{$_GET["param9"]}'",
					"param10" => "p.num_comprobante = '{$_GET["param10"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarProformas($condiciones) : $reporte->listarProformasLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalPrecioVenta = 0;

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
							break;
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'cajero':
							$cargo_detalle = "Cajero";
							break;
						default:
							break;
					}

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-info" style="color: black !important; margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idproforma . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->cliente,
						"3" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"4" => $reg->local,
						"5" => $reg->caja,
						"6" => $reg->tipo_comprobante,
						"7" => 'N° ' . $reg->num_comprobante,
						"8" => $reg->total_venta,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioVenta += $reg->total_venta;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "",
						"5" => "",
						"6" => "",
						"7" => "<strong>TOTAL</strong>",
						"8" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"9" => "",
						"10" => "",
					);
				}

				$results = array(
					"sEcho" => 1, //Información para el datatables
					"iTotalRecords" => count($data), //enviamos el total registros al datatable
					"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
					"aaData" => $data
				);
				echo json_encode($results);

				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
