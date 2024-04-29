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
	if ($_SESSION['personas'] == 1) {
		require_once "../modelos/Comisiones.php";

		$comisiones = new Comision();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$idlocal_session = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idpersonal = isset($_POST["idpersonal"]) ? limpiarCadena($_POST["idpersonal"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";

		switch ($_GET["op"]) {
			case 'listar':
				if ($cargo == "superadmin") {
					$rspta = $comisiones->listarPersonales();
				} else {
					$rspta = $comisiones->listarPersonalesPorUsuario($idlocal_session);
				}

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($reg != "superadmin" && $cargo == "admin") {
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

					$data[] = array(
						"0" => '<div style="display: flex; justify-content: center; flex-wrap: nowrap; gap: 3px">' .
							('<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="generarComision(' . $reg->idpersonal . ', \'' . $reg->nombre . '\', \'' . $reg->tipo_documento . '\', \'' . $reg->num_documento . '\', \'' . $reg->local . '\')"><i class="fa fa-usd"></i></button>') .
							(($reg->comisionado == "1") ? ('<button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="verComision(' . $reg->idpersonal . ')"><i class="fa fa-eye"></i></button>') : ('')) .
							(($reg->comisionado == "1") ? ('<button class="btn btn-success" style="color: black !important; margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="imprimirComision(' . $reg->idpersonal . ')"><i class="fa fa-print"></i></button>') : ('')) .
							'</div>',
						"1" => ucwords($reg->nombre),
						"2" => $reg->cargo_personal,
						"3" => $reg->total_comision,
						"4" => $reg->local,
						"5" => $reg->tipo_documento,
						"6" => $reg->num_documento,
						"7" => ucwords($reg->nombre),
						"8" => ucwords($cargo_detalle),
						"9" => $reg->fecha,
						"10" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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

			case 'mostrarComisionesPersonal':
				$rspta = $comisiones->mostrarComisionesPersonal($idpersonal, $idlocal);
				echo json_encode($rspta);
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
