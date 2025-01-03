<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['ventas'] == 1) {

    //Inlcuímos a la clase PDF_MC_Table
    require('PDF_MC_Table.php');

    //Instanciamos la clase para generar el documento pdf
    $pdf = new PDF_MC_Table();

    //Agregamos la primera página al documento pdf
    $pdf->AddPage();

    //Seteamos el inicio del margen superior en 25 pixeles 
    $y_axis_initial = 25;

    //Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(45, 6, '', 0, 0, 'C');
    $pdf->Cell(100, 6, 'LISTA DE VENTAS', 1, 0, 'C');
    $pdf->Ln(10);

    //Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
    $pdf->SetFillColor(232, 232, 232);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(37, 6, 'Fecha y hora', 1, 0, 'C', 1);
    $pdf->Cell(33, 6, 'Usuario', 1, 0, 'C', 1);
    $pdf->Cell(38, 6, 'Cliente', 1, 0, 'C', 1);
    $pdf->Cell(33, 6, 'Documento', 1, 0, 'C', 1);
    $pdf->Cell(25, 6, utf8_decode('Número'), 1, 0, 'C', 1);
    $pdf->Cell(25, 6, 'Total', 1, 0, 'C', 1);

    $pdf->Ln(10);
    //Comenzamos a crear las filas de los registros según la consulta mysql
    require_once "../modelos/Venta.php";
    $venta = new Venta();

    $idusuario = $_SESSION["idusuario"];
    $idlocalSession = $_SESSION["idlocal"];
    $cargo = $_SESSION["cargo"];

    if ($cargo == "superadmin" || $cargo == "admin_total") {
      $rspta = $venta->listar();
    } else {
      $rspta = $venta->listarPorUsuario($idlocalSession);
    }

    //Table with rows and columns
    $pdf->SetWidths(array(37, 33, 38, 33, 25, 25));

    while ($reg = $rspta->fetch_object()) {
      $fecha = $reg->fecha;
      $usuario = $reg->usuario . " " . $reg->apellido;
      $cliente = $reg->cliente;
      $tipo_comprobante = $reg->tipo_comprobante;
      $num_comprobante = $reg->num_comprobante;
      $total_venta = $reg->total_venta;

      $pdf->SetFont('Arial', '', 10);
      $pdf->Row(array($fecha, utf8_decode($usuario), utf8_decode($cliente), $tipo_comprobante, $num_comprobante, $total_venta . ' ' . (($reg->moneda == 'soles') ? 'S/.' : '$')));
    }

    //Mostramos el documento pdf
    $pdf->Output();

?>
<?php
  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }
}
ob_end_flush();
?>