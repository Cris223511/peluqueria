<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) && empty($_SESSION['cargo']) && $_GET["op"] !== 'listarTodosActivos' && $_GET["op"] !== 'guardaryeditar' && $_GET["op"] !== 'getLastCodigo') {
	session_unset();
	session_destroy();
	header("Location: ../vistas/login.html");
	exit();
}

require_once "../modelos/Articulo.php";

$articulo = new Articulo();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$idlocalSession = $_SESSION["idlocal"];
$cargo = $_SESSION["cargo"];

$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
$idmarca = isset($_POST["idmarca"]) ? limpiarCadena($_POST["idmarca"]) : "";
$idmedida = isset($_POST["idmedida"]) ? limpiarCadena($_POST["idmedida"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$codigo_producto = isset($_POST["codigo_producto"]) ? limpiarCadena($_POST["codigo_producto"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
$stock_minimo = isset($_POST["stock_minimo"]) ? limpiarCadena($_POST["stock_minimo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$talla = isset($_POST["talla"]) ? limpiarCadena($_POST["talla"]) : "";
$color = isset($_POST["color"]) ? limpiarCadena($_POST["color"]) : "";
$peso = isset($_POST["peso"]) ? limpiarCadena($_POST["peso"]) : "";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
$precio_compra = isset($_POST["precio_compra"]) ? limpiarCadena($_POST["precio_compra"]) : "";
$precio_venta = isset($_POST["precio_venta"]) ? limpiarCadena($_POST["precio_venta"]) : "";
$ganancia = isset($_POST["ganancia"]) ? limpiarCadena($_POST["ganancia"]) : "";
$comision = isset($_POST["comision"]) ? limpiarCadena($_POST["comision"]) : "";
$barra = isset($_POST["barra"]) ? limpiarCadena($_POST["barra"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':

		if (!empty($_FILES['imagen']['name'])) {
			$uploadDirectory = "../files/articulos/";

			$tempFile = $_FILES['imagen']['tmp_name'];
			$fileExtension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
			$newFileName = sprintf("%09d", rand(0, 999999999)) . '.' . $fileExtension;
			$targetFile = $uploadDirectory . $newFileName;

			// Verificar si es una imagen y mover el archivo
			$allowedExtensions = array('jpg', 'jpeg', 'png');
			if (in_array($fileExtension, $allowedExtensions) && move_uploaded_file($tempFile, $targetFile)) {
				// El archivo se ha movido correctamente, ahora $newFileName contiene el nombre del archivo
				$imagen = $newFileName;
			} else {
				// Error en la subida del archivo
				echo "Error al subir la imagen.";
				exit;
			}
		} else {
			// No se ha seleccionado ninguna imagen
			$imagen = $_POST["imagenactual"];
		}

		if (empty($idarticulo)) {
			$codigoExiste = $articulo->verificarCodigoExiste($codigo);
			$codigoProductoExiste = $articulo->verificarCodigoProductoExiste($codigo_producto, $idlocal);
			if ($codigoProductoExiste) {
				echo "El código del producto que ha ingresado ya existe en el local seleccionado.";
			} else if ($codigoExiste && $codigo != "") {
				echo "El código de barra del producto que ha ingresado ya existe.";
			} else {
				$rspta = $articulo->insertar($idusuario, $idcategoria, $idlocal, $idmarca, $idmedida, $codigo, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $talla, $color, $peso, $imagen, $precio_compra, $precio_venta, $ganancia,  $comision);
				echo $rspta ? "Producto registrado" : "El producto no se pudo registrar";
			}
		} else {
			$nombreExiste = $articulo->verificarCodigoProductoEditarExiste($codigo_producto, $idlocal, $idarticulo);
			if ($nombreExiste) {
				echo "El código del producto que ha ingresado ya existe en el local seleccionado.";
			} else {
				$rspta = $articulo->editar($idarticulo, $idcategoria, $idlocal, $idmarca, $idmedida, $codigo, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $talla, $color, $peso, $imagen, $precio_compra, $precio_venta, $ganancia,  $comision);
				echo $rspta ? "Producto actualizado" : "El producto no se pudo actualizar";
			}
		}
		break;

	case 'guardarComision':
		$rspta = $articulo->comisionArticulo($comision);
		echo $rspta ? "Comisión de productos modificados correctamente" : "Comisión de productos no se pudieron modificar";
		break;

	case 'desactivar':
		$rspta = $articulo->desactivar($idarticulo);
		echo $rspta ? "Producto desactivado" : "El producto no se puede desactivar";
		break;

	case 'activar':
		$rspta = $articulo->activar($idarticulo);
		echo $rspta ? "Producto activado" : "El producto no se puede activar";
		break;

	case 'eliminar':
		$rspta = $articulo->eliminar($idarticulo);
		echo $rspta ? "Producto eliminado" : "El producto no se puede eliminar";
		break;

	case 'mostrar':
		$rspta = $articulo->mostrar($idarticulo);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;

	case 'listar':
		$param1 = $_GET["param1"]; // valor marca
		$param2 = $_GET["param2"]; // valor categoria
		$param3 = $_GET["param3"]; // valor estado

		if ($param1 != '' && $param2 == '' && $param3 == '') {
			$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1'");
		} else if ($param1 == '' && $param2 != '' && $param3 == '') {
			$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idcategoria = '$param2'");
		} else if ($param1 == '' && $param2 == '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.stock = 0");
			}
		} else if ($param1 != '' && $param2 != '' && $param3 == '') {
			$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.idcategoria = '$param2'");
		} else if ($param1 != '' && $param2 == '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.stock = 0");
			}
		} else if ($param1 == '' && $param2 != '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idcategoria = '$param2' AND a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idcategoria = '$param2' AND a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idcategoria = '$param2' AND a.stock = 0");
			}
		} else if ($param1 != '' && $param2 != '' && $param3 != '') {
			if ($param3 == "1") {
				// Disponible
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.idcategoria = '$param2' AND a.stock > a.stock_minimo");
			} else if ($param3 == "2") {
				// Agotándose
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.idcategoria = '$param2' AND a.stock > 0 AND a.stock < a.stock_minimo");
			} else {
				// Agotado
				$rspta = $articulo->listarPorUsuarioParametro($idlocalSession, "a.idmarca = '$param1' AND a.idcategoria = '$param2' AND a.stock = 0");
			}
		} else {
			$rspta = $articulo->listarPorUsuario($idlocalSession);
		}

		$data = array();

		function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
		{
			if (($reg != "superadmin" && $reg != "admin_total") && $cargo == "admin") {
				return $buttonType;
			} elseif ($reg != "superadmin" && $cargo == "admin_total") {
				return $buttonType;
			} elseif ($cargo == "superadmin" || ($cargo == "cajero" && $idusuario == $_SESSION["idusuario"])) {
				return $buttonType;
			} else {
				return '';
			}
		}

		while ($reg = $rspta->fetch_object()) {
			$cargo_detalle = "";

			switch ($reg->cargo) {
				case 'superadmin':
					$cargo_detalle = "Superadministrador";
					break;
				case 'admin_total':
					$cargo_detalle = "Admin Total";
					break;
				case 'admin':
					$cargo_detalle = "Administrador";
					break;
				case 'cajero':
					$cargo_detalle = "Cajero";
					break;
				default:
					break;
			}

			$data[] = array(
				"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idarticulo . ')"><i class="fa fa-pencil"></i></button>') .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger "style="height: 35px;" onclick="eliminar(' . $reg->idarticulo . ')"><i class="fa fa-trash"></i></button>') .
					'</div>',
				"1" => '<a href="../files/articulos/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
									<img src="../files/articulos/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
								</a>',
				"2" => $reg->nombre,
				"3" => $reg->categoria,
				"4" => $reg->local,
				"5" => $reg->marca,
				"6" => $reg->codigo_producto,
				"7" => $reg->codigo,
				"8" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span style="color: #Ea9900; font-weight: bold">' . $reg->stock . '</span>' : (($reg->stock != '0') ? '<span>' . $reg->stock . '</span>' : '<span style="color: red; font-weight: bold">' . $reg->stock . '</span>'),
				"9" => $reg->stock_minimo,
				"10" => "S/. " . number_format($reg->precio_compra, 2, '.', ','),
				"11" => "S/. " . number_format($reg->precio_venta, 2, '.', ','),
				"12" => "S/. " . number_format($reg->ganancia, 2, '.', ','),
				"13" => "S/. " . number_format($reg->comision, 2, '.', ','),
				"14" => $reg->usuario,
				"15" => $cargo_detalle,
				"16" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span class="label bg-orange">agotandose</span>' : (($reg->stock != '0') ? '<span class="label bg-green">Disponible</span>' : '<span class="label bg-red">agotado</span>')
			);
		}
		$results = array(
			"sEcho" => 1, //Información para el datatables
			"iTotalRecords" => count($data), //enviamos el total registros al datatable
			"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;

	case 'getLastCodigo':
		if ($_POST["idlocal"] == 0 || $_POST["idlocal"] == "") {
			$result = $articulo->getLastCodigo($idlocalSession);
		} else {
			$result = $articulo->getLastCodigo($idlocal);
		}

		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$last_codigo = $row["last_codigo"];
		} else {
			$last_codigo = 'PRO0001';
		}
		echo $last_codigo;
		break;

		/* ======================= SELECTS ======================= */

	case 'listarTodosActivos':
		if ($cargo == "superadmin" || $cargo == "admin_total") {
			$rspta = $articulo->listarTodosActivos();
		} else {
			$rspta = $articulo->listarTodosActivosPorUsuario($idusuario, $idlocalSession);
		}

		$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

		$data = [];
		foreach ($result as $row) {
			$tabla = $row['tabla'];
			unset($row['tabla']);
			$data[$tabla][] = $row;
		}

		echo json_encode($data);
		break;
}

ob_end_flush();
