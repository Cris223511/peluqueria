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

require_once "../modelos/Marcas.php";

$marcas = new Marca();

// Variables de sesión a utilizar.
$idusuario = $_SESSION["idusuario"];
$cargo = $_SESSION["cargo"];

$idmarca = isset($_POST["idmarca"]) ? limpiarCadena($_POST["idmarca"]) : "";
$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (empty($idmarca)) {
			$nombreExiste = $marcas->verificarNombreExiste($titulo);
			if ($nombreExiste) {
				echo "El nombre de la marca ya existe.";
			} else {
				$rspta = $marcas->agregar($idusuario, $titulo, $descripcion);
				echo $rspta ? "Marca registrada" : "La marca no se pudo registrar";
			}
		} else {
			$nombreExiste = $marcas->verificarNombreEditarExiste($titulo, $idmarca);
			if ($nombreExiste) {
				echo "El nombre de la marca ya existe.";
			} else {
				$rspta = $marcas->editar($idmarca, $titulo, $descripcion);
				echo $rspta ? "Marca actualizada" : "La marca no se pudo actualizar";
			}
		}
		break;

	case 'desactivar':
		$rspta = $marcas->desactivar($idmarca);
		echo $rspta ? "Marca desactivada" : "La marca no se pudo desactivar";
		break;

	case 'activar':
		$rspta = $marcas->activar($idmarca);
		echo $rspta ? "Marca activada" : "La marca no se pudo activar";
		break;

	case 'eliminar':
		$rspta = $marcas->eliminar($idmarca);
		echo $rspta ? "Marca eliminado" : "La marca no se pudo eliminar";
		break;

	case 'mostrar':
		$rspta = $marcas->mostrar($idmarca);
		echo json_encode($rspta);
		break;

	case 'listar':

		if ($cargo == "superadmin" || $cargo == "admin" || $cargo == "admin_total" || $cargo == "cajero") {
			$rspta = $marcas->listar();
		} else {
			$rspta = $marcas->listarPorUsuario($idusuario);
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
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idmarca . ')"><i class="fa fa-pencil"></i></button>') .
					(($reg->estado == 'activado') ?
						(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idmarca . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idmarca . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
					mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idmarca . ')"><i class="fa fa-trash"></i></button>') .
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

	case 'selectMarcas':
		$rspta = $marcas->listar();

		echo '<option value="">- Seleccione -</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value="' . $reg->idmarca . '"> ' . $reg->titulo . '</option>';
		}
		break;
}

ob_end_flush();
