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
	if ($_SESSION['cajas'] == 1) {
		require_once "../modelos/Cajas.php";

		$cajas = new Caja();

		// Variables de sesión a utilizar.
		$idlocalSession = $_SESSION["idlocal"];
		$idusuarioSession = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idusuario = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : "";
		$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$monto = isset($_POST["monto"]) ? limpiarCadena($_POST["monto"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idcaja)) {
					$nombreExiste = $cajas->verificarNombreExiste($titulo);

					if ($nombreExiste) {
						echo "El nombre de la caja ya existe.";
					} else {
						$rspta = $cajas->agregar($idusuario, $idlocal, $titulo, $monto, $descripcion);
						echo $rspta ? "Caja registrada" : "La caja no se pudo registrar";
					}
				} else {
					$nombreExiste = $cajas->verificarNombreEditarExiste($titulo, $idcaja);
					$rspta2 = $cajas->mostrar($idcaja);

					if ($nombreExiste) {
						echo "El nombre de la caja ya existe.";
					} else {
						if (empty($monto) || $rspta2["monto"] == $monto) {
							$rspta = $cajas->editarSinMonto($idcaja, $idusuario, $idlocal, $titulo, $descripcion);
						} else {
							$rspta = $cajas->editar($idcaja, $idusuario, $idlocal, $titulo, $monto, $descripcion);
						}
						echo $rspta ? "Caja actualizada" : "La caja no se pudo actualizar";
					}
				}
				break;

			case 'validarCaja':
				$rspta = $cajas->validarCaja($idlocalSession);
				echo $rspta ? "true" : "false";
				break;

			case 'cerrar':
				$rspta = $cajas->agregarCajaCerrada($idcaja, $idusuarioSession);
				echo $rspta ? "Caja cerrada" : "La caja no se pudo cerrar";
				break;

			case 'aperturar':
				$rspta = $cajas->aperturar($idcaja);
				echo $rspta ? "Caja aperturada" : "La caja no se pudo aperturar";
				break;

			case 'eliminar':
				$rspta = $cajas->eliminar($idcaja);
				echo $rspta ? "Caja eliminada" : "La caja no se pudo eliminar";
				break;

			case 'eliminarCajaCerrada':
				$rspta = $cajas->eliminarCajaCerrada($idcaja);
				echo $rspta ? "Caja eliminada" : "La caja no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $cajas->mostrar($idcaja);
				echo json_encode($rspta);
				break;

			case 'prueba':
				$idcaja = $_POST['idcaja'];
				$idcaja_cerrada = $_POST['idcaja_cerrada'];
				$rspta = $cajas->listarDetallesVentasAnuladasCajaCerrada($idcaja, $idcaja_cerrada);

				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$rowData = array();
					foreach ($reg as $key => $value) {
						$rowData[$key] = $value;
					}
					$data[] = $rowData;
				}

				echo json_encode(["data" => $data]);
				break;

			case 'listar':
				$param1 = $_GET["param1"]; // valor fecha inicio
				$param2 = $_GET["param2"]; // valor fecha fin
				$param3 = $_GET["param3"]; // valor local

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $cajas->listarPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $cajas->listarPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $cajas->listarPorParametro("c.idlocal = '$param3'");
					} else {
						$rspta = $cajas->listar();
					}
				} else {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $cajas->listarPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $cajas->listarPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $cajas->listarPorUsuarioParametro($idlocalSession, "c.idlocal = '$param3'");
					} else {
						$rspta = $cajas->listarPorUsuario($idlocalSession);
					}
				}

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

				$data = array();

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

					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px;">' .
							('<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idcaja . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado != 'aperturado') ?
								('<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="aperturar(' . $reg->idcaja . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>') : ('<button class="btn btn-danger" style="margin-right: 3px; width: 35px; height: 35px;" onclick="cerrar(' . $reg->idcaja . ')"><i class="fa fa-close"></i></button>')) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idcaja . ')"><i class="fa fa-trash"></i></button>') .
							('<button class="btn btn-info" style="margin-right: 3px; height: 35px;" onclick="modalDetalles(' . $reg->idcaja . ')"><i class="fa fa-info-circle"></i></button>') .
							('<a target="_blank" href="../reportes/exTicketApertura.php?id=' . $reg->idcaja . '"> <button class="btn btn-success" style="height: 35px; color: white !important;"><i class="fa fa-print"></i></button></a>') .
							'</div>',
						"1" => $reg->titulo,
						"2" => $reg->local,
						"3" => 'S/. ' . number_format($reg->monto, 2, '.', ','),
						"4" => 'S/. ' . number_format($reg->monto_total, 2, '.', ','),
						"5" => ucwords($reg->nombre),
						"6" => ucwords($cargo_detalle),
						"7" => $reg->fecha,
						"8" => ($reg->estado == 'aperturado') ? '<span class="label bg-green">Aperturado</span>' :
							'<span class="label bg-red">Cerrado</span>'
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

			case 'listar2':
				$param1 = $_GET["param1"]; // valor fecha inicio
				$param2 = $_GET["param2"]; // valor fecha fin
				$param3 = $_GET["param3"]; // valor local

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $cajas->listarCerradasPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $cajas->listarCerradasPorParametro("DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $cajas->listarCerradasPorParametro("c.idlocal = '$param3'");
					} else {
						$rspta = $cajas->listarCerradas();
					}
				} else {
					if ($param1 != '' && $param2 != '' && $param3 == '') {
						$rspta = $cajas->listarCerradasPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2'");
					} else if ($param1 != '' && $param2 != '' && $param3 != '') {
						$rspta = $cajas->listarCerradasPorUsuarioParametro($idlocalSession, "DATE(c.fecha_hora) >= '$param1' AND DATE(c.fecha_hora) <= '$param2' AND c.idlocal = '$param3'");
					} else if ($param1 == '' && $param2 == '' && $param3 != '') {
						$rspta = $cajas->listarCerradasPorUsuarioParametro($idlocalSession, "c.idlocal = '$param3'");
					} else {
						$rspta = $cajas->listarCerradasPorUsuario($idlocalSession);
					}
				}

				// para que no le salga ninguna opción al cajero pero a los demás sí.
				function mostrarBoton2($reg, $cargo, $idusuario, $buttonType)
				{
					if (($reg != "superadmin" && $reg != "admin_total") && $cargo == "admin") {
						return $buttonType;
					} elseif ($reg != "superadmin" && $cargo == "admin_total") {
						return $buttonType;
					} elseif ($cargo == "superadmin") {
						return $buttonType;
					} else {
						return '';
					}
				}

				$data = array();

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

					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px;">' .
							mostrarBoton2($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px; margin-right: 3px;" onclick="eliminar(' . $reg->idcaja . ')"><i class="fa fa-trash"></i></button>') .
							('<a target="_blank" href="../reportes/exTicketCierre.php?idcaja=' . $reg->idcaja . '&idcaja_cerrada=' . $reg->idcaja_cerrada . '"><button class="btn btn-success" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-print"></i></button></a>') .
							('<button class="btn btn-warning" style="height: 35px;" onclick="modalDetalles(\'' . $reg->idcaja . '\',\'' . $reg->idcaja_cerrada . '\', \'' . $reg->fecha . '\', \'' . $reg->fecha_cierre . '\')"><i class="fa fa-bars"></i></button>') .
							// ('<button class="btn btn-info" style="margin-left: 3px; height: 35px;" onclick="prueba(' . $reg->idcaja . ',\'' . $reg->idcaja_cerrada . '\')"><i class="fa fa-info-circle"></i></button>') .
							'</div>',
						"1" => $reg->titulo,
						"2" => $reg->local,
						"3" => 'S/. ' . number_format($reg->monto, 2, '.', ','),
						"4" => 'S/. ' . number_format($reg->monto_total, 2, '.', ','),
						"5" => ucwords($reg->nombre),
						"6" => ucwords($cargo_detalle),
						"7" => ($reg->fecha == '00-00-0000 00:00:00') ? 'Sin registrar.' : $reg->fecha,
						"8" => ($reg->fecha_cierre == '00-00-0000 00:00:00') ? 'Sin registrar.' : $reg->fecha_cierre,
						"9" => '<span class="label bg-red">Cerrado</span>'
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

			case 'listarDetallesProductosCaja':
				$idcaja = $_GET['idcaja'];
				$idcaja_cerrada = $_GET['idcaja_cerrada'];

				$rspta = $cajas->listarDetallesProductosCajaCerrada($idcaja, $idcaja_cerrada);

				$data = array();

				$firstIteration = true;
				$totalSubtotal = 0;
				$totalIGV = 0;

				while ($reg = $rspta->fetch_object()) {
					$subtotal = ($reg->cantidad * $reg->precio_venta) - $reg->descuento;

					$igv = 0;
					if ($reg->impuesto == 0.18) {
						$igv = $subtotal * 0.18;
						$totalIGV += $igv;
					}

					$total = $subtotal + $igv;

					$data[] = array(
						"0" => ($reg->articulo != "") ? mb_strtoupper($reg->articulo) : mb_strtoupper($reg->servicio),
						"1" => ($reg->codigo != "") ? $reg->codigo : "N° " . $reg->codigo_servicio,
						"2" => $reg->cantidad,
						"3" => $reg->precio_venta,
						"4" => $reg->descuento,
						"5" => number_format($subtotal, 2),
						"6" => $reg->fecha,
					);

					$totalSubtotal += $subtotal;
					$firstIteration = false;
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "<strong>SUBTOTAL</strong>",
						"5" => '<strong>' . number_format($totalSubtotal, 2) . '</strong>',
						"6" => "",
					);

					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "<strong>IGV</strong>",
						"5" => '<strong>' . number_format($totalIGV, 2) . '</strong>',
						"6" => "",
					);

					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "<strong>TOTAL</strong>",
						"5" => '<strong>' . number_format($totalSubtotal + $totalIGV, 2) . '</strong>',
						"6" => "",
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

			case 'selectCajas':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $cajas->listar();
				} else {
					$rspta = $cajas->listarPorUsuario($idlocalSession);
				}

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idcaja . '" data-idlocal="' . $reg->idlocal . '" data-monto="' . $reg->monto_total . '"> ' . $reg->titulo . ' - ' . $reg->local . '</option>';
				}
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
