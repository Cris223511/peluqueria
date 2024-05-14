<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

// si no está logeado o no tiene ningún cargo...
if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	echo 'No está autorizado para realizar esta acción.';
	exit();
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html"); //Validamos el acceso solo a los usuarios logueados al sistema.
} else {
	//Validamos el acceso solo al usuario logueado y autorizado.
	if ($_SESSION['reportes'] == 1 || $_SESSION['reportesP'] == 1  || $_SESSION['reportesM'] == 1  || $_SESSION['reportesE'] == 1) {
		require_once "../modelos/Reporte.php";

		$reporte = new Reporte();

		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		switch ($_GET["op"]) {

				/* ======================= REPORTE DE VENTAS Y EMPLEADOS ======================= */

			case 'listarVentasEmpleados':
				$parametros = array(
					"v.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "v.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(v.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "v.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "v.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "v.estado = '{$_GET["param6"]}'",
					"param7" => "c.nombre LIKE '%{$_GET["param7"]}%'",
					"param8" => "c.num_documento = '{$_GET["param8"]}'",
					"param9" => "v.num_comprobante = '{$_GET["param9"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarVentasEmpleados($condiciones) : $reporte->listarVentasEmpleadosLocal($idlocalSession, $condiciones);

				$data = array();

				$lastIdVenta = null;
				$firstIteration = true;
				$totalPrecioVenta = 0;
				$ventasUnicas = array();
				$hayDatos = true;

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

					$hayDatos = false;

					// Verificar si el idventa actual es diferente al idventa del registro anterior
					// Verificar si es la primera iteración
					if (!$firstIteration && $reg->idventa != $lastIdVenta) {
						// Agregar una fila vacía al array antes de agregar el nuevo registro
						$data[] = array_fill(0, 12, ''); // Esto crea una fila vacía con 11 celdas
					}

					$data[] = array(
						"0" => $reg->fecha,
						"1" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"2" => $reg->cliente,
						"3" => ($reg->personal  == "") ? 'Sin registrar.' : $reg->personal,
						"4" => ($reg->idarticulo != "0") ? strtoupper($reg->nombre_articulo) : strtoupper($reg->nombre_servicio),
						"5" => $reg->local,
						"6" => $reg->caja,
						"7" => $reg->tipo_comprobante,
						"8" => 'N° ' . $reg->num_comprobante,
						"9" => $reg->total_venta,
						"10" => $reg->usuario . ' - ' . $cargo_detalle,
						"11" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					if (!isset($ventasUnicas[$reg->idventa])) {
						$ventasUnicas[$reg->idventa] = true;
						$totalPrecioVenta += $reg->total_venta;
					}

					$firstIteration = false; // Marcar que ya no es la primera iteración
					$lastIdVenta = $reg->idventa;
				}

				if (!empty($data)) {
					$data[] = array_fill(0, 12, '');
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "",
						"4" => "",
						"5" => "",
						"6" => "",
						"7" => "",
						"8" => "<strong>TOTAL</strong>",
						"9" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
						"10" => "",
						"11" => "",
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

				/* ======================= REPORTE DE COMPRAS ======================= */

			case 'listarCompras':
				$parametros = array(
					"c.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "c.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(c.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "c.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "c.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "c.estado = '{$_GET["param6"]}'",
					"param7" => "dvp.idmetodopago = '{$_GET["param7"]}'",
					"param8" => "p.nombre LIKE '%{$_GET["param8"]}%'",
					"param9" => "p.num_documento = '{$_GET["param9"]}'",
					"param10" => "c.num_comprobante = '{$_GET["param10"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarCompras($condiciones) : $reporte->listarComprasLocal($idlocalSession, $condiciones);

				$data = array();

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
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idcompra . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->proveedor . '\', \'' . $reg->proveedor_tipo_documento . '\', \'' . $reg->proveedor_num_documento . '\', \'' . $reg->proveedor_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_compra . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->proveedor_tipo_documento . ": " . $reg->proveedor_num_documento,
						"3" => $reg->proveedor,
						"4" => $reg->local,
						"5" => $reg->tipo_comprobante,
						"6" => 'N° ' . $reg->num_comprobante,
						"7" => $reg->total_compra,
						"8" => $reg->usuario . ' - ' . $cargo_detalle,
						"9" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
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
						"4" => "",
						"5" => "",
						"6" => "<strong>TOTAL</strong>",
						"7" => '<strong>' . number_format($totalPrecioCompra, 2) . '</strong>',
						"8" => "",
						"9" => "",
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

				/* ======================= REPORTE DE VENTAS ======================= */

			case 'listarVentas':
				$parametros = array(
					"v.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "v.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(v.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "v.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "v.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "v.estado = '{$_GET["param6"]}'",
					"param7" => "dvp.idmetodopago = '{$_GET["param7"]}'",
					"param8" => "c.nombre LIKE '%{$_GET["param8"]}%'",
					"param9" => "c.num_documento = '{$_GET["param9"]}'",
					"param10" => "v.num_comprobante = '{$_GET["param10"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarVentas($condiciones) : $reporte->listarVentasLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalPrecioVenta = 0;

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
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idventa . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"3" => $reg->cliente,
						"4" => $reg->local,
						"5" => $reg->caja,
						"6" => $reg->tipo_comprobante,
						"7" => 'N° ' . $reg->num_comprobante,
						"8" => $reg->total_venta,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
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
						"6" => "",
						"7" => "<strong>TOTAL</strong>",
						"8" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
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

				/* ======================= REPORTE DE PROFORMAS ======================= */

			case 'listarProformas':
				$parametros = array(
					"p.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "p.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(p.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
					"param3" => "p.tipo_comprobante = '{$_GET["param3"]}'",
					"param4" => "p.idlocal = '{$_GET["param4"]}'",
					"param5" => "u.idusuario = '{$_GET["param5"]}'",
					"param6" => "p.estado = '{$_GET["param6"]}'",
					"param7" => "dpp.idmetodopago = '{$_GET["param7"]}'",
					"param8" => "c.nombre LIKE '%{$_GET["param8"]}%'",
					"param9" => "c.num_documento = '{$_GET["param9"]}'",
					"param10" => "p.num_comprobante = '{$_GET["param10"]}'"
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarProformas($condiciones) : $reporte->listarProformasLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalPrecioVenta = 0;

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
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-info" style="margin-right: 3px; width: 35px; height: 35px; color: white !important;" onclick="modalDetalles(' . $reg->idproforma . ', \'' . $reg->usuario . '\', \'' . $reg->num_comprobante . '\', \'' . $reg->cliente . '\', \'' . $reg->cliente_tipo_documento . '\', \'' . $reg->cliente_num_documento . '\', \'' . $reg->cliente_direccion . '\', \'' . $reg->impuesto . '\', \'' . $reg->total_venta . '\', \'' . $reg->vuelto . '\')"><i class="fa fa-info-circle"></i></button></a>' .
							'</div>',
						"1" => $reg->fecha,
						"2" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"3" => $reg->cliente,
						"4" => $reg->local,
						"5" => $reg->caja,
						"6" => $reg->tipo_comprobante,
						"7" => 'N° ' . $reg->num_comprobante,
						"8" => $reg->total_venta,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
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
						"6" => "",
						"7" => "<strong>TOTAL</strong>",
						"8" => '<strong>' . number_format($totalPrecioVenta, 2) . '</strong>',
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

				/* ======================= MÉTODOS DE PAGO POR COMPRAS ======================= */

			case 'listarComprasMetodosPago':
				$parametros = array(
					"co.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "co.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(co.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
				);

				if (!empty($_GET['param3'])) {
					$param3_array = explode(',', $_GET['param3']);
					$param3_condition = [];

					foreach ($param3_array as $metodo_pago_id) {
						$param3_condition[] = "dcp.idmetodopago = '$metodo_pago_id'";
					}

					$filtros["param3"] = "(" . implode(' OR ', $param3_condition) . ")";
				}

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarComprasMetodosPago($condiciones) : $reporte->listarComprasMetodosPagoLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalMonto = 0;

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
						"0" => $reg->fecha,
						"1" => $reg->proveedor_tipo_documento . ": " . $reg->proveedor_num_documento,
						"2" => $reg->proveedor,
						"3" => $reg->metodo_pago_titulo,
						"4" => $reg->metodo_pago_monto,
						"5" => 'N° ' . $reg->num_comprobante,
						"6" => $reg->local,
						"7" => $reg->tipo_comprobante,
						"8" => $reg->usuario . ' - ' . $cargo_detalle,
						"9" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalMonto += $reg->metodo_pago_monto;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "<strong>TOTAL</strong>",
						"4" => '<strong>' . number_format($totalMonto, 2) . '</strong>',
						"5" => "",
						"6" => "",
						"7" => "",
						"8" => "",
						"9" => "",
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

				/* ======================= MÉTODOS DE PAGO POR VENTAS ======================= */

			case 'listarVentasMetodosPago':
				$parametros = array(
					"v.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "v.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(v.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
				);

				if (!empty($_GET['param3'])) {
					$param3_array = explode(',', $_GET['param3']);
					$param3_condition = [];

					foreach ($param3_array as $metodo_pago_id) {
						$param3_condition[] = "dvp.idmetodopago = '$metodo_pago_id'";
					}

					$filtros["param3"] = "(" . implode(' OR ', $param3_condition) . ")";
				}

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarVentasMetodosPago($condiciones) : $reporte->listarVentasMetodosPagoLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalMonto = 0;

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
						"0" => $reg->fecha,
						"1" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"2" => $reg->cliente,
						"3" => $reg->metodo_pago_titulo,
						"4" => $reg->metodo_pago_monto,
						"5" => 'N° ' . $reg->num_comprobante,
						"6" => $reg->local,
						"7" => $reg->caja,
						"8" => $reg->tipo_comprobante,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalMonto += $reg->metodo_pago_monto;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "<strong>TOTAL</strong>",
						"4" => '<strong>' . number_format($totalMonto, 2) . '</strong>',
						"5" => "",
						"6" => "",
						"7" => "",
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

				/* ======================= MÉTODOS DE PAGO POR PROFORMA ======================= */

			case 'listarProformasMetodosPago':
				$parametros = array(
					"p.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "p.idlocal = '$idlocalSession'";
				}

				$filtros = array(
					"param1" => "DATE(p.fecha_hora) BETWEEN '{$_GET["param1"]}' AND '{$_GET["param2"]}'",
				);

				if (!empty($_GET['param3'])) {
					$param3_array = explode(',', $_GET['param3']);
					$param3_condition = [];

					foreach ($param3_array as $metodo_pago_id) {
						$param3_condition[] = "dpp.idmetodopago = '$metodo_pago_id'";
					}

					$filtros["param3"] = "(" . implode(' OR ', $param3_condition) . ")";
				}

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarProformasMetodosPago($condiciones) : $reporte->listarProformasMetodosPagoLocal($idlocalSession, $condiciones);

				$data = array();

				$firstIteration = true;
				$totalMonto = 0;

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
						"0" => $reg->fecha,
						"1" => $reg->cliente_tipo_documento . ": " . $reg->cliente_num_documento,
						"2" => $reg->cliente,
						"3" => $reg->metodo_pago_titulo,
						"4" => $reg->metodo_pago_monto,
						"5" => 'N° ' . $reg->num_comprobante,
						"6" => $reg->local,
						"7" => $reg->caja,
						"8" => $reg->tipo_comprobante,
						"9" => $reg->usuario . ' - ' . $cargo_detalle,
						"10" => ($reg->estado == 'Iniciado') ? '<span class="label bg-blue">Iniciado</span>' : (($reg->estado == 'Entregado') ? '<span class="label bg-green">Entregado</span>' : (($reg->estado == 'Por entregar') ? '<span class="label bg-orange">Por entregar</span>' : (($reg->estado == 'En transcurso') ? '<span class="label bg-yellow">En transcurso</span>' : (($reg->estado == 'Finalizado') ? ('<span class="label bg-green">Finalizado</span>') : ('<span class="label bg-red">Anulado</span>'))))),
					);

					$totalMonto += $reg->metodo_pago_monto;
					$firstIteration = false; // Marcar que ya no es la primera iteración
				}

				if (!$firstIteration) {
					$data[] = array(
						"0" => "",
						"1" => "",
						"2" => "",
						"3" => "<strong>TOTAL</strong>",
						"4" => '<strong>' . number_format($totalMonto, 2) . '</strong>',
						"5" => "",
						"6" => "",
						"7" => "",
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

				/* ======================= REPORTE DE ARTICULOS MÁS VENDIDOS ======================= */

			case 'listarArticulosMasVendidos':
				$parametros = array(
					"a.eliminado = '0'"
				);

				if ($cargo != "superadmin") {
					$parametros[] = "a.idlocal = '$idlocalSession'";
				}

				$estadoSeleccionado = isset($_GET["param7"]) ? $_GET["param7"] : '';

				$filtros = array(
					"param3" => "a.idlocal = '{$_GET["param3"]}'",
					"param4" => "a.idmarca = '{$_GET["param4"]}'",
					"param5" => "a.idcategoria = '{$_GET["param5"]}'",
					"param6" => "a.idusuario = '{$_GET["param6"]}'",
					"param7" => $estadoSeleccionado == "AGOTANDOSE" ? "a.stock > 0 AND a.stock < a.stock_minimo" : ($estadoSeleccionado == "DISPONIBLE" ? "a.stock != '0'" : "a.stock = '0'")
				);

				foreach ($filtros as $param => $condicion) {
					if (!empty($_GET[$param])) {
						$parametros[] = $condicion;
					}
				}

				$condiciones = implode(" AND ", $parametros);

				$rspta = $cargo == "superadmin" ? $reporte->listarArticulosMasVendidos($condiciones) : $reporte->listarArticulosMasVendidosLocal($idlocalSession, $condiciones);

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

					$data[] = array(
						"0" => '<a href="../files/articulos/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
									<img src="../files/articulos/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
								</a>',
						"1" => $reg->nombre,
						"2" => $reg->cantidad,
						"3" => $reg->categoria,
						"4" => $reg->local,
						"5" => $reg->marca,
						"6" => $reg->codigo_producto,
						"7" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span style="color: #Ea9900; font-weight: bold">' . $reg->stock . '</span>' : (($reg->stock != '0') ? '<span>' . $reg->stock . '</span>' : '<span style="color: red; font-weight: bold">' . $reg->stock . '</span>'),
						"8" => "S/. " . number_format($reg->precio_venta, 2, '.', ','),
						"9" => $reg->usuario,
						"10" => $cargo_detalle,
						"11" => ($reg->stock > 0 && $reg->stock < $reg->stock_minimo) ? '<span class="label bg-orange">agotandose</span>' : (($reg->stock != '0') ? '<span class="label bg-green">Disponible</span>' : '<span class="label bg-red">agotado</span>')
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
