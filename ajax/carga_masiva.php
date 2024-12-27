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

$idusuario = $_SESSION["idusuario"];
$idlocalSession = $_SESSION["idlocal"];
$cargo = $_SESSION["cargo"];

switch ($_GET["op"]) {

		/* ===================  CARGAR DATOS EN EL MODAL DE AYUDA ====================== */

	case 'categorias':
		$rspta = $carga_masiva->listarCategorias();
		$data = array();

		foreach ($rspta as $reg) {
			$data[] = array(
				"0" => "<strong> {$reg['id']}</strong>",
				"1" =>  $reg["titulo"],
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg["descripcion"] == '') ? 'Sin registrar.' :  $reg["descripcion"]) . "</textarea>"
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

		foreach ($rspta as $reg) {
			$data[] = array(
				"0" => "<strong> {$reg['id']}</strong>",
				"1" => '<a href="../files/locales/' .  $reg["imagen"] . '" class="galleria-lightbox">
							<img src="../files/locales/' .  $reg["imagen"] . '" height="50px" width="50px" class="img-fluid">
						</a>',
				"2" =>  $reg["titulo"],
				"3" => ($reg["local_ruc"] ? "N° " .  $reg["local_ruc"] : "Sin registrar"),
				"4" => ($reg["empresa"] ?  $reg["empresa"] : "Sin registrar"),
				"5" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg["descripcion"] == '') ? 'Sin registrar.' :  $reg["descripcion"]) . "</textarea>"
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

		foreach ($rspta as $reg) {
			$data[] = array(
				"0" => "<strong> {$reg['id']}</strong>",
				"1" =>  $reg["titulo"],
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg["descripcion"] == '') ? 'Sin registrar.' :  $reg["descripcion"]) . "</textarea>"
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

		foreach ($rspta as $reg) {
			$data[] = array(
				"0" => "<strong> {$reg['id']}</strong>",
				"1" =>  $reg["titulo"],
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;' readonly>"
					. (($reg["descripcion"] == '') ? 'Sin registrar.' :  $reg["descripcion"]) . "</textarea>"
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
					"0" => '<button type="button" class="btn btn-bcp" onclick="validarFila(this)" style="width: 30px; height: 30px; border-radius: 50%;"><i style="margin-left: -4px" class="fa fa-eye"></i></button>',
					"1" => '<a href="../files/articulos/product.jpg" class="galleria-lightbox" style="z-index: 10000 !important;">
								<img src="../files/articulos/product.jpg" height="50px" width="50px" class="img-fluid">
							</a>',
					"2" => '<textarea name="nombre[]" class="form-control" maxlength="100" rows="2" placeholder="Ingrese el nombre del producto" required>' . $worksheet->getCell('B' . $row)->getValue() . '</textarea>',
					"3" => '<input type="number" name="idmedida[]" value="' . (is_numeric($worksheet->getCell('C' . $row)->getValue()) ? (int)$worksheet->getCell('C' . $row)->getValue() : 0) . '" maxlength="6" class="form-control" min="0" placeholder="U. de medida" required>',
					"4" => '<input type="number" name="idcategoria[]" value="' . (is_numeric($worksheet->getCell('D' . $row)->getValue()) ? (int)$worksheet->getCell('D' . $row)->getValue() : 0) . '" maxlength="6" class="form-control" min="0" placeholder="Categoría">',
					"5" => '<input type="number" name="idlocal[]" value="' . (is_numeric($worksheet->getCell('E' . $row)->getValue()) ? (int)$worksheet->getCell('E' . $row)->getValue() : 0) . '" maxlength="6" class="form-control" min="0" placeholder="Local" required>',
					"6" => '<input type="number" name="idmarca[]" value="' . (is_numeric($worksheet->getCell('F' . $row)->getValue()) ? (int)$worksheet->getCell('F' . $row)->getValue() : 0) . '" maxlength="6" class="form-control" min="0" placeholder="Marca">',
					"7" => '<input type="text" name="codigo_producto[]" value="' . $worksheet->getCell('G' . $row)->getValue() . '" maxlength="15" class="form-control" placeholder="Código del producto" required>',
					"8" => '<input type="number" name="stock[]" value="' . (is_numeric($worksheet->getCell('H' . $row)->getValue()) ? number_format($worksheet->getCell('H' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="6" class="form-control" step="any" min="0" placeholder="Stock">',
					"9" => '<input type="number" name="stock_minimo[]" value="' . (is_numeric($worksheet->getCell('I' . $row)->getValue()) ? number_format($worksheet->getCell('I' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="6" class="form-control" step="any" min="0" placeholder="Stock mínimo">',
					"10" => '<input type="number" name="precio_venta[]" value="' . (is_numeric($worksheet->getCell('J' . $row)->getValue()) ? number_format($worksheet->getCell('J' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="8" class="form-control" step="any" min="0" oninput="calcularGanancia(this)" placeholder="Precio de venta">',
					"11" => '<input type="number" name="precio_compra[]" value="' . (is_numeric($worksheet->getCell('K' . $row)->getValue()) ? number_format($worksheet->getCell('K' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="8" class="form-control" step="any" min="0" oninput="calcularGanancia(this)" placeholder="Precio de compra">',
					"12" => '<input type="number" name="ganancia[]" value="' . (is_numeric($worksheet->getCell('J' . $row)->getValue()) && is_numeric($worksheet->getCell('K' . $row)->getValue()) ? number_format((float)$worksheet->getCell('J' . $row)->getValue() - (float)$worksheet->getCell('K' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="8" class="form-control" step="any" min="0" readonly>',
					"13" => '<input type="number" name="precio_venta_mayor[]" value="' . (is_numeric($worksheet->getCell('M' . $row)->getValue()) ? number_format($worksheet->getCell('M' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="8" class="form-control" step="any" min="0" placeholder="Precio mayorista">',
					"14" => '<input type="number" name="comision[]" value="' . (is_numeric($worksheet->getCell('N' . $row)->getValue()) ? number_format($worksheet->getCell('N' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="8" class="form-control" step="any" min="0" placeholder="Comisión">',
					"15" => '<input type="text" name="codigo_barra[]" value="' . (ctype_digit($worksheet->getCell('O' . $row)->getValue()) ? $worksheet->getCell('O' . $row)->getValue() : '') . '" maxlength="13" class="form-control" placeholder="Código de barra">',
					"16" => '<textarea name="descripcion[]" class="form-control" maxlength="10000" rows="2" placeholder="Descripción">' . $worksheet->getCell('P' . $row)->getValue() . '</textarea>',
					"17" => '<textarea name="talla[]" class="form-control" maxlength="10000" rows="2" placeholder="Talla">' . $worksheet->getCell('Q' . $row)->getValue() . '</textarea>',
					"18" => '<textarea name="color[]" class="form-control" maxlength="10000" rows="2" placeholder="Color">' . $worksheet->getCell('R' . $row)->getValue() . '</textarea>',
					"19" => '<input type="number" name="peso[]" value="' . (is_numeric($worksheet->getCell('S' . $row)->getValue()) ? number_format($worksheet->getCell('S' . $row)->getValue(), 2, '.', '') : '0.00') . '" maxlength="6" class="form-control" step="any" min="0" placeholder="Peso">',
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

		/* ===================  VALIDACIONES DE TODAS LAS FILAS DE LA TABLA ====================== */

	case 'validarFilasProductos':
		$data = json_decode(file_get_contents('php://input'), true);

		// Cargar datos para las validaciones
		$categorias = $carga_masiva->listarCategorias();
		$locales = $carga_masiva->listarLocales();
		$medidas = $carga_masiva->listarMedidas();
		$marcas = $carga_masiva->listarMarcas();

		// Crear listas para validación
		$categoriaIds = array_column($categorias, 'id');
		$localIds = array_column($locales, 'id');
		$medidaIds = array_column($medidas, 'id');
		$marcaIds = array_column($marcas, 'id');

		$errores = [];
		$codigosExistentes = []; // Para rastrear los códigos ya procesados

		foreach ($data as $index => $fila) {
			$filaErrores = [];

			// Validación del índice 2
			if (strlen($fila['nombre']) > 100) {
				$filaErrores[] = 2;
			}

			// Validación del índice 3
			if (!is_numeric($fila['idmedida']) || !in_array($fila['idmedida'], $medidaIds)) {
				$filaErrores[] = 3;
			}

			// Validación del índice 4
			if ($fila['idcategoria'] && (!is_numeric($fila['idcategoria']) || !in_array($fila['idcategoria'], $categoriaIds))) {
				$filaErrores[] = 4;
			}

			// Validación del índice 5
			if (
				!is_numeric($fila['idlocal']) || !in_array($fila['idlocal'], $localIds) ||
				($cargo !== 'superadmin' && $cargo !== 'admin_total' && $fila['idlocal'] != $idlocalSession)
			) {
				$filaErrores[] = 5;
			}

			// Validación del índice 6
			if ($fila['idmarca'] && (!is_numeric($fila['idmarca']) || !in_array($fila['idmarca'], $marcaIds))) {
				$filaErrores[] = 6;
			}

			// Validación del índice 7
			$codigoProducto = $fila['codigo_producto'];
			$idLocal = $fila['idlocal'];

			if (
				empty($codigoProducto) || strlen($codigoProducto) > 20 ||
				!preg_match('/^[A-Z]{3}\d{5}$/', $codigoProducto) ||
				$carga_masiva->verificarCodigoProductoExiste($codigoProducto, $idLocal) ||
				(isset($codigosExistentes[$idLocal]) && in_array($codigoProducto, $codigosExistentes[$idLocal]))
			) {
				$filaErrores[] = 7;
			}

			// Registrar el código procesado si es válido
			if (!isset($codigosExistentes[$idLocal])) {
				$codigosExistentes[$idLocal] = [];
			}
			$codigosExistentes[$idLocal][] = $codigoProducto;

			// Validaciones del índice 8 al 14
			foreach (range(8, 14) as $col) {
				if (!empty($fila['col' . $col]) && (!is_numeric($fila['col' . $col]) || $fila['col' . $col] < 0 || $fila['col' . $col] >= 99999999.99)) {
					$filaErrores[] = $col;
				}
			}

			// Validación del índice 15
			if (!empty($fila['codigo_barra']) && (!is_numeric($fila['codigo_barra']) || strlen($fila['codigo_barra']) > 13 ||
				$carga_masiva->verificarCodigoBarraExiste($fila['codigo_barra']))) {
				$filaErrores[] = 15;
			}

			// Validación del índice 19
			if (!empty($fila['peso']) && (!is_numeric($fila['peso']) || $fila['peso'] < 0 || $fila['peso'] >= 99999999.99)) {
				$filaErrores[] = 19;
			}

			// Validación del índice 20 y 21
			foreach ([20, 21] as $col) {
				if (!empty($fila['col' . $col]) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fila['col' . $col])) {
					$filaErrores[] = $col;
				}
			}

			// Registrar errores de la fila
			if (!empty($filaErrores)) {
				$errores[] = ['fila' => $index, 'columnas' => $filaErrores];
			}
		}

		echo json_encode(['status' => 'success', 'errores' => $errores]);
		break;

		/* ===================  VALIDACIONES DE SOLO UNA FILA DE LA TABLA ====================== */

	case 'validarFilaProducto':
		$datos = json_decode(file_get_contents('php://input'), true);

		$fila = $datos['fila']; // Fila específica a validar
		$todosCodigos = $datos['todosCodigos'];
		$filaIndex = $datos['filaIndex'];

		// Cargar datos para las validaciones
		$categorias = $carga_masiva->listarCategorias();
		$locales = $carga_masiva->listarLocales();
		$medidas = $carga_masiva->listarMedidas();
		$marcas = $carga_masiva->listarMarcas();

		// Crear listas para validación
		$categoriaIds = array_column($categorias, 'id');
		$localIds = array_column($locales, 'id');
		$medidaIds = array_column($medidas, 'id');
		$marcaIds = array_column($marcas, 'id');

		$errores = [];
		$mensajeErrores = [];

		// Validación del índice 2
		if (strlen($fila['nombre']) > 100) {
			$errores[] = 2;
			$mensajeErrores[] = "El nombre supera los 100 caracteres permitidos.";
		}

		// Validación del índice 3
		if (empty($fila['idmedida'])) {
			$errores[] = 3;
			$mensajeErrores[] = "La unidad de medida es obligatoria.";
		} elseif (!is_numeric($fila['idmedida'])) {
			$errores[] = 3;
			$mensajeErrores[] = "La unidad de medida debe ser un valor numérico.";
		} elseif (!in_array($fila['idmedida'], $medidaIds)) {
			$errores[] = 3;
			$mensajeErrores[] = "La unidad de medida no existe.";
		}

		// Validación del índice 4
		if (!empty($fila['idcategoria'])) {
			if (!is_numeric($fila['idcategoria'])) {
				$errores[] = 4;
				$mensajeErrores[] = "La categoría debe ser un valor numérico.";
			} elseif (!in_array($fila['idcategoria'], $categoriaIds)) {
				$errores[] = 4;
				$mensajeErrores[] = "La categoría no existe.";
			}
		}

		// Validación del índice 5
		if (empty($fila['idlocal'])) {
			$errores[] = 5;
			$mensajeErrores[] = "El local es obligatorio.";
		} elseif (!is_numeric($fila['idlocal'])) {
			$errores[] = 5;
			$mensajeErrores[] = "El local debe ser un valor numérico.";
		} elseif (!in_array($fila['idlocal'], $localIds)) {
			$errores[] = 5;
			$mensajeErrores[] = "El local no existe.";
		} elseif (
			$cargo !== 'superadmin' && $cargo !== 'admin_total' &&
			$fila['idlocal'] != $idlocalSession
		) {
			$errores[] = 5;
			$mensajeErrores[] = "No estás autorizado para utilizar este local.";
		}

		// Validación del índice 6
		if (!empty($fila['idmarca'])) {
			if (!is_numeric($fila['idmarca'])) {
				$errores[] = 6;
				$mensajeErrores[] = "La marca debe ser un valor numérico.";
			} elseif (!in_array($fila['idmarca'], $marcaIds)) {
				$errores[] = 6;
				$mensajeErrores[] = "La marca no existe.";
			}
		}

		// Validación del índice 7 (Código del producto)
		$codigoProducto = $fila['codigo_producto'];
		$idLocal = $fila['idlocal'];

		if (empty($codigoProducto)) {
			$errores[] = 7;
			$mensajeErrores[] = "El código del producto es obligatorio.";
		} elseif (strlen($codigoProducto) > 20) {
			$errores[] = 7;
			$mensajeErrores[] = "El código del producto no puede superar los 20 caracteres.";
		} elseif (!preg_match('/^[A-Z]{3}\d{5}$/', $codigoProducto)) {
			$errores[] = 7;
			$mensajeErrores[] = "El código del producto debe tener el formato 'XXX00000' (tres letras seguidas de cinco dígitos).";
		} else {
			$encontradoAnteriormente = false;

			// Verificar si ya existe en la base de datos
			if ($carga_masiva->verificarCodigoProductoExiste($codigoProducto, $idLocal)) {
				$errores[] = 7;
				$mensajeErrores[] = "El código del producto ya existe en la base de datos para este local.";
			}

			// Verificar si ya existe en las filas anteriores
			foreach ($todosCodigos as $index => $codigo) {
				if (
					$codigo['codigo_producto'] === $codigoProducto &&
					$codigo['idlocal'] === $idLocal &&
					$index < $filaIndex
				) {
					$encontradoAnteriormente = true;
					break;
				}
			}

			if ($encontradoAnteriormente) {
				$errores[] = 7;
				$mensajeErrores[] = "El código del producto ya fue registrado con el idlocal previamente en esta carga.";
			}
		}

		// Validaciones del índice 8 al 14 (Valores numéricos como stock, precios, etc.)
		foreach (range(8, 14) as $col) {
			if (!empty($fila['col' . $col])) {
				if (!is_numeric($fila['col' . $col])) {
					$errores[] = $col;
					$mensajeErrores[] = "El valor de la columna $col debe ser numérico.";
				} elseif ($fila['col' . $col] < 0) {
					$errores[] = $col;
					$mensajeErrores[] = "El valor de la columna $col no puede ser negativo.";
				} elseif ($fila['col' . $col] >= 99999999.99) {
					$errores[] = $col;
					$mensajeErrores[] = "El valor de la columna $col supera el límite permitido.";
				}
			}
		}

		// Validación del índice 15 (Código de barra)
		if (!empty($fila['codigo_barra'])) {
			if (!is_numeric($fila['codigo_barra'])) {
				$errores[] = 15;
				$mensajeErrores[] = "El código de barra debe ser numérico.";
			} elseif (strlen($fila['codigo_barra']) > 13) {
				$errores[] = 15;
				$mensajeErrores[] = "El código de barra no puede superar los 13 dígitos.";
			} elseif ($carga_masiva->verificarCodigoBarraExiste($fila['codigo_barra'])) {
				$errores[] = 15;
				$mensajeErrores[] = "El código de barra ya existe.";
			}
		}

		// Validación del índice 19 (Peso)
		if (!empty($fila['peso'])) {
			if (!is_numeric($fila['peso'])) {
				$errores[] = 19;
				$mensajeErrores[] = "El peso debe ser numérico.";
			} elseif ($fila['peso'] < 0) {
				$errores[] = 19;
				$mensajeErrores[] = "El peso no puede ser negativo.";
			} elseif ($fila['peso'] >= 99999999.99) {
				$errores[] = 19;
				$mensajeErrores[] = "El peso supera el límite permitido.";
			}
		}

		// Validación del índice 20 y 21 (Fechas)
		foreach ([20, 21] as $col) {
			if (!empty($fila['col' . $col]) && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fila['col' . $col])) {
				$errores[] = $col;
				$mensajeErrores[] = "La fecha de la columna $col no tiene un formato válido.";
			}
		}

		// Respuesta de validación
		if (!empty($errores)) {
			echo json_encode([
				'status' => 'error',
				'errores' => $errores,
				'mensajes' => $mensajeErrores
			]);
		} else {
			echo json_encode([
				'status' => 'success',
				'message' => 'La fila no tiene errores.'
			]);
		}
		break;

		/* =================== GUARDAR LOS PRODUCTOS DE LA FILA ====================== */

	case 'guardarProductos':
		// Decodificar los datos enviados desde el frontend
		$data = json_decode(file_get_contents('php://input'), true);

		// Inicialización
		$errores = [];
		$articulosGuardados = 0;

		$uploadDirectory = "../files/articulos/";
		$imagenPorDefecto = "product.jpg"; // Imagen predeterminada

		foreach ($data as $index => $fila) {
			$idlocal = $fila['idlocal'];
			$codigo_producto = $fila['codigo_producto'];
			$codigo_barra = isset($fila['codigo_barra']) ? $fila['codigo_barra'] : "";

			// Asignar la imagen por defecto
			$imagen = $imagenPorDefecto;

			// Validaciones previas al guardado
			if ($carga_masiva->verificarCodigoProductoExiste($codigo_producto, $idlocal)) {
				$errores[] = [
					'fila' => $index,
					'mensaje' => "El código del producto $codigo_producto ya existe en el local $idlocal."
				];
				continue;
			}

			if (!empty($codigo_barra) && $carga_masiva->verificarCodigoBarraExiste($codigo_barra)) {
				$errores[] = [
					'fila' => $index,
					'mensaje' => "El código de barra $codigo_barra ya existe en la base de datos."
				];
				continue;
			}

			// Verificar si se envió una imagen y procesarla
			if (!empty($fila['imagen']) && strpos($fila['imagen'], 'data:image') === 0) {
				$fileInfo = explode(',', $fila['imagen']);
				$decodedImage = base64_decode($fileInfo[1]);
				$fileExtension = strtolower(explode('/', mime_content_type($fila['imagen']))[1]);
				$newFileName = sprintf("%09d", rand(0, 999999999)) . '.' . $fileExtension;

				// Validar extensión permitida
				$allowedExtensions = ['jpg', 'jpeg', 'png', 'jfif', 'bmp'];
				if (in_array($fileExtension, $allowedExtensions)) {
					$targetFile = $uploadDirectory . $newFileName;
					if (file_put_contents($targetFile, $decodedImage)) {
						$imagen = $newFileName; // Actualizar la imagen si se guarda correctamente
					} else {
						$errores[] = [
							'fila' => $index,
							'mensaje' => "Error al guardar la imagen del producto $codigo_producto."
						];
						continue;
					}
				} else {
					$errores[] = [
						'fila' => $index,
						'mensaje' => "Formato de imagen no permitido para el producto $codigo_producto."
					];
					continue;
				}
			}

			// Intentar insertar el producto
			$rspta = $carga_masiva->insertarProductosCargaMasiva(
				$idusuario,
				isset($fila['idcategoria']) ? $fila['idcategoria'] : null,
				$idlocal,
				isset($fila['idmarca']) ? $fila['idmarca'] : null,
				isset($fila['idmedida']) ? $fila['idmedida'] : null,
				$codigo_barra,
				$codigo_producto,
				$fila['nombre'],
				isset($fila['stock']) ? $fila['stock'] : 0,
				isset($fila['stock_minimo']) ? $fila['stock_minimo'] : 0,
				isset($fila['descripcion']) ? $fila['descripcion'] : "",
				isset($fila['talla']) ? $fila['talla'] : "",
				isset($fila['color']) ? $fila['color'] : "",
				isset($fila['peso']) ? $fila['peso'] : 0,
				isset($fila['fecha_emision']) ? $fila['fecha_emision'] : null,
				isset($fila['fecha_vencimiento']) ? $fila['fecha_vencimiento'] : null,
				isset($fila['item_1']) ? $fila['item_1'] : "",
				isset($fila['item_2']) ? $fila['item_2'] : "",
				$imagen,
				isset($fila['precio_compra']) ? $fila['precio_compra'] : 0,
				isset($fila['precio_venta']) ? $fila['precio_venta'] : 0,
				isset($fila['precio_venta_mayor']) ? $fila['precio_venta_mayor'] : 0,
				isset($fila['ganancia']) ? $fila['ganancia'] : 0,
				isset($fila['comision']) ? $fila['comision'] : 0
			);

			// Resultado del intento de inserción
			if ($rspta) {
				$articulosGuardados++;
			} else {
				$errores[] = [
					'fila' => $index,
					'mensaje' => "Error al guardar el producto $codigo_producto en el local $idlocal."
				];
			}
		}

		// Responder al cliente
		if (count($errores) > 0) {
			echo json_encode([
				'status' => 'error',
				'message' => "$articulosGuardados productos guardados correctamente, pero hubo errores en algunas filas.",
				'errores' => $errores
			]);
		} else {
			echo json_encode([
				'status' => 'success',
				'message' => "$articulosGuardados productos guardados correctamente."
			]);
		}
		break;

	default:
		echo "Operación no válida.";
		break;
}
ob_end_flush();
