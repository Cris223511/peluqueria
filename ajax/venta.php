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

		$idarticulo = isset($_POST["idarticulo"]) ? $_POST["idarticulo"] : [];
		$idservicio = isset($_POST["idservicio"]) ? $_POST["idservicio"] : [];

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
		$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
		$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
		$impuesto = isset($_POST["impuesto"]) ? limpiarCadena($_POST["impuesto"]) : "";
		$total_venta = isset($_POST["total_venta"]) ? limpiarCadena($_POST["total_venta"]) : "";
		$vuelto = isset($_POST["vuelto"]) ? limpiarCadena($_POST["vuelto"]) : "";
		$comentario_interno = isset($_POST["comentario_interno"]) ? limpiarCadena($_POST["comentario_interno"]) : "";
		$comentario_externo = isset($_POST["comentario_externo"]) ? limpiarCadena($_POST["comentario_externo"]) : "";

		$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";

		$sunat = isset($_POST["sunat"]) ? limpiarCadena($_POST["sunat"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idventa)) {
					$numeroExiste = $venta->verificarNumeroExiste($num_comprobante, $idlocalSession);
					if ($numeroExiste) {
						echo "El número correlativo que ha ingresado ya existe en el local seleccionado.";
					} else {
						$rspta = $venta->insertar($idusuario, $idlocalSession, $idcliente, $idcaja, $tipo_comprobante, $num_comprobante, $impuesto, $total_venta, $vuelto, $comentario_interno, $comentario_externo, $idarticulo, $idservicio, $_POST["idpersonal"], $_POST["cantidad"], $_POST["precio_compra"], $_POST["precio_venta"], $_POST["descuento"], $_POST["metodo_pago"], $_POST["monto"]);
						if (is_array($rspta) && $rspta[0] === true) {
							echo json_encode($rspta);
						} else {
							echo $rspta;
						}
					}
				} else {
				}
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

				if ($cargo == "superadmin") {
					if ($fecha_inicio == "" && $fecha_fin == "") {
						$rspta = $venta->listar();
					} else {
						$rspta = $venta->listarPorFecha($fecha_inicio, $fecha_fin);
					}
				} else {
					if ($fecha_inicio == "" && $fecha_fin == "") {
						$rspta = $venta->listarPorUsuario($idlocalSession);
					} else {
						$rspta = $venta->listarPorUsuarioFecha($idlocalSession, $fecha_inicio, $fecha_fin);
					}
				}

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($reg != "superadmin" && $cargo == "admin") {
						return $buttonType;
					} elseif ($cargo == "superadmin" || ($cargo == "cajero" && $idusuario == $_SESSION["idusuario"])) {
						return $buttonType;
					} else {
						return '';
					}
				}

				$firstIteration = true;
				$totalPrecioVenta = 0;

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

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							'<a data-toggle="modal" href="#myModal9"><button class="btn btn-success" style="color: black !important; margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalImpresion(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-print"></i></button></a>' .
							'<a data-toggle="modal" href="#myModal10"><button class="btn btn-info" style="color: black !important; margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ')"><i class="fa fa-info-circle"></i></button></a>' .
							(($reg->estado == 'Iniciado' || $reg->estado == 'Entregado' || $reg->estado == 'Por entregar' || $reg->estado == 'En transcurso') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<a data-toggle="modal" href="#myModal11"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="modalEstadoVenta(' . $reg->idventa . ', \'' . $reg->num_comprobante . '\')"><i class="fa fa-gear"></i></button></a>')) : ('')) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="eliminar(' . $reg->idventa . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => '<a target="_blank" href="../reportes/exFactura.php?id=' . $reg->idventa . '"> <button class="btn btn-info" style="color: black !important; margin-right: 3px; height: 35px; color: white !important;"><i class="fa fa-save"></i></button></a>',
						"2" => $reg->cliente,
						"3" => $reg->local,
						"4" => $reg->caja,
						"5" => $reg->tipo_comprobante,
						"6" => 'N° ' . $reg->num_comprobante,
						"7" => $reg->total_venta,
						"8" => $reg->usuario . ' - ' . $cargo_detalle,
						"9" => $reg->fecha,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-red">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalPrecioVenta += $reg->total_venta;
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
						"6" => "<strong>TOTAL</strong>",
						"7" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"8" => "",
						"9" => "",
						"10" => "",
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

				/* ======================= SUNAT ======================= */

			case 'consultaSunat':
				$data = "";
				$curl = curl_init();

				try {
					if (strlen($sunat) == 8) {
						// DNI
						curl_setopt($curl, CURLOPT_URL, 'https://api.apis.net.pe/v1/dni?numero=' . $sunat);
					} elseif (strlen($sunat) == 11) {
						// RUC
						curl_setopt($curl, CURLOPT_URL, 'https://api.apis.net.pe/v1/ruc?numero=' . $sunat);
					} elseif (strlen($sunat) < 8) {
						// Mensaje para DNI no válido
						$data = "El DNI debe tener 8 caracteres.";
					} elseif (strlen($sunat) > 8 && strlen($sunat) < 11) {
						// Mensaje para RUC no válido
						$data = "El RUC debe tener 11 caracteres.";
					}

					if (!empty($data)) {
						echo $data;
						break;
					}

					// Configurar opciones de cURL
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

					// Ejecutar la solicitud
					$response = curl_exec($curl);

					if ($response === false) {
						throw new Exception(curl_error($curl));
					}

					// Verificar si la respuesta contiene "Not Found" y ajustar el mensaje en consecuencia
					if (stripos($response, 'Not Found') !== false || stripos($response, '{"error":"RUC invalido"}') !== false) {
						$data = (strlen($sunat) == 8) ? "DNI no encontrado" : "RUC no encontrado";
					} else {
						$data = $response;
					}
				} catch (Exception $e) {
					// Capturar excepción y proporcionar mensaje controlado
					$data = "Error al procesar la solicitud: " . $e->getMessage();
				} finally {
					// Cerrar cURL
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

				/* ======================= SELECTS ======================= */

			case 'listarTodosLocalActivosPorUsuario':
				if ($cargo == "superadmin") {
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

				if ($cargo == "superadmin") {
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
				if ($cargo == "superadmin") {
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
