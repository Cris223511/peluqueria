<?php
ob_start();

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

require('../modelos/Comisiones.php');
$comision = new Comision();

$rspta1 = $comision->verComisionesEmpleado($_GET["id"]);
$rspta2 = $comision->verDatosEmpleado($_GET["id"]);
$rspta3 = $comision->verComisionesEmpleado($_GET["id"]);

$reg1 = (object) $rspta1;
$reg2 = (object) $rspta2;
$reg3 = $rspta3->fetch_object();

require('ticket/code128.php');

# Modificando el ancho y alto del ticket #
$pdf = new PDF_Code128('P', 'mm', array(70, 150));
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(4, 10, 4);
$pdf->AddPage();

$y = 2; // inicialización de variable de posición Y.
$size = 0; // inicialización de variable de tamaño.

# Encabezado y datos del ticket #
$y = $pdf->cuerpoComisiones(
    $y,
    "COMISIÓN",
    $logo,
    $ext_logo,
    $reg2->local ?? '',
    $reg2->local_ruc ?? '',
    $reg2->nombre ?? '',
    $reg2->tipo_documento ?? '',
    $reg2->num_documento ?? '',
    $reg2->cargo_personal ?? '',
);

# TÍTULO #
$pdf->SetY($y += 30.5);
$pdf->SetFont('hypermarket', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("PRODUCTOS COMISIONADOS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
$pdf->Ln(3);

$y += 13.5;

# Tabla para los detalles de la venta #
$cols = array(
    "PRODUCTO / SERVICIO" => 44,
    mb_convert_encoding(mb_strtoupper("COMISIÓN"), 'ISO-8859-1', 'UTF-8') => 20,
);

$aligns = array(
    "PRODUCTO / SERVICIO" => "L",
    mb_convert_encoding(mb_strtoupper("COMISIÓN"), 'ISO-8859-1', 'UTF-8') => "R",
);

$pdf->SetFont('hypermarket', '', 8.5);
$pdf->addCols($cols, $aligns, $y);
$cols = array(
    "PRODUCTO / SERVICIO" => "L",
    mb_convert_encoding(mb_strtoupper("COMISIÓN"), 'ISO-8859-1', 'UTF-8') => "R",
);

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y += 4;

$comisionTotal = 0;

$esUltimoBucle = false;
$hizoSaltoLinea = false;
$contador = 0;

$totalRegistros = $rspta3->num_rows;
$anchoColumna = 44;

$ultimoNombreProducto = null; // Variable para almacenar el último $nombreProducto impreso

while ($reg3) {
    $nombreProducto = ($reg3->idarticulo != "0") ? $reg3->nombre_articulo : $reg3->nombre_servicio;
    $anchoTexto = $pdf->GetStringWidth($nombreProducto ?? '');

    // Verificamos si el $nombreProducto actual es igual al último $nombreProducto impreso
    if ($nombreProducto != $ultimoNombreProducto) {
        $line = array(
            "PRODUCTO / SERVICIO" => (mb_convert_encoding(mb_strtoupper($nombreProducto), 'ISO-8859-1', 'UTF-8')),
            mb_convert_encoding(mb_strtoupper("COMISIÓN"), 'ISO-8859-1', 'UTF-8') => (number_format($reg3->comision, 2) ?? 0.00),
        );
        $pdf->SetFont('hypermarket', '', 8);
        $size = $pdf->addLine($y - 4, $line) ?? 0;

        $ultimoNombreProducto = $nombreProducto; // Actualizamos el último $nombreProducto impreso

        $contador++;
        $esUltimoBucle = ($contador === $totalRegistros);
        $hizoSaltoLinea = ($anchoTexto > $anchoColumna);

        if ($esUltimoBucle && $hizoSaltoLinea) {
            $y += ($size - 1) ?? 0;
        } else if ($esUltimoBucle) {
            $y += ($size + 1.5) ?? 0;
        } else {
            $y += ($size + 2) ?? 0;
        }

        $comisionTotal += ($reg3->comision ?? 0.00);
    }

    $reg3 = $rspta3->fetch_object();
}

# SEPARADOR #
$pdf->Ln(3.5);
$pdf->SetX(1.5);
$pdf->SetFont('hypermarket', '', 10);
$pdf->Cell(0, -2, utf8_decode("-----------------------------------------------"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("-----------------------------------------------"), 0, 0, 'L');

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
$pdf->Output("I", "ticket_comision_" . mt_rand(10000000, 99999999) . ".pdf", true);

ob_end_flush();
