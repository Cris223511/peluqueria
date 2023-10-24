<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

// si no está logeado o no tiene ningún cargo...
if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	// opciones a las que NO pueden tener acceso... si no colocamos ninguno, quiere decir
	// que tiene acceso a todas las opciones si es que está logeado o tiene un cargo.
	if (($_GET["op"] == 'selectLocal' || $_GET["op"] == 'selectLocalUsuario' || $_GET["op"] == 'selectLocalDisponible')) {
		echo 'No está autorizado para realizar esta acción.';
		exit();
	}
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['almacen'] == 1) {
		require_once "../modelos/Articulo.php";

		$articulo = new Articulo();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
		$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$idmarca = isset($_POST["idmarca"]) ? limpiarCadena($_POST["idmarca"]) : "";
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$codigo_producto = isset($_POST["codigo_producto"]) ? limpiarCadena($_POST["codigo_producto"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
		$stock_minimo = isset($_POST["stock_minimo"]) ? limpiarCadena($_POST["stock_minimo"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
		$barra = isset($_POST["barra"]) ? limpiarCadena($_POST["barra"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':

				if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
					$imagen = $_POST["imagenactual"];
				} else {
					$ext = explode(".", $_FILES["imagen"]["name"]);
					if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png") {
						$imagen = round(microtime(true)) . '.' . end($ext);
						move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/articulos/" . $imagen);
					}
				}
				if (empty($idarticulo)) {
					$codigoExiste = $articulo->verificarCodigoExiste($codigo);
					$codigoProductoExiste = $articulo->verificarCodigoProductoExiste($codigo_producto);
					if ($codigoProductoExiste) {
						echo "El código del producto que ha ingresado ya existe.";
					} else if ($codigoExiste && $codigo != "") {
						echo "El código de barra del producto que ha ingresado ya existe.";
					} else {
						$rspta = $articulo->insertar($idcategoria, $idlocal, $idmarca, $codigo, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $imagen);
						echo $rspta ? "producto registrado" : "El producto no se pudo registrar";
					}
				} else {
					$nombreExiste = $articulo->verificarCodigoProductoEditarExiste($codigo_producto, $idarticulo);
					if ($nombreExiste) {
						echo "El código del producto que ha ingresado ya existe.";
					} else {
						$rspta = $articulo->editar($idarticulo, $idcategoria, $idlocal, $idmarca, $codigo, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $imagen);
						echo $rspta ? "producto actualizado" : "El producto no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $articulo->desactivar($idarticulo);
				echo $rspta ? "producto desactivado" : "El producto no se puede desactivar";
				break;

			case 'activar':
				$rspta = $articulo->activar($idarticulo);
				echo $rspta ? "producto activado" : "El producto no se puede activar";
				break;

			case 'eliminar':
				$rspta = $articulo->eliminar($idarticulo);
				echo $rspta ? "producto eliminado" : "El producto no se puede eliminar";
				break;

			case 'mostrar':
				$rspta = $articulo->mostrar($idarticulo);
				//Codificar el resultado utilizando json
				echo json_encode($rspta);
				break;

			case 'listar':
				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $articulo->listar();
				} else {
					$rspta = $articulo->listarPorUsuario($idusuario);
				}

				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							('<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idarticulo . ')"><i class="fa fa-pencil"></i></button>') .
							('<button class="btn btn-danger "style="height: 35px;" onclick="eliminar(' . $reg->idarticulo . ')"><i class="fa fa-trash"></i></button>') . '</div>',
						"1" => $reg->nombre,
						"2" => $reg->categoria,
						"3" => $reg->local,
						"4" => $reg->marca,
						"5" => $reg->codigo_producto,
						"6" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span style="color: #Ea9900; font-weight: bold">' . $reg->stock . '</span>' : (($reg->stock != '0') ? '<span>' . $reg->stock . '</span>' : '<span style="color: red; font-weight: bold">' . $reg->stock . '</span>'),
						"7" => $reg->stock_minimo,
						"8" => $reg->precio_venta == '' ? "0.00" : $reg->precio_venta,
						"9" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px' >",
						"10" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span class="label bg-orange">agotandose</span>' : (($reg->stock != '0') ? '<span class="label bg-green">Disponible</span>' : '<span class="label bg-red">agotado</span>')
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

				/* ======================= SELECTS ======================= */

			case 'listarTodosActivos':
				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $articulo->listarTodosActivos();
				} else {
					$rspta = $articulo->listarTodosActivosPorUsuario($idusuario);
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
		//Fin de las validaciones de acceso
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
