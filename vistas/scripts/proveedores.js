var tabla;
var tabla2;

function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	$('#mCompras').addClass("treeview active");
	$('#lProveedor').addClass("active");
}

function limpiar() {
	$("#idproveedor").val("");
	$("#nombre").val("");
	$("#tipo_documento").val("");
	$("#num_documento").val("");
	$("#direccion").val("");
	$("#descripcion").val("");
	$("#telefono").val("");
	$("#email").val("");
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
				url: '../ajax/proveedores.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(2), td:eq(3), td:eq(5), td:eq(6), td:eq(8), td:eq(9), td:eq(10), td:eq(11)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/proveedores.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			console.log(datos);
			datos = limpiarCadena(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El proveedor no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			} else if (!isNaN(Number(datos))) {
				limpiar();
				bootbox.alert("Proveedor registrado correctamente");
				mostrarform(false);
				tabla.ajax.reload();
			} else {
				limpiar();
				bootbox.alert(datos);
				mostrarform(false);
				tabla.ajax.reload();
			}
		}
	});
}

function mostrar(idproveedor) {
	$.post("../ajax/proveedores.php?op=mostrar", { idproveedor: idproveedor }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);

		console.log(data);

		$("#nombre").val(data.nombre);
		$("#tipo_documento").val(data.tipo_documento);
		$("#num_documento").val(data.num_documento);
		$("#direccion").val(data.direccion);
		$("#descripcion").val(data.descripcion);
		$("#telefono").val(data.telefono);
		$("#email").val(data.email);
		$("#idproveedor").val(data.idproveedor);
	})
}

function desactivar(idproveedor) {
	bootbox.confirm("¿Está seguro de desactivar al proveedor?", function (result) {
		if (result) {
			$.post("../ajax/proveedores.php?op=desactivar", { idproveedor: idproveedor }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function activar(idproveedor) {
	bootbox.confirm("¿Está seguro de activar al proveedor?", function (result) {
		if (result) {
			$.post("../ajax/proveedores.php?op=activar", { idproveedor: idproveedor }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function eliminar(idproveedor) {
	bootbox.confirm("¿Estás seguro de eliminar al proveedor?", function (result) {
		if (result) {
			$.post("../ajax/proveedores.php?op=eliminar", { idproveedor: idproveedor }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function modalComprasProveedor(idproveedor, nombre, tipo_documento, num_documento) {
	$(".proveedor_detalles").text(capitalizarTodasLasPalabras(`${nombre} - ${tipo_documento}: ${num_documento}`));

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
					title: 'HISTORIAL DE COMPRAS DEL PROVEEDOR: ' + capitalizarTodasLasPalabras(nombre),
					filename: 'historial_compra',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'historial_compra_' + randomNum;
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
				url: '../ajax/proveedores.php?op=listarComprasProveedor',
				data: { idproveedor: idproveedor },
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7)').addClass('nowrap-cell');
			},
			"initComplete": function (settings, json) {
				$('#myModal1').modal('show');
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
		let cantidadTotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;
			let precio = moneda == "soles" ? "S/. " + item.precio_compra : item.precio_compra + " $";
			let descuento = moneda == "soles" ? "S/. " + item.descuento : item.descuento + " $";
			let subtotalFila = ((item.cantidad * item.precio_compra) - item.descuento).toFixed(2);
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
			subtotal += item.cantidad * item.precio_compra;
			// Calcular cantidad
			cantidadTotal += Number(item.cantidad);
		});

		let igv = subtotal * (impuesto);

		let subtotal_detalle = moneda == "soles" ? "S/. " + subtotal.toFixed(2) : subtotal.toFixed(2) + " $";
		let igv_detalle = moneda == "soles" ? "S/. " + igv.toFixed(2) : igv.toFixed(2) + " $";
		let total_detalle = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

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
		let total_pagos = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

		$('#subtotal_pagos').text(subtotal_pagos);
		$('#vueltos_pagos').text(vueltos_pagos);
		$('#total_pagos').text(total_pagos);

		let comentario_val = comentario_interno == "" ? "Sin registrar." : comentario_interno;

		$('#comentario_interno_detalle').text(comentario_val);
		$('#atendido_compra').text(capitalizarTodasLasPalabras(usuario));
	});
}

function modalImpresion(idcompra, num_comprobante) {
	$("#num_comprobante_final").text(num_comprobante);

	limpiarModalImpresion();

	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto, index) {
		var ruta = (index === 0) ? "exTicketCompra" : "exA4Compra";
		$("#myModal3 a:has(button:contains('" + texto + "'))").attr("href", "../reportes/" + ruta + ".php?id=" + idcompra);
	});
}

function limpiarModalImpresion() {
	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto) {
		$("#myModal3 a:has(button:contains('" + texto + "'))").removeAttr("href");
	});
}


init();