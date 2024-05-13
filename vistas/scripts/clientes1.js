var tabla;
var tabla2;

function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	$('#mVentas').addClass("treeview active");
	$('#lClientes').addClass("active");

	$.post('../ajax/locales.php?op=selectLocalesUsuario', function (r) {
		console.log(r);
		$('#idlocal').html(r);
		$('#idlocal').selectpicker('refresh');
		actualizarRUC();
	});
}

function actualizarRUC() {
	const selectLocal = document.getElementById("idlocal");
	const localRUCInput = document.getElementById("local_ruc");
	const selectedOption = selectLocal.options[selectLocal.selectedIndex];

	if (selectedOption.value !== "") {
		const localRUC = selectedOption.getAttribute('data-local-ruc');
		localRUCInput.value = localRUC;
	} else {
		localRUCInput.value = "";
	}
}

function limpiar() {
	$("#idcliente").val("");
	$("#nombre").val("");
	$("#tipo_documento").val("");
	$("#num_documento").val("");
	$("#direccion").val("");
	$("#descripcion").val("");
	$("#telefono").val("");
	$("#email").val("");

	$("#idlocal").val($("#idlocal option:first").val());
	$("#idlocal").selectpicker('refresh');

	actualizarRUC();
}

function mostrarform(flag) {
	limpiar();
	if (flag) {
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
	}
	else {
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

function cancelarform() {
	limpiar();
	mostrarform(false);
}

function listar() {
	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
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
				url: '../ajax/clientes.php?op=listar',
				type: "get",
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
			"iDisplayLength": 5,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(2), td:eq(3), td:eq(4), td:eq(6), td:eq(7), td:eq(9), td:eq(10), td:eq(11), td:eq(12)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/clientes.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El cliente no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			}
			limpiar();
			bootbox.alert("Cliente registrado correctamente.");
			mostrarform(false);
			tabla.ajax.reload();
		}
	});
}

function mostrar(idcliente) {
	$.post("../ajax/clientes.php?op=mostrar", { idcliente: idcliente }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);

		console.log(data);

		$("#nombre").val(data.nombre);
		$("#idlocal").val(data.idlocal);
		$('#idlocal').selectpicker('refresh');
		$("#tipo_documento").val(data.tipo_documento);
		$("#num_documento").val(data.num_documento);
		$("#direccion").val(data.direccion);
		$("#descripcion").val(data.descripcion);
		$("#telefono").val(data.telefono);
		$("#email").val(data.email);
		$("#idcliente").val(data.idcliente);

		actualizarRUC();
	})
}

function desactivar(idcliente) {
	bootbox.confirm("¿Está seguro de desactivar al cliente?", function (result) {
		if (result) {
			$.post("../ajax/clientes.php?op=desactivar", { idcliente: idcliente }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function activar(idcliente) {
	bootbox.confirm("¿Está seguro de activar al cliente?", function (result) {
		if (result) {
			$.post("../ajax/clientes.php?op=activar", { idcliente: idcliente }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function eliminar(idcliente) {
	bootbox.confirm("¿Estás seguro de eliminar al cliente?", function (result) {
		if (result) {
			$.post("../ajax/clientes.php?op=eliminar", { idcliente: idcliente }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function verificarModalCliente(idcliente, nombre, tipo_documento, num_documento, local) {
	var $btnVentas = $('#myModal .btn-ventas');
	var $btnProformas = $('#myModal .btn-proformas');

	$btnVentas.data('idcliente', idcliente);
	$btnVentas.data('nombre', nombre);
	$btnVentas.data('tipo-documento', tipo_documento);
	$btnVentas.data('num-documento', num_documento);
	$btnVentas.data('local', local);

	$btnProformas.data('idcliente', idcliente);
	$btnProformas.data('nombre', nombre);
	$btnProformas.data('tipo-documento', tipo_documento);
	$btnProformas.data('num-documento', num_documento);
	$btnProformas.data('local', local);

	$('#myModal').modal('show');
}

$('#myModal .btn-ventas').on('click', function () {
	var idcliente = $(this).data('idcliente');
	var nombre = $(this).data('nombre');
	var tipo_documento = $(this).data('tipo-documento');
	var num_documento = $(this).data('num-documento');
	var local = $(this).data('local');
	modalVentasCliente(idcliente, nombre, tipo_documento, num_documento, local);
});

// Asignar evento de clic al botón "VER DE PROFORMAS"
$('#myModal .btn-proformas').on('click', function () {
	var idcliente = $(this).data('idcliente');
	var nombre = $(this).data('nombre');
	var tipo_documento = $(this).data('tipo-documento');
	var num_documento = $(this).data('num-documento');
	var local = $(this).data('local');
	modalProformasCliente(idcliente, nombre, tipo_documento, num_documento, local);
});

function modalVentasCliente(idcliente, nombre, tipo_documento, num_documento, local) {
	$(".cliente_detalles").text(capitalizarTodasLasPalabras(`${nombre} - ${tipo_documento}: ${num_documento} - ${local}`));

	tabla2 = $('#tbldetalles').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
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
					title: 'HISTORIAL DE VENTAS DEL CLIENTE: ' + capitalizarTodasLasPalabras(nombre),
					filename: 'historial_venta',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'historial_venta_' + randomNum;
						$.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 10;
						doc.styles.tableHeader.fontSize = 10;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/clientes.php?op=listarVentasCliente',
				data: { idcliente: idcliente },
				type: "get",
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
			"iDisplayLength": 5,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			},
			"initComplete": function (settings, json) {
				$('#myModal1').modal('show');
			}
		}).DataTable();
}

function modalDetalles(idventa, usuario, num_comprobante, cliente, cliente_tipo_documento, cliente_num_documento, cliente_direccion, impuesto, total_venta, vuelto) {
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
		$('#direccion_cliente').text((cliente_direccion != "") ? cliente_direccion : "Sin registrar");
		$('#nota_de_venta').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('.detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;

			let row = `
                <tr>
                    <td width: 44%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(descripcion)}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.cantidad}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.precio_venta}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.descuento}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${((item.cantidad * item.precio_venta) - item.descuento).toFixed(2)}</td>
                </tr>`;

			tbody.append(row);

			// Calcular subtotal
			subtotal += item.cantidad * item.precio_venta;
		});

		let igv = subtotal * (impuesto);

		$('#subtotal_detalle').text(subtotal.toFixed(2));
		$('#igv_detalle').text(igv.toFixed(2));
		$('#total_detalle').text(total_venta);

		// Actualizar detalles de la tabla pagos
		let tbodyPagos = $('.detallesPagosFinal tbody');
		tbodyPagos.empty();

		let subtotalPagos = 0;

		data.pagos.forEach(item => {
			let row = `
                <tr>
                    <td width: 80%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(item.metodo_pago)}</td>
                    <td width: 20%; min-width: 40px; white-space: nowrap;">${item.monto}</td>
                </tr>`;

			tbodyPagos.append(row);

			// Calcular subtotalPagos
			subtotalPagos += parseFloat(item.monto);
		});

		$('#subtotal_pagos').text(subtotalPagos.toFixed(2));
		$('#vueltos_pagos').text(vuelto);
		$('#total_pagos').text(total_venta);

		$('#atendido_venta').text(capitalizarTodasLasPalabras(usuario));
	});
}

function modalProformasCliente(idcliente, nombre, tipo_documento, num_documento, local) {
	$(".cliente_detalles").text(capitalizarTodasLasPalabras(`${nombre} - ${tipo_documento}: ${num_documento} - ${local}`));

	tabla2 = $('#tbldetalles2').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
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
					title: 'HISTORIAL DE COTIZACIÓN DEL CLIENTE: ' + capitalizarTodasLasPalabras(nombre),
					filename: 'historial_cotizacion',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'historial_cotizacion_' + randomNum;
						$.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 10;
						doc.styles.tableHeader.fontSize = 10;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/clientes.php?op=listarProformasCliente',
				data: { idcliente: idcliente },
				type: "get",
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
			"iDisplayLength": 5,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			},
			"initComplete": function (settings, json) {
				$('#myModal3').modal('show');
			}
		}).DataTable();
}

function modalDetalles2(idproforma, usuario, num_comprobante, cliente, cliente_tipo_documento, cliente_num_documento, cliente_direccion, impuesto, total_venta, vuelto) {
	$.post("../ajax/proforma.php?op=listarDetallesProductoVenta", { idproforma: idproforma }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		// Actualizar datos del cliente
		let nombreCompleto = cliente;

		if (cliente_tipo_documento && cliente_num_documento) {
			nombreCompleto += ' - ' + cliente_tipo_documento + ': ' + cliente_num_documento;
		}

		$('#nombre_cliente2').text(nombreCompleto);
		$('#direccion_cliente2').text((cliente_direccion != "") ? cliente_direccion : "Sin registrar");
		$('#nota_de_venta2').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('.detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;

			let row = `
                <tr>
                    <td width: 44%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(descripcion)}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.cantidad}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.precio_venta}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.descuento}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${((item.cantidad * item.precio_venta) - item.descuento).toFixed(2)}</td>
                </tr>`;

			tbody.append(row);

			// Calcular subtotal
			subtotal += item.cantidad * item.precio_venta;
		});

		let igv = subtotal * (impuesto);

		$('#subtotal_detalle2').text(subtotal.toFixed(2));
		$('#igv_detalle2').text(igv.toFixed(2));
		$('#total_detalle2').text(total_venta);

		// Actualizar detalles de la tabla pagos
		let tbodyPagos = $('.detallesPagosFinal tbody');
		tbodyPagos.empty();

		let subtotalPagos = 0;

		data.pagos.forEach(item => {
			let row = `
                <tr>
                    <td width: 80%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(item.metodo_pago)}</td>
                    <td width: 20%; min-width: 40px; white-space: nowrap;">${item.monto}</td>
                </tr>`;

			tbodyPagos.append(row);

			// Calcular subtotalPagos
			subtotalPagos += parseFloat(item.monto);
		});

		$('#subtotal_pagos2').text(subtotalPagos.toFixed(2));
		$('#vueltos_pagos2').text(vuelto);
		$('#total_pagos2').text(total_venta);

		$('#atendido_venta2').text(capitalizarTodasLasPalabras(usuario));
	});
}

init();