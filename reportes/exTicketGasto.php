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
        require('../modelos/Perfiles.php');
        $perfil = new Perfiles();
        $rspta = $perfil->mostrarReporte();

        # Datos de la empresa #
        $logo = $rspta["imagen"];
        $ext_logo = strtolower(pathinfo($rspta["imagen"], PATHINFO_EXTENSION));
        $empresa = $rspta["titulo"];
        $auspiciado = $rspta["auspiciado"];
        $ruc = ($rspta["ruc"] == '') ? 'Sin registrar' : $rspta["ruc"];
        $direccion = ($rspta["direccion"] == '') ? 'Sin registrar' : $rspta["direccion"];
        $telefono = ($rspta["telefono"] == '') ? 'Sin registrar' : number_format($rspta["telefono"], 0, '', ' ');
        $email = ($rspta["email"] == '') ? 'Sin registrar' : $rspta["email"];

        require('../modelos/Gastos.php');
        $gasto = new Gasto();

        $rspta = $gasto->mostrar($_GET["id"]);

        $reg = (object) $rspta;

        require('ticket/code128.php');

        # Modificando el ancho y alto del ticket #
        $pdf = new PDF_Code128('P', 'mm', array(70, 150));
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(4, 10, 4);
        $pdf->AddPage();

        $y = 2; // inicialización de variable de posición Y.
        $size = 0; // inicialización de variable de tamaño.

        # Encabezado y datos del ticket #
        $y = $pdf->cuerpoCaja(
            $y,
            "GASTOS DE CAJA",
            $logo,
            $ext_logo,
            $reg->fecha ?? '',
            $reg->local ?? '',
            $reg->local_ruc ?? '',
            $reg->nombre ?? '',
            $reg->caja ?? '',
            'MONTO: ' . $reg->monto ?? '',
            '',
            $reg->descripcion ?? '',
        );

        # Créditos #
        $pdf->creditos(
            $y,
            $empresa . "\n" .
                "Ruc: " . $ruc . "\n" .
                "Dirección: " . $direccion . "\n" .
                "Teléfono: " . $telefono . "\n" .
                "Email: " . $email . "\n"
        );

        # Nombre del archivo PDF #
        $pdf->Output("I", "ticket_gasto_" . mt_rand(10000000, 99999999) . ".pdf", true);
    } else {
        require 'noacceso.php';
    }
}

ob_end_flush();
