<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo'])) {
	echo json_encode(['error' => 'No está autorizado para realizar esta acción.']);
	exit();
}

require_once "../modelos/Usuario.php";

$usuario = new Usuario();

switch ($_GET["op"]) {
	case 'listar':
		if (!isset($_SESSION["nombre"])) {
			header("Location: ../vistas/login.html");
		} else {
			if ($_SESSION['personas'] == 1) {
				require_once "../modelos/Trabajadores.php";

				$trabajadores = new Trabajador();

				// Variables de sesión a utilizar.
				$idusuario = $_SESSION["idusuario"];
				$idlocal = $_SESSION["idlocal"];
				$cargo = $_SESSION["cargo"];

				switch ($_GET["op"]) {
					case 'listar':

						$rspta = $trabajadores->listarUsuariosPorLocal($idlocal);

						$data = array();

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

							$data[] = array(
								"0" => $reg->login,
								"1" => $cargo_detalle,
								"2" => $reg->nombre,
								"3" => $reg->tipo_documento,
								"4" => $reg->num_documento,
								"5" => $telefono,
								"6" => $reg->email,
								"7" => $reg->local,
								"8" => "N° " . $reg->local_ruc,
								"9" => '<a href="../files/usuarios/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
											<img src="../files/usuarios/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
										</a>',
								"10" => ($reg->estado) ? '<span class="label bg-green">Activado</span>' :
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
				}
			} else {
				require 'noacceso.php';
			}
		}
}

ob_end_flush();
