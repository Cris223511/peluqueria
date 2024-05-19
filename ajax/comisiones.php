<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	echo 'No está autorizado para realizar esta acción.';
	exit();
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html");
} else {
	if ($_SESSION['comisiones'] == 1) {
		require_once "../modelos/Comisiones.php";

		$comisiones = new Comision();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$idlocal_session = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idpersonalUniq = isset($_POST["idpersonalUniq"]) ? limpiarCadena($_POST["idpersonalUniq"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				$rspta = $comisiones->insertar($idpersonalUniq, $_POST["detalles"], $_POST["idpersonal"], $_POST["idcliente"],  $_POST["comision"], $_POST["tipo"]);
				echo $rspta ? "Empleado comisionado correctamente." : "El empleado no se pudo comisionar.";
				break;

			case 'listar':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $comisiones->listarPersonales();
				} else {
					$rspta = $comisiones->listarPersonalesPorUsuario($idlocal_session);
				}

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if (($reg != "superadmin" && $reg != "admin_total") && $cargo == "admin") {
						return $buttonType;
					} elseif ($reg != "superadmin" && $cargo == "admin_total") {
						return $buttonType;
					} elseif ($cargo == "superadmin" || ($cargo == "cajero" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}


				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
							break;
						case 'admin_total':
							$cargo_detalle = "Admin Total";
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

					$telefono = ($reg->telefono == '') ? 'Sin registrar' : number_format($reg->telefono, 0, '', ' ');

					$totalEmpleado = mysqli_fetch_assoc($comisiones->verTotalComisionEmpleado($reg->idpersonal));

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							// ('<button class="btn btn-warning" style="margin-right: 3px; width: 38px; height: 35px;" onclick="generarComision(' . $reg->idpersonal . ', ' . $reg->idlocal . ', \'' . $reg->nombre . '\', \'' . $reg->cargo_personal . '\', \'' . $reg->tipo_documento . '\', \'' . $reg->num_documento . '\', \'' . $reg->local . '\')"><i class="fa fa-usd"></i></button>') .
							('<button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="verComision(' . $reg->idpersonal . ', \'' . $reg->nombre . '\', \'' . $reg->cargo_personal . '\', \'' . $reg->tipo_documento . '\', \'' . $reg->num_documento . '\', \'' . $reg->local . '\')"><i class="fa fa-eye"></i></button>') .
							('<a target="_blank" href="../reportes/exTicketComision.php?id=' . $reg->idpersonal . '"> <button class="btn btn-success" style="margin-right: 3px; width: 38px; height: 35px; color: white !important;"><i class="fa fa-print"></i></button></a>') .
							'</div>',
						"1" => ucwords($reg->nombre) . ' - ' . $reg->cargo_personal,
						"2" => $totalEmpleado["comision_total"],
						"3" => $reg->local,
						"4" => $reg->tipo_documento,
						"5" => $reg->num_documento,
						"6" => ucwords($reg->usuario),
						"7" => ucwords($cargo_detalle),
						"8" => ($reg->fecha != "00-00-0000 00:00:00") ? $reg->fecha : "Sin registrar.",
						"9" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
							'<span class="label bg-red">Desactivado</span>'
					);
				}
				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);

				echo json_encode($results);
				break;

			case 'verComision':
				$idpersonal = $_GET['idpersonal'];
				$fecha_inicio = $_GET["fecha_inicio"];
				$fecha_fin = $_GET["fecha_fin"];


				if ($fecha_inicio == "" && $fecha_fin == "") {
					$rspta = $comisiones->verComisionesEmpleado($idpersonal);
				} else {
					$rspta = $comisiones->verComisionesEmpleadoPorFecha($idpersonal, $fecha_inicio, $fecha_fin);
				}

				$data = array();

				$firstIteration = true;
				$totalComision = 0;

				while ($reg = $rspta->fetch_object()) {
					$data[] = array(
						"0" => ($reg->idarticulo != "0") ? strtoupper($reg->nombre_articulo) : strtoupper($reg->nombre_servicio),
						"1" => $reg->cliente,
						"2" => $reg->personal,
						"3" => $reg->comision,
						"4" => ($reg->tipo == "1") ? 'S/.' : '%',
						"5" => $reg->fecha,
					);

					$totalComision += $reg->comision;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "<div style='text-align: end; margin-right: 20px;'><strong>TOTAL</strong></div>",
						"3" => '<strong>' . number_format($totalComision, 2) . '</strong>',
						"4" => "",
						"5" => "",
					);
				}

				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);

				echo json_encode($results);
				break;

			case 'mostrarComisionesPersonal':
				$idpersonal = isset($_POST["idpersonal"]) ? limpiarCadena($_POST["idpersonal"]) : "";

				$rspta = $comisiones->mostrarComisionesPersonal($idpersonal, $idlocal);
				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$data[] = $reg;
				}

				echo json_encode($data);
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
