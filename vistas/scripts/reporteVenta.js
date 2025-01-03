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

	$.post("../ajax/clientes.php?op=selectClientes", function (r) {
		console.log(r);
		$("#clienteBuscar").html(r);
		$('#clienteBuscar').selectpicker('refresh');
	})

	$('#mReportes').addClass("treeview active");
	$('#lReporteVenta').addClass("active");
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
				url: '../ajax/reporte.php?op=listarVentas',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin", "tipoDocBuscar", "localBuscar", "estadoBuscar", "clienteBuscar", "numDocBuscar", "numTicketBuscar", "usuarioBuscar", "metodopagoBuscar"];

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
	const clienteBuscar = document.getElementById("clienteBuscar");
	const numDocBuscar = document.getElementById("numDocBuscar");
	const numTicketBuscar = document.getElementById("numTicketBuscar");

	if (fecha_inicio.value == "" && fecha_fin.value == "" && tipoDocBuscar.value == "" && localBuscar.value == "" && usuarioBuscar.value == "" && estadoBuscar.value == "" && metodopagoBuscar.value == "" && clienteBuscar.value == "" && numDocBuscar.value == "" && numTicketBuscar.value == "") {
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
	param8 = clienteBuscar.value;
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
				url: '../ajax/reporte.php?op=listarVentas',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function modalDetalles(idventa, usuario, num_comprobante, cliente, cliente_tipo_documento, cliente_num_documento, cliente_direccion, impuesto, total_venta, vuelto, comentario_interno, moneda) {
	$.post("../ajax/venta.php?op=listarDetallesProductoVenta", { idventa: idventa }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		// Actualizar datos del cliente
		let nombreCompleto = cliente;

		if (cliente_tipo_documento && cliente_num_documento) {
			nombreCompleto += ' - ' + cliente_tipo_documento + ': ' + cliente_num_documento;
		}

		$('#nombre_cliente').text(nombreCompleto);
		$('#direccion_cliente').text((cliente_direccion != "") ? cliente_direccion : "SIN REGISTRAR");
		$('#tipo_moneda').text(moneda == "soles" ? "SOLES" : "DÓLARES");
		$('#nota_de_venta').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('#detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;
		let cantidadTotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;
			let precio = moneda == "soles" ? "S/. " + item.precio_venta : item.precio_venta + " $";
			let descuento = moneda == "soles" ? "S/. " + item.descuento : item.descuento + " $";
			let subtotalFila = ((item.cantidad * item.precio_venta) - item.descuento).toFixed(2);
			let subtotalFinal = moneda == "soles" ? "S/. " + subtotalFila : subtotalFila + " $";

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
			subtotal += item.cantidad * item.precio_venta;
			// Calcular cantidad
			cantidadTotal += Number(item.cantidad);
		});

		let igv = subtotal * (impuesto);

		let subtotal_detalle = moneda == "soles" ? "S/. " + subtotal.toFixed(2) : subtotal.toFixed(2) + " $";
		let igv_detalle = moneda == "soles" ? "S/. " + igv.toFixed(2) : igv.toFixed(2) + " $";
		let total_detalle = moneda == "soles" ? "S/. " + total_venta : total_venta + " $";

		$('#subtotal_detalle').text(subtotal_detalle);
		$('#igv_detalle').text(igv_detalle);
		$('#total_detalle').text(total_detalle);
		$('#total_cantidad').text(cantidadTotal.toFixed(2));

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
		let total_pagos = moneda == "soles" ? "S/. " + total_venta : total_venta + " $";

		$('#subtotal_pagos').text(subtotal_pagos);
		$('#vueltos_pagos').text(vueltos_pagos);
		$('#total_pagos').text(total_pagos);

		let comentario_val = comentario_interno == "" ? "Sin registrar." : comentario_interno;

		$('#comentario_interno_detalle').text(comentario_val);
		$('#atendido_venta').text(capitalizarTodasLasPalabras(usuario));
	});
}

init();