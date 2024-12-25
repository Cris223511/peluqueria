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
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
			// Intentar leer el archivo Excel
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

			// Verificar si el archivo es un tipo de archivo válido
			$allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
			$fileMimeType = mime_content_type($file);
			if (!in_array($fileMimeType, $allowedMimeTypes)) {
				echo json_encode(["status" => "error", "message" => "El archivo no es un archivo Excel válido."]);
				exit();
			}

			// Verificar si la extensión del archivo es válido
			$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			if (!in_array(strtolower($ext), ['xls', 'xlsx'])) {
				echo json_encode(["status" => "error", "message" => "El archivo no tiene una extensión permitida."]);
				exit();
			}

			// Obtener los datos desde la fila 4 hasta que encuentre una fila vacía en la columna B
			$data = [];
			$row = 4;
			$maxRows = 200; // Límite de filas

			while (!empty($worksheet->getCell('B' . $row)->getValue())) {
				// Verificar si el número de filas supera el límite
				if ($row - 4 + 1 > $maxRows) { // Resta 4 para empezar desde la primera fila válida (B4)
					echo json_encode(["status" => "error", "message" => "El archivo excede el número máximo de filas permitido ($maxRows)."]);
					exit();
				}

				$data[] = array(
					"0" => '<span class="label bg-green">OK</span>',
					"1" => '<a href="../files/articulos/product.jpg" class="galleria-lightbox" style="z-index: 10000 !important;">
								<img src="../files/articulos/product.jpg" height="50px" width="50px" class="img-fluid">
							</a>',
					"2" => '<textarea name="nombre[]" class="form-control" maxlength="100" rows="2" placeholder="Ingrese el nombre del producto" required>' . $worksheet->getCell('B' . $row)->getValue() . '</textarea>',
					"3" => '<input type="number" name="idmedida[]" value="' . $worksheet->getCell('C' . $row)->getValue() . '" maxlength="6" class="form-control" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="U. de medida" required>',
					"4" => '<input type="number" name="idcategoria[]" value="' . $worksheet->getCell('D' . $row)->getValue() . '" maxlength="6" class="form-control" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Categoría">',
					"5" => '<input type="number" name="idlocal[]" value="' . $worksheet->getCell('E' . $row)->getValue() . '" maxlength="6" class="form-control" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Local" required>',
					"6" => '<input type="number" name="idmarca[]" value="' . $worksheet->getCell('F' . $row)->getValue() . '" maxlength="6" class="form-control" min="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" placeholder="Marca">',
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
					"20" => '<input type="date" name="fecha_emision[]" value="' . (!empty($worksheet->getCell('T' . $row)->getValue()) ? (PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($worksheet->getCell('T' . $row)) ? PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($worksheet->getCell('T' . $row)->getValue())->format('Y-m-d') : (DateTime::createFromFormat('d/m/Y', $worksheet->getCell('T' . $row)->getValue()) ? DateTime::createFromFormat('d/m/Y', $worksheet->getCell('T' . $row)->getValue())->format('Y-m-d') : '')) : '') . '" class="form-control">',
					"21" => '<input type="date" name="fecha_vencimiento[]" value="' . (!empty($worksheet->getCell('U' . $row)->getValue()) ? (PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($worksheet->getCell('U' . $row)) ? PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($worksheet->getCell('U' . $row)->getValue())->format('Y-m-d') : (DateTime::createFromFormat('d/m/Y', $worksheet->getCell('U' . $row)->getValue()) ? DateTime::createFromFormat('d/m/Y', $worksheet->getCell('U' . $row)->getValue())->format('Y-m-d') : '')) : '') . '" class="form-control">',
					"22" => '<textarea name="item_1[]" class="form-control" maxlength="10000" rows="2" placeholder="Item 1">' . $worksheet->getCell('V' . $row)->getValue() . '</textarea>',
					"23" => '<textarea name="item_2[]" class="form-control" maxlength="10000" rows="2" placeholder="Item 2">' . $worksheet->getCell('W' . $row)->getValue() . '</textarea>',
					"24" => '<input type="text" value="' . $_SESSION["nombre"] . '" class="form-control" readonly>',
					"25" => '<input type="text" value="' . getCargo($_SESSION["cargo"]) . '" class="form-control" readonly>',
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
		} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
			// Capturar excepciones relacionadas con la lectura del archivo Excel
			echo json_encode(["status" => "error", "message" => "El archivo Excel está dañado o no es válido. Por favor, cargue un archivo válido."]);
			exit();
		} catch (\Exception $e) {
			// Capturar cualquier otra excepción no prevista
			echo json_encode(["status" => "error", "message" => "Ocurrió un error inesperado: " . $e->getMessage()]);
			exit();
		}
		break;

	default:
		echo "Operación no válida.";
		break;
}
ob_end_flush();
