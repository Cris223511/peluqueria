var tabla;
let select = $("#idlocal"); // select

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})

	$("#imagenmuestra").hide();
	//Mostramos los permisos
	$.post("../ajax/usuario.php?op=permisos&id=", function (r) {
		$("#permisos").html(r);
	});

	$('#mAcceso').addClass("treeview active");
	$('#lUsuarios').addClass("active");

	$("#checkAll").prop("checked", false);
}

function toggleCheckboxes(checkbox) {
	var checkboxes = document.querySelectorAll('#permisos input[type="checkbox"]');

	checkboxes.forEach(function (cb) {
		cb.checked = checkbox.checked;
	});
}

function cargarLocalDisponible() {
	select.empty();
	// Cargamos los items al select "local principal"
	$.post("../ajax/locales.php?op=selectLocales", function (data) {
		// console.log(data);
		objSelects = JSON.parse(data);
		console.log(objSelects);
		if (objSelects.length != 0) {
			select.html('<option value="">- Seleccione -</option>');

			objSelects.locales.forEach(function (opcion) {
				select.append('<option value="' + opcion.idlocal + '" data-local-ruc="' + opcion.local_ruc + '">' + opcion.titulo + '</option>');
			});
			select.selectpicker('refresh');
		} else {
			console.log("no hay datos =)")
		}
		limpiar();
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

//Función limpiar
function limpiar() {
	$("#nombre").val("");
	$("#idlocal").val($("#idlocal option:first").val());
	$("#idlocal").selectpicker('refresh');
	$("tipo_documento").val("");
	$("#num_documento").val("");
	$("#direccion").val("");
	$("#telefono").val("");
	$("#email").val("");
	$("#local_ruc").val("");
	$("#cargo").val("admin");
	$('#cargo option[value="superadmin"]').remove();
	$("#cargo").selectpicker('refresh');
	$("#login").val("");
	$("#clave").val("");
	$("#imagen").val("");
	$("#imagenmuestra").attr("src", "");
	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#idusuario").val("");

	$("#checkAll").prop("checked", false);
}

//Función mostrar formulario
function mostrarform(flag) {
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
		cargarLocalDisponible();

		// Desmarcamos todos los selects excepto el de "Acceso"
		$("#permisos input[type='checkbox']").each(function () {
			if ($(this).val() !== "2") {
				$(this).prop('checked', false);
			}
		});
	}
}

//Función cancelarform
function cancelarform() {
	limpiar();
	mostrarform(false);
}

function verificarCargo(cargo) {
	console.log(cargo);
	$('#cargo option[value="superadmin"]').remove();

	if (cargo == "superadmin") {
		$('#cargo').prepend('<option value="superadmin">Superadministrador</option>');
		$('#cargo').selectpicker('refresh');
	}
}

//Función Listar
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
						doc.defaultStyle.fontSize = 8.5;
						doc.styles.tableHeader.fontSize = 8.5;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/usuario.php?op=listar',
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
			"iDisplayLength": 5,//Paginación
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11)').addClass('nowrap-cell');
			},
		}).DataTable();
}
//Función para guardar o editar

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled", true);
	$("input[name='permiso[]'][value='2']").prop("disabled", false);
	var formData = new FormData($("#formulario")[0]);
	$("input[name='permiso[]'][value='2']").prop("disabled", true);

	$.ajax({
		url: "../ajax/usuario.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El nombre del usuario que ha ingresado ya existe." || datos == "El número de documento que ha ingresado ya existe.") {
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

function mostrar(idusuario) {
	$.post("../ajax/usuario.php?op=mostrar", { idusuario: idusuario }, function (data, status) {
		// console.log(data);
		data = JSON.parse(data);
		console.log(data);
		mostrarform(true);

		$("#nombre").val(data.nombre);
		$("#tipo_documento").val(data.tipo_documento);
		$("#tipo_documento").trigger("change");
		$("#tipo_documento").selectpicker('refresh');
		$("#num_documento").val(data.num_documento);
		$("#direccion").val(data.direccion);
		$("#telefono").val(data.telefono);
		$("#email").val(data.email);
		$("#idlocal").val(data.idlocal);
		$("#idlocal").selectpicker('refresh');
		$("#local_ruc").val(data.local_ruc);
		$("#cargo").val(data.cargo);
		$("#cargo").selectpicker('refresh');
		$("#login").val(data.login);
		$("#clave").val(data.clave);
		$("#imagenmuestra").show();
		$("#imagenmuestra").attr("src", "../files/usuarios/" + data.imagen);
		$("#imagenactual").val(data.imagen);
		$("#idusuario").val(data.idusuario);

		$("#checkAll").prop("checked", false);
	});

	$.post("../ajax/usuario.php?op=permisos&id=" + idusuario, function (r) {
		$("#permisos").html(r);
	});
}

//Función para desactivar registros
function desactivar(idusuario) {
	bootbox.confirm("¿Está seguro de desactivar el usuario?", function (result) {
		if (result) {
			$.post("../ajax/usuario.php?op=desactivar", { idusuario: idusuario }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para activar registros
function activar(idusuario) {
	bootbox.confirm("¿Está seguro de activar el usuario?", function (result) {
		if (result) {
			$.post("../ajax/usuario.php?op=activar", { idusuario: idusuario }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para eliminar los registros
function eliminar(idusuario) {
	bootbox.confirm("¿Estás seguro de eliminar el usuario?", function (result) {
		if (result) {
			$.post("../ajax/usuario.php?op=eliminar", { idusuario: idusuario }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				cargarLocalDisponible();
			});
		}
	})
}

init();