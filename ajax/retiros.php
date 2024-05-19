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
	if ($_SESSION['cajas'] == 1) {
		require_once "../modelos/Retiros.php";

		$retiros = new Retiro();

		// Variables de sesión a utilizar.
		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];
		$idusuario = $_SESSION["idusuario"];

		$idretiro = isset($_POST["idretiro"]) ? limpiarCadena($_POST["idretiro"]) : "";
		$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$monto = isset($_POST["monto"]) ? limpiarCadena($_POST["monto"]) : "";
		$monto_caja = isset($_POST["monto_caja"]) ? limpiarCadena($_POST["monto_caja"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				$verificarMonto = $retiros->verificarMonto($idcaja, $monto);

				if ($verificarMonto) {
					echo "El monto que desea retirar no puede ser mayor al monto total de la caja.";
				} else {
					$rspta = $retiros->agregar($idusuario, $idcaja, $idlocal, $descripcion, $monto, $monto_caja);
					echo $rspta ? "Retiro registrado" : "El retiro no se pudo registrar";
				}
				break;

			case 'eliminar':
				$rspta = $retiros->eliminar($idretiro, $idcaja);
				echo $rspta ? "Retiro eliminado" : "El retiro no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $retiros->mostrar($idretiro);
				echo json_encode($rspta);
				break;

			case 'listar':
				$param1 = $_GET["param1"]; // valor fecha inicio
				$param2 = $_GET["param2"]; // valor fecha fin
				$param3 = $_GET["param3"]; // valor local

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $retiros->listarPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $retiros->listarPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $retiros->listarPorParametro("c.idlocal = '$param3'");
					} else {
						$rspta = $retiros->listar();
					}
				} else {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $retiros->listarPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $retiros->listarPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $retiros->listarPorUsuarioParametro($idlocalSession, "c.idlocal = '$param3'");
					} else {
						$rspta = $retiros->listarPorUsuario($idlocalSession);
					}
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

					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px;">' .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idretiro . ', ' . $reg->idcaja . ')"><i class="fa fa-trash"></i></button>') .
							('<button class="btn btn-info" style="margin-right: 3px; height: 35px;" onclick="modalDetalles(' . $reg->idretiro . ')"><i class="fa fa-info-circle"></i></button>') .
							('<a target="_blank" href="../reportes/exTicketRetiro.php?id=' . $reg->idretiro . '"> <button class="btn btn-success" style="height: 35px; color: white !important;"><i class="fa fa-print"></i></button></a>') .
							'</div>',
						"1" => $reg->caja,
						"2" => $reg->local,
						"3" => 'S/. ' . number_format($reg->monto_caja, 2, '.', ','),
						"4" => 'S/. ' . number_format($reg->monto, 2, '.', ','),
						"5" => 'S/. ' . number_format($reg->monto_total, 2, '.', ','),
						"6" => ucwords($reg->nombre),
						"7" => ucwords($cargo_detalle),
						"8" => $reg->fecha
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
ob_end_flush();
