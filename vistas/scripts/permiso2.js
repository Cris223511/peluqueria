var tabla;

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();
	$('#mAcceso').addClass("treeview active");
	$('#lPermisos').addClass("active");
}

//Función mostrar formulario
function mostrarform(flag) {
	//limpiar();
	if (flag) {
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
	}
	else {
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").hide();
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
					// 'orientation': 'landscape',
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
						doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');

						doc.content[1].table.body.forEach(function (row) {
							row.forEach(function (cell, i) {
								cell.alignment = 'center';
							});
						});
					},
				},
			],
			"ajax":
			{
				url: '../ajax/permiso.php?op=listar',
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
			"order": []
		}).DataTable();
}


init();