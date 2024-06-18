//Función que se ejecuta al inicio
function init() {
	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});
	$('#mPerfilUsuario').addClass("treeview active");
	$('#lConfPortada').addClass("active");

	mostrar();
}

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/confPortada.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datas) {
			$("#btnGuardar").prop("disabled", false);
			bootbox.alert(datas);
			mostrar();
		}
	});
}

function mostrar() {
	$.post("../ajax/confPortada.php?op=mostrar", function (datas, status) {
		var data = JSON.parse(datas);
		console.log(data);
		$("#imagenmuestra").show();

		if (data && data !== "") {
			$("#imagenmuestra").attr("src", "../files/portadas/" + data.imagen);
		} else {
			$("#imagenmuestra").attr("src", "../files/portadas/default.jpg");
		}
	});
}

init();