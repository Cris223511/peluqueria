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

	$.post("../ajax/marcas.php?op=selectMarcas", function (r) {
		console.log(r);
		$("#marcaBuscar").html(r);
		$('#marcaBuscar').selectpicker('refresh');
	})

	$.post("../ajax/categoria.php?op=selectCategoria", function (r) {
		console.log(r);
		$("#categoriaBuscar").html(r);
		$('#categoriaBuscar').selectpicker('refresh');
	})

	$('#mReportesP').addClass("treeview active");
	$('#lReporteProductosV').addClass("active");
}

function listar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";
	let param4 = "";
	let param5 = "";
	let param6 = "";
	let param7 = "";

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
				url: '../ajax/reporte.php?op=listarArticulosMasVendidos',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3, param4: param4, param5: param5, param6: param6, param7: param7 },
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
	const selects = ["fecha_inicio", "fecha_fin", "localBuscar", "marcaBuscar", "categoriaBuscar", "usuarioBuscar", "estadoBuscar"];

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

	// Obtener los selectores
	const fecha_inicio = document.getElementById("fecha_inicio");
	const fecha_fin = document.getElementById("fecha_fin");
	const localBuscar = document.getElementById("localBuscar");
	const marcaBuscar = document.getElementById("marcaBuscar");
	const categoriaBuscar = document.getElementById("categoriaBuscar");
	const usuarioBuscar = document.getElementById("usuarioBuscar");
	const estadoBuscar = document.getElementById("estadoBuscar");

	if (fecha_inicio.value == "" && fecha_fin.value == "" && localBuscar.value == "" && marcaBuscar.value == "" && categoriaBuscar.value == "" && usuarioBuscar.value == "" && estadoBuscar.value == "") {
		bootbox.alert("Debe seleccionar al menos un campo para realizar la búsqueda.");
		return;
	}

	if (fecha_inicio.value > fecha_fin.value) {
		bootbox.alert("La fecha inicial no puede ser mayor que la fecha final.");
		return;
	}

	param1 = fecha_inicio.value;
	param2 = fecha_fin.value;
	param3 = localBuscar.value;
	param4 = marcaBuscar.value;
	param5 = categoriaBuscar.value;
	param6 = usuarioBuscar.value;
	param7 = estadoBuscar.value;

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
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/reporte.php?op=listarArticulosMasVendidos',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3, param4: param4, param5: param5, param6: param6, param7: param7 },
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