var tabla;

function init() {
	listar();
	$('#mCargaMasiva').addClass("treeview active");
}

function listar() {
	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],
			"aProcessing": true,
			"aServerSide": false,
			dom: '<Bl<f>rtip>',
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
				{
					'extend': 'pdfHtml5',
					'orientation': 'landscape',
					'exportOptions': {
						'columns': function (idx, data, node) {
							return idx > 1 ? true : false;
						}
					},
					'customize': function (doc) {
						doc.defaultStyle.fontSize = 7;
						doc.styles.tableHeader.fontSize = 7;
					},
				},
			],
			"data": [],
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
			"drawCallback": function (settings) {
				ocultarColumnasPorNombre("tbllistado", columnasAocultar);
				mostrarOcultarPrecioCompraCampo();
			},
			"initComplete": function () {
				agregarBuscadorColumna(this.api(), 10, "Buscar por código.");
				agregarBuscadorColumna(this.api(), 5, "Buscar por categoría.");
			},
		}).DataTable();
}

init();