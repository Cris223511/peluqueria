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
	$("#spinnerLoader").show();
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
		},
		complete: function () {
			$("#spinnerLoader").hide();
		},
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

	$("#spinnerLoader").show();

	// AJAX para enviar el archivo
	$.ajax({
		url: '../ajax/carga_masiva.php?op=importarProductos',
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function (data) {
			console.log("OK");
			try {
				// Intenta parsear la respuesta como JSON
				const response = JSON.parse(data);

				// Verifica el estado de la respuesta
				if (response.status === "error") {
					bootbox.alert(response.message);
				} else {
					// Actualizar la tabla con el formato esperado
					const tabla = $('#tbllistado').DataTable();

					// Limpia completamente la tabla antes de cargar nuevos datos
					tabla.clear().draw();

					// Añadir los nuevos datos
					tabla.rows.add(response.aaData).draw();

					bootbox.alert("Datos cargados exitosamente.");
					evitarCaracteresEspecialesCamposNumericos();
					aplicarRestrictATodosLosInputs();

					// Mostrar el botón de guardar
					$("#section_btnguardar").show();
					$("#btnguardar").show();

					// validar la tabla
					validarDatosTabla();
				}
			} catch (e) {
				bootbox.alert("Error al procesar el archivo. Verifique el formato y los datos.");
				console.log("Respuesta no válida JSON:", data);
			}
		},
		error: function (err) {
			bootbox.alert("Error al cargar el archivo. Intente nuevamente.");
			console.log("Error del servidor:", err.responseText);
		},
		complete: function () {
			$("#spinnerLoader").hide();
			document.getElementById('fileInput').value = '';
		}
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

/* ===================  VALIDACIONES DE TODAS LAS FILAS DE LA TABLA ====================== */

// Función para validar los datos de la tabla
function validarDatosTabla() {
	const tabla = $('#tbllistado').DataTable();
	const filas = [];

	// Recorrer todas las filas del DataTable
	tabla.rows().every(function () {
		const fila = this.node();
		filas.push({
			nombre: $(fila).find('textarea[name="nombre[]"]').val(),
			idmedida: $(fila).find('input[name="idmedida[]"]').val(),
			idcategoria: $(fila).find('input[name="idcategoria[]"]').val(),
			idlocal: $(fila).find('input[name="idlocal[]"]').val(),
			idmarca: $(fila).find('input[name="idmarca[]"]').val(),
			codigo_producto: $(fila).find('input[name="codigo_producto[]"]').val(),
			stock: $(fila).find('input[name="stock[]"]').val(),
			stock_minimo: $(fila).find('input[name="stock_minimo[]"]').val(),
			precio_venta: $(fila).find('input[name="precio_venta[]"]').val(),
			precio_compra: $(fila).find('input[name="precio_compra[]"]').val(),
			codigo_barra: $(fila).find('input[name="codigo_barra[]"]').val(),
			peso: $(fila).find('input[name="peso[]"]').val(),
			fecha_emision: $(fila).find('input[name="fecha_emision[]"]').val(),
			fecha_vencimiento: $(fila).find('input[name="fecha_vencimiento[]"]').val(),
		});
	});

	$("#spinnerLoader").show();

	// Enviar los datos al servidor para validar
	$.ajax({
		url: '../ajax/carga_masiva.php?op=validarFilasProductos',
		type: 'POST',
		contentType: 'application/json',
		data: JSON.stringify(filas),
		success: function (response) {
			response = JSON.parse(response); // Asegurarte de parsear la respuesta
			console.log("datos validados con éxito => ", response);
			if (response.status === 'success') {
				pintarErrores(response.errores, tabla); // Pasar tabla como parámetro
			}
		},
		error: function (err) {
			console.log('Error al validar:', err.responseText);
		},
		complete: function () {
			$("#spinnerLoader").hide();
		},
	});
}

// Función para pintar los errores en las celdas
function pintarErrores(errores, tabla) {
	errores.forEach(error => {
		const filaIndex = error.fila; // Índice de la fila en los datos enviados
		const columnas = error.columnas; // Índices de las columnas con errores

		// Obtener el nodo de la fila visible en DataTables
		const fila = tabla.row(filaIndex).node(); // Usa tabla para obtener la fila correspondiente

		if (fila) {
			// Pintar cada columna con error en la fila
			columnas.forEach(columnaIndex => {
				const celda = $(fila).find(`td:eq(${columnaIndex})`);
				celda.css('background-color', '#FFCCCC'); // Pintar la celda con color rojo claro
			});
		} else {
			console.warn(`No se pudo encontrar la fila con índice ${filaIndex}`);
		}
	});
}

/* ===================  VALIDACIONES DE SOLO UNA FILA DE LA TABLA ====================== */

function validarFila(boton) {
	const fila = $(boton).closest('tr'); // Obtener la fila correspondiente
	const tabla = $('#tbllistado').DataTable();
	const filaIndex = tabla.row(fila).index(); // Obtener el índice de la fila

	// Obtener todos los idlocal, codigo_producto y codigo_barra de la tabla
	const todosCodigos = [];
	tabla.rows().every(function () {
		const filaData = this.node();
		todosCodigos.push({
			idlocal: $(filaData).find('input[name="idlocal[]"]').val(),
			codigo_producto: $(filaData).find('input[name="codigo_producto[]"]').val(),
			codigo_barra: $(filaData).find('input[name="codigo_barra[]"]').val() // Agregar codigo_barra
		});
	});

	// Datos de la fila específica a validar
	const filaDatos = {
		nombre: fila.find('textarea[name="nombre[]"]').val(),
		idmedida: fila.find('input[name="idmedida[]"]').val(),
		idcategoria: fila.find('input[name="idcategoria[]"]').val(),
		idlocal: fila.find('input[name="idlocal[]"]').val(),
		idmarca: fila.find('input[name="idmarca[]"]').val(),
		codigo_producto: fila.find('input[name="codigo_producto[]"]').val(),
		stock: fila.find('input[name="stock[]"]').val(),
		stock_minimo: fila.find('input[name="stock_minimo[]"]').val(),
		precio_venta: fila.find('input[name="precio_venta[]"]').val(),
		precio_compra: fila.find('input[name="precio_compra[]"]').val(),
		codigo_barra: fila.find('input[name="codigo_barra[]"]').val(),
		peso: fila.find('input[name="peso[]"]').val(),
		fecha_emision: fila.find('input[name="fecha_emision[]"]').val(),
		fecha_vencimiento: fila.find('input[name="fecha_vencimiento[]"]').val(),
	};

	// Combinar los datos de la fila con todos los códigos y el índice de la fila
	const datosAEnviar = {
		fila: filaDatos,
		todosCodigos: todosCodigos,
		filaIndex: filaIndex,
	};

	console.log("estos datos envío al servidor =) => ", datosAEnviar);

	$("#spinnerLoader").show();

	// Enviar los datos al servidor para validar
	$.ajax({
		url: '../ajax/carga_masiva.php?op=validarFilaProducto',
		type: 'POST',
		contentType: 'application/json',
		data: JSON.stringify(datosAEnviar),
		success: function (response) {
			console.log(response);
			response = JSON.parse(response); // Asegúrate de parsear la respuesta
			console.log("Resultado de validación de fila:", response);

			const botonIcono = $(boton).find('i'); // Icono dentro del botón

			limpiarErroresFila(fila);

			if (response.status === 'error') {
				// Hay errores en la fila
				$(boton).removeClass('btn-bcp btn-success').addClass('btn-danger'); // Cambiar a estado de error
				botonIcono
					.removeClass('fa-eye fa-check')
					.addClass('fa-times') // Cambiar a ícono de error
					.attr('style', 'margin-left: -3px !important'); // Cambiar el estilo del margen izquierdo

				pintarErroresFila(fila, response.errores); // Pintar las celdas con errores
				mostrarBotonErrores(fila, true); // Mostrar botón para ver errores

				// Almacenar los mensajes de error en la fila para el modal
				fila.data('erroresColumnas', response.errores); // Los índices de las columnas
				fila.data('errores', response.mensajes); // Guardar los mensajes en la fila
			} else {
				// No hay errores en la fila
				$(boton).removeClass('btn-bcp btn-danger').addClass('btn-success'); // Cambiar a estado de éxito
				botonIcono
					.removeClass('fa-eye fa-times')
					.addClass('fa-check') // Cambiar a ícono de éxito
					.attr('style', 'margin-left: -4px'); // Cambiar el estilo del margen izquierdo

				limpiarErroresFila(fila); // Limpiar cualquier error previo
				mostrarBotonErrores(fila, false); // Ocultar botón para ver errores
			}
		},
		error: function (err) {
			console.log('Error al validar la fila:', err.responseText);
		},
		complete: function () {
			$("#spinnerLoader").hide();
		},
	});
}

// Función para pintar errores en una fila
function pintarErroresFila(fila, errores) {
	errores.forEach(columnaIndex => {
		const celda = fila.find(`td:eq(${columnaIndex})`);
		celda.css('background-color', '#FFCCCC'); // Pintar celda en rojo claro
	});
}

// Función para limpiar errores de una fila
function limpiarErroresFila(fila) {
	fila.find('td').css('background-color', ''); // Quitar el color de fondo
}

// Función para mostrar u ocultar el botón de ver errores
function mostrarBotonErrores(fila, mostrar) {
	let botonErrores = fila.find('.btn-info'); // Buscar si ya existe el botón de ver errores

	if (mostrar) {
		// Si debe mostrar el botón y no existe, agregarlo
		if (botonErrores.length === 0) {
			const nuevoBoton = `<a data-toggle="modal" href="#myModal2"><button type="button" class="btn btn-info" onclick="verErrores(this)" style="width: 30px; height: 30px; border-radius: 50%; margin-left: 5px;"><i style="margin-left: -3px" class="fa fa-info-circle"></i></button></a>`;
			fila.find('td:eq(0)').append(nuevoBoton); // Agregar el botón en la primera columna
		}
	} else {
		// Si no debe mostrar el botón y existe, eliminarlo
		if (botonErrores.length > 0) {
			botonErrores.remove();
		}
	}
}

const nombresColumnas = {
	2: "Nombre del producto",
	3: "Unidad de medida",
	4: "Categoría",
	5: "Local",
	6: "Marca",
	7: "Código del producto",
	8: "Stock",
	9: "Stock mínimo",
	10: "Precio de venta",
	11: "Precio de compra",
	12: "Ganancia",
	13: "Precio mayorista",
	14: "Comisión",
	15: "Código de barra",
	19: "Peso",
	20: "Fecha de emisión",
	21: "Fecha de vencimiento",
};

// Función para mostrar los errores en el modal 
function verErrores(boton) {
	// Obtener la fila correspondiente al botón
	const fila = $(boton).closest('tr');

	// Obtener los errores almacenados en la fila
	const errores = fila.data('errores') || [];
	const columnas = fila.data('erroresColumnas') || []; // Obtén las columnas asociadas a los errores

	// Limpiar la tabla del modal antes de insertar nuevos datos
	const tablaErrores = $('#tablaErrores tbody');
	tablaErrores.empty();

	// Insertar los errores en la tabla
	errores.forEach((mensaje, index) => {
		// Buscar el nombre de la columna correspondiente
		const columna = nombresColumnas[columnas[index]] || "Desconocida";

		const filaHtml = `
            <tr>
                <td>${index + 1}</td>
                <td>${columna}</td>
                <td>${mensaje}</td>
            </tr>
        `;
		tablaErrores.append(filaHtml);
	});
}

/* =================== VALIDAR TODAS LAS FILAS Y GUARDAR ====================== */

function validarYGuardarProductos() {
	bootbox.confirm({
		message: "¿Estás seguro de que deseas validar y guardar todos los productos? Este proceso no se puede deshacer.",
		buttons: {
			confirm: {
				label: 'Guardar',
				className: 'btn-bcp'
			},
			cancel: {
				label: 'Cancelar',
				className: 'btn-warning'
			}
		},
		callback: function (result) {
			if (result) {
				// Si el usuario confirma, continúa con el flujo original
				const tabla = $('#tbllistado').DataTable();
				const filas = [];

				// Restablecer todos los botones de la columna "Estado" al ícono inicial
				restablecerEstadoBotones(tabla);

				// Recorrer todas las filas del DataTable
				tabla.rows().every(function () {
					const fila = this.node();
					filas.push({
						nombre: $(fila).find('textarea[name="nombre[]"]').val(),
						idmedida: $(fila).find('input[name="idmedida[]"]').val(),
						idcategoria: $(fila).find('input[name="idcategoria[]"]').val(),
						idlocal: $(fila).find('input[name="idlocal[]"]').val(),
						idmarca: $(fila).find('input[name="idmarca[]"]').val(),
						codigo_producto: $(fila).find('input[name="codigo_producto[]"]').val(),
						stock: $(fila).find('input[name="stock[]"]').val(),
						stock_minimo: $(fila).find('input[name="stock_minimo[]"]').val(),
						precio_venta: $(fila).find('input[name="precio_venta[]"]').val(),
						precio_compra: $(fila).find('input[name="precio_compra[]"]').val(),
						codigo_barra: $(fila).find('input[name="codigo_barra[]"]').val(),
						peso: $(fila).find('input[name="peso[]"]').val(),
						fecha_emision: $(fila).find('input[name="fecha_emision[]"]').val(),
						fecha_vencimiento: $(fila).find('input[name="fecha_vencimiento[]"]').val(),
					});
				});

				// Mostrar spinner
				$("#spinnerLoader").show();

				// Validar las filas en el servidor
				$.ajax({
					url: '../ajax/carga_masiva.php?op=validarFilasProductos',
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(filas),
					success: function (response) {
						response = JSON.parse(response); // Asegurarse de parsear la respuesta
						console.log("Respuesta de validación completa:", response);

						if (response.status === 'success') {
							// Limpia cualquier error previo en las filas
							limpiarErroresTabla(tabla);

							// Si hay errores, pintarlos en la tabla y mostrar los íconos correspondientes
							if (response.errores.length > 0) {
								pintarErrores(response.errores, tabla);
								bootbox.alert("Existen errores en la tabla. Corríjalos antes de guardar.");
							} else {
								// No hay errores: proceder a guardar los datos
								guardarProductos(filas);
							}
						} else {
							bootbox.alert("Ocurrió un error al validar los datos. Intente nuevamente.");
						}
					},
					error: function (err) {
						console.log("Error al validar las filas:", err.responseText);
						bootbox.alert("Error al validar los datos. Intente nuevamente.");
					},
					complete: function () {
						// Ocultar spinner
						$("#spinnerLoader").hide();
					},
				});
			}
		}
	});
}

function guardarProductos(filas) {
	$("#spinnerLoader").show(); // Mostrar el spinner

	$.ajax({
		url: '../ajax/carga_masiva.php?op=guardarProductos',
		type: 'POST',
		contentType: 'application/json',
		data: JSON.stringify(filas),
		success: function (response) {
			console.log(response);
			response = JSON.parse(response); // Asegurarse de parsear la respuesta
			console.log("Respuesta de guardado:", response);

			if (response.status === 'success') {
				bootbox.alert("Productos guardados correctamente.");
				// Aquí puedes limpiar la tabla o recargarla
				$('#tbllistado').DataTable().clear().draw();
			} else {
				bootbox.alert(response.message || "Error al guardar los productos.");
			}
		},
		error: function (err) {
			console.log("Error al guardar los productos:", err.responseText);
			bootbox.alert("Error al guardar los productos. Intente nuevamente.");
		},
		complete: function () {
			$("#spinnerLoader").hide(); // Ocultar el spinner
		},
	});
}

// Función para restablecer los botones en la primera columna (Estado)
function restablecerEstadoBotones(tabla) {
	tabla.rows().every(function () {
		const fila = this.node();
		const columnaEstado = $(fila).find('td:eq(0)'); // Primera columna (Estado)

		// Reemplazar el contenido de la columna por el botón inicial
		columnaEstado.html(`
            <button type="button" class="btn btn-bcp" onclick="validarFila(this)" style="width: 30px; height: 30px; border-radius: 50%;">
                <i style="margin-left: -4px" class="fa fa-eye"></i>
            </button>
        `);
	});
}

// Función para limpiar los errores de la tabla
function limpiarErroresTabla(tabla) {
	tabla.rows().every(function () {
		const fila = this.node();
		limpiarErroresFila($(fila)); // Reutilizamos la función existente
	});
}

// Función para formatear los códigos de los productos.
function formatearCodigoProducto(input) {
	let codigoProducto = input.value.trim();

	// Regex para capturar hasta la última letra como alfanuméricos y lo demás como números
	const partes = codigoProducto.match(/^(.*[A-Za-z])?(.*)$/) || ["", "", ""];
	const letras = partes[1] || ""; // Alfanuméricos (hasta la última letra)
	let numeros = partes[2] || ""; // Números después de la última letra

	// Si la parte numérica contiene solo ceros, o no hay números, establecer como 1
	if (!numeros || /^0+$/.test(numeros)) {
		numeros = "1";
	}

	// Asegurarse de que los números tengan al menos 5 dígitos
	numeros = numeros.padStart(5, "0");

	// Formatear el código final
	const codigoFormateado = letras + numeros;

	// Actualizar el valor del input
	input.value = codigoFormateado;
}

// Ejecutar init al cargar la página
document.addEventListener('DOMContentLoaded', function () {
	init();
});
