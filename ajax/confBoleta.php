<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}
require_once "../modelos/Perfiles.php";

$perfil = new Perfiles();

$idreporte = isset($_POST["idreporte"]) ? limpiarCadena($_POST["idreporte"]) : "";
$auspiciado = isset($_POST["auspiciado"]) ? limpiarCadena($_POST["auspiciado"]) : "";
$moneda = isset($_POST["moneda"]) ? limpiarCadena($_POST["moneda"]) : "";
$cambio = isset($_POST["cambio"]) ? limpiarCadena($_POST["cambio"]) : "";
$ruc = isset($_POST["ruc"]) ? limpiarCadena($_POST["ruc"]) : "";
$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (!isset($_SESSION["nombre"])) {
			header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
		} else {
			//Validamos el acceso solo al usuario logueado y autorizado.
			if ($_SESSION['perfilu'] == 1) {
				$_SESSION["moneda"] = $moneda;
				$_SESSION["cambio"] = $cambio;

				$rspta = $perfil->actualizarBoleta($idreporte, $ruc, $direccion, $telefono, $email, $auspiciado, $moneda, $cambio);
				echo $rspta ? "Boleta actualizado correctamente" : "Boleta no se pudo actualizar";
			} else {
				require 'noacceso.php';
			}
		}
		break;

	case 'mostrar':
		$rspta = $perfil->mostrarReporte();
		echo json_encode($rspta);
		break;
}
ob_end_flush();
