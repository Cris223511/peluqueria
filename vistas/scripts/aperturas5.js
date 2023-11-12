var tabla;
var idSession;
var idLocal;

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
			],
			"ajax":
			{
				url: '../ajax/cajas.php?op=listar',
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
				$(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/cajas.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
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

		console.log(data);

		$("#titulo").val(data.titulo);
		$("#idlocal").val(data.idlocal);
		$('#idlocal').selectpicker('refresh');
		$("#idusuario").val(data.idusuario);
		$('#idusuario').selectpicker('refresh');
		$("#monto").val(data.monto);
		$("#descripcion").val(data.descripcion);
		$("#idcaja").val(data.idcaja);
	})
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

init();