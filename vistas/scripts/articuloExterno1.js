var tabla;

var idlocal = 0;

function actualizarCorrelativoProducto(idlocal) {
	$.post("../ajax/articuloExterno.php?op=getLastCodigo", { idlocal: idlocal }, function (num) {
		console.log(num);
		const partes = num.match(/([a-zA-Z]+)(\d+)/) || ["", "", ""];

		const letras = partes[1];
		const numeros = partes[2];

		const siguienteCorrelativo = generarSiguienteCorrelativo(numeros);

		$("#cod_part_1").val(letras);
		$("#cod_part_2").val(siguienteCorrelativo);
	});
}

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})

	$("#formulario2").on("submit", function (e) {
		guardaryeditar2(e);
	})

	$("#imagenmuestra").hide();
	$('#mAlmacen').addClass("treeview active");
	$('#lArticulosExternos').addClass("active");

	$.post("../ajax/articuloExterno.php?op=listarTodosActivos", function (data) {
		// console.log(data)
		const obj = JSON.parse(data);
		console.log(obj);

		const selects = {
			"idmarca": $("#idmarca, #idmarcaBuscar"),
			"idcategoria": $("#idcategoria, #idcategoriaBuscar"),
			"idlocal": $("#idlocal"),
			"idmedida": $("#idmedida"),
		};

		for (const selectId in selects) {
			if (selects.hasOwnProperty(selectId)) {
				const select = selects[selectId];
				const atributo = selectId.replace('id', '');

				if (obj.hasOwnProperty(atributo)) {
					select.empty();
					select.html('<option value="">- Seleccione -</option>');
					obj[atributo].forEach(function (opcion) {
						if (atributo != "local") {
							select.append('<option value="' + opcion.id + '">' + opcion.titulo + '</option>');
						} else {
							select.append('<option value="' + opcion.id + '" data-local-ruc="' + opcion.ruc + '">' + opcion.titulo + '</option>');
						}
					});
					select.selectpicker('refresh');
				}
			}
		}

		$("#idlocal").val($("#idlocal option:first").val());
		$("#idlocal").selectpicker('refresh');

		$('#idcategoria').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregarCategoria(event)');
		$('#idcategoria').closest('.form-group').find('input[type="text"]').attr('maxlength', '40');

		$('#idmarca').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregarMarca(event)');
		$('#idmarca').closest('.form-group').find('input[type="text"]').attr('maxlength', '40');

		$('#idmedida').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregarMedida(event)');
		$('#idmedida').closest('.form-group').find('input[type="text"]').attr('maxlength', '40');

		actualizarRUC();

		idlocal = $("#idlocal").val();
		actualizarCorrelativoProducto(idlocal);
	});
}

function listarTodosActivos(selectId) {
	$.post("../ajax/articuloExterno.php?op=listarTodosActivos", function (data) {
		const obj = JSON.parse(data);

		const select = $("#" + selectId);
		const atributo = selectId.replace('id', '');

		if (obj.hasOwnProperty(atributo)) {
			select.empty();
			select.html('<option value="">- Seleccione -</option>');
			obj[atributo].forEach(function (opcion) {
				if (atributo !== "almacen") {
					select.append('<option value="' + opcion.id + '">' + opcion.titulo + '</option>');
				}
			});
			select.selectpicker('refresh');
		}

		select.closest('.form-group').find('input[type="text"]').attr('onkeydown', 'agregar' + atributo.charAt(0).toUpperCase() + atributo.slice(1) + '(event)');
		select.closest('.form-group').find('input[type="text"]').attr('maxlength', '40');
		$("#" + selectId + ' option:last').prop("selected", true);
		select.selectpicker('refresh');
		select.selectpicker('toggle');
	});
}

function agregarCategoria(e) {
	let inputValue = $('#idcategoria').closest('.form-group').find('input[type="text"]');

	if (e.key === "Enter") {
		if ($('.no-results').is(':visible')) {
			e.preventDefault();
			$("#titulo2").val(inputValue.val());

			var formData = new FormData($("#formularioCategoria")[0]);

			$.ajax({
				url: "../ajax/categoria.php?op=guardaryeditar",
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,

				success: function (datos) {
					datos = limpiarCadena(datos);
					if (!datos) {
						console.log("No se recibieron datos del servidor.");
						return;
					} else if (datos == "El nombre de la categoría que ha ingresado ya existe.") {
						bootbox.alert(datos);
						return;
					} else {
						// bootbox.alert(datos);
						listarTodosActivos("idcategoria");
						$("#idcategoria2").val("");
						$("#titulo2").val("");
						$("#descripcion2").val("");
					}
				}
			});
		}
	}
}

function agregarMarca(e) {
	let inputValue = $('#idmarca').closest('.form-group').find('input[type="text"]');

	if (e.key === "Enter") {
		if ($('.no-results').is(':visible')) {
			e.preventDefault();
			$("#titulo3").val(inputValue.val());

			var formData = new FormData($("#formularioMarcas")[0]);

			$.ajax({
				url: "../ajax/marcas.php?op=guardaryeditar",
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,

				success: function (datos) {
					datos = limpiarCadena(datos);
					if (!datos) {
						console.log("No se recibieron datos del servidor.");
						return;
					} else if (datos == "El nombre de la marca que ha ingresado ya existe.") {
						bootbox.alert(datos);
						return;
					} else {
						// bootbox.alert(datos);
						listarTodosActivos("idmarca");
						$("#idmarca3").val("");
						$("#titulo3").val("");
						$("#descripcion3").val("");
					}
				}
			});
		}
	}
}

function agregarMedida(e) {
	let inputValue = $('#idmedida').closest('.form-group').find('input[type="text"]');

	if (e.key === "Enter") {
		if ($('.no-results').is(':visible')) {
			e.preventDefault();
			$("#titulo4").val(inputValue.val());

			var formData = new FormData($("#formularioMedidas")[0]);

			$.ajax({
				url: "../ajax/medidas.php?op=guardaryeditar",
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,

				success: function (datos) {
					datos = limpiarCadena(datos);
					if (!datos) {
						console.log("No se recibieron datos del servidor.");
						return;
					} else if (datos == "El nombre de la medida que ha ingresado ya existe.") {
						bootbox.alert(datos);
						return;
					} else {
						// bootbox.alert(datos);
						listarTodosActivos("idmedida");
						$("#idmedida4").val("");
						$("#titulo4").val("");
						$("#descripcion4").val("");
					}
				}
			});
		}
	}
}

function changeGanancia() {
	let precio_compra = parseFloat($("#precio_compra").val()) || 0;
	let precio_venta = parseFloat($("#precio_venta").val()) || 0;

	// Si el precio de compra está presente pero el precio de venta es 0 o vacío, la ganancia será 0
	if (precio_venta === 0) {
		$("#ganancia").val("0.00");
		return;
	}

	// Si ambos precios son mayores a 0, calculamos la ganancia
	if (precio_venta > 0 && precio_compra >= 0) {
		let ganancia = precio_venta - precio_compra;
		$("#ganancia").val(ganancia.toFixed(2));
	} else {
		// Si alguno de los valores es inválido, mostramos ganancia como 0.00
		$("#ganancia").val("0.00");
	}
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
	$("#codigo_barra").val("");
	$("#cod_part_1").val("");
	$("#cod_part_2").val("");
	$("#nombre").val("");
	$("#local_ruc").val("");
	$("#descripcion").val("");
	$("#talla").val("");
	$("#color").val("");
	$("#peso").val("");
	$("#fecha_emision").val("");
	$("#fecha_vencimiento").val("");
	$("#nota_1").val("");
	$("#nota_2").val("");
	$("#stock").val("");
	$("#stock_minimo").val("");
	$("#imagenmuestra").attr("src", "");
	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#imagen").val("");
	$("#precio_compra").val("");
	$("#precio_venta").val("");
	$("#precio_venta_mayor").val("");
	$("#ganancia").val("0.00");
	$("#comision").val("");
	$("#print").hide();
	$("#idarticulo").val("");

	$("#idcategoria").val($("#idcategoria option:first").val());
	$("#idcategoria").selectpicker('refresh');
	$("#idlocal").val($("#idlocal option:first").val());
	$("#idlocal").selectpicker('refresh');
	$("#idmarca").val($("#idmarca option:first").val());
	$("#idmarca").selectpicker('refresh');
	$("#idmedida").val($("#idmedida option:first").val());
	$("#idmedida").selectpicker('refresh');
	actualizarRUC();

	idlocal = $("#idlocal").val();
	actualizarCorrelativoProducto(idlocal);

	$(".btn1").show();
	$(".btn2").hide();

	detenerEscaneo();
}

//Función mostrar formulario
function mostrarform(flag) {
	limpiar();
	if (flag) {
		$(".listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
		$("#btncomisiones").hide();
		$("#btnDetalles1").show();
		$("#btnDetalles2").hide();
		$("#frmDetalles").hide();
	}
	else {
		$(".listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
		$("#btncomisiones").show();
		$("#btnDetalles1").show();
		$("#btnDetalles2").hide();
		$("#frmDetalles").hide();
	}
}

function frmDetalles(bool) {
	if (bool == true) { $("#frmDetalles").show(); $("#btnDetalles1").hide(); $("#btnDetalles2").show(); }
	if (bool == false) { $("#frmDetalles").hide(); $("#btnDetalles1").show(); $("#btnDetalles2").hide(); }
	// $('html, body').animate({ scrollTop: $(document).height() }, 10);
}

//Función cancelarform
function cancelarform() {
	limpiar();
	mostrarform(false);
}

//Función Listar
function listar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";

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
			"ajax":
			{
				url: '../ajax/articuloExterno.php?op=listar',
				type: "get",
				data: { param1: param1, param2: param2, param3: param3 },
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11, td:eq(12), td:eq(13), td:eq(14), td:eq(15)').addClass('nowrap-cell');
			},
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

//Función para guardar o editar

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento

	var codigoBarra = $("#codigo_barra").val();

	// var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);

	// if (!formatoValido && codigoBarra != "") {
	// 	bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
	// 	$("#btnGuardar").prop("disabled", false);
	// 	return;
	// }

	// var stock = parseFloat($("#stock").val());
	// var stock_minimo = parseFloat($("#stock_minimo").val());

	// if (stock_minimo > stock) {
	// 	bootbox.alert("El stock mínimo no puede ser mayor que el stock normal.");
	// 	return;
	// }

	var precio_compra = parseFloat($("#precio_compra").val());
	var precio_venta = parseFloat($("#precio_venta").val());
	var precio_venta_mayor = parseFloat($("#precio_venta_mayor").val());

	if ((precio_venta > 0 && precio_venta < precio_compra) || (precio_venta_mayor > 0 && precio_venta_mayor < precio_compra)) {
		bootbox.alert("El precio de venta no puede ser menor que el precio de compra.");
		return;
	}

	$("#btnGuardar").prop("disabled", true);
	$("#ganancia").prop("disabled", false);

	formatearNumeroCorrelativo();

	var parteLetras = $("#cod_part_1").val();
	var parteNumeros = $("#cod_part_2").val();
	var codigoCompleto = parteLetras + parteNumeros;

	var formData = new FormData($("#formulario")[0]);
	formData.append("codigo_producto", codigoCompleto);

	formData.append("param", "1");

	$("#ganancia").prop("disabled", true);

	let detalles = frmDetallesVisible() ? obtenerDetalles() : { comision: '0.00', talla: '', color: '', peso: '0.00', fecha_emision: '', fecha_vencimiento: '', nota_1: '', nota_2: '', codigo: '' };

	for (let key in detalles) {
		formData.append(key, detalles[key]);
	}

	idlocal = $("#idlocal").val();
	actualizarCorrelativoProducto(idlocal);

	$.ajax({
		url: "../ajax/articuloExterno.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El código de barra del producto que ha ingresado ya existe." || datos == "El código del producto que ha ingresado ya existe en el local seleccionado.") {
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

function obtenerDetalles() {
	let detalles = {
		comision: $("#comision").val(),
		talla: $("#talla").val(),
		color: $("#color").val(),
		peso: $("#peso").val(),
		fecha_emision: $("#fecha_emision").val(),
		fecha_vencimiento: $("#fecha_vencimiento").val(),
		nota_1: $("#nota_1").val(),
		nota_2: $("#nota_2").val(),
		codigo: $("#codigo_barra").val()
	};

	if (!detalles.comision) detalles.comision = '0.00';
	if (!detalles.talla) detalles.talla = '';
	if (!detalles.color) detalles.color = '';
	if (!detalles.peso) detalles.peso = '0.00';
	if (!detalles.fecha_emision) detalles.fecha_emision = '';
	if (!detalles.fecha_vencimiento) detalles.fecha_vencimiento = '';
	if (!detalles.nota_1) detalles.nota_1 = '';
	if (!detalles.nota_2) detalles.nota_2 = '';
	if (!detalles.codigo) detalles.codigo = '';

	return detalles;
}

function frmDetallesVisible() {
	return $("#frmDetalles").is(":visible");
}

function mostrar(idarticulo) {
	mostrarform(true);
	frmDetalles(true);

	$(".btn1").show();
	$(".btn2").hide();

	$.post("../ajax/articuloExterno.php?op=mostrar", { idarticulo: idarticulo }, function (data, status) {
		data = JSON.parse(data);
		console.log(data);

		$("#idcategoria").val(data.idcategoria);
		$('#idcategoria').selectpicker('refresh');
		$("#idlocal").val(data.idlocal);
		$('#idlocal').selectpicker('refresh');
		$("#idmarca").val(data.idmarca);
		$('#idmarca').selectpicker('refresh');
		$("#idmedida").val(data.idmedida);
		$('#idmedida').selectpicker('refresh');
		$("#codigo_barra").val(data.codigo);

		const { letras, numeros } = separarCodigoProducto(data.codigo_producto);

		// Actualizar los valores en los campos
		$("#cod_part_1").val(letras); // Alfanuméricos hasta la última letra
		$("#cod_part_2").val(numeros); // Números restantes

		$("#nombre").val(data.nombre);
		$("#stock").val(data.stock);
		$("#stock_minimo").val(data.stock_minimo);
		$("#descripcion").val(data.descripcion);
		$("#talla").val(data.talla);
		$("#color").val(data.color);
		$("#peso").val(data.peso);
		data.fecha_emision_formateada != "0000-00-00" ? $("#fecha_emision").val(data.fecha_emision_formateada) : null;
		data.fecha_vencimiento_formateada != "0000-00-00" ? $("#fecha_vencimiento").val(data.fecha_vencimiento_formateada) : null;
		$("#nota_1").val(data.nota_1);
		$("#nota_2").val(data.nota_2);
		$("#imagenmuestra").show();
		$("#imagenmuestra").attr("src", "../files/articulos/" + data.imagen);
		$("#precio_compra").val(data.precio_compra);
		$("#precio_venta").val(data.precio_venta);
		$("#precio_venta_mayor").val(data.precio_venta_mayor);
		$("#ganancia").val(data.ganancia);
		$("#comision").val(data.comision);
		$("#imagenactual").val(data.imagen);
		$("#idarticulo").val(data.idarticulo);
		generarbarcode(0);
		actualizarRUC();
	})
}

function separarCodigoProducto(codigoProducto) {
	// Regex para capturar hasta la última letra como alfanuméricos y lo demás como números
	const partes = codigoProducto.match(/^(.*[A-Za-z])(\d*)$/) || ["", "", ""];

	const letras = partes[1] || ""; // Todo hasta la última letra
	const numeros = partes[2] || ""; // Números después de la última letra

	return { letras, numeros };
}

function limpiarModalComision() {
	$("#comision2").val("");
	$("#btnGuardarComision").prop("disabled", false);
}

function verificarModalComision() {
	bootbox.confirm("¿Está seguro de modificar la comisión de todos los productos?", function (result) {
		if (result) {
			$("#formulario2").submit();
		}
	})
}

function guardaryeditar2(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardarComision").prop("disabled", true);
	var formData = new FormData($("#formulario2")[0]);

	$.ajax({
		url: "../ajax/articuloExterno.php?op=guardarComision",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			limpiarModalComision();
			bootbox.alert(datos);
			$('#myModal').modal('hide');
			tabla.ajax.reload();
		}
	});
}

//Función para desactivar registros
function desactivar(idarticulo) {
	bootbox.confirm("¿Está seguro de desactivar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articuloExterno.php?op=desactivar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para activar registros
function activar(idarticulo) {
	bootbox.confirm("¿Está seguro de activar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articuloExterno.php?op=activar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para eliminar los registros
function eliminar(idarticulo) {
	bootbox.confirm("¿Estás seguro de eliminar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articuloExterno.php?op=eliminar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function convertirMayus() {
	var inputCodigo = document.getElementById("codigo_producto");
	inputCodigo.value = inputCodigo.value.toUpperCase();
}

function resetear() {
	const selects = ["idmarcaBuscar", "idcategoriaBuscar", "estadoBuscar", "fecha_inicio", "fecha_fin"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
		$("#" + selectId).selectpicker('refresh');
	}

	listar();
}

//Función buscar
function buscar() {
	let param1 = "";
	let param2 = "";
	let param3 = "";

	// Obtener los selectores
	const selectMarca = document.getElementById("idmarcaBuscar");
	const selectCategoria = document.getElementById("idcategoriaBuscar");
	const selectEstado = document.getElementById("estadoBuscar");

	if (selectMarca.value == "" && selectCategoria.value == "" && selectEstado.value == "") {
		bootbox.alert("Debe seleccionar al menos un campo para realizar la búsqueda.");
		return;
	}

	param1 = selectMarca.value;
	param2 = selectCategoria.value;
	param3 = selectEstado.value;

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
			"ajax":
			{
				url: '../ajax/articuloExterno.php?op=listar',
				data: { param1: param1, param2: param2, param3: param3 },
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10), td:eq(11), td:eq(12), td:eq(13), td:eq(14), td:eq(15)').addClass('nowrap-cell');
			},
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
				document.getElementById('codigo').value = codigoBarra;
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

	// for (var  i= 0; i < codigo.length; i++) {
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