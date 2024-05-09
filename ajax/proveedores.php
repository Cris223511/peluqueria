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
	if ($_SESSION['compras'] == 1) {
		require_once "../modelos/Proveedores.php";

		$proveedores = new Proveedor();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idproveedor = isset($_POST["idproveedor"]) ? limpiarCadena($_POST["idproveedor"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
		$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
		$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
		$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idproveedor)) {
					$rspta = $proveedores->agregar($idusuario, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email);
					if (is_numeric($rspta)) {
						echo $rspta;
					} else {
						echo "El proveedor no se pudo registrar";
					}
				} else {
					$rspta = $proveedores->editar($idproveedor, $nombre, $tipo_documento, $num_documento, $direccion, $descripcion, $telefono, $email);
					echo $rspta ? "Proveedor actualizado" : "El proveedor no se pudo actualizar";
				}
				break;

			case 'desactivar':
				$rspta = $proveedores->desactivar($idproveedor);
				echo $rspta ? "Proveedor desactivado" : "El proveedor no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $proveedores->activar($idproveedor);
				echo $rspta ? "Proveedor activado" : "El proveedor no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $proveedores->eliminar($idproveedor);
				echo $rspta ? "Proveedor eliminado" : "El proveedor no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $proveedores->mostrar($idproveedor);
				echo json_encode($rspta);
				break;

			case 'listar':
				$rspta = $proveedores->listarProveedores();

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

					$telefono = ($reg->telefono == '') ? 'Sin registrar' : number_format($reg->telefono, 0, '', ' ');
					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							'<button class="btn btn-bcp" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalComprasProveedor(' . $reg->idproveedor . ', \'' . $reg->nombre . '\', \'' . $reg->tipo_documento . '\', \'' . $reg->num_documento . '\')"><i class="fa fa-file-text"></i></button>' .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idproveedor . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado == 'activado') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idproveedor . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idproveedor . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idproveedor . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => ucwords($reg->nombre),
						"2" => $reg->tipo_documento,
						"3" => $reg->num_documento,
						"4" => ($reg->direccion == "") ? "Sin registrar" : $reg->direccion,
						"5" => $telefono,
						"6" => ($reg->email == "") ? "Sin registrar" : $reg->email,
						"7" => ($reg->descripcion == "") ? "Sin registrar" : $reg->descripcion,
						"8" => ucwords($reg->usuario),
						"9" => ucwords($cargo_detalle),
						"10" => $reg->fecha,
						"11" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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

			case 'listarComprasProveedor':
				$idproveedor = $_GET["idproveedor"];

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $proveedores->listarComprasProveedor($idproveedor);
				} else {
					$rspta = $proveedores->listarComprasProveedorhaLocal($idproveedor, $idlocal_session);
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

				$firstIteration = true;
				$totalPrecioCompra = 0;

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
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<a data-toggle="modal" href="#myModal2"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idcompra . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->proveedor . '\', \'' . $reg->proveedor_tipo_documento . '\', \'' . $reg->proveedor_num_documento . '\', \'' . $reg->proveedor_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_compra . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->local,
						"3" => $reg->tipo_comprobante,
						"4" => 'N° ' . $reg->num_comprobante,
						"5" => $reg->total_compra,
						"6" => $reg->usuario . ' - ' . $cargo_detalle,
						"7" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioCompra += $reg->total_compra;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "<strong>TOTAL</strong>",
						"5" => '<strong>' . number_format($totalPrecioCompra, 2) . '</strong>',
						"6" => "",
						"7" => "",
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
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
