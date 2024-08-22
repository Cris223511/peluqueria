var tabla;

//Función que se ejecuta al inicio
function init() {
	listar();

	$.post("../ajax/locales.php?op=selectLocalesUsuario", function (r) {
		console.log(r);
		$("#localBuscar").html(r);
		$('#localBuscar').selectpicker('refresh');
	});

	$.post("../ajax/usuario.php?op=selectUsuarios", function (r) {
		console.log(r);
		$("#usuarioBuscar").html(r);
		$('#usuarioBuscar').selectpicker('refresh');
	})

	$.post("../ajax/metodo_pago.php?op=selectMetodoPago", function (r) {
		console.log(r);
		$("#metodopagoBuscar").html(r);
		$('#metodopagoBuscar').selectpicker('refresh');
	})

	$.post("../ajax/proveedores.php?op=selectProveedores", function (r) {
		console.log(r);
		$("#proveedorBuscar").html(r);
		$('#proveedorBuscar').selectpicker('refresh');
	})

	$('#mReportes').addClass("treeview active");
	$('#lReporteCompra').addClass("active");
}

function listar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";
	let param4 = "";
	let param5 = "";
	let param6 = "";
	let param7 = "";
	let param8 = "";
	let param9 = "";
	let param10 = "";

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [10, 25, 75, 100],
			"aProcessing": true,
			"aServerSide": true,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'orientation': 'landscape',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 8;
						doc.styles.tableHeader.fontSize = 8;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/reporte.php?op=listarCompras',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3, param4: param4, param5: param5, param6: param6, param7: param7, param8: param8, param9: param9, param10: param10 },
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"language": {
				"lengthMenu": "Mostrar : _MENU_ registros",
				"buttons": {
					"copyTitle": "Tabla Copiada",
					"copySuccess": {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			"bDestroy": true,
			"iDisplayLength": 10,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin", "tipoDocBuscar", "localBuscar", "estadoBuscar", "proveedorBuscar", "numDocBuscar", "numTicketBuscar", "usuarioBuscar", "metodopagoBuscar"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
		$("#" + selectId).selectpicker('refresh');
	}

	listar();
}

function buscar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";
	let param4 = "";
	let param5 = "";
	let param6 = "";
	let param7 = "";
	let param8 = "";
	let param9 = "";
	let param10 = "";

	// Obtener los selectores
	const fecha_inicio = document.getElementById("fecha_inicio");
	const fecha_fin = document.getElementById("fecha_fin");
	const tipoDocBuscar = document.getElementById("tipoDocBuscar");
	const localBuscar = document.getElementById("localBuscar");
	const usuarioBuscar = document.getElementById("usuarioBuscar");
	const estadoBuscar = document.getElementById("estadoBuscar");
	const metodopagoBuscar = document.getElementById("metodopagoBuscar");
	const proveedorBuscar = document.getElementById("proveedorBuscar");
	const numDocBuscar = document.getElementById("numDocBuscar");
	const numTicketBuscar = document.getElementById("numTicketBuscar");

	if (fecha_inicio.value == "" && fecha_fin.value == "" && tipoDocBuscar.value == "" && localBuscar.value == "" && usuarioBuscar.value == "" && estadoBuscar.value == "" && metodopagoBuscar.value == "" && proveedorBuscar.value == "" && numDocBuscar.value == "" && numTicketBuscar.value == "") {
		bootbox.alert("Debe seleccionar al menos un campo para realizar la búsqueda.");
		return;
	}

	if (fecha_inicio.value > fecha_fin.value) {
		bootbox.alert("La fecha inicial no puede ser mayor que la fecha final.");
		return;
	}

	param1 = fecha_inicio.value;
	param2 = fecha_fin.value;
	param3 = tipoDocBuscar.value;
	param4 = localBuscar.value;
	param5 = usuarioBuscar.value;
	param6 = estadoBuscar.value;
	param7 = metodopagoBuscar.value;
	param8 = proveedorBuscar.value;
	param9 = numDocBuscar.value;
	param10 = numTicketBuscar.value;

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [10, 25, 75, 100],
			"aProcessing": true,
			"aServerSide": true,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'orientation': 'landscape',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 8;
						doc.styles.tableHeader.fontSize = 8;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/reporte.php?op=listarCompras',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3, param4: param4, param5: param5, param6: param6, param7: param7, param8: param8, param9: param9, param10: param10 },
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"language": {
				"lengthMenu": "Mostrar : _MENU_ registros",
				"buttons": {
					"copyTitle": "Tabla Copiada",
					"copySuccess": {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			"bDestroy": true,
			"iDisplayLength": 10,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function modalDetalles(idcompra, usuario, num_comprobante, proveedor, proveedor_tipo_documento, proveedor_num_documento, proveedor_direccion, impuesto, total_compra, vuelto, comentario_interno, moneda) {
	$.post("../ajax/compra.php?op=listarDetallesProductoCompra", { idcompra: idcompra }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		// Actualizar datos del proveedor
		let nombreCompleto = proveedor;

		if (proveedor_tipo_documento && proveedor_num_documento) {
			nombreCompleto += ' - ' + proveedor_tipo_documento + ': ' + proveedor_num_documento;
		}

		$('#nombre_proveedor').text(nombreCompleto);
		$('#direccion_proveedor').text((proveedor_direccion != "") ? proveedor_direccion : "SIN REGISTRAR");
		$('#tipo_moneda').text(moneda == "soles" ? "SOLES" : "DÓLARES");
		$('#boleta').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('#detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;
			let precio = moneda == "soles" ? "S/. " + item.precio_compra : item.precio_compra + " $";
			let descuento = moneda == "soles" ? "S/. " + item.descuento : item.descuento + " $";
			let subtotal = ((item.cantidad * item.precio_compra) - item.descuento).toFixed(2);
			let subtotalFinal = moneda == "soles" ? "S/. " + subtotal : subtotal + " $";

			let row = `
                <tr>
                    <td width: 44%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(descripcion)}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.cantidad}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${precio}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${descuento}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${subtotalFinal}</td>
                </tr>`;

			tbody.append(row);

			// Calcular subtotal
			subtotal += item.cantidad * item.precio_compra;
		});

		let igv = subtotal * (impuesto);

		let subtotal_detalle = moneda == "soles" ? "S/. " + subtotal.toFixed(2) : subtotal.toFixed(2) + " $";
		let igv_detalle = moneda == "soles" ? "S/. " + igv.toFixed(2) : igv.toFixed(2) + " $";
		let total_detalle = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

		$('#subtotal_detalle').text(subtotal_detalle);
		$('#igv_detalle').text(igv_detalle);
		$('#total_detalle').text(total_detalle);

		// Actualizar detalles de la tabla pagos
		let tbodyPagos = $('#detallesPagosFinal tbody');
		tbodyPagos.empty();

		let subtotalPagos = 0;

		data.pagos.forEach(item => {
			let monto = moneda == "soles" ? "S/. " + item.monto : item.monto + " $";

			let row = `
                <tr>
                    <td width: 80%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(item.metodo_pago)}</td>
                    <td width: 20%; min-width: 40px; white-space: nowrap;">${monto}</td>
                </tr>`;

			tbodyPagos.append(row);

			// Calcular subtotalPagos
			subtotalPagos += parseFloat(item.monto);
		});

		let subtotal_pagos = moneda == "soles" ? "S/. " + subtotalPagos.toFixed(2) : subtotalPagos.toFixed(2) + " $";
		let vueltos_pagos = moneda == "soles" ? "S/. " + vuelto : vuelto + " $";
		let total_pagos = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

		$('#subtotal_pagos').text(subtotal_pagos);
		$('#vueltos_pagos').text(vueltos_pagos);
		$('#total_pagos').text(total_pagos);

		let comentario_val = comentario_interno == "" ? "Sin registrar." : comentario_interno;

		$('#comentario_interno_detalle').text(comentario_val);
		$('#atendido_compra').text(capitalizarTodasLasPalabras(usuario));
	});
}

init();