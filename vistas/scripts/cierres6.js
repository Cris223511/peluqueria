var tabla;
var tabla2;
var idSession;
// var idLocal;

function init() {
	$('#listadoregistros').show();
	$('#formularioregistros').hide();
	$('#btnagregar').show();

	listar();

	$('#formulario').on('submit', function (e) {
		guardaryeditar(e);
	});

	$.post('../ajax/locales.php?op=selectLocalesUsuario', function (r) {
		console.log(r);
		$('#idlocal').html(r);
		$('#idlocal').selectpicker('refresh');
	});

	$('#mCajas').addClass('treeview active');
	$('#lCierres').addClass('active');
}

function listar() {
	let param1 = '';
	let param2 = '';
	let param3 = '';

	tabla = $('#tbllistado').dataTable(
		{
			'lengthMenu': [5, 10, 25, 75, 100],
			'aProcessing': true,
			'aServerSide': true,
			dom: '<Bl<f>rtip>',
			buttons: [
				{
					extend: 'copyHtml5',
					text: 'Copiar',
					'exportOptions': {
						'columns': ':not(:first-child)'
					}
				},
				{
					extend: 'excelHtml5',
					text: 'Excel',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					extend: 'csvHtml5',
					text: 'CSV',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					'extend': 'pdfHtml5',
					'text': 'PDF',
					'title': 'FLUJO DE CAJA (CIERRES DE CAJA)',
					'filename': 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'action': function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
				{
					extend: 'colvis',
					text: 'VER / OCULTAR',
					'exportOptions': {
						'columns': ':not(:first-child)'
					}
				}
			],
			'ajax':
			{
				url: '../ajax/cajas.php?op=listar2',
				type: 'get',
				data: { param1: param1, param2: param2, param3: param3 },
				dataType: 'json',
				error: function (e) {
					console.log(e.responseText);
				}
			},
			'language': {
				'lengthMenu': 'Mostrar : _MENU_ registros',
				'buttons': {
					'copyTitle': 'Tabla Copiada',
					'copySuccess': {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			'bDestroy': true,
			'iDisplayLength': 5,
			'order': [],
			'createdRow': function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin", "idlocal"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
		$("#" + selectId).selectpicker('refresh');
	}

	listar();
}

//Función buscar
function buscar() {
	let param1 = '';
	let param2 = '';
	let param3 = '';

	const selectFechaInicio = document.getElementById('fecha_inicio');
	const selectFechaFin = document.getElementById('fecha_fin');
	const selectLocal = document.getElementById('idlocal');

	if (selectFechaInicio.value === "" && selectFechaFin.value === "" && selectLocal.value === "") {
		alert("Debe seleccionar al menos un campo para realizar la búsqueda.");
		return;
	}

	if (selectFechaInicio.value !== "" || selectFechaFin.value !== "") {
		if (selectFechaInicio.value === "" || selectFechaFin.value === "") {
			alert("Los campos de fecha inicial y fecha final son obligatorios.");
			return;
		} else {
			const fechaInicio = new Date(selectFechaInicio.value);
			const fechaFin = new Date(selectFechaFin.value);

			if (fechaInicio > fechaFin) {
				alert("La fecha inicial no puede ser mayor que la fecha final.");
				return;
			}
		}
	}

	param1 = selectFechaInicio.value;
	param2 = selectFechaFin.value;
	param3 = selectLocal.value;

	tabla = $('#tbllistado').dataTable(
		{
			'lengthMenu': [5, 10, 25, 75, 100],//mostramos el menú de registros a revisar
			'aProcessing': true,//Activamos el procesamiento del datatables
			'aServerSide': true,//Paginación y filtrado realizados por el servidor
			dom: '<Bl<f>rtip>',//Definimos los elementos del control de tabla
			buttons: [
				{
					extend: 'copyHtml5',
					text: 'Copiar',
					'exportOptions': {
						'columns': ':not(:first-child)'
					}
				},
				{
					extend: 'excelHtml5',
					text: 'Excel',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					extend: 'csvHtml5',
					text: 'CSV',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					'extend': 'pdfHtml5',
					'text': 'PDF',
					'title': 'FLUJO DE CAJA (CIERRES DE CAJA)',
					'filename': 'cierre_caja',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'action': function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
				{
					extend: 'colvis',
					text: 'VER / OCULTAR',
					'exportOptions': {
						'columns': ':not(:first-child)'
					}
				}
			],
			'ajax':
			{
				url: '../ajax/cajas.php?op=listar2',
				type: 'get',
				data: { param1: param1, param2: param2, param3: param3 },
				dataType: 'json',
				error: function (e) {
					console.log(e.responseText);
				}
			},
			'language': {
				'lengthMenu': 'Mostrar : _MENU_ registros',
				'buttons': {
					'copyTitle': 'Tabla Copiada',
					'copySuccess': {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			'bDestroy': true,
			'iDisplayLength': 5,//Paginación
			'order': [],
			'createdRow': function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
		}).DataTable();
}

init();

function modalDetalles(idcaja, idcaja_cerrada, fecha, fecha_cierre) {
	$("#fecha_hora_caja").text(fecha);
	$("#fecha_hora_cierre_caja").text(fecha_cierre);

	tabla2 = $('#tbldetalles').dataTable(
		{
			'lengthMenu': [5, 10, 25, 75, 100],
			'aProcessing': true,
			'aServerSide': true,
			dom: '<Bl<f>rtip>',
			buttons: [
				{
					extend: 'copyHtml5',
					text: 'Copiar',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					}
				},
				{
					extend: 'excelHtml5',
					text: 'Excel',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'productos_cierre_caja',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'productos_cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					extend: 'csvHtml5',
					text: 'CSV',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'productos_cierre_caja',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'productos_cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
					}
				},
				{
					extend: 'pdfHtml5',
					text: 'PDF',
					title: 'FLUJO DE CAJA (CIERRES DE CAJA)',
					filename: 'productos_cierre_caja',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					},
					action: function (e, dt, button, config) {
						var randomNum = Math.floor(Math.random() * 100000000);
						config.filename = 'productos_cierre_caja_' + randomNum;
						$.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
				{
					extend: 'colvis',
					text: 'VER / OCULTAR',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					}
				}
			],
			'ajax':
			{
				url: '../ajax/cajas.php?op=listarDetallesProductosCaja',
				type: 'get',
				data: { idcaja: idcaja, idcaja_cerrada: idcaja_cerrada },
				dataType: 'json',
				error: function (e) {
					console.log(e.responseText);
				}
			},
			'language': {
				'lengthMenu': 'Mostrar : _MENU_ registros',
				'buttons': {
					'copyTitle': 'Tabla Copiada',
					'copySuccess': {
						_: '%d líneas copiadas',
						1: '1 línea copiada'
					}
				}
			},
			'bDestroy': true,
			'iDisplayLength': 5,
			'order': [],
			'createdRow': function (row, data, dataIndex) {
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6)').addClass('nowrap-cell');
			},
			"initComplete": function (settings, json) {
				$("#myModal").modal("show");
			}
		}).DataTable();
}

function prueba(idcaja, idcaja_cerrada) {
	$.post("../ajax/cajas.php?op=prueba", { idcaja: idcaja, idcaja_cerrada: idcaja_cerrada }, function (e) {
		console.log(e);
		console.log(JSON.parse(e));
	});
}

function eliminar(idcaja) {
	bootbox.confirm("¿Estás seguro de eliminar la caja?", function (result) {
		if (result) {
			$.post("../ajax/cajas.php?op=eliminarCajaCerrada", { idcaja: idcaja }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}