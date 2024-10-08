var tabla;

function init() {
	mostrarform(false);

	listar();

	$('#formulario').on('submit', function (e) {
		guardaryeditar(e);
	});

	listarSelectCajas();

	$.post('../ajax/locales.php?op=selectLocalesUsuario', function (r) {
		console.log(r);
		$('#idlocal').html(r);
		$('#idlocal').selectpicker('refresh');
	});

	$('#mCajas').addClass('treeview active');
	$('#lGastos').addClass('active');
}

function listarSelectCajas() {
	$.post('../ajax/cajas.php?op=selectCajas', function (r) {
		console.log(r);
		$('#idcaja').html(r);
		$('#idcaja').selectpicker('refresh');
	});
}

function mostrarform(flag) {
	limpiar();
	if (flag) {
		$(".listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
	}
	else {
		$(".listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

function cancelarform() {
	limpiar();
	mostrarform(false);
}

function limpiar() {
	$("#idcaja").val("");
	$('#idcaja').selectpicker('refresh');
	$("#monto").val("");
	$("#monto_caja").val("");
	$("#descripcion").val("");
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
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			'ajax':
			{
				url: '../ajax/gastos.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
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
			'lengthMenu': [5, 10, 25, 75, 100],
			'aProcessing': true,
			'aServerSide': true,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'exportOptions': {
						'columns': ':not(:first-child)'
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			'ajax':
			{
				url: '../ajax/gastos.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			}
		}).DataTable();
}

var montoCaja = 0;
var selectCaja = 0;

function changeCaja(e) {
	let idcaja = e.value;

	if (e.value != "") {
		$.post("../ajax/gastos.php?op=validarCaja", { idcaja: idcaja }, function (e) {
			e = limpiarCadena(e);
			console.log(e);
			const obj = JSON.parse(e);
			console.log(obj);

			if (obj.estado != "aperturado") {
				bootbox.alert("La caja que ha seleccionado se encuentra cerrada.");
				$("#idcaja").val("");
				$("#idcaja").selectpicker("refresh");
				return;
			} else {
				selectCaja = document.getElementById("idcaja");
				montoCaja = selectCaja.options[selectCaja.selectedIndex].getAttribute("data-monto");
				document.getElementById("monto_caja").value = montoCaja;
			}
		});
	}
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);

	var idlocal = $("#idcaja option:selected").data("idlocal");
	var formData = new FormData($("#formulario")[0]);
	formData.append('idlocal', idlocal);
	formData.append('monto_caja', montoCaja);

	$.ajax({
		url: "../ajax/gastos.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El monto que desea gastar no puede ser mayor al monto total de la caja.") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			}
			limpiar();
			bootbox.alert(datos);
			mostrarform(false);
			tabla.ajax.reload();
			listarSelectCajas();
		}
	});
}

function modalDetalles(idgasto) {
	$.post("../ajax/gastos.php?op=mostrar", { idgasto: idgasto }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		Object.keys(data).forEach(function (key) {
			data[key] = data[key].toUpperCase();
		});

		$("#local_gasto").text(data.local);
		$("#usuario_gasto").text(data.nombre);

		let fechaHora = data.fecha.split(" ");
		let fecha = fechaHora[0];
		let hora = fechaHora[1];

		$("#fecha_gasto").text(formatFecha(fecha));
		$("#hora_gasto").text(formatHora(hora));

		$("#monto_caja_mostrar").text("S/. " + parseFloat(data.monto_caja).toFixed(2));
		$("#monto_gasto_mostrar").text("S/. " + parseFloat(data.monto).toFixed(2));
		$("#monto_total_mostrar").text("S/. " + parseFloat(data.monto_total).toFixed(2));
		$("#descripcion_gasto").text((data.descripcion != "") ? data.descripcion : "Sin registrar.");

		$("#myModal").modal("show");
	});
}

function eliminar(idgasto, idcaja) {
	bootbox.confirm("¿Estás seguro de eliminar el gasto?", function (result) {
		if (result) {
			$.post("../ajax/gastos.php?op=eliminar", { idgasto: idgasto, idcaja: idcaja }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				listarSelectCajas();
			});
		}
	})
}

init();