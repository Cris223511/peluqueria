<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

require_once "../modelos/Carga_masiva.php";

$articulo = new CargaMasiva();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$idlocalSession = $_SESSION["idlocal"];
$cargo = $_SESSION["cargo"];

switch ($_GET["op"]) {
	case 'cargamasiva':
		break;
}

ob_end_flush();
