<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['perfilu'] == 1) {

    require('PDF_MC_Table.php');

    $pdf = new PDF_MC_Table();

    $pdf->AddPage();

    $y_axis_initial = 25;

    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(45, 6, '', 0, 0, 'C');
    $pdf->Cell(100, 6, 'LISTA DE LOCALES DISPONIBLES', 1, 0, 'C');
    $pdf->Ln(10);

    $pdf->SetFillColor(232, 232, 232);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 6, utf8_decode('Local'), 1, 0, 'C', 1);
    $pdf->Cell(30, 6, utf8_decode('Empresa'), 1, 0, 'C', 1);
    $pdf->Cell(90, 6, utf8_decode('Descripción'), 1, 0, 'C', 1);
    $pdf->Cell(40, 6, utf8_decode('Fecha y hora'), 1, 0, 'C', 1);

    $pdf->Ln(10);
    require_once "../modelos/LocalesDisponibles.php";
    $locales = new LocalDisponible();

    $rspta = $locales->listarLocalesDisponibles();

    $pdf->SetWidths(array(30, 30, 90, 40));

    while ($reg = $rspta->fetch_object()) {
      $titulo = $reg->titulo;
      $empresa = $reg->empresa;
      $descripcion = $reg->descripcion;
      $fecha = $reg->fecha;

      $pdf->SetFont('Arial', '', 10);
      $pdf->Row(array(utf8_decode($titulo), utf8_decode($empresa), utf8_decode($descripcion), utf8_decode($fecha)));
    }

    $pdf->Output();

?>
<?php
  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }
}
ob_end_flush();
?>