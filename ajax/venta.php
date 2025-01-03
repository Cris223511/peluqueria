<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['ventas'] == 1) {
		require_once "../modelos/Venta.php";

		$venta = new Venta();

		$idusuario = $_SESSION["idusuario"];
		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idventa = isset($_POST["idventa"]) ? limpiarCadena($_POST["idventa"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
		$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
		$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
		$moneda = isset($_POST["moneda"]) ? limpiarCadena($_POST["moneda"]) : "";
		$impuesto = isset($_POST["impuesto"]) ? limpiarCadena($_POST["impuesto"]) : "";
		$total_venta = isset($_POST["total_venta"]) ? limpiarCadena($_POST["total_venta"]) : "";
		$vuelto = isset($_POST["vuelto"]) ? limpiarCadena($_POST["vuelto"]) : "";
		$comentario_interno = isset($_POST["comentario_interno"]) ? limpiarCadena($_POST["comentario_interno"]) : "";
		$comentario_externo = isset($_POST["comentario_externo"]) ? limpiarCadena($_POST["comentario_externo"]) : "";
		$cantidad_cuotas = isset($_POST["cantidad_cuotas"]) ? limpiarCadena($_POST["cantidad_cuotas"]) : "";
		$pagar_cuotas = isset($_POST["pagar_cuotas"]) ? limpiarCadena($_POST["pagar_cuotas"]) : "";

		$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";

		$sunat = isset($_POST["sunat"]) ? limpiarCadena($_POST["sunat"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idventa)) {
					$numeroExiste = $venta->verificarNumeroExiste($num_comprobante, (($idlocal != "") ? $idlocal : $idlocalSession));
					if ($numeroExiste) {
						echo "El número correlativo que ha ingresado ya existe en el local seleccionado.";
					} else {
						$rspta = $venta->insertar($idusuario, (($idlocal != "") ? $idlocal : $idlocalSession), $idcliente, $idcaja, $tipo_comprobante, $num_comprobante, $moneda, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $cantidad_cuotas, $pagar_cuotas, $_POST["detalles"], $_POST["idpersonal"], $_POST["cantidad"], $_POST["precio_compra"], $_POST["precio_venta"], $_POST["comision"], $_POST["descuento"], $_POST["metodo_pago"], $_POST["monto"]);
						if (is_array($rspta) && $rspta[0] === true) {
							echo json_encode($rspta);
						} else {
							echo $rspta;
						}
					}
				} else {
				}
				break;

			case 'mostrar':
				$rspta = $venta->mostrar($idventa);
				echo json_encode($rspta);
				break;

			case 'anular':
				$rspta = $venta->anular($idventa);
				echo $rspta ? "Venta anulada" : "Venta no se puede anular";
				break;

			case 'cambiarEstado':
				$rspta = $venta->cambiarEstado($idventa, $estado);
				echo $rspta ? "Estado de la venta actualizada con éxito." : "El estado de la venta no se puede actualizar.";
				break;

			case 'validarCaja':
				$rspta = $venta->validarCaja($idlocalSession);
				echo json_encode($rspta);
				break;

			case 'eliminar':
				$rspta = $venta->eliminar($idventa);
				echo $rspta ? "Venta eliminada" : "Venta no se puede eliminar";
				break;

			case 'listar':
				$fecha_inicio = $_GET["fecha_inicio"];
				$fecha_fin = $_GET["fecha_fin"];
				$estado = $_GET["estado"];

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					if ($fecha_inicio == "" && $fecha_fin == "" && $estado == "") {
						$rspta = $venta->listar();
					} else if ($fecha_inicio == "" && $fecha_fin == ""  && $estado != "") {
						$rspta = $venta->listarEstado($estado);
					} else if ($fecha_inicio != "" && $fecha_fin != ""  && $estado == "") {
						$rspta = $venta->listarPorFecha($fecha_inicio, $fecha_fin);
					} else {
						$rspta = $venta->listarPorFechaEstado($fecha_inicio, $fecha_fin, $estado);
					}
				} else {
					if ($fecha_inicio == "" && $fecha_fin == "" && $estado == "") {
						$rspta = $venta->listarPorUsuario($idlocalSession);
					} else if ($fecha_inicio == "" && $fecha_fin == ""  && $estado != "") {
						$rspta = $venta->listarPorUsuarioEstado($idlocalSession, $estado);
					} else if ($fecha_inicio != "" && $fecha_fin != ""  && $estado == "") {
						$rspta = $venta->listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin);
					} else {
						$rspta = $venta->listarPorUsuarioFechaEstado($idlocalSession, $fecha_inicio, $fecha_fin, $estado);
					}
				}

				$data = array();

				// ANTIGUO
				// function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				// {
				// 	if ($reg != "superadmin" && $cargo == "admin") {
				// 		return $buttonType;
				// 	} elseif ($cargo == "superadmin" || ($cargo == "cajero" && $idusuario == $_SESSION["idusuario"])) {
				// 		return $buttonType;
				// 	} else {
				// 		return '';
				// 	}
				// }

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

				$firstIteration = true;
				$totalPrecioVentaSoles = 0;
				$totalPrecioVentaDolares = 0;

				$totalMontoPagarSoles = 0;
				$totalMontoPagarDolares = 0;

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
							'<a data-toggle="modal" href="#myModal9"><button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal10"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\', \'' . $reg->comentario_interno . '\', \'' . $reg->moneda . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							(($reg->pagar_cuotas == 1) ?
								'<a data-toggle="modal" href="#myModal13"><button class="btn btn-warning" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalCuotas(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i style="margin-left: -2px" class="fa fa-money"></i></button></a>'
								: '') .
							(($reg->estado == 'Completado' || $reg->estado == 'Pendiente' || $reg->estado == 'Iniciado' || $reg->estado == 'Entregado' || $reg->estado == 'Por entregar' || $reg->estado == 'En transcurso' || $reg->estado == 'Finalizado') ?
								(($_SESSION["cargo"] == 'superadmin' || $_SESSION["cargo"] == 'admin_total') ? (($reg->pagar_cuotas == 0) ? (mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<a data-toggle="modal" href="#myModal11"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="modalEstadoVenta(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-gear"></i></button></a>')) : "") : "") .
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="anular(' . $reg->idventa . ')"><i class="fa fa-close"></i></button>')) : '') .
							mostrarBoton2($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idventa . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exA4Venta.php?id=' . $reg->idventa . '"> <button class="btn btn-info" style="margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->cliente,
						"3" => $reg->local,
						"4" => $reg->caja,
						"5" => $reg->tipo_comprobante,
						"6" => 'N° ' . $reg->num_comprobante,
						"7" => $reg->monto_pagado,
						"8" => $reg->total_venta,
						"9" => ($reg->moneda == 'soles') ? 'Soles' : 'Dólares',
						"10" => $reg->usuario . ' - ' . $cargo_detalle,
						"11" => $reg->fecha,
						"12" => ($reg->estado == 'Completado') ? '<span class="label bg-green">Completado</span>' : (($reg->estado == 'Pendiente') ? '<span class="label bg-orange">Pendiente</span>' : (($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))))),
					);

					if ($reg->moneda == 'soles') {
						$totalPrecioVentaSoles += $reg->total_venta;
						$totalMontoPagarSoles += $reg->monto_pagado;
					} else {
						$totalPrecioVentaDolares += $reg->total_venta;
						$totalMontoPagarDolares += $reg->monto_pagado;
					}

					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "",
						"5" => "",
						"6" => "<strong>TOTAL EN SOLES</strong>",
						"7" => '<strong>' . number_format($totalMontoPagarSoles, 2) . '</strong>',
						"8" => '<strong>' . number_format($totalPrecioVentaSoles, 2) . '</strong>',
						"9" => "",
						"10" => "",
						"11" => "",
						"12" => "",
					);

					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "",
						"5" => "",
						"6" => "<strong>TOTAL EN DÓLARES</strong>",
						"7" => '<strong>' . number_format($totalMontoPagarDolares, 2) . '</strong>',
						"8" => '<strong>' . number_format($totalPrecioVentaDolares, 2) . '</strong>',
						"9" => "",
						"10" => "",
						"11" => "",
						"12" => "",
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

				/* ======================= REGISTRO DE CUOTAS ======================= */

			case 'registrarCuotas':
				// Validar que los datos fueron enviados correctamente
				if (empty($_POST['idventa']) || empty($_POST['montoCuota'])) {
					echo json_encode(["status" => "error", "message" => "Datos insuficientes para registrar el pago."]);
					exit;
				}

				$montoCuota = (float)$_POST['montoCuota'];

				// Obtener la venta actual desde el modelo
				$ventaActual = $venta->mostrar($idventa);

				if (!$ventaActual) {
					echo json_encode(["status" => "error", "message" => "Venta no encontrada."]);
					exit;
				}

				$totalVenta = (float)$ventaActual['total_venta'];
				$montoPagado = (float)$ventaActual['monto_pagado'];
				$cantidadCuotas = (int)$ventaActual['cantidad_cuotas'];

				// Validar el monto de la cuota
				$residuo = $totalVenta - $montoPagado;
				if ($montoCuota > $residuo) {
					echo json_encode(["status" => "error", "message" => "El monto ingresado excede el total restante de la venta."]);
					exit;
				}

				// Validar si es la última cuota
				if ($cantidadCuotas === 1 && $montoCuota != $residuo) {
					echo json_encode(["status" => "error", "message" => "Debido a que se encuentra en la última cuota, debe ingresar el monto exacto para completar el pago (el residuo debe ser 0)."]);
					exit;
				}

				// Actualizar los valores
				$nuevoMontoPagado = $montoPagado + $montoCuota;
				$nuevaCantidadCuotas = ($nuevoMontoPagado >= $totalVenta) ? 0 : $cantidadCuotas - 1;

				// Actualizar el estado solo si se completa el monto total
				$estado = ($nuevoMontoPagado >= $totalVenta) ? "Completado" : $ventaActual['estado'];

				$resultado = $venta->actualizarCuotas($idventa, $nuevoMontoPagado, $nuevaCantidadCuotas, $estado);

				if ($resultado) {
					echo json_encode(["status" => "success", "message" => "Pago registrado correctamente."]);
				} else {
					echo json_encode(["status" => "error", "message" => "Error al registrar el pago en la base de datos."]);
				}
				break;

				/* ======================= SUNAT ======================= */

			case 'consultaSunat':
				// Token para la API
				$token = 'apis-token-9398.CgMfcskFz1G61BmjTE6j4lt8mXwqhxwL';

				$data = "";
				$curl = curl_init();

				try {
					if (strlen($sunat) == 8) {
						// DNI
						$url = 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $sunat;
						$referer = 'https://apis.net.pe/consulta-dni-api';
					} elseif (strlen($sunat) == 11) {
						// RUC
						$url = 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . $sunat;
						$referer = 'http://apis.net.pe/api-ruc';
					} elseif (strlen($sunat) < 8) {
						// Mensaje para DNI no válido
						$data = "El DNI debe tener 8 caracteres.";
						echo $data;
						break;
					} elseif (strlen($sunat) > 8 && strlen($sunat) < 11) {
						// Mensaje para RUC no válido
						$data = "El RUC debe tener 11 caracteres.";
						echo $data;
						break;
					}

					// configuración de cURL
					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYPEER => 0,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 2,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_CUSTOMREQUEST => 'GET',
						CURLOPT_HTTPHEADER => array(
							'Referer: ' . $referer,
							'Authorization: Bearer ' . $token
						),
					));

					$response = curl_exec($curl);

					if ($response === false) {
						throw new Exception(curl_error($curl));
					}

					if (stripos($response, 'Not Found') !== false || stripos($response, '{"message":"ruc no valido"}') !== false) {
						// Mensaje para DNI no válido o RUC no válido
						$data = (strlen($sunat) == 8) ? "DNI no valido" : "RUC no valido";
					} elseif (stripos($response, '{"message":"Superaste el limite permitido por tu token"}') !== false) {
						// Mensaje cuando se supera el límite de consultas a la SUNAT
						$data = "Acaba de superar el límite de 1000 consultas a la SUNAT este mes";
					} else {
						// Respuesta válida de la API
						$data = $response;
					}
				} catch (Exception $e) {
					$data = "Error al procesar la solicitud: " . $e->getMessage();
				} finally {
					curl_close($curl);
				}

				echo $data;
				break;

			case 'getLastNumComprobante':
				$row = mysqli_fetch_assoc($venta->getLastNumComprobante($idlocalSession));
				if ($row != null) {
					$last_num_comprobante = $row["last_num_comprobante"];
					echo $last_num_comprobante;
				} else {
					echo $row;
				}
				break;

			case 'getLastNumComprobante':
				$row = mysqli_fetch_assoc($venta->getLastNumComprobante($idlocalSession));
				$lastNumComp = $row["last_num_comprobante"] != null ? $row["last_num_comprobante"] : "0";
				echo $lastNumComp;
				break;

			case 'getLastNumComprobanteLocal':
				$row1 = mysqli_fetch_assoc($venta->getLastNumComprobante($idlocal));
				$row2 = mysqli_fetch_assoc($venta->getCajaLocal($idlocal));
				$row3 = mysqli_fetch_assoc($venta->verificarCajaLocal($idlocal));

				$lastNumComp = $row1["last_num_comprobante"] != null ? $row1["last_num_comprobante"] : "0";
				$idcajaLocal = $row2["idcaja"] != null ? $row2["idcaja"] : "0";

				$response = array(
					"last_num_comprobante" => $lastNumComp,
					"idcaja" => $idcajaLocal,
					"estado" => $row3["estado"],
				);
				echo json_encode($response);
				break;

				/* ======================= SELECTS ======================= */

			case 'listarTodosLocalActivosPorUsuario':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $venta->listarTodosLocalActivos();
				} else {
					$rspta = $venta->listarTodosLocalActivosPorUsuario($idlocalSession);
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

			case 'listarArticulosPorCategoria':
				$idcategoria = isset($_POST["idcategoria"]) ? limpiarCadena($_POST["idcategoria"]) : "";

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $venta->listarArticulosPorCategoria($idcategoria);
				} else {
					$rspta = $venta->listarArticulosPorCategoriaLocal($idcategoria, $idlocalSession);
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

			case 'listarDetallesProductoVenta':
				$rspta1 = $venta->listarDetallesProductoVenta($idventa);
				$rspta2 = $venta->listarDetallesMetodosPagoVenta($idventa);

				$articulos = array();
				$pagos = array();

				while ($row = mysqli_fetch_assoc($rspta1)) {
					$articulos[] = $row;
				}

				while ($row = mysqli_fetch_assoc($rspta2)) {
					$pagos[] = $row;
				}

				$data = array(
					"articulos" => $articulos,
					"pagos" => $pagos
				);

				echo json_encode($data);
				break;


			case 'listarMetodosDePago':
				$rspta = $venta->listarMetodosDePago();

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$tabla = $row['tabla'];
					unset($row['tabla']);
					$data[$tabla][] = $row;
				}

				echo json_encode($data);
				break;

			case 'listarClientes':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $venta->listarClientes();
				} else {
					$rspta = $venta->listarClientesLocal($idlocalSession);
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
