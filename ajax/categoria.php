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
	if ($_SESSION['almacen'] == 1) {
		require_once "../modelos/Categoria.php";

		$categorias = new Categoria();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idcategoria)) {
					$nombreExiste = $categorias->verificarNombreExiste($titulo);
					if ($nombreExiste) {
						echo "El nombre de la categoría ya existe.";
					} else {
						$rspta = $categorias->agregar($idusuario, $titulo, $descripcion);
						echo $rspta ? "Categoria registrada" : "La categoría no se pudo registrar";
					}
				} else {
					$rspta = $categorias->editar($idcategoria, $titulo, $descripcion);
					echo $rspta ? "Categoria actualizada" : "La categoría no se pudo actualizar";
				}
				break;

			case 'desactivar':
				$rspta = $categorias->desactivar($idcategoria);
				echo $rspta ? "Categoria desactivada" : "La categoría no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $categorias->activar($idcategoria);
				echo $rspta ? "Categoria activada" : "La categoría no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $categorias->eliminar($idcategoria);
				echo $rspta ? "Categoria eliminado" : "La categoría no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $categorias->mostrar($idcategoria);
				echo json_encode($rspta);
				break;

			case 'listar':

				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $categorias->listar();
				} else {
					$rspta = $categorias->listarPorUsuario($idusuario);
				}

				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
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
					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							(($reg->estado == 'activado') ?
								(('<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idcategoria . ')"><i class="fa fa-pencil"></i></button>')) .
								(('<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idcategoria . ')"><i class="fa fa-close"></i></button>')) .
								(('<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idcategoria . ')"><i class="fa fa-trash"></i></button>')) : (('<button class="btn btn-warning" style="margin-right: 3px;" onclick="mostrar(' . $reg->idcategoria . ')"><i class="fa fa-pencil"></i></button>')) .
								(('<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idcategoria . ')"><i class="fa fa-check"></i></button>')) .
								(('<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idcategoria . ')"><i class="fa fa-trash"></i></button>'))) . '</div>',
						"1" => ucwords($reg->nombre),
						"2" => ucwords($cargo_detalle),
						"3" => $reg->titulo,
						"4" => $reg->descripcion,
						"5" => $reg->fecha,
						"6" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
							'<span class="label bg-red">Desactivado</span>'
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

			case 'selectCategoria':
				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $categorias->listar();
				} else {
					$rspta = $categorias->listarPorUsuario($idusuario);
				}

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idcategoria . '"> ' . $reg->titulo . ' - ' . $reg->nombre . '</option>';
				}
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
