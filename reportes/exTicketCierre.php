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

        require('../modelos/Cajas.php');
        $cajas = new Caja();

        $rspta1 = $cajas->mostrarCerradas($_GET["idcaja"]);
        $rspta2 = $cajas->listarDetallesProductosCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta3 = $cajas->listarDetallesVentasCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta3_1 = $cajas->listarPrimerayUltimaVentaCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta3_2 = $cajas->listarDetallesVentasAnuladasCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta4 = $cajas->listarDetallesEstadoVentasCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta5 = $cajas->listarDetallesMetodosPagoCajaCerrada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta6 = $cajas->listarDetallesCajaAperturada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta7 = $cajas->listarDetallesRetirosCajaAperurada($_GET["idcaja"], $_GET["idcaja_cerrada"]);
        $rspta8 = $cajas->listarDetallesGastosCajaAperurada($_GET["idcaja"], $_GET["idcaja_cerrada"]);

        $reg1 = (object) $rspta1;
        $reg2 = $rspta2->fetch_object();
        $reg3 = $rspta3->fetch_object();
        $reg3_1 = $rspta3_1->fetch_object();
        $reg3_2 = $rspta3_2->fetch_object();
        $reg4 = $rspta4->fetch_object();
        $reg5 = $rspta5->fetch_object();
        $reg6 = $rspta6->fetch_object();

        $reg7 = '';
        if ($rspta7 !== false) {
            $reg7 = $rspta7->fetch_object();
        }

        $reg8 = '';
        if ($rspta8 !== false) {
            $reg8 = $rspta8->fetch_object();
        }

        require('ticket/code128.php');

        # Modificando el ancho y alto del ticket #
        $pdf = new PDF_Code128('P', 'mm', array(70, 440));
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(4, 10, 4);
        $pdf->AddPage();

        $y = 2; // inicialización de variable de posición Y.
        $size = 0; // inicialización de variable de tamaño.

        # Encabezado y datos del ticket #
        $pdf->encabezadoCierre(
            $y,
            $logo,
            $ext_logo,
            "FECHA REGISTRO: " . ($reg1->fecha ?? ''),
            "FECHA CIERRE: " . ($reg1->fecha_cierre ?? ''),
            "CIERRE DE CAJA",
            $reg1->local ?? '',
            $reg1->local_ruc ?? '',
            $reg4->anulados ?? '',
            $reg4->emitidos ?? '',
            $reg4->validos ?? '',
        );

        $pdf->Ln(2.5);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 65.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("DOCUMENTOS POR VENTA"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 78;

        # Tabla para los detalles de la venta #
        $cols = array(
            "DOCUMENTO" => 17,
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => 17,
            "CANTIDAD" => 15,
            "TOTAL VENTA" => 15,
        );

        $aligns = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $cantidadTotal = 0;
        $ventaTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = $rspta3->num_rows;
        $anchoColumna = 17;

        while ($reg3) {
            $anchoTexto = $pdf->GetStringWidth($reg3->tipo_comprobante ?? '');

            $line = array(
                "DOCUMENTO" => ($reg3->tipo_comprobante ?? ''),
                mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => ($reg3->num_comprobante ?? ''),
                "CANTIDAD" => ($reg3->cantidad ?? 0.00),
                "TOTAL VENTA" => (number_format($reg3->total_venta, 2) ?? 0.00),
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y - 4, $line) ?? 0;

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

            $cantidadTotal += ($reg3->cantidad ?? 0.00);
            $ventaTotal += ($reg3->total_venta ?? 0.00);

            $reg3 = $rspta3->fetch_object();
        }

        # Tabla para los totales de las ventas (TOTALES) #

        # TOTAL #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotal = array(
            "DOCUMENTO" => "",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "TOTAL",
            "CANTIDAD" => $cantidadTotal,
            "TOTAL VENTA" => number_format($ventaTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeSubtotal = $pdf->addLine($y, $lineTotal) ?? 0;

        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("DOCUMENTOS INICIALES / FINALES"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 18;

        # Tabla para los detalles de la venta #
        $cols = array(
            "DOCUMENTO" => 17,
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => 17,
            "CANTIDAD" => 15,
            "TOTAL VENTA" => 15,
        );

        $aligns = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $cantidadTotal = 0;
        $ventaTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = $rspta3->num_rows;
        $anchoColumna = 17;

        $ultimoNumComprobante = null; // Variable para almacenar el último num_comprobante impreso

        while ($reg3_1) {
            $anchoTexto = $pdf->GetStringWidth($reg3_1->tipo_comprobante ?? '');

            // Verificamos si el num_comprobante actual es igual al último num_comprobante impreso
            if ($reg3_1->num_comprobante != $ultimoNumComprobante) {
                $line = array(
                    "DOCUMENTO" => ($reg3_1->tipo_comprobante ?? ''),
                    mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => ($reg3_1->num_comprobante ?? ''),
                    "CANTIDAD" => ($reg3_1->cantidad ?? 0.00),
                    "TOTAL VENTA" => (number_format($reg3_1->total_venta, 2) ?? 0.00),
                );
                $pdf->SetFont('hypermarket', '', 8);
                $size = $pdf->addLine($y - 4, $line) ?? 0;

                $ultimoNumComprobante = $reg3_1->num_comprobante; // Actualizamos el último num_comprobante impreso

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

                $cantidadTotal += ($reg3_1->cantidad ?? 0.00);
                $ventaTotal += ($reg3_1->total_venta ?? 0.00);
            }

            $reg3_1 = $rspta3_1->fetch_object();
        }

        # Tabla para los totales de las ventas (TOTALES) #

        # TOTAL #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotal = array(
            "DOCUMENTO" => "",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "TOTAL",
            "CANTIDAD" => $cantidadTotal,
            "TOTAL VENTA" => number_format($ventaTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeSubtotal = $pdf->addLine($y, $lineTotal) ?? 0;

        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("DOCUMENTOS ANULADOS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 18;

        # Tabla para los detalles de la venta #
        $cols = array(
            "DOCUMENTO" => 17,
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => 17,
            "CANTIDAD" => 15,
            "TOTAL VENTA" => 15,
        );

        $aligns = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            "DOCUMENTO" => "L",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "L",
            "CANTIDAD" => "C",
            "TOTAL VENTA" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $cantidadTotal = 0;
        $ventaTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = $rspta3->num_rows;
        $anchoColumna = 17;

        $ultimoNumComprobante = null; // Variable para almacenar el último num_comprobante impreso

        while ($reg3_2) {
            $anchoTexto = $pdf->GetStringWidth($reg3_2->tipo_comprobante ?? '');

            // Verificamos si el num_comprobante actual es igual al último num_comprobante impreso
            if ($reg3_2->num_comprobante != $ultimoNumComprobante) {
                $line = array(
                    "DOCUMENTO" => ($reg3_2->tipo_comprobante ?? ''),
                    mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => ($reg3_2->num_comprobante ?? ''),
                    "CANTIDAD" => ($reg3_2->cantidad ?? 0.00),
                    "TOTAL VENTA" => (number_format($reg3_2->total_venta, 2) ?? 0.00),
                );
                $pdf->SetFont('hypermarket', '', 8);
                $size = $pdf->addLine($y - 4, $line) ?? 0;

                $ultimoNumComprobante = $reg3_2->num_comprobante; // Actualizamos el último num_comprobante impreso

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

                $cantidadTotal += ($reg3_2->cantidad ?? 0.00);
                $ventaTotal += ($reg3_2->total_venta ?? 0.00);
            }

            $reg3_2 = $rspta3_2->fetch_object();
        }

        # Tabla para los totales de las ventas (TOTALES) #

        # TOTAL #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotal = array(
            "DOCUMENTO" => "",
            mb_convert_encoding(mb_strtoupper("N° DOCUMENTO"), 'ISO-8859-1', 'UTF-8') => "TOTAL",
            "CANTIDAD" => $cantidadTotal,
            "TOTAL VENTA" => number_format($ventaTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeSubtotal = $pdf->addLine($y, $lineTotal) ?? 0;

        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("APERTURAS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 18;

        # Tabla para los detalles de la caja aperturada #
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => 24,
            "FECHA Y HORA" => 22,
            "MONTO INICIAL" => 18,
        );

        $aligns = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO INICIAL" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO INICIAL" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $montoTotal = 0;
        $montoTotalAcumulado = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = $rspta6->num_rows;
        $anchoColumna = 24;

        while ($reg6) {
            $anchoTexto = $pdf->GetStringWidth($reg6->caja ?? '');

            $line = array(
                mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => ($reg6->caja ?? ''),
                "FECHA Y HORA" => ($reg6->fecha ?? 0.00),
                "MONTO INICIAL" => (number_format($reg6->monto, 2) ?? 0.00),
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y - 4, $line) ?? 0;

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

            $montoTotal += ($reg6->monto ?? 0.00);
            $montoTotalAcumulado += ($reg6->monto_total ?? 0.00);

            $reg6 = $rspta6->fetch_object();
        }

        # Tabla para los totales de la caja aperturada (MONTOS) #

        # TOTAL ACUMULADO #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotalAcumulado = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "",
            "FECHA Y HORA" => "TOTAL ACUMULADO",
            "MONTO INICIAL" => number_format($montoTotalAcumulado - $montoTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeTotalAcumulado = $pdf->addLine($y, $lineTotalAcumulado) ?? 0;

        $y += $sizeTotalAcumulado + 2;

        # TOTAL #
        $lineTotal = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "",
            "FECHA Y HORA" => "TOTAL",
            "MONTO INICIAL" => number_format($montoTotalAcumulado, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeTotal = $pdf->addLine($y, $lineTotal);

        $pdf->addLineFormat($lineTotalAcumulado);
        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("RETIROS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 18;

        # Tabla para los detalles de los retiros #
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => 24,
            "FECHA Y HORA" => 22,
            "MONTO" => 18,
        );

        $aligns = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $montoTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = ($rspta7 !== false) ? $rspta7->num_rows : 0;
        $anchoColumna = 24;

        while ($reg7) {
            $anchoTexto = $pdf->GetStringWidth($reg7->caja ?? '');

            $line = array(
                mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => ($reg7->caja ?? ''),
                "FECHA Y HORA" => ($reg7->fecha ?? 0.00),
                "MONTO" => (number_format($reg7->monto_retiro, 2) ?? 0.00),
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y - 4, $line) ?? 0;

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

            $montoTotal += ($reg7->monto_retiro ?? 0.00);

            $reg7 = $rspta7->fetch_object();
        }

        # Tabla para los totales de la caja aperturada (MONTOS) #

        # TOTAL #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotal = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "",
            "FECHA Y HORA" => "TOTAL",
            "MONTO" => number_format($montoTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeSubtotal = $pdf->addLine($y, $lineTotal) ?? 0;

        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5.5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("GASTOS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 18;

        # Tabla para los detalles de los gastos #
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => 24,
            "FECHA Y HORA" => 22,
            "MONTO" => 18,
        );

        $aligns = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO" => "R",
        );

        $pdf->SetFont('hypermarket', '', 8.5);
        $pdf->addCols($cols, $aligns, $y);
        $cols = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "L",
            "FECHA Y HORA" => "L",
            "MONTO" => "R",
        );

        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        $y += 4;

        $montoTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = ($rspta8 !== false) ? $rspta8->num_rows : 0;
        $anchoColumna = 24;

        while ($reg8) {
            $anchoTexto = $pdf->GetStringWidth($reg8->caja ?? '');

            $line = array(
                mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => ($reg8->caja ?? ''),
                "FECHA Y HORA" => ($reg8->fecha ?? 0.00),
                "MONTO" => (number_format($reg8->monto_gasto, 2) ?? 0.00),
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y - 4, $line) ?? 0;

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

            $montoTotal += ($reg8->monto_gasto ?? 0.00);

            $reg8 = $rspta8->fetch_object();
        }

        # Tabla para los totales de la caja aperturada (MONTOS) #

        # TOTAL #
        $y += ($size - 4) ?? 0;
        $pdf->Line(3, $y - 2.1, 67, $y - 2.1);

        $lineTotal = array(
            mb_convert_encoding(mb_strtoupper("DESCRIPCIÓN"), 'ISO-8859-1', 'UTF-8') => "",
            "FECHA Y HORA" => "TOTAL",
            "MONTO" => number_format($montoTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeSubtotal = $pdf->addLine($y, $lineTotal) ?? 0;

        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');


        # TÍTULO #
        $pdf->SetY($y + 5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("VENTAS POR FORMA DE PAGO"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y += 17.5;

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
        $vueltoTotal = 0;

        $esUltimoBucle = false;
        $hizoSaltoLinea = false;
        $contador = 0;

        $totalRegistros = $rspta5->num_rows;
        $anchoColumna = 49;

        // Array para almacenar los vueltos por idventa
        $vueltosPorVenta = [];
        $idsProcesados = []; // Array para almacenar los idventa ya procesados

        while ($reg5) {
            $idventa = $reg5->idventa;

            $anchoTexto = $pdf->GetStringWidth($reg5->metodo_pago ?? '');

            // Verificar si el idventa ya está en el array de ids procesados
            if (!in_array($idventa, $idsProcesados)) {
                if (isset($vueltosPorVenta[$idventa])) {
                    $vueltosPorVenta[$idventa] += ($reg5->vuelto ?? 0.00);
                } else {
                    $vueltosPorVenta[$idventa] = ($reg5->vuelto ?? 0.00);
                }

                $idsProcesados[] = $idventa; // Agregar idventa al array de ids procesados
            }

            $line = array(
                "METODO PAGO" => ($reg5->metodo_pago ?? ''),
                "MONTO" => (number_format($reg5->monto_total, 2) ?? 0.00),
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y - 4, $line) ?? 0;

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

            $montoTotal += ($reg5->monto_total ?? 0.00);

            $reg5 = $rspta5->fetch_object();
        }

        // Sumar los vueltos totales
        $vueltoTotal = array_sum($vueltosPorVenta);

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
            "MONTO" => number_format($vueltoTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeVuelto = $pdf->addLine($y, $lineVuelto);

        $y += $sizeVuelto + 2;

        # TOTAL #
        $lineTotal = array(
            "METODO PAGO" => "TOTAL",
            "MONTO" => number_format($montoTotal - $vueltoTotal, 2),
        );

        $pdf->SetFont('hypermarket', '', 8);
        $sizeTotal = $pdf->addLine($y, $lineTotal);

        $pdf->addLineFormat($lineVuelto);
        $pdf->addLineFormat($lineSubtotal);
        $pdf->addLineFormat($lineTotal);

        $pdf->SetFont('hypermarket', '', 10);
        $pdf->Ln(3);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetX(1.5);
        $pdf->Cell(0, -2, utf8_decode("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"), 0, 0, 'L');

        # TÍTULO #
        $pdf->SetY($y + 5);
        $pdf->SetFont('hypermarket', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("DETALLE DE PRODUCTOS"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
        $pdf->Ln(3);

        $y -= 7;

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
        $anchoColumna = 20;

        $totalIGV = 0;

        while ($reg2) {
            $subtotal = ($reg2->cantidad * $reg2->precio_venta) - $reg2->descuento;

            $textoProducto = utf8_decode(($reg2->articulo != "") ? mb_strtoupper($reg2->articulo) : mb_strtoupper($reg2->servicio));
            $anchoTexto = $pdf->GetStringWidth($textoProducto);

            $line = array(
                "PRODUCTO" => $textoProducto,
                "CANTIDAD" => "$reg2->cantidad",
                "P.U." => number_format($reg2->precio_venta, 2),
                "DSCTO" => number_format($reg2->descuento, 2),
                "SUBTOTAL" => "" . number_format($subtotal, 2) . ""
            );
            $pdf->SetFont('hypermarket', '', 8);
            $size = $pdf->addLine($y, $line) ?? 0;

            $igv = 0;
            if ($reg2->impuesto == 0.18) {
                $igv = $subtotal * 0.18;
                $totalIGV += $igv;
            }

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

            $totalSubtotal += $subtotal;
            $totalProductos++;
            $totalUnidades += $reg2->cantidad ?? 0;

            $reg2 = $rspta2->fetch_object();
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
            "SUBTOTAL" => number_format(($totalIGV), 2)
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
            "SUBTOTAL" => number_format($totalSubtotal + $totalIGV, 2)
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
        $total_venta = number_format($totalSubtotal + $totalIGV ?? 0.00, 2);

        // Eliminar caracteres no numéricos
        $total_venta_numeric = (float) str_replace(',', '', $total_venta);

        $izquierda = floor($total_venta_numeric);
        $derecha = round(($total_venta_numeric - $izquierda) * 100);

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

        $y += 17;

        # Pie del ticket #
        $y = $pdf->pieCierre(
            $y,
            $reg1->nombre ?? '',
        );

        $y += 4;

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
        $pdf->Output("I", "ticket_cierre_" . mt_rand(10000000, 99999999) . ".pdf", true);
    } else {
        require 'noacceso.php';
    }
}

ob_end_flush();
