<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html");
} else {
	if ($_SESSION['servicios'] == 1) {
		require_once "../modelos/Servicios.php";

		$servicios = new Servicio();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idservicio = isset($_POST["idservicio"]) ? limpiarCadena($_POST["idservicio"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$codigo_barra = isset($_POST["codigo_barra"]) ? limpiarCadena($_POST["codigo_barra"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$costo = isset($_POST["costo"]) ? limpiarCadena($_POST["costo"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idservicio)) {
					$nombreExiste = $servicios->verificarNombreExiste($titulo);
					$codigoExiste = $servicios->verficarCodigoExiste($codigo);
					$codigoBarraExiste = $servicios->verificarCodigoBarraExiste($codigo_barra);

					if ($nombreExiste) {
						echo "El nombre del servicio ya existe.";
					} else if ($codigoExiste) {
						echo "El código del servicio ya existe.";
					} else if ($codigoBarraExiste) {
						echo "El código de barra del servicio que ha ingresado ya existe.";
					} else {
						$rspta = $servicios->agregar($idusuario, $titulo, $codigo, $codigo_barra, $descripcion, $costo);
						echo $rspta ? "Servicio registrado" : "El servicio no se pudo registrar";
					}
				} else {
					$nombreExiste = $servicios->verificarNombreEditarExiste($titulo, $idservicio);
					$codigoExiste = $servicios->verficarCodigoEditarExiste($codigo, $idservicio);
					$codigoBarraExiste = $servicios->verificarCodigoBarraEditarExiste($codigo_barra, $idservicio);
					if ($nombreExiste) {
						echo "El nombre del servicio ya existe.";
					} else if ($codigoExiste) {
						echo "El código del servicio ya existe.";
					} else if ($codigoBarraExiste) {
						echo "El código de barra del servicio que ha ingresado ya existe.";
					} else {
						$rspta = $servicios->editar($idservicio, $titulo, $codigo, $codigo_barra, $descripcion, $costo);
						echo $rspta ? "Servicio actualizado" : "El servicio no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $servicios->desactivar($idservicio);
				echo $rspta ? "Servicio desactivado" : "El servicio no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $servicios->activar($idservicio);
				echo $rspta ? "Servicio activado" : "El servicio no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $servicios->eliminar($idservicio);
				echo $rspta ? "Servicio eliminado" : "El servicio no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $servicios->mostrar($idservicio);
				echo json_encode($rspta);
				break;

			case 'listar':

				if ($cargo == "superadmin" || $cargo == "admin" || $cargo == "admin_total" || $cargo == "cajero") {
					$rspta = $servicios->listar();
				} else {
					$rspta = $servicios->listarPorUsuario($idusuario);
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
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idservicio . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado == 'activado') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idservicio . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idservicio . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idservicio . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => $reg->titulo,
						"2" => "N° " . $reg->codigo,
						"3" => ($reg->codigo_barra != "") ? $reg->codigo_barra : "Sin registrar.",
						"4" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;'' readonly>" . (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>",
						"5" => "S/. " . number_format($reg->costo, 2, '.', ','),
						"6" => ucwords($reg->nombre),
						"7" => ucwords($cargo_detalle),
						"8" => $reg->fecha,
						"9" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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

			case 'getLastCodigo':
				$result = $servicios->getLastCodigo();
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					$last_codigo = $row["last_codigo"];
				} else {
					$last_codigo = '00000';
				}
				echo $last_codigo;
				break;

				// case 'selectServicios':
				// 	if ($cargo == "superadmin" || $cargo == "admin_total") {
				// 		$rspta = $servicios->listar();
				// 	} else {
				// 		$rspta = $servicios->listarPorUsuario($idusuario);
				// 	}

				// 	echo '<option value="">- Seleccione -</option>';
				// 	while ($reg = $rspta->fetch_object()) {
				// 		echo '<option value="' . $reg->idservicio . '"> ' . $reg->titulo . ' - ' . $reg->nombre . '</option>';
				// 	}
				// 	break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
