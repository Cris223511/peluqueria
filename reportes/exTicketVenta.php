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

require('../modelos/Venta.php');
$venta = new Venta();

$rspta1 = $venta->listarDetallesVenta($_GET["id"]);
$rspta2 = $venta->listarDetallesProductoVenta($_GET["id"]);
$rspta3 = $venta->listarDetallesMetodosPagoVenta($_GET["id"]);

$reg1 = $rspta1->fetch_object();

require('./ticket/code128.php');

# Modificando el ancho y alto del ticket #
$pdf = new PDF_Code128('P', 'mm', array(70, 300));
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(4, 10, 4);
$pdf->AddPage();

$y = 2; // inicialización de variable de posición Y.
$size = 0; // inicialización de variable de tamaño.

# Encabezado y datos del ticket #
$pdf->encabezado1(
    $y,
    $logo,
    $ext_logo,
    $reg1->num_comprobante ?? '',
    $reg1->fecha_hora ?? '',
    $reg1->tipo_comprobante ?? '',
    $reg1->local ?? '',
    $reg1->local_ruc ?? '',
    $reg1->estado ?? '',
);

$pdf->Ln(3);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

$y += 52;

# Encabezado y datos del ticket #
$pdf->encabezado2(
    $y,
    "CLIENTE: " . $reg1->cliente ?? '',
    ($reg1->telefono  ?? '' != "") ? number_format($reg1->telefono, 0, '', ' ') : '',
    $reg1->tipo_documento ?? '',
    $reg1->num_documento ?? '',
);

# Separador #
$pdf->Ln(3);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

# Tabla para los detalles de los productos #
$cols = array(
    "PRODUCTO" => 20,
    "CANTIDAD" => 10,
    "P.U." => 11,
    "DSCTO" => 11,
    "SUBTOTAL" => 12
);

$aligns = array(
    "PRODUCTO" => "L",
    "CANTIDAD" => "C",
    "P.U." => "R",
    "DSCTO" => "R",
    "SUBTOTAL" => "R"
);

$y += 24.5;

$pdf->SetFont('hypermarket', '', 8.5);
$pdf->addCols($cols, $aligns, $y);
$cols = array(
    "PRODUCTO" => "L",
    "CANTIDAD" => "C",
    "P.U." => "R",
    "DSCTO" => "R",
    "SUBTOTAL" => "R"
);

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$subtotal = 0;
$totalSubtotal = 0;
$totalProductos = 0;
$totalUnidades = 0;

$esUltimoBucle = false;
$hizoSaltoLinea = false;
$contador = 0;

$totalRegistros = $rspta2->num_rows;
$anchoColumnaProducto = 20;

while ($reg2 = $rspta2->fetch_object()) {
    $subtotal = ($reg2->cantidad * $reg2->precio_venta) - $reg2->descuento;

    $textoProducto = utf8_decode(($reg2->articulo != "") ? mb_strtoupper($reg2->articulo) : mb_strtoupper($reg2->servicio));
    $anchoTexto = $pdf->GetStringWidth($textoProducto);

    $line = array(
        "PRODUCTO" => $textoProducto,
        "CANTIDAD" => "$reg2->cantidad",
        "P.U." => number_format($reg2->precio_venta, 2),
        "DSCTO" => number_format($reg2->descuento, 2),
        "SUBTOTAL" => number_format($subtotal, 2)
    );
    $pdf->SetFont('hypermarket', '', 8);
    $size = $pdf->addLine($y, $line) ?? 0;

    $contador++;
    $esUltimoBucle = ($contador === $totalRegistros);
    $hizoSaltoLinea = ($anchoTexto > $anchoColumnaProducto);

    if ($esUltimoBucle && $hizoSaltoLinea) {
        $y += ($size - 1) ?? 0;
    } else if ($esUltimoBucle) {
        $y += ($size + 1.5) ?? 0;
    } else {
        $y += ($size + 2) ?? 0;
    }

    $totalSubtotal += $subtotal;
    $totalProductos++;
    $totalUnidades += $reg2->cantidad ?? 0;
}

# Tabla para los totales de los productos (SUBTOTAL, IGV Y TOTAL) #

# SUBTOTAL #
$y += $size ?? 0;
$pdf->Line(3, $y - 2.1, 67, $y - 2.1);

$lineSubtotal = array(
    "PRODUCTO" => "",
    "CANTIDAD" => "",
    "P.U." => "",
    "DSCTO" => "SUBTOTAL",
    "SUBTOTAL" => number_format($totalSubtotal, 2)
);

$pdf->SetFont('hypermarket', '', 8);
$sizeSubtotal = $pdf->addLine($y, $lineSubtotal);

$y += $sizeSubtotal + 2;

# IGV #
$lineIGV = array(
    "PRODUCTO" => "",
    "CANTIDAD" => "",
    "P.U." => "",
    "DSCTO" => "IGV",
    "SUBTOTAL" => number_format((($totalSubtotal) * ($reg1->impuesto ?? 0.00)), 2)
);

$pdf->SetFont('hypermarket', '', 8);
$sizeIGV = $pdf->addLine($y, $lineIGV);

$y += $sizeIGV + 2;

# TOTAL #
$lineTotal = array(
    "PRODUCTO" => "",
    "CANTIDAD" => "",
    "P.U." => "",
    "DSCTO" => "TOTAL",
    "SUBTOTAL" => number_format($reg1->total_venta ?? 0.00, 2)
);

$pdf->SetFont('hypermarket', '', 8);
$sizeTotal = $pdf->addLine($y, $lineTotal);

$pdf->addLineFormat($lineIGV);
$pdf->addLineFormat($lineSubtotal);
$pdf->addLineFormat($lineTotal);

# Separador #
$pdf->SetFont('hypermarket', '', 10);
$pdf->Ln(3);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

$y += 7;

# Cuerpo y datos del ticket #
$formatterES = new NumberFormatter("es-ES", NumberFormatter::SPELLOUT);
$total_venta = $reg1->total_venta ?? 0.00;

$izquierda = floor($total_venta);
$derecha = round(($total_venta - $izquierda) * 100);

$texto = $formatterES->format($izquierda) . " NUEVOS SOLES CON " . $formatterES->format($derecha) . " CÉNTIMOS";
$textoEnMayusculas = mb_strtoupper($texto, 'UTF-8');

$y = $pdf->cuerpo(
    $y,
    $totalProductos,
    $totalUnidades,
    $textoEnMayusculas,
);

# Separador #
$pdf->SetFont('hypermarket', '', 10);
$pdf->Ln(3);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

$y += 15.5;

# Tabla para los métodos de pago #
$cols = array(
    "METODO PAGO" => 49,
    "MONTO" => 15,
);

$aligns = array(
    "METODO PAGO" => "L",
    "MONTO" => "R",
);

$pdf->SetFont('hypermarket', '', 8.5);
$pdf->addCols($cols, $aligns, $y);
$cols = array(
    "METODO PAGO" => "L",
    "MONTO" => "R",
);

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y += 4;

$montoTotal = 0;

$esUltimoBucle = false;
$hizoSaltoLinea = false;
$contador = 0;

$totalRegistros = $rspta3->num_rows;
$anchoColumnaProducto = 49;

while ($reg3 = $rspta3->fetch_object()) {
    $anchoTexto = $pdf->GetStringWidth($reg3->metodo_pago ?? '');

    $line = array(
        "METODO PAGO" => ($reg3->metodo_pago ?? ''),
        "MONTO" => (number_format($reg3->monto, 2) ?? 0.00),
    );
    $pdf->SetFont('hypermarket', '', 8);
    $size = $pdf->addLine($y - 4, $line) ?? 0;

    $contador++;
    $esUltimoBucle = ($contador === $totalRegistros);
    $hizoSaltoLinea = ($anchoTexto > $anchoColumnaProducto);

    if ($esUltimoBucle && $hizoSaltoLinea) {
        $y += ($size - 1) ?? 0;
    } else if ($esUltimoBucle) {
        $y += ($size + 1.5) ?? 0;
    } else {
        $y += ($size + 2) ?? 0;
    }

    $montoTotal += ($reg3->monto ?? 0.00);
}

# Tabla para los totales de los métodos de pago (SUBTOTAL, VUELTO y TOTAL) #

# SUBTOTAL #
$y += ($size - 4) ?? 0;
$pdf->Line(3, $y - 2.1, 67, $y - 2.1);

$lineSubtotal = array(
    "METODO PAGO" => "SUBTOTAL",
    "MONTO" => number_format($montoTotal, 2),
);

$pdf->SetFont('hypermarket', '', 8);
$sizeSubtotal = $pdf->addLine($y, $lineSubtotal) ?? 0;

$y += $sizeSubtotal + 2;

# VUELTO #
$lineVuelto = array(
    "METODO PAGO" => "VUELTO",
    "MONTO" => $reg1->vuelto ?? '0.00',
);

$pdf->SetFont('hypermarket', '', 8);
$sizeVuelto = $pdf->addLine($y, $lineVuelto);

$y += $sizeVuelto + 2;

# TOTAL #
$lineTotal = array(
    "METODO PAGO" => "TOTAL",
    "MONTO" => number_format($reg1->total_venta ?? 0.00, 2),
);

$pdf->SetFont('hypermarket', '', 8);
$sizeTotal = $pdf->addLine($y, $lineTotal);

$pdf->addLineFormat($lineVuelto);
$pdf->addLineFormat($lineSubtotal);
$pdf->addLineFormat($lineTotal);

# Separador #
$pdf->SetFont('hypermarket', '', 10);
$pdf->Ln(3);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
$pdf->Ln(1);
$pdf->SetX(1.5);
$pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

$y += 7;

# Pie del ticket #
$y = $pdf->pie(
    $y,
    $reg1->usuario ?? '',
    $reg1->comentario_externo ?? '',
    $auspiciado,
);

$y += 4;

# generador de QR #
require './ticket/phpqrcode/qrlib.php';

$serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$redirectUrl = $serverUrl . $_SERVER['REQUEST_URI'];
$codeText = $redirectUrl;

$size = 12;
$level = 'H';
$filePath = './ticket/qrcode.png';

QRcode::png($codeText, $filePath, $level, $size ?? 0);
$pdf->Image($filePath, 20, null, 30);

unlink($filePath);

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
$pdf->Output("I", "ticket_venta_" . mt_rand(10000000, 99999999) . ".pdf", true);

ob_end_flush();
