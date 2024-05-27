var tabla;

//Función que se ejecuta al inicio
function init() {
	listar();

	$.post("../ajax/metodo_pago.php?op=selectMetodoPago", function (r) {
		console.log(r);
		$("#metodopagoBuscar").html(r);
		$('#metodopagoBuscar').selectpicker('refresh');
	})

	$('#mReportesM').addClass("treeview active");
	$('#lReporteProformaMetodoPago').addClass("active");
}

var valoresMetodoPago = [];

function agregarPago() {
	var valorSeleccionado = $('#metodopagoBuscar').val();
	var textoSeleccionado = $('#metodopagoBuscar option:selected').text().trim();

	if (valorSeleccionado && !$('#contenedorPagos input[type="hidden"][value="' + valorSeleccionado + '"]').length) {
		var nuevoPago =
			'<input type="hidden" value="' + valorSeleccionado + '">' +
			'<span class="item_pago">' + textoSeleccionado +
			'<a href="#" class="borrar_pago" onclick="borrarPago(this); return false;"></a>' +
			'</span>';

		$('#contenedorPagos').append(nuevoPago);

		// Agregar el valor al array
		valoresMetodoPago.push(valorSeleccionado);
	} else {
		bootbox.alert("No puede agregar el mismo método de pago dos veces.");
	}

	$("#metodopagoBuscar").val("");
	$("#metodopagoBuscar").selectpicker('refresh');
}

function borrarPago(e) {
	var $itemPago = $(e).closest('.item_pago');
	var valorBorrado = $itemPago.prev('input[type="hidden"]').val();
	valoresMetodoPago = valoresMetodoPago.filter(function (valor) { return valor !== valorBorrado; });
	$itemPago.prev('input[type="hidden"]').remove();
	$itemPago.remove();
}

function listar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";

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
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 7.5;
						doc.styles.tableHeader.fontSize = 7.5;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/reporte.php?op=listarProformasMetodosPago',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3 },
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin", "metodopagoBuscar"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
		$("#" + selectId).selectpicker('refresh');
	}

	$('#contenedorPagos').empty();
	valoresMetodoPago = [];

	listar();
}

function buscar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";

	// Obtener los selectores
	const fecha_inicio = document.getElementById("fecha_inicio");
	const fecha_fin = document.getElementById("fecha_fin");

	param1 = fecha_inicio.value;
	param2 = fecha_fin.value;
	param3 = valoresMetodoPago.join(',');

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
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 7.5;
						doc.styles.tableHeader.fontSize = 7.5;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/reporte.php?op=listarProformasMetodosPago',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3 },
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11)').addClass('nowrap-cell');
			}
		}).DataTable();
}

init();