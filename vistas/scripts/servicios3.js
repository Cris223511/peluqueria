var tabla;
var siguienteCorrelativo = "";

function actualizarCorrelativo() {
	$.post("../ajax/servicios.php?op=getLastCodigo", function (num) {
		console.log(num);
		siguienteCorrelativo = generarSiguienteCorrelativo(num);
		$("#codigo").val(siguienteCorrelativo);
	});
}

function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});
	$('#mServicios').addClass("treeview active");

	actualizarCorrelativo();
}

function limpiar() {
	$("#idservicio").val("");
	$("#titulo").val("");
	$("#codigo_barra").val("");
	$("#codigo").val(siguienteCorrelativo);
	$("#descripcion").val("");
	$("#costo").val("");

	$(".btn1").show();
	$(".btn2").hide();
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
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/servicios.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8)').addClass('nowrap-cell');
			},
			"initComplete": function () {
				agregarBuscadorColumna(this.api(), 2, "Buscar por código.");
			},
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);
	formatearNumeroCorrelativo();

	var codigoBarra = $("#codigo_barra").val();

	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/servicios.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El nombre del servicio ya existe." || datos == "El código de barra del servicio que ha ingresado ya existe." || datos == "El código del servicio ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				return;
			}
			limpiar();
			bootbox.alert(datos);
			mostrarform(false);
			tabla.ajax.reload();
			actualizarCorrelativo();
		}
	});
}

function mostrar(idservicio) {
	$.post("../ajax/servicios.php?op=mostrar", { idservicio: idservicio }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);

		$(".btn1").show();
		$(".btn2").hide();

		console.log(data);

		$("#titulo").val(data.titulo);
		$("#codigo_barra").val(data.codigo_barra);
		$("#codigo").val(data.codigo);
		$("#descripcion").val(data.descripcion);
		$("#costo").val(data.costo);
		$("#idservicio").val(data.idservicio);
	})
}

function desactivar(idservicio) {
	bootbox.confirm("¿Está seguro de desactivar el servicio?", function (result) {
		if (result) {
			$.post("../ajax/servicios.php?op=desactivar", { idservicio: idservicio }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function activar(idservicio) {
	bootbox.confirm("¿Está seguro de activar el servicio?", function (result) {
		if (result) {
			$.post("../ajax/servicios.php?op=activar", { idservicio: idservicio }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function eliminar(idservicio) {
	bootbox.confirm("¿Estás seguro de eliminar el servicio?", function (result) {
		if (result) {
			$.post("../ajax/servicios.php?op=eliminar", { idservicio: idservicio }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				actualizarCorrelativo();
			});
		}
	})
}

var quaggaIniciado = false;

function escanear() {

	// Intentar acceder a la cámara
	navigator.mediaDevices.getUserMedia({ video: true })
		.then(function (stream) {
			$(".btn1").hide();
			$(".btn2").show();

			// Acceso a la cámara exitoso, inicializa Quagga
			Quagga.init({
				inputStream: {
					name: "Live",
					type: "LiveStream",
					target: document.querySelector('#camera')
				},
				decoder: {
					readers: ["code_128_reader"]
				}
			}, function (err) {
				if (err) {
					console.log(err);
					return;
				}
				console.log("Initialization finished. Ready to start");
				Quagga.start();
				quaggaIniciado = true;
			});

			$("#camera").show();

			Quagga.onDetected(function (data) {
				console.log(data.codeResult.code);
				var codigoBarra = data.codeResult.code;
				document.getElementById('codigo_barra').value = codigoBarra;
			});
		})
		.catch(function (error) {
			bootbox.alert("No se encontró una cámara conectada.");
		});
}

function detenerEscaneo() {
	if (quaggaIniciado) {
		Quagga.stop();
		$(".btn1").show();
		$(".btn2").hide();
		$("#camera").hide();
		formatearNumero();
		quaggaIniciado = false;
	}
}

$("#codigo_barra").on("input", function () {
	formatearNumero();
});

function formatearNumero() {
	var codigo = $("#codigo_barra").val().replace(/\s/g, '').replace(/\D/g, '');
	var formattedCode = '';

	// for (var i = 0; i < codigo.length; i++) {
	// 	if (i === 1 || i === 3 || i === 7 || i === 8 || i === 12 || i === 13) {
	// 		formattedCode += ' ';
	// 	}

	// 	formattedCode += codigo[i];
	// }

	// var maxLength = parseInt($("#codigo_barra").attr("maxlength"));
	// if (formattedCode.length > maxLength) {
	// 	formattedCode = formattedCode.substring(0, maxLength);
	// }

	$("#codigo_barra").val(codigo);
	generarbarcode(0);
}

function borrar() {
	$("#codigo_barra").val("");
	$("#codigo_barra").focus();
	$("#print").hide();
}

//función para generar el número aleatorio del código de barra
function generar() {
	var codigo = "775";
	codigo += generarNumero(10000, 999) + "";
	codigo += Math.floor(Math.random() * 10) + "";
	codigo += generarNumero(100, 9) + "";
	codigo += Math.floor(Math.random() * 10);
	$("#codigo_barra").val(codigo);
	generarbarcode(1);
}

function generarNumero(max, min) {
	var numero = Math.floor(Math.random() * (max - min + 1)) + min;
	var numeroFormateado = ("0000" + numero).slice(-4);
	return numeroFormateado;
}

// Función para generar el código de barras
function generarbarcode(param) {

	// if (param == 1) {
	// 	var codigo = $("#codigo_barra").val().replace(/\s/g, '');
	// 	console.log(codigo.length);

	// 	if (!/^\d+$/.test(codigo)) {
	// 		bootbox.alert("El código de barra debe contener solo números.");
	// 		return;
	// 	} else if (codigo.length !== 13) {
	// 		bootbox.alert("El código de barra debe tener 13 dígitos.");
	// 		return;
	// 	} else {
	// 		codigo = codigo.slice(0, 1) + " " + codigo.slice(1, 3) + " " + codigo.slice(3, 7) + " " + codigo.slice(7, 8) + " " + codigo.slice(8, 12) + " " + codigo.slice(12, 13);
	// 	}
	// } else {
	// 	var codigo = $("#codigo_barra").val()
	// }

	var codigo = $("#codigo_barra").val().replace(/\s/g, '');

	if (codigo != "") {
		JsBarcode("#barcode", codigo);
		$("#codigo_barra").val(codigo);
		$("#print").show();
	} else {
		$("#print").hide();
	}
}

function convertirMayus() {
	var inputCodigo = document.getElementById("cod_part_1");
	inputCodigo.value = inputCodigo.value.toUpperCase();
}

//Función para imprimir el código de barras
function imprimir() {
	$("#print").printArea();
}

init();