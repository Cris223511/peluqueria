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

require('../modelos/Compra.php');
$compra = new Compra();

$rspta1 = $compra->listarDetallesCompra($_GET["id"]);
$rspta2 = $compra->listarDetallesProductoCompra($_GET["id"]);
$rspta3 = $compra->listarDetallesMetodosPagoCompra($_GET["id"]);

$reg1 = $rspta1->fetch_object();

require('A4/Compra.php');

# Modificando la hoja del reporte #
$pdf = new PDF_Invoice('P', 'mm', 'A4');
$pdf->AddPage();

$y = 2; // inicialización de variable de posición Y.
$size = 0; // inicialización de variable de tamaño.

# Encabezado y datos del reporte #
$pdf->encabezado(
  $y,
  $logo,
  $ext_logo,
  $reg1->num_comprobante ?? '',
  $reg1->tipo_comprobante ?? '',
  $reg1->local ?? '',
  $reg1->local_ruc ?? '',
  $reg1->estado ?? '',
  $reg1->usuario ?? '',
  (($reg1->tipo_documento_usuario) ? ($reg1->tipo_documento_usuario . ": " . $reg1->num_documento_usuario) : ("Tipo y N° doc. sin registrar.")) . "\n" .
    "Dirección: " . $reg1->direccion_usuario . "\n" .
    "Teléfono: " . (($reg1->telefono_usuario != "") ? number_format($reg1->telefono_usuario, 0, '', ' ') : 'Sin registrar') . "\n" .
    "Email: " . $reg1->email_usuario,
  $empresa,
  "RUC: " . $ruc . "\n" .
    "Dirección: " . $direccion . "\n" .
    "Teléfono: " . $telefono . "\n" .
    "Email: " . $email . "\n"
);

$y += 67;

# Separador #
$pdf->SetDrawColor(241, 210, 76);
$pdf->SetLineWidth(1);
$pdf->Line(15, $y, 195, $y);
$pdf->SetFillColor(241, 210, 76);
$pdf->Circle(195, $y, 2, 'F');

# Datos del proveedor #
$y = $pdf->proveedor(
  $y,
  $reg1->proveedor ?? '',
  ($reg1->telefono != "") ? number_format($reg1->telefono, 0, '', ' ') : '',
  $reg1->tipo_documento ?? '',
  $reg1->num_documento ?? '',
  $reg1->fecha_hora ?? '',
  $reg1->impuesto ?? '',
  $reg1->comentario_externo ?? '',
);

# Separador #
$pdf->SetDrawColor(241, 210, 76);
$pdf->SetLineWidth(1);
$pdf->Line(15, $y, 195, $y);
$pdf->SetFillColor(241, 210, 76);
$pdf->Circle(195, $y, 2, 'F');

$pdf->SetDrawColor(255, 255, 255);

# Tabla para los detalles de los productos #
$cols = array(
  "PRODUCTO" => 86,
  "CANTIDAD" => 24,
  "P.U." => 24,
  "DSCTO" => 24,
  "SUBTOTAL" => 24
);

$aligns = array(
  "PRODUCTO" => "L",
  "CANTIDAD" => "C",
  "P.U." => "R",
  "DSCTO" => "R",
  "SUBTOTAL" => "R"
);

$y += 5.5;

$pdf->SetFont('Arial', 'B', 10);
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

$y += 9;

while ($reg2 = $rspta2->fetch_object()) {
  $subtotal = ($reg2->cantidad * $reg2->precio_compra) - $reg2->descuento;

  $textoProducto = utf8_decode($reg2->idarticulo == "0" ? mb_strtoupper($reg2->servicio) : mb_strtoupper($reg2->articulo));
  $anchoTexto = $pdf->GetStringWidth($textoProducto);

  $line = array(
    "PRODUCTO" => $textoProducto,
    "CANTIDAD" => "$reg2->cantidad",
    "P.U." => number_format($reg2->precio_compra ?? 0.00, 2),
    "DSCTO" => number_format($reg2->descuento ?? 0.00, 2),
    "SUBTOTAL" => number_format($subtotal ?? 0.00, 2)
  );
  $pdf->SetFont('Arial', '', 10);
  $size = $pdf->addLine($y, $line) ?? 0;

  $contador++;
  $esUltimoBucle = ($contador === $totalRegistros);
  $hizoSaltoLinea = ($anchoTexto > $anchoColumnaProducto);

  if ($esUltimoBucle && $hizoSaltoLinea) {
    $y += ($size + 1) ?? 0;
  } else if ($esUltimoBucle) {
    $y += ($size + 2.5) ?? 0;
  } else {
    $y += ($size + 3) ?? 0;
  }

  $totalSubtotal += $subtotal;
  $totalProductos++;
  $totalUnidades += $reg2->cantidad ?? 0;
}

# Tabla para los totales de los productos (SUBTOTAL, IGV Y TOTAL) #

# SUBTOTAL #
$y += $size ?? 0;
$pdf->Line(15, $y - 2.3, 197, $y - 2.3);

$y += 1.5 ?? 0;

$lineSubtotal = array(
  "PRODUCTO" => "",
  "CANTIDAD" => "",
  "P.U." => "",
  "DSCTO" => "SUBTOTAL",
  "SUBTOTAL" => number_format($totalSubtotal, 2)
);

$pdf->SetFont('Arial', 'B', 10);
$sizeSubtotal = $pdf->addLine($y, $lineSubtotal);

$y += $sizeSubtotal + 3;

# IGV #
$lineIGV = array(
  "PRODUCTO" => "",
  "CANTIDAD" => "",
  "P.U." => "",
  "DSCTO" => "IGV",
  "SUBTOTAL" => number_format((($totalSubtotal) * ($reg1->impuesto ?? 0.00)), 2)
);

$pdf->SetFont('Arial', 'B', 10);
$sizeIGV = $pdf->addLine($y, $lineIGV);

$y += $sizeIGV + 3;

# TOTAL #
$lineTotal = array(
  "PRODUCTO" => "",
  "CANTIDAD" => "",
  "P.U." => "",
  "DSCTO" => "TOTAL",
  "SUBTOTAL" => number_format($reg1->total_compra ?? 0.00, 2)
);

$pdf->SetFont('Arial', 'B', 10);
$sizeTotal = $pdf->addLine($y, $lineTotal);

$pdf->addLineFormat($lineIGV);
$pdf->addLineFormat($lineSubtotal);
$pdf->addLineFormat($lineTotal);

$y += 6;

# Separador #
$pdf->SetDrawColor(241, 210, 76);
$pdf->SetLineWidth(1);
$pdf->Line(15, $y, 195, $y);
$pdf->SetFillColor(241, 210, 76);
$pdf->Circle(195, $y, 2, 'F');

$y += 5;

# Cuerpo y datos del reporte #
$formatterES = new NumberFormatter("es-ES", NumberFormatter::SPELLOUT);
$total_compra = $reg1->total_compra ?? 0.00;

$izquierda = floor($total_compra);
$derecha = round(($total_compra - $izquierda) * 100);

$texto = $formatterES->format($izquierda) . " NUEVOS SOLES CON " . $formatterES->format($derecha) . " CÉNTIMOS";
$textoEnMayusculas = mb_strtoupper($texto, 'UTF-8');

$y = $pdf->cuerpo(
  $y,
  $totalProductos,
  $totalUnidades,
  $textoEnMayusculas,
);

$y += 8;

# Separador #
$pdf->SetDrawColor(241, 210, 76);
$pdf->SetLineWidth(1);
$pdf->Line(15, $y, 195, $y);
$pdf->SetFillColor(241, 210, 76);
$pdf->Circle(195, $y, 2, 'F');

$pdf->SetDrawColor(255, 255, 255);

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
$pdf->Image($filePath, 11.5, $y, 42);

unlink($filePath);

# Créditos #
$pdf->creditosReporte($y, $reg1->tipo_comprobante ?? '', $auspiciado);

# Tabla para los métodos de pago #
$cols = array(
  "METODO PAGO" => 132,
  "MONTO" => 50,
);

$aligns = array(
  "METODO PAGO" => "R",
  "MONTO" => "R",
);

$pdf->SetFont('Arial', 'B', 10);
$pdf->addCols2($cols, $aligns, $y);
$cols = array(
  "METODO PAGO" => "R",
  "MONTO" => "R",
);

$pdf->addLineFormat($cols);
$pdf->addLineFormat($cols);

$y += 14;

$montoTotal = 0;

$esUltimoBucle = false;
$hizoSaltoLinea = false;
$contador = 0;

$totalRegistros = $rspta3->num_rows;
$anchoColumnaProducto = 49;

while ($reg3 = $rspta3->fetch_object()) {
  $line = array(
    "METODO PAGO" => ($reg3->metodo_pago ?? ''),
    "MONTO" => ($reg3->monto ?? 0.00),
  );
  $pdf->SetFont('Arial', '', 10);
  $size = $pdf->addLine($y - 4, $line) ?? 0;

  $contador++;
  $esUltimoBucle = ($contador === $totalRegistros);
  $hizoSaltoLinea = ($anchoTexto > $anchoColumnaProducto);

  if ($esUltimoBucle && $hizoSaltoLinea) {
    $y += ($size + 1) ?? 0;
  } else if ($esUltimoBucle) {
    $y += ($size + 1) ?? 0;
  } else {
    $y += ($size + 3) ?? 0;
  }

  $montoTotal += ($reg3->monto ?? 0.00);
}

# Tabla para los totales de los métodos de pago (SUBTOTAL, VUELTO y TOTAL) #

# SUBTOTAL #
$y += $size - 2 ?? 0;
$pdf->Line(105.5, $y - 3, 197, $y - 3);

$lineSubtotal = array(
  "METODO PAGO" => "SUBTOTAL",
  "MONTO" => number_format($montoTotal, 2),
);

$pdf->SetFont('Arial', 'B', 10);
$sizeSubtotal = $pdf->addLine($y, $lineSubtotal) ?? 0;

$y += $sizeSubtotal + 3;

# VUELTO #
$lineVuelto = array(
  "METODO PAGO" => "VUELTO",
  "MONTO" => $reg1->vuelto ?? '0.00',
);

$pdf->SetFont('Arial', 'B', 10);
$sizeIGV = $pdf->addLine($y, $lineVuelto);

$y += $sizeIGV + 3;

# TOTAL #
$lineTotal = array(
  "METODO PAGO" => "TOTAL",
  "MONTO" => number_format($reg1->total_compra ?? 0.00, 2),
);

$pdf->SetFont('Arial', 'B', 10);
$sizeTotal = $pdf->addLine($y, $lineTotal);

$pdf->addLineFormat($lineVuelto);
$pdf->addLineFormat($lineSubtotal);
$pdf->addLineFormat($lineTotal);

# Nombre del archivo PDF #
$pdf->Output("I", "reporte_compra_" . mt_rand(10000000, 99999999) . ".pdf", true);

ob_end_flush();
