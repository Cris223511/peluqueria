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

require_once "../modelos/Medidas.php";

$medidas = new Medida();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$cargo = $_SESSION["cargo"];

$idmedida = isset($_POST["idmedida"]) ? limpiarCadena($_POST["idmedida"]) : "";
$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (empty($idmedida)) {
			$nombreExiste = $medidas->verificarNombreExiste($titulo);
			if ($nombreExiste) {
				echo "El nombre de la medida ya existe.";
			} else {
				$rspta = $medidas->agregar($idusuario, $titulo, $descripcion);
				echo $rspta ? "Medida registrada" : "La medida no se pudo registrar";
			}
		} else {
			$nombreExiste = $medidas->verificarNombreEditarExiste($titulo, $idmedida);
			if ($nombreExiste) {
				echo "El nombre de la medida ya existe.";
			} else {
				$rspta = $medidas->editar($idmedida, $titulo, $descripcion);
				echo $rspta ? "Medida actualizada" : "La medida no se pudo actualizar";
			}
		}
		break;

	case 'desactivar':
		$rspta = $medidas->desactivar($idmedida);
		echo $rspta ? "Medida desactivada" : "La medida no se pudo desactivar";
		break;

	case 'activar':
		$rspta = $medidas->activar($idmedida);
		echo $rspta ? "Medida activada" : "La medida no se pudo activar";
		break;

	case 'eliminar':
		$rspta = $medidas->eliminar($idmedida);
		echo $rspta ? "Medida eliminado" : "La medida no se pudo eliminar";
		break;

	case 'mostrar':
		$rspta = $medidas->mostrar($idmedida);
		echo json_encode($rspta);
		break;

	case 'listar':

		if ($cargo == "superadmin" || $cargo == "admin" || $cargo == "admin_total" || $cargo == "cajero") {
			$rspta = $medidas->listar();
		} else {
			$rspta = $medidas->listarPorUsuario($idusuario);
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
			$reg->descripcion = (strlen($reg->descripcion) > 100) ? substr($reg->descripcion, 0, 100) . "..." : $reg->descripcion;

			$data[] = array(
				"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
					(($reg->idmedida != "1" && $reg->idmedida != "2" && $reg->idmedida != "3") ?
						(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idmedida . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado == 'activado') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idmedida . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idmedida . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idmedida . ')"><i class="fa fa-trash"></i></button>')) : ("")) .
					'</div>',
				"1" => $reg->titulo,
				"2" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;'' readonly>" . (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>",
				"3" => ucwords($reg->nombre),
				"4" => ucwords($cargo_detalle),
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

		// case 'selectMedidas':
		// 	if ($cargo == "superadmin" || $cargo == "admin_total") {
		// 		$rspta = $medidas->listar();
		// 	} else {
		// 		$rspta = $medidas->listarPorUsuario($idusuario);
		// 	}

		// 	echo '<option value="">- Seleccione -</option>';
		// 	while ($reg = $rspta->fetch_object()) {
		// 		echo '<option value="' . $reg->idmedida . '"> ' . $reg->titulo . ' - ' . $reg->nombre . '</option>';
		// 	}
		// 	break;
}
ob_end_flush();
