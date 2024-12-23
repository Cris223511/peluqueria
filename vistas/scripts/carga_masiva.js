var tabla;
var tablas = [];

async function init() {
	$('#mCargaMasiva').addClass("treeview active");
	$("#section_btnguardar").hide();
	$("#btnguardar").hide();
	listar();

	await cargarTablas();
	pintarTablas();
}

function listar() {
	tabla = $('#tbllistado').DataTable({
		"lengthMenu": [5, 10, 25, 75, 100],
		"aProcessing": true,
		"aServerSide": false,
		dom: '<Bl<f>rtip>',
		buttons: [
			'copyHtml5',
			'excelHtml5',
			'csvHtml5',
			{
				extend: 'pdfHtml5',
				orientation: 'landscape',
				exportOptions: {
					columns: function (idx, data, node) {
						return idx > 1 ? true : false;
					}
				},
				customize: function (doc) {
					doc.defaultStyle.fontSize = 7;
					doc.styles.tableHeader.fontSize = 7;
				}
			}
		],
		"data": [], // Se inicializa vacío y se rellenará dinámicamente
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
			// Aplica estilos solo a la primera columna (ESTADO)
			$('td', row).eq(0).css({ "text-align": "center", "vertical-align": "middle" });
		}
	});
}

// Cargar datos de las 4 tablas del modal de manera asíncrona
async function cargarTablas() {
	const tablasAConsultar = ['categorias', 'locales', 'marcas', 'medidas'];
	const promises = tablasAConsultar.map((tabla) => cargarDatos(tabla));
	tablas = await Promise.all(promises);
}

// Realizar la petición AJAX para obtener los datos de cada tabla
async function cargarDatos(op) {
	return new Promise((resolve, reject) => {
		$.ajax({
			url: `../ajax/carga_masiva.php?op=${op}`,
			type: "get",
			dataType: "json",
			success: function (data) {
				resolve(data);
			},
			error: function (e) {
				console.log(e.responseText);
				reject(e);
			}
		});
	});
}

// Pintar cada tabla del modal
function pintarTabla(tableId, tabIndex) {
	$(`#${tableId}`).dataTable({
		"lengthMenu": [15, 25, 50, 100],
		"aProcessing": true,
		"aServerSide": false,
		dom: '<Bl<f>rtip>',
		buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5'],
		"data": tablas[tabIndex]?.aaData || [],
		"language": {
			"emptyTable": tablas[tabIndex]?.aaData ? "No existen datos" : "Cargando...",
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
		"iDisplayLength": 15,
		"order": []
	});
}

// Pintar todas las tablas del modal
function pintarTablas() {
	// Asigna un índice a cada tabla del modal
	const tablasIds = ['tbllistado_categorias', 'tbllistado_locales', 'tbllistado_marcas', 'tbllistado_medidas'];
	tablasIds.forEach((id, index) => {
		pintarTabla(id, index);
	});
}

/* ===================  DESCARGAR LA PLANTILLA ====================== */

function descargarPlantilla() {
	$.ajax({
		url: "../ajax/carga_masiva.php?op=descargarPlantilla",
		type: "POST",
		xhrFields: {
			responseType: 'blob' // Manejar el archivo como un blob
		},
		success: function (response, status, xhr) {
			const blob = new Blob([response], { type: xhr.getResponseHeader('Content-Type') });
			const link = document.createElement('a');
			const url = window.URL.createObjectURL(blob);

			link.href = url;
			link.download = "Plantilla de productos - Peluquería.xlsx";
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			window.URL.revokeObjectURL(url);
			bootbox.alert("Plantilla de productos descargado correctamente.");
		},
		error: function (error) {
			console.log("Error al descargar la plantilla: ", error);
			bootbox.alert("Ocurrió un error al descargar la plantilla.");
		}
	});
}

/* ===================  IMPORTAR LA PLANTILLA A LA TABLA ====================== */

function importarProductos() {
	// Simular clic en el input de archivo
	document.getElementById('fileInput').click();
}

function handleFile(event) {
	const file = event.target.files[0];

	// Validar si se seleccionó un archivo
	if (!file) {
		bootbox.alert("Por favor, seleccione un archivo.");
		return;
	}

	// Validar tipo de archivo
	const validExtensions = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
	if (!validExtensions.includes(file.type)) {
		bootbox.alert("El archivo seleccionado no es válido. Por favor, suba un archivo Excel (.xlsx o .xls).");
		return;
	}

	// Preparar el archivo para enviar al servidor
	const formData = new FormData();
	formData.append('file', file);

	// AJAX para enviar el archivo
	$.ajax({
		url: '../ajax/carga_masiva.php?op=importarProductos',
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function (data) {
			try {
				// Intenta parsear la respuesta como JSON
				const response = JSON.parse(data);

				// Verifica el estado de la respuesta
				if (response.status === "error") {
					bootbox.alert(response.message);
				} else {
					// Actualizar la tabla con el formato esperado
					const tabla = $('#tbllistado').DataTable();
					tabla.clear().rows.add(response.aaData).draw(); // Añade los datos a la tabla y los renderiza
					bootbox.alert("Datos cargados exitosamente.");
					evitarCaracteresEspecialesCamposNumericos();
					aplicarRestrictATodosLosInputs();
				}
			} catch (e) {
				console.error("Respuesta no válida JSON:", data);
				bootbox.alert("Error al procesar el archivo. Verifique el formato y los datos.");
			}
		},
		error: function (err) {
			bootbox.alert("Error al cargar el archivo. Intente nuevamente.");
			console.error("Error del servidor:", err.responseText);
		},
	});
}

// Función para calcular la ganancia en la fila correspondiente donde se escriba
function calcularGanancia(element) {
	// Encuentra la fila correspondiente
	const row = element.closest('tr');

	// Obtén los valores de precio_venta y precio_compra
	const precioVentaInput = row.querySelector('input[name="precio_venta[]"]');
	const precioCompraInput = row.querySelector('input[name="precio_compra[]"]');
	const gananciaInput = row.querySelector('input[name="ganancia[]"]');

	if (precioVentaInput && precioCompraInput && gananciaInput) {
		const precioVenta = parseFloat(precioVentaInput.value) || 0;
		const precioCompra = parseFloat(precioCompraInput.value) || 0;

		// Calcula la ganancia
		const ganancia = (precioVenta - precioCompra).toFixed(2);

		// Actualiza el campo de ganancia
		gananciaInput.value = ganancia;
	}
}

// Ejecutar init al cargar la página
document.addEventListener('DOMContentLoaded', function () {
	init();
});
