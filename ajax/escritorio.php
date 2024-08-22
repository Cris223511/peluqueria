<?php
ob_start();
if (strlen(session_id()) < 1) {
    session_start(); // Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo']) && $_GET["op"] !== 'guardaryeditar') {
    session_unset();
    session_destroy();
    header("Location: ../vistas/login.html");
    exit();
}

require_once "../modelos/Perfiles.php";

$perfiles = new Perfiles();

// Variables de sesión a utilizar
$idlocal = $_SESSION['idlocal'];
$cargo = $_SESSION["cargo"];

// Variables a recoger del JS.
$moneda = isset($_POST["moneda"]) ? limpiarCadena($_POST["moneda"]) : "soles";

switch ($_GET["op"]) {
    case 'filtrarPorMoneda':
        // Realizamos las consultas en función del tipo de cambio seleccionado
        if ($cargo == "superadmin" || $cargo == "admin_total") {
            $compras10 = $perfiles->comprasultimos_10dias($moneda);
            $ventas10 = $perfiles->ventasultimos_10dias($moneda);
            $proformas10 = $perfiles->proformasultimos_10dias($moneda);
            $totalCompras = $perfiles->totalCompras($moneda)["total"];
            $totalVentas = $perfiles->totalVentas($moneda)["total"];
            $totalVentasProforma = $perfiles->totalVentasProforma($moneda)["total"];
        } else {
            $compras10 = $perfiles->comprasultimos_10diasUsuario($idlocal, $moneda);
            $ventas10 = $perfiles->ventasultimos_10diasUsuario($idlocal, $moneda);
            $proformas10 = $perfiles->proformasultimos_10diasUsuario($idlocal, $moneda);
            $totalCompras = $perfiles->totalComprasUsuario($idlocal, $moneda)["total"];
            $totalVentas = $perfiles->totalVentasUsuario($idlocal, $moneda)["total"];
            $totalVentasProforma = $perfiles->totalVentasProformaUsuario($idlocal, $moneda)["total"];
        }

        // Procesar las fechas y totales para los gráficos
        $totalesc = [];
        while ($regfechac = $compras10->fetch_object()) {
            $totalesc[] = $regfechac->total;
        }

        $totalesv = [];
        while ($regfechav = $ventas10->fetch_object()) {
            $totalesv[] = $regfechav->total;
        }

        $totalesp = [];
        while ($regfechap = $proformas10->fetch_object()) {
            $totalesp[] = $regfechap->total;
        }

        // Respuesta JSON con los datos
        echo json_encode([
            'totalCompras' => number_format($totalCompras, 2),
            'totalVentas' => number_format($totalVentas, 2),
            'totalVentasProforma' => number_format($totalVentasProforma, 2),
            'totalesc' => $totalesc,
            'totalesv' => $totalesv,
            'totalesp' => $totalesp
        ]);
        break;
}

ob_end_flush();
