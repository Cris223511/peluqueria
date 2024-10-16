objSelects = {};

//Función que se ejecuta al inicio
function init() {
	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});
	$('#mPerfilUsuario').addClass("treeview active");
	$('#lConfBoleta').addClass("active");

	mostrar();
}

function manejarTipoCambio() {
	var tipoCambio = document.getElementById('moneda').value;
	var cambioInput = document.getElementById('cambio');

	if (tipoCambio === 'soles' || tipoCambio == '') {
		cambioInput.disabled = true;
	} else {
		cambioInput.disabled = false;
	}
}

function guardaryeditar(e) {
	e.preventDefault(); // No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled", true);

	var tipoCambio = document.getElementById('moneda').value;
	var cambioInput = document.getElementById('cambio');
	var formData;

	if (tipoCambio === 'soles' || tipoCambio == '') {
		cambioInput.disabled = false;
		formData = new FormData($("#formulario")[0]);
		cambioInput.disabled = true;
	} else {
		formData = new FormData($("#formulario")[0]);
	}

	$.ajax({
		url: "../ajax/confBoleta.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,
		success: function (datos) {
			datos = limpiarCadena(datos);
			if (!datos) {
				console.log("No se recibieron datos del servidor.");
				$("#btnGuardar").prop("disabled", false);
				return;
			} else {
				bootbox.alert(datos);
				$("#btnGuardar").prop("disabled", false);
				mostrar();
			}
		}
	});
}

function mostrar() {
	$.post("../ajax/confBoleta.php?op=mostrar", function (data, status) {
		data = JSON.parse(data);
		console.log(data);
		$("#idreporte").val(data.idreporte);
		$("#titulo").val(data.titulo);
		$("#auspiciado").val(data.auspiciado);
		$("#moneda").val(data.moneda);
		$("#moneda").trigger("onchange");
		$("#cambio").val(data.cambio);
		$("#ruc").val(data.ruc);
		$("#direccion").val(data.direccion);
		$("#telefono").val(data.telefono);
		$("#email").val(data.email);
	});
}

document.addEventListener('DOMContentLoaded', function () {
	init();
});