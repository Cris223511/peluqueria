var tabla;

function init() {
	listar();

	$('#mPersonas').addClass("treeview active");
	$('#lComisiones').addClass("active");
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
						doc.defaultStyle.fontSize = 8;
						doc.styles.tableHeader.fontSize = 8;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/comisiones.php?op=listar',
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
				$(row).find('td:eq(0), td:eq(2), td:eq(4), td:eq(5) td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function generarComision(idpersonal, nombre, tipo_documento, num_documento, local) {
	$("#trabajador_comisionar").text(capitalizarTodasLasPalabras(`${nombre} - ${tipo_documento}: ${num_documento} - ${local}`));
	$("#myModal1").modal("show");
	// $.post("../ajax/comisiones.php?op=mostrarPersonal", { idpersonal: idpersonal }, function (data, status) {
	// 	console.log(data);
	// 	data = JSON.parse(data);
	// 	console.log(data);
	// })
}

init();