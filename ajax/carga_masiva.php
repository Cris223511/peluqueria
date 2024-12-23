<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo']) && $_GET["op"] !== 'guardaryeditar') {
	session_unset();
	session_destroy();
	header("Location: ../vistas/login.html");
	exit();
}

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

require_once "../modelos/Carga_masiva.php";
$carga_masiva = new CargaMasiva();

$idlocalSession = $_SESSION["idlocal"];
$cargo = $_SESSION["cargo"];

switch ($_GET["op"]) {

		/* ===================  CARGAR DATOS EN EL MODAL DE AYUDA ====================== */

	case 'categorias':
		$rspta = $carga_masiva->listarCategorias();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => "<strong>$reg->id</strong>",
				"1" => $reg->titulo,
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>"
			);
		}

		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		echo json_encode($results);
		break;

	case 'locales':
		if ($cargo == "superadmin" || $cargo == "admin" || $cargo == "admin_total") {
			$rspta = $carga_masiva->listarLocales();
		} else {
			$rspta = $carga_masiva->listarLocalesPorUsuario($idlocalSession);
		}

		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => "<strong>$reg->id</strong>",
				"1" => '<a href="../files/locales/' . $reg->imagen . '" class="galleria-lightbox">
							<img src="../files/locales/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
						</a>',
				"2" => $reg->titulo,
				"3" => ($reg->local_ruc ? "N° " . $reg->local_ruc : "Sin registrar"),
				"4" => ($reg->empresa ? $reg->empresa : "Sin registrar"),
				"5" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>"
			);
		}

		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		echo json_encode($results);
		break;

	case 'marcas':
		$rspta = $carga_masiva->listarMarcas();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => "<strong>$reg->id</strong>",
				"1" => $reg->titulo,
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>"
			);
		}

		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		echo json_encode($results);
		break;

	case 'medidas':
		$rspta = $carga_masiva->listarMedidas();
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => "<strong>$reg->id</strong>",
				"1" => $reg->titulo,
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>"
			);
		}

		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		echo json_encode($results);
		break;

		/* ===================  DESCARGAR LA PLANTILLA ====================== */

	case 'descargarPlantilla':
		// Obtener el último código de producto
		$result = $carga_masiva->getLastCodigo($idlocalSession);
		$last_codigo = 'PRO0000'; // Valor por defecto

		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			if ($row && !empty($row["last_codigo"])) {
				$last_codigo = $row["last_codigo"];
			}
		}

		// Lógica para incrementar el número del código
		preg_match('/([A-Z]*)(\d+)$/', $last_codigo, $matches); // Divide el código en letras y números
		$prefix = $matches[1]; // Letras iniciales
		$number = (int)$matches[2]; // Parte numérica como entero
		$next_number = str_pad($number + 1, strlen($matches[2]), '0', STR_PAD_LEFT); // Suma 1 y conserva el formato
		$next_codigo = $prefix . $next_number; // Reconstruye el nuevo código

		try {
			// Leer el archivo original
			$inputFile = '../vistas/temp/plantilla.xlsx';
			$spreadsheet = IOFactory::load($inputFile);

			// Modificar las celdas
			$worksheet = $spreadsheet->getActiveSheet();
			$worksheet->setCellValue('G4', $next_codigo);
			$worksheet->setCellValue('E4', $idlocalSession);

			// Guardar una copia con el nuevo nombre
			$outputFile = '../vistas/temp/Plantilla de productos - Peluquería.xlsx';
			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save($outputFile);

			// Descargar el archivo
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="Plantilla de productos - Peluquería.xlsx"');
			readfile($outputFile);

			// Eliminar el archivo duplicado después de la descarga
			unlink($outputFile);
		} catch (Exception $e) {
			echo "Error al procesar la plantilla: " . $e->getMessage();
		}
		break;

		/* ===================  IMPORTAR LA PLANTILLA A LA TABLA ====================== */

	case 'importarProductos':

		// Función auxiliar para obtener el cargo
		function getCargo($cargo)
		{
			switch ($cargo) {
				case 'superadmin':
					return "Superadministrador";
				case 'admin_total':
					return "Admin Total";
				case 'admin':
					return "Administrador";
				case 'cajero':
					return "Cajero";
				default:
					return "Sin cargo";
			}
		}

		if (!isset($_FILES['file'])) {
			echo json_encode(["status" => "error", "message" => "No se recibió ningún archivo."]);
			exit();
		}

		$file = $_FILES['file']['tmp_name'];

		try {
			// Leer el archivo Excel
			$spreadsheet = IOFactory::load($file);
			$worksheet = $spreadsheet->getActiveSheet();

			// Validar que sea la plantilla correcta
			$plantillaTitulo = $worksheet->getCell('B1')->getValue();
			if ($plantillaTitulo !== "PLANTILLA PARA IMPORTACIÓN DE PRODUCTOS (COLOCAR EL MISMO FORMATO DEL EJEMPLO)") {
				echo json_encode(["status" => "error", "message" => "Usted no utilizó la plantilla correcta. Por favor, descargue la plantilla desde el sistema."]);
				exit();
			}

			// Verificar que hay al menos un producto registrado
			if (empty($worksheet->getCell('B4')->getValue())) {
				echo json_encode(["status" => "error", "message" => "Debe registrar al menos un producto en la plantilla antes de cargarla."]);
				exit();
			}

			// Obtener los datos desde la fila 4 hasta que encuentre una fila vacía en la columna B
			$data = [];
			$row = 4;

			while (!empty($worksheet->getCell('B' . $row)->getValue())) {
				$data[] = array(
					"0" => '<span class="label bg-green">OK</span>',
					"1" => '<a href="../files/articulos/product.jpg" class="galleria-lightbox" style="z-index: 10000 !important;">
								<img src="../files/articulos/product.jpg" height="50px" width="50px" class="img-fluid">
							</a>',
					"2" => '<textarea name="nombre[]" class="form-control" maxlength="100" rows="2" placeholder="Ingrese el nombre del producto" required>' . $worksheet->getCell('B' . $row)->getValue() . '</textarea>',
					"3" => '<textarea name="idmedida[]" class="form-control" maxlength="10" rows="2" placeholder="Unidad de medida" required>' . $worksheet->getCell('C' . $row)->getValue() . '</textarea>',
					"4" => '<textarea name="idcategoria[]" class="form-control" maxlength="10" rows="2" placeholder="Categoría">' . $worksheet->getCell('D' . $row)->getValue() . '</textarea>',
					"5" => '<textarea name="idlocal[]" class="form-control" maxlength="10" rows="2" placeholder="Local" required>' . $worksheet->getCell('E' . $row)->getValue() . '</textarea>',
					"6" => '<textarea name="idmarca[]" class="form-control" maxlength="10" rows="2" placeholder="Marca">' . $worksheet->getCell('F' . $row)->getValue() . '</textarea>',
					"7" => '<input type="text" name="codigo_producto[]" value="' . $worksheet->getCell('G' . $row)->getValue() . '" maxlength="15" class="form-control" placeholder="Código del producto" required>',
					"8" => '<input type="number" name="stock[]" value="' . number_format($worksheet->getCell('H' . $row)->getValue(), 2, '.', '') . '" maxlength="6" class="form-control" step="any" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Stock">',
					"9" => '<input type="number" name="stock_minimo[]" value="' . number_format($worksheet->getCell('I' . $row)->getValue(), 2, '.', '') . '" maxlength="6" class="form-control" step="any" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Stock mínimo">',
					"10" => '<input type="number" name="precio_venta[]" value="' . number_format($worksheet->getCell('J' . $row)->getValue(), 2, '.', '') . '" maxlength="8" class="form-control" step="any" min="0" oninput="calcularGanancia(this)" placeholder="Precio de venta">',
					"11" => '<input type="number" name="precio_compra[]" value="' . number_format($worksheet->getCell('K' . $row)->getValue(), 2, '.', '') . '" maxlength="8" class="form-control" step="any" min="0" oninput="calcularGanancia(this)" placeholder="Precio de compra">',
					"12" => '<input type="number" name="ganancia[]" value="' . number_format((float)$worksheet->getCell('J' . $row)->getValue() - (float)$worksheet->getCell('K' . $row)->getValue(), 2, '.', '') . '" maxlength="8" class="form-control" step="any" min="0" readonly>',
					"13" => '<input type="number" name="precio_venta_mayor[]" value="' . number_format($worksheet->getCell('M' . $row)->getValue(), 2, '.', '') . '" maxlength="8" class="form-control" step="any" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Precio mayorista">',
					"14" => '<input type="number" name="comision[]" value="' . number_format($worksheet->getCell('N' . $row)->getValue(), 2, '.', '') . '" maxlength="8" class="form-control" step="any" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Comisión">',
					"15" => '<input type="text" name="codigo_barra[]" value="' . $worksheet->getCell('O' . $row)->getValue() . '" maxlength="13" class="form-control" placeholder="Código de barra">',
					"16" => '<textarea name="descripcion[]" class="form-control" maxlength="10000" rows="2" placeholder="Descripción">' . $worksheet->getCell('P' . $row)->getValue() . '</textarea>',
					"17" => '<textarea name="talla[]" class="form-control" maxlength="10000" rows="2" placeholder="Talla">' . $worksheet->getCell('Q' . $row)->getValue() . '</textarea>',
					"18" => '<textarea name="color[]" class="form-control" maxlength="10000" rows="2" placeholder="Color">' . $worksheet->getCell('R' . $row)->getValue() . '</textarea>',
					"19" => '<input type="number" name="peso[]" value="' . number_format($worksheet->getCell('S' . $row)->getValue(), 2, '.', '') . '" maxlength="6" class="form-control" step="any" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Peso">',
					"20" => '<input type="date" name="fecha_emision[]" value="' . $worksheet->getCell('T' . $row)->getValue() . '" class="form-control">',
					"21" => '<input type="date" name="fecha_vencimiento[]" value="' . $worksheet->getCell('U' . $row)->getValue() . '" class="form-control">',
					"22" => '<textarea name="item_1[]" class="form-control" maxlength="10000" rows="2" placeholder="Item 1">' . $worksheet->getCell('V' . $row)->getValue() . '</textarea>',
					"23" => '<textarea name="item_2[]" class="form-control" maxlength="10000" rows="2" placeholder="Item 2">' . $worksheet->getCell('W' . $row)->getValue() . '</textarea>',
					"24" => '<input type="text" name="usuario[]" value="' . $_SESSION["nombre"] . '" class="form-control" readonly>',
					"25" => '<input type="text" name="cargo[]" value="' . getCargo($_SESSION["cargo"]) . '" class="form-control" readonly>',
				);
				$row++;
			}

			// Construir el formato esperado para DataTable
			$results = array(
				"sEcho" => 1,
				"iTotalRecords" => count($data),
				"iTotalDisplayRecords" => count($data),
				"aaData" => $data
			);

			echo json_encode($results);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(["status" => "error", "message" => "Error al procesar el archivo: " . $e->getMessage()]);
			exit();
		}
		break;

	default:
		echo "Operación no válida.";
		break;
}
ob_end_flush();
