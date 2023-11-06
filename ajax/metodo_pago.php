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
	if ($_SESSION['pagos'] == 1) {
		require_once "../modelos/Metodo_pago.php";

		$metodo_pago = new MetodoPago();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idmetodopago = isset($_POST["idmetodopago"]) ? limpiarCadena($_POST["idmetodopago"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idmetodopago)) {
					// $nombreExiste = $metodo_pago->verificarNombreExiste($titulo);
					// if ($nombreExiste) {
					// 	echo "El nombre del método de pago ya existe.";
					// } else {
						$rspta = $metodo_pago->agregar($idusuario, $titulo, $descripcion);
						echo $rspta ? "Método de pago registrado" : "El método de pago no se pudo registrar";
					// }
				} else {
					$nombreExiste = $metodo_pago->verificarNombreEditarExiste($titulo, $idmetodopago);
					if ($nombreExiste) {
						echo "El nombre del método de pago ya existe.";
					} else {
						$rspta = $metodo_pago->editar($idmetodopago, $titulo, $descripcion);
						echo $rspta ? "Método de pago actualizado" : "El método de pago no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $metodo_pago->desactivar($idmetodopago);
				echo $rspta ? "Método de pago desactivado" : "El método de pago no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $metodo_pago->activar($idmetodopago);
				echo $rspta ? "Método de pago activado" : "El método de pago no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $metodo_pago->eliminar($idmetodopago);
				echo $rspta ? "Método de pago eliminado" : "El método de pago no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $metodo_pago->mostrar($idmetodopago);
				echo json_encode($rspta);
				break;

			case 'listar':

				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $metodo_pago->listar();
				} else {
					$rspta = $metodo_pago->listarPorUsuario($idusuario);
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
								(('<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idmetodopago . ')"><i class="fa fa-pencil"></i></button>')) .
								(('<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idmetodopago . ')"><i class="fa fa-close"></i></button>')) .
								(('<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idmetodopago . ')"><i class="fa fa-trash"></i></button>')) : (('<button class="btn btn-warning" style="margin-right: 3px;" onclick="mostrar(' . $reg->idmetodopago . ')"><i class="fa fa-pencil"></i></button>')) .
								(('<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idmetodopago . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>')) .
								(('<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idmetodopago . ')"><i class="fa fa-trash"></i></button>'))) . '</div>',
						"1" => $reg->titulo,
						"2" => $reg->descripcion,
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

				// case 'selectMetodoPago':
				// 	if ($cargo == "superadmin" || $cargo == "admin") {
				// 		$rspta = $metodo_pago->listar();
				// 	} else {
				// 		$rspta = $metodo_pago->listarPorUsuario($idusuario);
				// 	}

				// 	echo '<option value="">- Seleccione -</option>';
				// 	while ($reg = $rspta->fetch_object()) {
				// 		echo '<option value="' . $reg->idmetodopago . '"> ' . $reg->titulo . ' - ' . $reg->nombre . '</option>';
				// 	}
				// 	break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
