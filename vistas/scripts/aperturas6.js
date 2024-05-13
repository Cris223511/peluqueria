var tabla;
var idSession;
var idLocal;
var contador;
var vendido;

function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	$.post("../ajax/locales.php?op=selectLocalesUsuario", function (r) {
		console.log(r);
		$("#idlocal").html(r);
		$('#idlocal').selectpicker('refresh');
		$("#idlocal2").html(r);
		$('#idlocal2').selectpicker('refresh');

		$.post("../ajax/usuario.php?op=getSessionId", function (r) {
			console.log(r);
			data = JSON.parse(r);
			idLocal = data.idlocal;
			$("#idlocal").val(idLocal);
			$('#idlocal').selectpicker('refresh');
		})
	});

	$.post("../ajax/usuario.php?op=selectUsuarios", function (r) {
		console.log(r);
		$("#idusuario").html(r);
		$('#idusuario').selectpicker('refresh');

		$.post("../ajax/usuario.php?op=getSessionId", function (r) {
			console.log(r);
			data = JSON.parse(r);
			idSession = data.idusuario;
			$("#idusuario").val(idSession);
			$('#idusuario').selectpicker('refresh');
		})
	})

	$('#mCajas').addClass("treeview active");
	$('#lAperturas').addClass("active");
}

function limpiar() {
	$("#idcaja").val("");
	$("#idlocal").val(idLocal);
	$('#idlocal').selectpicker('refresh');
	$("#idusuario").val(idSession);
	$('#idusuario').selectpicker('refresh');
	$("#titulo").val("");
	$("#monto").val("");
	$("#descripcion").val("");
}

function validarCaja() {
	$.post("../ajax/cajas.php?op=validarCaja", function (e) {
		console.log(e);
		if (e == "true" || e == true) {
			bootbox.alert("Usted ya tiene una caja registrada en su local actual. <br><br><strong>Nota:</strong> Solo puede agregar cuando su local no tenga una caja registrada.");
		} else {
			mostrarform(true);
		}
	});
}

function mostrarform(flag) {
	limpiar();
	$("#idlocal").prop("disabled", false);
	if (flag) {
		$(".listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
		$("#monto").attr("name", "monto");
		$("#monto").prop("disabled", false);
		$("#desbloquearMonto").hide();
		$("#desbloquearMonto i").removeClass("fa-unlock-alt").addClass("fa-lock");
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
				url: '../ajax/cajas.php?op=listar',
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);
	$("#idlocal").prop("disabled", false);
	var formData = new FormData($("#formulario")[0]);
	$("#idlocal").prop("disabled", true);

	$.ajax({
		url: "../ajax/cajas.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El nombre de la caja ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			}
			limpiar();
			bootbox.alert(datos);
			mostrarform(false);
			tabla.ajax.reload();
		}
	});
}

function mostrar(idcaja) {
	$.post("../ajax/cajas.php?op=mostrar", { idcaja: idcaja }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);
		$("#desbloquearMonto").show();
		$("#monto").removeAttr("name");

		$("#idlocal").prop("disabled", true);

		console.log(data);

		contador = data.contador;
		vendido = data.vendido;

		$("#titulo").val(data.titulo);
		$("#idlocal").val(data.idlocal);
		$('#idlocal').selectpicker('refresh');
		$("#idusuario").val(data.idusuario);
		$('#idusuario').selectpicker('refresh');
		$("#monto").val(data.monto);
		$("#descripcion").val(data.descripcion);
		$("#idcaja").val(data.idcaja);

		$("#monto").prop("disabled", true);

		$("#desbloquearMonto").attr("onclick", "verificarMonto('" + data.estado + "', " + contador + ", " + vendido + ")");
	})
}

function modalDetalles(idcaja) {
	$.post("../ajax/cajas.php?op=mostrar", { idcaja: idcaja }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		Object.keys(data).forEach(function (key) {
			data[key] = data[key].toUpperCase();
		});

		$("#local_caja").text(data.local);
		$("#usuario_caja").text(data.nombre);

		let fechaHora = data.fecha.split(" ");
		let fecha = fechaHora[0];
		let hora = fechaHora[1];

		$("#fecha_caja").text(formatFecha(fecha));
		$("#hora_caja").text(formatHora(hora));

		$("#monto_caja").text("S/. " + parseFloat(data.monto).toFixed(2));
		$("#descripcion_caja").text((data.descripcion != "") ? data.descripcion : "Sin registrar.");

		$("#myModal").modal("show");
	});
}

function verificarMonto(estado, contador, vendido) {
	if (vendido == 1) {
		bootbox.alert("La caja ya ha sido utilizada para hacer las ventas, por la cual no puede volver a modificar el monto.");
	} else if (estado == "cerrado") {
		bootbox.alert("Necesita volver a abrir la caja para modificar el monto.");
	} else if (contador != 0) {
		bootbox.confirm("¿Está seguro de modificar el monto? Le queda <strong>" + contador + "</strong> intento(s).", function (result) {
			if (result) {
				$("#monto").prop("disabled", false);
				$("#monto").attr("name", "monto");
				$("#desbloquearMonto i").removeClass("fa-lock").addClass("fa-unlock-alt");
				$("#desbloquearMonto").attr("onclick", "bloquearMonto('" + estado + "', " + contador + ", " + vendido + ")");
			}
		});
	} else {
		bootbox.alert("Usted superó el límite de intentos, por la cual no puede volver a editar el monto.");
	}
}

function bloquearMonto(estado, contador, vendido) {
	$("#monto").prop("disabled", true);
	$("#monto").removeAttr("name");
	$("#desbloquearMonto i").removeClass("fa-unlock-alt").addClass("fa-lock");
	$("#desbloquearMonto").attr("onclick", "verificarMonto('" + estado + "', " + contador + ", " + vendido + ")");
}

function cerrar(idcaja) {
	bootbox.confirm("¿Está seguro de cerrar la caja?", function (result) {
		if (result) {
			$.post("../ajax/cajas.php?op=cerrar", { idcaja: idcaja }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function aperturar(idcaja) {
	bootbox.confirm("¿Está seguro de aperturar la caja?", function (result) {
		if (result) {
			$.post("../ajax/cajas.php?op=aperturar", { idcaja: idcaja }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function eliminar(idcaja) {
	bootbox.confirm("¿Estás seguro de eliminar la caja?", function (result) {
		if (result) {
			$.post("../ajax/cajas.php?op=eliminar", { idcaja: idcaja }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin", "idlocal2"];

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
	const selectLocal = document.getElementById('idlocal2');

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
				url: '../ajax/cajas.php?op=listar',
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			}
		}).DataTable();
}

init();