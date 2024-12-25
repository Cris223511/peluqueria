var tabla;
let lastNumComp = 0;

let moneda = $("#moneda").val();
let valor_dolar = 0.27;

inicializeGLightbox();

//correlativos

function actualizarCorrelativo() {
	$.post("../ajax/compra.php?op=getLastNumComprobante", function (e) {
		console.log(e);
		lastNumComp = generarSiguienteCorrelativo(e);
		$("#num_comprobante_final1").text(lastNumComp);
	});
}

function actualizarCorrelativoLocal(idlocal) {
	if (idlocal === "") {
		return;
	}

	$.post("../ajax/compra.php?op=getLastNumComprobanteLocal", { idlocal: idlocal }, function (e) {
		console.log(e);
		const obj = JSON.parse(e);
		console.log(obj);
		// if (obj.idcaja == 0) {
		// 	bootbox.alert("El local seleccionado no tiene una caja disponible.");
		// 	$("#idlocal_session").val("");
		// 	$("#idlocal_session").selectpicker('refresh');
		// 	$("#num_comprobante_final1").text(lastNumComp);
		// } else {
		lastNumComp = generarSiguienteCorrelativo(obj.last_num_comprobante);
		// idCajaFinal = obj.idcaja;
		// }
	});
}

function actualizarCorrelativoProducto(idlocal) {
	$.post("../ajax/articulo.php?op=getLastCodigo", { idlocal: idlocal }, function (num) {
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
	listarDatos();

	$("#formulario").on("submit", function (e) { guardaryeditar(e); });
	$("#formulario2").on("submit", function (e) { guardaryeditar2(e); });
	$("#formulario3").on("submit", function (e) { guardaryeditar3(e); });
	$("#formulario4").on("submit", function (e) { guardaryeditar4(e); });
	$("#formSunat").on("submit", function (e) { buscarSunat(e); });
	$("#formulario5").on("submit", function (e) { guardaryeditar5(e); });
	$("#formulario6").on("submit", function (e) { guardaryeditar6(e); });
	$("#formulario7").on("submit", function (e) { guardaryeditar7(e); });
	$("#formulario8").on("submit", function (e) { guardaryeditar8(e); });

	$('#mCompras').addClass("treeview active");
	$('#lCompras').addClass("active");

	// productos

	$("#btnDetalles1").show();
	$("#btnDetalles2").hide();
	$("#frmDetalles").hide();

	$(".btn1").show();
	$(".btn2").hide();

	$("#imagenmuestra").hide();

	$.post("../ajax/articulo.php?op=listarTodosActivos", function (data) {
		const obj = JSON.parse(data);
		console.log(obj);

		const selects = {
			"idmarca": $("#idmarca, #idmarcaBuscar"),
			"idcategoria": $("#idcategoria, #idcategoriaBuscar"),
			"idlocal": $("#idlocal3"),
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

		actualizarRUC5();

		var comisionarValor = localStorage.getItem('comisionarValor') || '2';

		$("#comisionar").val(comisionarValor).selectpicker("refresh");

		$("#comisionar").on('change', function () {
			localStorage.setItem('comisionarValor', $(this).val());
		});
	});
}

function listarTodosActivos(selectId) {
	$.post("../ajax/articulo.php?op=listarTodosActivos", function (data) {
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
						$("#descripcion6").val("");
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
						$("#descripcion7").val("");
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
						$("#descripcion8").val("");
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


function actualizarRUC5() {
	const selectLocal = document.getElementById("idlocal3");
	const localRUCInput = document.getElementById("local_ruc3");
	const selectedOption = selectLocal.options[selectLocal.selectedIndex];

	if (selectedOption.value !== "") {
		const localRUC = selectedOption.getAttribute('data-local-ruc');
		localRUCInput.value = localRUC;
	} else {
		localRUCInput.value = "";
	}

	idlocal = $("#idlocal3").val();
	actualizarCorrelativoProducto(idlocal);
}

//Función limpiar modal de artículos
function limpiarModalArticulos() {
	$("#codigo_barra").val("");
	$("#cod_part_1").val("");
	$("#cod_part_2").val("");
	$("#nombre3").val("");
	$("#local_ruc3").val("");
	$("#descripcion5").val("");
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
	$("#imagen2").val("");
	$("#precio_compra").val("");
	$("#precio_venta").val("");
	$("#precio_venta_mayor").val("");
	$("#ganancia").val("0.00");
	$("#comision").val("");
	$("#print").hide();
	$("#idarticulo").val("");

	$("#idcategoria").val($("#idcategoria option:first").val());
	$("#idcategoria").selectpicker('refresh');
	$("#idlocal3").val($("#idlocal3 option:first").val());
	$("#idlocal3").selectpicker('refresh');
	$("#idmarca").val($("#idmarca option:first").val());
	$("#idmarca").selectpicker('refresh');
	$("#idmedida").val($("#idmedida option:first").val());
	$("#idmedida").selectpicker('refresh');

	idlocal = 0;

	actualizarRUC5();

	$(".btn1").show();
	$(".btn2").hide();

	detenerEscaneo();

}

function limpiar() {
	limpiarModalMetodoPago();
	limpiarModalProveedor();
	limpiarModalProveedor2();
	limpiarModalProveedor4();
	limpiarModalPrecuenta();
	limpiarModalArticulos();

	listarDatos();

	$("#detalles tbody").empty();
	$("#inputsMontoMetodoPago").empty();
	$("#inputsMetodoPago").empty();

	$("#total_compra_valor").html((moneda === 'dolares') ? "0.00 $" : "S/. 0.00");
	$("#tipo_comprobante").val("ORDEN DE COMPRA");
	$("#tipo_comprobante").selectpicker('refresh');

	$("#comentario_interno_final").val("");
	$("#comentario_externo_final").val("");
	$("#idlocal_session_final").val("");
	$("#igvFinal").val("0.00");
	$("#total_compra_final").val("");
	$("#vuelto_final").val("");
}

function limpiarTodo() {
	bootbox.confirm("¿Estás seguro de limpiar los datos de la compra?, perderá todos los datos registrados.", function (result) {
		if (result) {
			limpiar();
		}
	})
}

function agregar() {
	mostrarform(true);
	actualizarCorrelativo();
}

function frmDetalles(bool) {
	if (bool == true) { $("#frmDetalles").show(); $("#btnDetalles1").hide(); $("#btnDetalles2").show(); }
	if (bool == false) { $("#frmDetalles").hide(); $("#btnDetalles1").show(); $("#btnDetalles2").hide(); }
	// $('html, body').animate({ scrollTop: $(document).height() }, 10);
}

//Función para guardar o editar

function guardaryeditar8(e) {
	e.preventDefault(); // No se activará la acción predeterminada del evento

	// var codigoBarra = $("#codigo_barra").val();
	// var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);

	// if (!formatoValido && codigoBarra != "") {
	// 	bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
	// 	$("#btnGuardarProducto").prop("disabled", false);
	// 	return;
	// }

	var precio_compra = parseFloat($("#precio_compra").val());
	var precio_venta = parseFloat($("#precio_venta").val());
	var precio_venta_mayor = parseFloat($("#precio_venta_mayor").val());


	if ((precio_venta > 0 && precio_venta < precio_compra) || (precio_venta_mayor > 0 && precio_venta_mayor < precio_compra)) {
		bootbox.alert("El precio de venta no puede ser menor que el precio de compra.");
		return;
	}

	$("#btnGuardarProducto").prop("disabled", true);
	$("#ganancia").prop("disabled", false);

	formatearNumeroCorrelativo();

	var parteLetras = $("#cod_part_1").val();
	var parteNumeros = $("#cod_part_2").val();
	var codigoCompleto = parteLetras + parteNumeros;

	var formData = new FormData($("#formulario8")[0]);
	formData.append("codigo_producto", codigoCompleto);

	formData.append("param", "0");

	$("#ganancia").prop("disabled", true);

	let detalles = frmDetallesVisible() ? obtenerDetalles() : { comision: '0.00', talla: '', color: '', peso: '0.00', fecha_emision: '', fecha_vencimiento: '', nota_1: '', nota_2: '', codigo: '' };

	for (let key in detalles) {
		formData.append(key, detalles[key]);
	}

	$.ajax({
		url: "../ajax/articulo.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			console.log(datos);
			var response = JSON.parse(datos);
			console.log(response);
			let idarticulo = response[0];
			let mensaje = response[1];

			if (mensaje == "El código de barra del producto que ha ingresado ya existe." || mensaje == "El código del producto que ha ingresado ya existe en el local seleccionado.") {
				bootbox.alert(mensaje);
				$("#btnGuardarProducto").prop("disabled", false);
				return;
			}

			if (idarticulo == 0) {
				bootbox.alert(mensaje);
				$("#btnGuardarProducto").prop("disabled", false);
				return;
			}

			console.log("el idarticulo es =) => ", idarticulo);

			limpiarModalArticulos();
			$("#myModal12").modal("hide");
			$("#btnGuardarProducto").prop("disabled", false);
			bootbox.alert(mensaje);

			$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
				const obj = JSON.parse(data);

				let articulo = obj.articulo;
				let servicio = obj.servicio;

				listarSelectsArticulos(articulo, servicio);
				listarArticulos(articulo, servicio);

				setTimeout(function () {
					seleccionarProductoPorId(idarticulo);
				}, 200);
			});
		}
	});
}

function seleccionarProductoPorId(idarticulo) {
	var selectElement = document.getElementById('productos1');
	var options = selectElement.options;

	for (var i = 0; i < options.length; i++) {
		if (options[i].value == idarticulo) {
			selectElement.selectedIndex = i;
			verificarProducto(
				options[i].getAttribute('data-tipo-producto'),
				options[i].value,
				options[i].getAttribute('data-nombre'),
				options[i].getAttribute('data-local'),
				options[i].getAttribute('data-stock'),
				options[i].getAttribute('data-precio-compra'),
				options[i].getAttribute('data-codigo')
			);
			selectElement.value = "";
			$(selectElement).selectpicker('refresh');
			colocarNegritaStocksSelects();
			break;
		}
	}
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
	// var codigo = $("#codigo_barra").val().replace(/\s/g, '').replace(/\D/g, '');
	// var formattedCode = '';

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

	// $("#codigo_barra").val(formattedCode);
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

function convertirMayusProduct() {
	var inputCodigo = document.getElementById("cod_part_1");
	inputCodigo.value = inputCodigo.value.toUpperCase();
}

//Función para imprimir el código de barras
function imprimir() {
	$("#print").printArea();
}

function listarDatos() {
	$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);
		console.log(obj);

		let articulo = obj.articulo || [];
		let servicio = obj.servicio || [];
		let metodo_pago = obj.metodo_pago || [];
		let proveedores = obj.proveedores || [];
		let categoria = obj.categoria || [];
		let locales = obj.locales || [];

		$("#categoria").empty();
		$("#pagos").empty();

		$("#productos1").empty();
		$("#productos2").empty();
		$("#idproveedor").empty();

		listarArticulos(articulo, servicio);
		listarCategoria(categoria);
		listarMetodoPago(metodo_pago);
		listarSelects(articulo, servicio, proveedores, locales);
	});
}

function listarTodosLosArticulos() {
	$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);

		let articulo = obj.articulo || [];
		let servicio = obj.servicio || [];

		listarArticulos(articulo, servicio);
	});
}

function listarArticulosPorCategoria(idcategoria) {
	$.post("../ajax/compra.php?op=listarArticulosPorCategoria", { idcategoria: idcategoria }, function (data) {
		const articulos = JSON.parse(data).articulo || [];
		console.log(articulos);

		listarArticulos(articulos, []);
	});
}

function listarArticulos(articulos, servicios) {
	$("#productos").empty();
	let productosContainer = $("#productos");

	if ((articulos.length > 0 || servicios.length > 0) && !(articulos.length === 0 && servicios.length === 0)) {
		articulos.forEach((articulo) => {

			articulo.stock = +articulo.stock;
			articulo.stock_minimo = +articulo.stock_minimo;

			// Formatear precios
			let precioCompraFormateado = (moneda === 'dolares') ? articulo.precio_compra + ' $' : 'S/ ' + articulo.precio_compra;

			var stockHtml = ((articulo.stock) > 0 && (articulo.stock) < (articulo.stock_minimo)) ? '<span style="color: #Ea9900; font-weight: bold">' + articulo.stock + '</span>' : ((articulo.stock != '0') ? '<span style="color: #00a65a; font-weight: bold">' + articulo.stock + '</span>' : '<span style="color: red; font-weight: bold">' + articulo.stock + '</span>');
			var labelHtml = ((articulo.stock) > 0 && (articulo.stock) < (articulo.stock_minimo)) ? '<span class="label bg-orange" style="width: min-content;">agotandose</span>' : ((articulo.stock != '0') ? '<span class="label bg-green" style="width: min-content;">Disponible</span>' : '<span class="label bg-red" style="width: min-content;">agotado</span>');

			let html = `
				<div class="draggable" style="padding: 10px; width: 180px;">
					<div class="caja-productos">
						<a href="../files/articulos/${articulo.imagen}" class="galleria-lightbox">
							<img src="../files/articulos/${articulo.imagen}" class="img-fluid">
						</a>
						<h1>${articulo.nombre}</h1>
						<h4>${articulo.marca ?? "Sin registrar."}</h4>
						<div class="subcaja-gris">
							<span>STOCK: <strong>${stockHtml}</strong></span>
							${labelHtml}
							<span><strong>${precioCompraFormateado}</strong></span>
						</div>
						<a style="width: 100%;" onclick="verificarProducto('producto','${articulo.id}','${articulo.nombre}','${articulo.local}','${articulo.stock}','${articulo.precio_compra}','${articulo.codigo_producto}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
					</div>
				</div>
			`;

			productosContainer.append(html);
		});

		servicios.forEach((servicio) => {

			// Formatear precios
			let precioCompraFormateado = (moneda === 'dolares') ? servicio.precio_compra + ' $' : 'S/ ' + servicio.precio_compra;

			let html = `
					<div class="draggable" style="padding: 10px; width: 180px;">
						<div class="caja-productos">
							<a href="../files/articulos/${servicio.imagen}" class="galleria-lightbox">
								<img src="../files/articulos/${servicio.imagen}" class="img-fluid">
							</a>
							<h1>${servicio.nombre}</h1>
							<h4>${servicio.marca ?? "Sin registrar."}</h4>
							<div class="subcaja-gris">
								<span><strong>ㅤ</strong></span>
								<span class="label bg-green" style="width: min-content;">Disponible</span>
								<span><strong>${precioCompraFormateado}</strong></span>
							</div>
							<a style="width: 100%;" onclick="verificarProducto('servicio','${servicio.id}','${servicio.nombre}','Sin registrar.','${servicio.stock}','${servicio.precio_compra}','${servicio.codigo}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
						</div>
					</div>
				`;

			productosContainer.append(html);
		});

		buttonsScrollingCarousel();
		inicializeGLightbox();
	} else {
		let html = `
				<div class="draggable" style="padding: 10px; width: 100%;">
					<div class="caja-productos-vacia">
						<h4>no se encontraron productos y/o servicios.</h4>
					</div>
				</div>
			`;

		productosContainer.append(html);
		buttonsScrollingCarousel();
	}
}

function listarCategoria(categorias) {
	let categoriaContainer = $("#categoria");

	categorias.forEach((categoria) => {
		let startClickX, startClickY;

		let html = `
            <div class="draggable" style="padding: 5px">
                <div class="caja-categoria">
                    <h1>${capitalizarPrimeraLetra(categoria.nombre)}</h1>
                    <h4><strong>Productos: ${categoria.cantidad}</strong></h4>
                </div>
            </div>
        `;

		let $categoriaElement = $(html);

		$categoriaElement.mousedown(function (e) {
			startClickX = e.pageX;
			startClickY = e.pageY;
		});

		$categoriaElement.mouseup(function (e) {
			let endClickX = e.pageX;
			let endClickY = e.pageY;

			let dragDistance = Math.sqrt(Math.pow(endClickX - startClickX, 2) + Math.pow(endClickY - startClickY, 2));

			if (dragDistance < 5) {
				$(".caja-categoria").removeClass("categoriaSelected");
				$(this).find(".caja-categoria").addClass("categoriaSelected");
				listarArticulosPorCategoria(categoria.id);
			}
		});

		categoriaContainer.append($categoriaElement);
	});

	inicializeGLightbox();
	inicializegScrollingCarousel();
}


function listarMetodoPago(metodosPago) {
	let pagosContainer = $("#pagos");

	metodosPago.forEach((metodo) => {
		let html = `<a data-id="${metodo.id}" onclick="cambiarEstado('${metodo.id}', '${metodo.nombre}')" class="grayscale"><img src="../files/metodo_pago/${metodo.imagen}" width="100%" class="img-fluid"></a>`;
		pagosContainer.append(html);
	});

	let htmlFinal = `<a data-toggle="modal" href="#myModal2" onclick="limpiarModalMetodoPago();"><img src="../files/metodo_pago/otros.jpg" width="100%" class="img-fluid"></a>`;
	pagosContainer.append(htmlFinal);
}

function listarSelects(articulos, servicios, proveedores, locales) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-codigo="${articulo.codigo_producto}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca ?? "Marca sin registrar."} - ${articulo.codigo?.replace(/\s/g, '') || "C. barra sin registrar."} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar." data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')} - ${servicio.codigo_barra?.replace(/\s/g, '') ? `Código de barra: ${servicio.codigo_barra.replace(/\s/g, '')}` : "C. de barra sin registrar."}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');

	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-codigo="${articulo.codigo_producto}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca ?? "Marca sin registrar."} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar." data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')} - ${servicio.codigo_barra?.replace(/\s/g, '') ? `Código de barra: ${servicio.codigo_barra.replace(/\s/g, '')}` : "C. de barra sin registrar."}</option>`;
		selectProductos2.append(optionHtml);
	});

	let selectProveedores = $("#idproveedor");
	selectProveedores.empty();
	selectProveedores.append('<option value="">Buscar proveedor.</option>');

	proveedores.forEach((proveedor) => {
		let optionHtml = `<option value="${proveedor.id}">${proveedor.nombre} - ${proveedor.tipo_documento}: ${proveedor.num_documento}</option>`;
		selectProveedores.append(optionHtml);
	});

	if ($("#idlocal_session").length) {
		let selectLocales = $("#idlocal_session");
		selectLocales.empty();
		selectLocales.append('<option value="">- Seleccione -</option>');

		locales.forEach((local) => {
			let optionHtml = `<option value="${local.id}">${local.nombre} - ${local.local_ruc}</option>`;
			selectLocales.append(optionHtml);
		});

		selectLocales.selectpicker('refresh');
	}

	let selectLocales6 = $("#idlocal_session_final");
	selectLocales6.empty();
	selectLocales6.append('<option value="">- Seleccione -</option>');

	locales.forEach((local) => {
		let optionHtml = `<option value="${local.id}">${local.nombre} - ${local.local_ruc}</option>`;
		selectLocales6.append(optionHtml);
	});

	// Después de agregar todas las opciones, actualizamos el plugin selectpicker
	selectProductos1.selectpicker('refresh');
	selectProductos2.selectpicker('refresh');
	selectProveedores.selectpicker('refresh');

	$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'checkEnter(event)');
	$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('oninput', 'checkDNI(this)');
	$('#idproveedor').closest('.form-group').find('.dropdown-menu.open').addClass('idproveedorInput');

	$("#idproveedor").val(0);
	$("#idproveedor").selectpicker("refresh");

	colocarNegritaStocksSelects();
}

function listarSelectsArticulos(articulos, servicios) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-codigo="${articulo.codigo_producto}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca ?? "Marca sin registrar."} - ${articulo.codigo?.replace(/\s/g, '') || "C. barra sin registrar."} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar." data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')} - ${servicio.codigo_barra?.replace(/\s/g, '') ? `Código de barra: ${servicio.codigo_barra.replace(/\s/g, '')}` : "C. de barra sin registrar."}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');
	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-codigo="${articulo.codigo_producto}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca ?? "Marca sin registrar."} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar." data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')} - ${servicio.codigo_barra?.replace(/\s/g, '') ? `Código de barra: ${servicio.codigo_barra.replace(/\s/g, '')}` : "C. de barra sin registrar."}</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos1.selectpicker('refresh');
	selectProductos2.selectpicker('refresh');

	colocarNegritaStocksSelects();
}

function colocarNegritaStocksSelects() {
	$('#productos1, #productos2').closest('.form-group').find('.text').each(function () {
		var contenido = $(this).html();
		contenido = contenido.replace(/(PRODUCTOS:|SERVICIOS:)/g, '<strong>$1</strong>');
		contenido = contenido.replace(/\((STOCK: \d+)\)/g, '<strong>($1)</strong>');
		contenido = contenido.replace(/\b(N° \d+)\b/, '<strong>$1</strong>');
		$(this).html(contenido);
	});
}

function checkEnter(event) {
	let inputValue = $('#idproveedor').closest('.form-group').find('input[type="text"]');

	if (event.key === "Enter") {
		if ($('.no-results').is(':visible') && /^\d{1,11}$/.test(inputValue.val())) {
			$('#myModal3').modal('show');
			$("#sunat").val(inputValue.val());
			limpiarModalProveedor();
			console.log("di enter en idproveedor =)");
		} else {
			inputValue.removeAttr('maxlength');
		}
	}
}

function checkDNI(value) {
	let inputValue = $(value);
	let inputValueText = inputValue.val();

	if ($('.no-results').is(':visible') && /^\D*\d{2,}/.test(inputValueText)) {
		console.log("hay solo números =)");
		onlyNumbers(inputValue[0]);
		inputValue.attr('maxlength', 11);
	} else {
		inputValue.removeAttr('maxlength');
	}
}

function seleccionarProducto(selectElement) {
	var selectedOption = selectElement.options[selectElement.selectedIndex];
	verificarProducto(selectedOption.getAttribute('data-tipo-producto'), selectedOption.value, selectedOption.getAttribute('data-nombre'), selectedOption.getAttribute('data-local'), selectedOption.getAttribute('data-stock'), selectedOption.getAttribute('data-precio-compra'), selectedOption.getAttribute('data-codigo'))
	selectElement.value = "";
	$(selectElement).selectpicker('refresh');
	colocarNegritaStocksSelects();
}

let idarticuloGlobal = "";
let nombreGlobal = "";
let localGlobal = "";
let precioCompraGlobal = "";
let codigoGlobal = "";
let tipoProductoFinal = "";

function verificarProducto(tipoarticulo, idarticulo, nombre, local, stock, precio_compra, codigo) {
	var existeProducto = validarTablaProductos(tipoarticulo, idarticulo);

	// if (stock == 0) {
	// 	bootbox.alert("El producto seleccionado se encuentra sin stock.");
	// 	return;
	// }

	if (!existeProducto) {
		console.log("esto traigo =) =>", tipoarticulo, idarticulo, nombre, local, stock, precio_compra, codigo);

		idarticuloGlobal = idarticulo;
		nombreGlobal = nombre;
		localGlobal = local;
		stockGlobal = stock;
		precioCompraGlobal = precio_compra;
		codigoGlobal = codigo;
		tipoProductoFinal = tipoarticulo;

		agregarDetalle(tipoProductoFinal, idarticuloGlobal, nombreGlobal, localGlobal, stockGlobal, precioCompraGlobal, codigoGlobal);
	} else {
		bootbox.alert("No puedes agregar el mismo artículo o servicio dos veces.");
	}
}

function validarTablaProductos(tipoarticulo, idarticulo) {
	var existeProducto = false;

	if ($('#detalles .filas').length > 0) {
		$('#detalles .filas').each(function () {
			var idArticuloActual = $(this).find(tipoarticulo === "producto" ? 'input[name="idarticulo[]"]' : 'input[name="idservicio[]"]').val();

			if (idArticuloActual === idarticulo) {
				existeProducto = true;
				return false;
			}
		});
	}

	return existeProducto;
}

// METODO DE PAGO

function cambiarEstado(id, nombre) {
	var elemento = document.querySelector(`#pagos a[data-id="${id}"]`);
	var montoMetodoPago = document.getElementById('montoMetodoPago');

	if (elemento.classList.contains('grayscale')) {
		elemento.classList.remove('grayscale');
		elemento.classList.add('color');

		// Agregar input en inputsMetodoPago
		var inputMetodoPago = document.createElement('input');
		inputMetodoPago.type = 'hidden';
		inputMetodoPago.name = 'metodo_pago[]';
		inputMetodoPago.value = id;
		document.getElementById('inputsMetodoPago').appendChild(inputMetodoPago);

		// Agregar input en inputsMontoMetodoPago
		var inputMonto = document.createElement('input');
		inputMonto.type = 'hidden';
		inputMonto.name = 'monto[]';
		inputMonto.value = '';
		inputMonto.setAttribute('data-id', id); // Establecer el atributo data-id
		document.getElementById('inputsMontoMetodoPago').appendChild(inputMonto);

		// Agregar HTML al div MontoMetodoPago
		var divMontoMetodoPago = document.createElement('div');
		divMontoMetodoPago.setAttribute('data-id', id); // Establecer el atributo data-id

		divMontoMetodoPago.innerHTML = `
			<div style="padding: 10px; border-top: 1px solid #d2d6de; display: flex; justify-content: space-between; align-items: center;">
				<h5 class="infotitulo" style="margin: 0; padding: 0;">${capitalizarTodasLasPalabras(nombre)}</h5>
				<input type="number" class="form-control" step="any" style="width: 120px; height: 30px;" value="0.00" lang="en-US" oninput="actualizarVuelto();" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0.1" required>
			</div>
		`;
		montoMetodoPago.appendChild(divMontoMetodoPago);

		evitarCaracteresEspecialesCamposNumericos();
		aplicarRestrictATodosLosInputs();
	} else {
		elemento.classList.remove('color');
		elemento.classList.add('grayscale');

		// Remover input del inputsMetodoPago
		var inputToRemove = document.querySelector(`input[name="metodo_pago[]"][value="${id}"]`);
		if (inputToRemove) {
			inputToRemove.parentNode.removeChild(inputToRemove);
		}

		// Remover input del inputsMontoMetodoPago
		var inputMontoToRemove = document.querySelector(`input[name="monto[]"][data-id="${id}"]`);
		if (inputMontoToRemove) {
			inputMontoToRemove.parentNode.removeChild(inputMontoToRemove);
		}

		// Remover HTML del div MontoMetodoPago
		var divToRemove = montoMetodoPago.querySelector(`div[data-id="${id}"]`);
		if (divToRemove) {
			divToRemove.parentNode.removeChild(divToRemove);
		}
	}
}

function listarMetodosDePago() {
	$.post("../ajax/compra.php?op=listarMetodosDePago", function (data) {
		console.log(data);
		const obj = JSON.parse(data);
		console.log(obj);

		let metodo_pago = obj.metodo_pago;

		$("#pagos").empty();
		$("#inputsMetodoPago").empty();
		$("#inputsMontoMetodoPago").empty();
		$("#montoMetodoPago").empty();
		listarMetodoPago(metodo_pago);
	});
}

function limpiarModalMetodoPago() {
	$("#idmetodopago").val("");
	$("#titulo").val("");
	$("#imagen").val("");
	$("#descripcion").val("");
	$("#btnGuardarMetodoPago").prop("disabled", false);
}

function guardaryeditar2(e) {
	e.preventDefault();
	$("#btnGuardarMetodoPago").prop("disabled", true);
	var formData = new FormData($("#formulario2")[0]);

	$.ajax({
		url: "../ajax/metodo_pago.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El nombre del método de pago ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarMetodoPago").prop("disabled", false);
				return;
			}
			bootbox.alert(datos);
			$('#myModal2').modal('hide');
			listarMetodosDePago();
			limpiarModalMetodoPago();
		}
	});
}

// PROVEEDORES NUEVOS (POR SUNAT)

function listarProveedores(idproveedor) {
	$.post("../ajax/compra.php?op=listarProveedores", function (data) {
		console.log(data);
		const obj = JSON.parse(data);
		console.log(obj);

		let proveedores = obj.proveedores;

		let selectProveedores = $("#idproveedor");
		selectProveedores.empty();
		selectProveedores.append('<option value="">Buscar proveedor.</option>');

		proveedores.forEach((proveedor) => {
			let optionHtml = `<option value="${proveedor.id}">${proveedor.nombre} - ${proveedor.tipo_documento}: ${proveedor.num_documento}</option>`;
			selectProveedores.append(optionHtml);
		});

		selectProveedores.val(idproveedor);
		selectProveedores.selectpicker('refresh');

		$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'checkEnter(event)');
		$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('oninput', 'checkDNI(this)');
	});
}

function limpiarModalProveedor() {
	$("#idproveedor2").val("");
	$("#nombre").val("");
	$("#tipo_documento").val("");
	$("#num_documento").val("");
	$("#direccion").val("");
	$("#telefono").val("");
	$("#email").val("");
	$("#descripcion2").val("");

	habilitarTodoModalProveedor();

	$("#btnSunat").prop("disabled", false);
	$("#btnGuardarProveedor").prop("disabled", true);
}

function guardaryeditar3(e) {
	e.preventDefault();
	$("#btnGuardarProveedor").prop("disabled", true);

	deshabilitarTodoModalProveedor();
	var formData = new FormData($("#formulario3")[0]);
	habilitarTodoModalProveedor();

	$.ajax({
		url: "../ajax/proveedores.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			console.log(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El proveedor no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor").prop("disabled", false);
				return;
			}
			bootbox.alert("Proveedor registrado correctamente.");
			$('#myModal3').modal('hide');
			listarProveedores(datos);
			limpiarModalProveedor();
			$("#sunat").val("");
		}
	});
}

function buscarSunat(e) {
	e.preventDefault();
	var formData = new FormData($("#formSunat")[0]);
	limpiarModalProveedor();
	$("#btnSunat").prop("disabled", true);

	$.ajax({
		url: "../ajax/compra.php?op=consultaSunat",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			console.log(datos);
			if (datos == "DNI no valido" || datos == "RUC no valido") {
				limpiarModalProveedor();
				bootbox.confirm(datos + ", ¿deseas crear un proveedor manualmente?", function (result) {
					if (result) {
						(datos == "DNI no valido") ? $("#tipo_documento4").val("DNI") : $("#tipo_documento4").val("RUC");

						$("#tipo_documento4").trigger("change");

						let inputValue = $('#sunat').val();
						$("#num_documento4").val(inputValue);

						$('#myModal3').modal('hide');
						$('#myModal6').modal('show');
					}
				})
				$("#btnSunat").prop("disabled", false);
			} else if (datos == "El DNI debe tener 8 caracteres." || datos == "El RUC debe tener 11 caracteres.") {
				bootbox.alert(datos);
				limpiarModalProveedor();
				$("#btnSunat").prop("disabled", false);
			} else {
				const obj = JSON.parse(datos);
				console.log(obj);

				if (obj.tipoDocumento == "1") {
					var nombreCompleto = capitalizarTodasLasPalabras(obj.nombres + " " + obj.apellidoPaterno + " " + obj.apellidoMaterno);
					var direccionCompleta = "";
				} else {
					var nombreCompleto = capitalizarTodasLasPalabras(obj.razonSocial);
					var direccionCompleta = capitalizarTodasLasPalabras(obj.provincia + ", " + obj.distrito + ", " + obj.direccion);
				}

				console.log("Nombre completo es =) =>" + nombreCompleto);
				console.log("Direccion completa es =) =>" + direccionCompleta);

				$("#nombre").val(nombreCompleto);
				$("#tipo_documento").val(obj.tipoDocumento == "1" ? "DNI" : "RUC");
				$("#num_documento").val(obj.numeroDocumento);
				$("#direccion").val(direccionCompleta);
				$("#telefono").val(obj.telefono);
				$("#email").val(obj.email);

				// Deshabilitar los campos solo si están vacíos
				$("#nombre").prop("disabled", (obj.hasOwnProperty("nombres") || obj.hasOwnProperty("razonSocial")) && nombreCompleto !== "" ? true : false);
				$("#direccion").prop("disabled", obj.hasOwnProperty("direccion") && direccionCompleta !== "" ? true : false);
				$("#telefono").prop("disabled", obj.hasOwnProperty("telefono") && obj.telefono !== "" ? true : false);
				$("#email").prop("disabled", obj.hasOwnProperty("email") && obj.email !== "" ? true : false);

				$("#descripcion2").prop("disabled", false);

				$("#sunat").val("");

				$("#btnSunat").prop("disabled", false);
				$("#btnGuardarProveedor").prop("disabled", false);
			}
		}
	});
}

function habilitarTodoModalProveedor() {
	$("#tipo_documento").prop("disabled", true);
	$("#num_documento").prop("disabled", true);
	$("#nombre").prop("disabled", true);
	$("#direccion").prop("disabled", true);
	$("#telefono").prop("disabled", true);
	$("#email").prop("disabled", true);
	$("#descripcion2").prop("disabled", true);
}

function deshabilitarTodoModalProveedor() {
	$("#tipo_documento").prop("disabled", false);
	$("#num_documento").prop("disabled", false);
	$("#nombre").prop("disabled", false);
	$("#direccion").prop("disabled", false);
	$("#telefono").prop("disabled", false);
	$("#email").prop("disabled", false);
	$("#descripcion2").prop("disabled", false);
}

// PROVEEDORES NUEVOS (CARNET POR EXTRANJERÍA)

function limpiarModalProveedor2() {
	$("#idproveedor3").val("");
	$("#nombre2").val("");
	$("#tipo_documento2").val("");
	$("#num_documento2").val("");
	$("#direccion2").val("");
	$("#telefono2").val("");
	$("#email2").val("");
	$("#descripcion3").val("");

	$("#btnGuardarProveedor2").prop("disabled", false);
}

function guardaryeditar4(e) {
	e.preventDefault();
	$("#btnGuardarProveedor2").prop("disabled", true);
	var formData = new FormData($("#formulario4")[0]);

	$.ajax({
		url: "../ajax/proveedores.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			console.log(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El proveedor no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor2").prop("disabled", false);
				return;
			}
			bootbox.alert("Proveedor registrado correctamente.");
			$('#myModal4').modal('hide');
			listarProveedores(datos);
			limpiarModalProveedor2();
		}
	});
}

// PROOVEDORES NUEVOS (CLIENTE GENÉRICO)

function seleccionarPublicoGeneral() {
	$("#idproveedor").val(0);
	$("#idproveedor").selectpicker("refresh");
}

// PROVEEDORES NUEVOS (POR SI NO ENCUENTRA LA SUNAT)

function limpiarModalProveedor4() {
	$("#idproveedor4").val("");
	$("#nombre4").val("");
	$("#tipo_documento4").val("");
	$("#num_documento4").val("");
	$("#direccion3").val("");
	$("#telefono3").val("");
	$("#email3").val("");
	$("#descripcion4").val("");

	$("#btnGuardarProveedor4").prop("disabled", false);
}

function guardaryeditar6(e) {
	e.preventDefault();
	$("#btnGuardarProveedor4").prop("disabled", true);
	var formData = new FormData($("#formulario6")[0]);

	$.ajax({
		url: "../ajax/proveedores.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			console.log(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El proveedor no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor4").prop("disabled", false);
				return;
			}
			bootbox.alert("Proveedor registrado correctamente.");
			$('#myModal6').modal('hide');
			listarProveedores(datos);
			limpiarModalProveedor4();
		}
	});
}

// PRECUENTA

function verificarModalPrecuenta() {
	if ($('#idproveedor').val() === "") {
		bootbox.alert("Debe seleccionar un proveedor.");
		return;
	}

	if ($('.filas').length === 0) {
		bootbox.alert("Debe agregar por lo menos un producto o servicio.");
		return;
	}

	let detallesValidos = true;
	$('.filas').each(function (index, fila) {
		let precioCompra = $(fila).find('input[name="precio_compra[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		if (!precioCompra || !descuento || !cantidad) {
			detallesValidos = false;
			return false;
		}
	});

	if (!detallesValidos) {
		bootbox.alert("Debe llenar los campos de los artículos o servicios.");
		return;
	}

	if ($('#inputsMetodoPago input[name="metodo_pago[]"]').length === 0) {
		bootbox.alert("Debe seleccionar al menos un método de pago.");
		return;
	}

	let totalCompra = parseFloat($("#total_compra_valor").text().replace(moneda === 'dolares' ? ' $' : 'S/. ', '').replace(',', ''));

	if (totalCompra < 0) {
		bootbox.alert("El total de compra no puede ser negativo.");
		return;
	}

	totalOriginal = totalCompra;

	actualizarTablaDetallesProductosPrecuenta();
	mostrarDatosModalPrecuenta();
	actualizarVuelto();

	$("#myModal7").modal("show");
}

let descuentoFinal = 0;

function mostrarDatosModalPrecuenta() {
	let proveedorSeleccionado = $("#idproveedor option:selected").text();
	$("#proveedorFinal").html(capitalizarTodasLasPalabras(proveedorSeleccionado));

	$("#totalItems").html(cont);

	let totalFinal = $("#total_compra_valor").text();

	totalOriginal = Number(totalFinal).toFixed(2);

	$(".totalFinal1").html('TOTAL A PAGAR: ' + totalFinal);
	$(".totalFinal2").html('OP. GRAVADAS: ' + totalFinal);

	$("#igv").val("0.00");

	$(".descuentoFinal").html('DESCUENTOS TOTALES: ' + (moneda === 'dolares' ? descuentoFinal.toFixed(2) + ' $' : 'S/. ' + descuentoFinal.toFixed(2)));

	totalTemp = 0;
	totalOriginalBackup = 0;
}

function actualizarTablaDetallesProductosPrecuenta() {
	$('.filas').each(function (index, fila) {
		let precioCompra = $(fila).find('input[name="precio_compra[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		let filaPrecuenta = $('#detallesProductosPrecuenta .filas').eq(index);
		filaPrecuenta.find('input[name="precio_compra[]"]').val(precioCompra);
		filaPrecuenta.find('input[name="descuento[]"]').val(descuento);
		filaPrecuenta.find('input[name="cantidad[]"]').val(cantidad);
	});
}

function actualizarTablaDetallesProductosCompra() {
	$('#detallesProductosPrecuenta .filas').each(function (index, fila) {
		let id1 = $(fila).find('input[name="idarticulo[]"]').val();
		let id2 = $(fila).find('input[name="idservicio[]"]').val();
		let precioCompra = $(fila).find('input[name="precio_compra[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		let filaCompra = $('#detalles .filas').eq(index);
		filaCompra.find('input[name="idarticulo[]"]').val(id1);
		filaCompra.find('input[name="idservicio[]"]').val(id2);
		filaCompra.find('input[name="precio_compra[]"]').val(precioCompra);
		filaCompra.find('input[name="descuento[]"]').val(descuento);
		filaCompra.find('input[name="cantidad[]"]').val(cantidad);
	});
}

function verificarCantidadArticulos(param) {
	if ($('.filas').length === 0 && param != 1) {
		bootbox.alert("Debe agregar por lo menos un producto o servicio.");
		$('#myModal7').modal('hide');
	}
}

function actualizarVuelto() {
	var totalAPagar = parseFloat($('.totalFinal1').text().replace(moneda === 'dolares' ? 'TOTAL A PAGAR: ' : 'TOTAL A PAGAR: S/. ', '').replace(moneda === 'dolares' ? ' $' : '', ''));
	var sumaMontosMetodoPago = 0;

	$('#montoMetodoPago input[type="number"]').each(function () {
		sumaMontosMetodoPago += parseFloat($(this).val() || 0);
	});

	var vuelto = sumaMontosMetodoPago - totalAPagar;
	$('#vuelto').val(vuelto.toFixed(2));
}

let totalOriginal = 0;
let totalTemp = 0;
let totalOriginalBackup = 0;  // Variable para guardar el valor original

function actualizarIGV(igv) {
	let textoTotal = $(".totalFinal1").text();
	let numeroTotal = textoTotal.match(moneda === 'dolares' ? /(\d+\.\d+) \$/ : /S\/\. (\d+\.\d+)/);

	if (numeroTotal && numeroTotal.length > 1) {
		totalOriginal = parseFloat(numeroTotal[1]);
	}

	// Inicializa totalOriginalBackup la primera vez que se llama a la función
	if (totalOriginalBackup === 0) {
		totalOriginalBackup = totalOriginal;
	}

	let totalCompra = 0;

	if (igv.value == 0.18) {
		totalCompra = totalOriginal + (totalOriginal * 0.18);
		totalTemp = totalCompra;
	} else {
		totalCompra = totalOriginalBackup;  // Restablece al valor original
		totalTemp = totalOriginalBackup;  // Restablece totalTemp al valor original
	}

	$(".totalFinal1").html('TOTAL A PAGAR: ' + (moneda === 'dolares' ? Number(totalCompra).toFixed(2) + ' $' : 'S/. ' + Number(totalCompra).toFixed(2)));
	$(".totalFinal2").html('OP. GRAVADAS: ' + (moneda === 'dolares' ? Number(totalCompra).toFixed(2) + ' $' : 'S/. ' + Number(totalCompra).toFixed(2)));

	actualizarVuelto();
}

// GUARDAR LA PRECUENTA Y COMPRA

function guardaryeditar7(e) {
	e.preventDefault();

	// VALIDACIONES

	var valorCero = $("#montoMetodoPago input[type='number']").filter(function () {
		return $(this).val() === "0.00";
	}).length > 0;

	if (valorCero) {
		bootbox.alert("El valor de los métodos de pago no puede ser igual a 0.00.");
		return;
	}

	var vuelto = parseFloat($("#vuelto").val());

	if (vuelto < 0) {
		bootbox.alert("El vuelto debe ser mayor o igual a 0.");
		return;
	}

	let textoTotal = $(".totalFinal1").text();
	let totalCompra = parseFloat(textoTotal.match(/\d+\.\d+/)[0]);

	if (totalCompra <= 0) {
		bootbox.alert("El total a pagar no puede ser negativo o igual a cero.");
		return;
	}

	// ACTUALIZAR CAMPOS DE LA COMPRA

	// actualizo los inputs de los montos de los métodos de pago
	$("#montoMetodoPago div").each(function () {
		var dataId = $(this).attr("data-id");
		var monto = $(this).find("input[type='number']").val();
		$("#inputsMontoMetodoPago input[data-id='" + dataId + "']").val(monto);
	});

	// actualizo los campos de los productos de la compra por lo de la precuenta (si son modificados desde la precuenta)
	actualizarTablaDetallesProductosCompra();

	// actualizo el total final de la compra, comentarios e impuesto
	let idlocalSession = $("#idlocal_session").length ? $("#idlocal_session").val() : '';
	let comentarioInterno = $("#comentario_interno").val();
	let comentarioExterno = $("#comentario_externo").val();
	let impuesto = $("#igv").val();
	let totalCompraFinal = $(".totalFinal1").text().match(/\d+\.\d+/)[0];
	let vueltoFinal = $("#vuelto").val();

	console.log(impuesto);

	$("#idlocal_session_final").val(idlocalSession);
	$("#comentario_interno_final").val(comentarioInterno);
	$("#comentario_externo_final").val(comentarioExterno);
	$("#igvFinal").val(impuesto);
	$("#total_compra_final").val(totalCompraFinal);
	$("#vuelto_final").val(vueltoFinal);

	// ENVIAR DATOS AL SERVIDOR

	$("#formulario").submit();
}

function limpiarModalPrecuenta() {
	$("#proveedorFinal").html("");
	$(".totalFinal1").html('TOTAL A PAGAR: ' + (moneda === 'dolares' ? '0.00 $' : 'S/. 0.00'));
	$(".totalFinal2").html('OP. GRAVADAS: ' + (moneda === 'dolares' ? '0.00 $' : 'S/. 0.00'));
	$(".descuentoFinal").html('DESCUENTOS TOTALES: ' + (moneda === 'dolares' ? '0.00 $' : 'S/. 0.00'));
	$("#detallesProductosPrecuenta tbody").empty();
	$("#montoMetodoPago").empty();

	cont = 0;
	$("#totalItems").html(cont);

	$("#igv").val("0.00");
	$("#vuelto").val("0.00");
	$("#idlocal_session").val("");
	$("#idlocal_session").selectpicker('refresh');
	$("#comentario_interno").val("");
	$("#comentario_externo").val("");
}

function mostrarform(flag) {
	if (flag) {
		$(".listadoregistros").hide();
		$(".caja").hide();
		$("#formularioregistros").show();
	}
	else {
		$(".listadoregistros").show();
		$(".caja").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

function cancelarform() {
	limpiar();
	mostrarform(false);
}

function cancelarform2() {
	limpiarModalArticulos();
	$("#myModal12").modal("hide");
}

function listar() {
	$("#fecha_inicio").val("");
	$("#fecha_fin").val("");
	$("#estadoBuscar").val("");

	var fecha_inicio = $("#fecha_inicio").val();
	var fecha_fin = $("#fecha_fin").val();
	var estado = $("#estadoBuscar").val();

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [10, 25, 50, 100],
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
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/compra.php?op=listar',
				data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, estado: estado },
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
			"iDisplayLength": 10,//Paginación
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			},
			"drawCallback": function (settings) {
				mostrarOcultarPrecioCompraCampo();
			},
			"initComplete": function () {
				agregarBuscadorColumna(this.api(), 5, "Buscar por N° ticket.");
			},
		}).DataTable();
}

function buscar() {
	var fecha_inicio = $("#fecha_inicio").val();
	var fecha_fin = $("#fecha_fin").val();
	var estado = $("#estadoBuscar").val();

	if ((fecha_inicio != "" && fecha_fin == "") || (fecha_inicio == "" && fecha_fin != "") || (fecha_inicio != "" && fecha_fin == "" && estado != "") || (fecha_inicio == "" && fecha_fin != "" && estado != "")) {
		bootbox.alert("Los campos de fecha inicial y fecha final son obligatorios.");
		return;
	} else if (fecha_inicio > fecha_fin) {
		bootbox.alert("La fecha inicial no puede ser mayor que la fecha final.");
		return;
	}

	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [10, 25, 50, 100],
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
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 9;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/compra.php?op=listar',
				data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, estado: estado },
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
			"iDisplayLength": 10,
			"order": [],
			"createdRow": function (row, data, dataIndex) {
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			},
			"initComplete": function () {
				agregarBuscadorColumna(this.api(), 5, "Buscar por N° ticket.");
			},
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();

	var formData = new FormData($("#formulario")[0]);
	formData.append('num_comprobante', lastNumComp);

	var detalles = [];

	$('#detalles .filas').each(function () {
		var tipo = $(this).find('input[name="idarticulo[]"]').length ? "_producto" : "_servicio";
		var id = $(this).find('input[name="idarticulo[]"]').val() || $(this).find('input[name="idservicio[]"]').val();
		detalles.push(id + tipo);
	});

	console.log(detalles);

	formData.append('detalles', JSON.stringify(detalles));

	$.ajax({
		url: "../ajax/compra.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			let obj;

			try {
				obj = JSON.parse(datos);

				if (Array.isArray(obj)) {
					console.log(obj);
					limpiar();
					mostrarform(false);
					$("#myModal7").modal("hide");
					modalPrecuentaFinal(obj[1]);
					tabla.ajax.reload();
				} else {
					console.log("Datos no son un array.");
				}

			} catch (e) {
				// Si la conversión a JSON falla, datos es probablemente una cadena.
				console.log(datos);
				console.log(typeof (datos));
				if (datos == "Uno de los productos no forman parte del local seleccionado.") {
					console.log("entro al if =)");

					var local = $("#idlocal_session option:selected").text();
					var localLimpiado = local.replace(/ - \d{3,}.*/, '');

					bootbox.alert(datos + " Debe asegurarse de seleccionar solo los productos que sean del local: <strong>" + localLimpiado + "</strong>.");
				} else {
					bootbox.alert(datos);
				}

				return;
			}
		},
	});
}

function modalPrecuentaFinal(idcompra) {
	$('#myModal8').modal('show');
	limpiarModalPrecuentaFinal();

	var nombresBotones = ['LISTADO DE PRECUENTAS', 'NUEVA PRECUENTA', 'REPORTE DE PRECUENTAS', 'GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto, index) {
		$("button:contains('" + texto + "')").attr("onclick", "opcionesPrecuentaFinal(" + (index + 1) + ", " + idcompra + ");");
	});
}

function opcionesPrecuentaFinal(correlativo, idcompra) {
	switch (correlativo) {
		case 1:
			$("#myModal8").modal('hide');
			break;
		case 2:
			$("#myModal8").modal('hide');
			$("#btnagregar").click();
			break;
		case 3:
			window.open("./reporteCompra.php", '_blank');
			break;
		case 4:
			window.open("../reportes/exTicketCompra.php?id=" + idcompra, '_blank');
			break;
		case 5:
			window.open("../reportes/exA4Compra.php?id=" + idcompra, '_blank');
			break;
		default:
	}
	console.log("correlativo =) =>", correlativo);
	console.log("idcompra =) =>", idcompra);
}

function limpiarModalPrecuentaFinal() {
	var nombresBotones = ['LISTADO DE PRECUENTAS', 'NUEVA PRECUENTA', 'REPORTE DE PRECUENTAS', 'GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").removeAttr("onclick");
	});
}

// FUNCIONES Y BOTONES DE LAS COMPRAS

function modalDetalles(idcompra, usuario, num_comprobante, proveedor, proveedor_tipo_documento, proveedor_num_documento, proveedor_direccion, impuesto, total_compra, vuelto, comentario_interno, moneda) {
	$.post("../ajax/compra.php?op=listarDetallesProductoCompra", { idcompra: idcompra }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		// Actualizar datos del proveedor
		let nombreCompleto = proveedor;

		if (proveedor_tipo_documento && proveedor_num_documento) {
			nombreCompleto += ' - ' + proveedor_tipo_documento + ': ' + proveedor_num_documento;
		}

		$('#nombre_proveedor').text(nombreCompleto);
		$('#direccion_proveedor').text((proveedor_direccion != "") ? proveedor_direccion : "SIN REGISTRAR");
		$('#tipo_moneda').text(moneda == "soles" ? "SOLES" : "DÓLARES");
		$('#boleta').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('#detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;
		let cantidadTotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;
			let precio = moneda == "soles" ? "S/. " + item.precio_compra : item.precio_compra + " $";
			let descuento = moneda == "soles" ? "S/. " + item.descuento : item.descuento + " $";
			let subtotalFila = ((item.cantidad * item.precio_compra) - item.descuento).toFixed(2);
			let subtotalFinal = moneda == "soles" ? "S/. " + subtotalFila : subtotalFila + " $";

			let row = `
                <tr>
                    <td width: 44%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(descripcion)}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.cantidad}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${precio}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${descuento}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${subtotalFinal}</td>
                </tr>`;

			tbody.append(row);

			// Calcular subtotal
			subtotal += item.cantidad * item.precio_compra;
			// Calcular cantidad
			cantidadTotal += Number(item.cantidad);
		});

		let igv = subtotal * (impuesto);

		let subtotal_detalle = moneda == "soles" ? "S/. " + subtotal.toFixed(2) : subtotal.toFixed(2) + " $";
		let igv_detalle = moneda == "soles" ? "S/. " + igv.toFixed(2) : igv.toFixed(2) + " $";
		let total_detalle = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

		$('#subtotal_detalle').text(subtotal_detalle);
		$('#igv_detalle').text(igv_detalle);
		$('#total_detalle').text(total_detalle);
		$('#total_cantidad').text(cantidadTotal.toFixed(2));

		// Actualizar detalles de la tabla pagos
		let tbodyPagos = $('#detallesPagosFinal tbody');
		tbodyPagos.empty();

		let subtotalPagos = 0;

		data.pagos.forEach(item => {
			let monto = moneda == "soles" ? "S/. " + item.monto : item.monto + " $";

			let row = `
                <tr>
                    <td width: 80%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(item.metodo_pago)}</td>
                    <td width: 20%; min-width: 40px; white-space: nowrap;">${monto}</td>
                </tr>`;

			tbodyPagos.append(row);

			// Calcular subtotalPagos
			subtotalPagos += parseFloat(item.monto);
		});

		let subtotal_pagos = moneda == "soles" ? "S/. " + subtotalPagos.toFixed(2) : subtotalPagos.toFixed(2) + " $";
		let vueltos_pagos = moneda == "soles" ? "S/. " + vuelto : vuelto + " $";
		let total_pagos = moneda == "soles" ? "S/. " + total_compra : total_compra + " $";

		$('#subtotal_pagos').text(subtotal_pagos);
		$('#vueltos_pagos').text(vueltos_pagos);
		$('#total_pagos').text(total_pagos);

		let comentario_val = comentario_interno == "" ? "Sin registrar." : comentario_interno;

		$('#comentario_interno_detalle').text(comentario_val);
		$('#atendido_compra').text(capitalizarTodasLasPalabras(usuario));
	});
}

function modalImpresion(idcompra, num_comprobante) {
	$("#num_comprobante_final2").text(num_comprobante);

	limpiarModalImpresion();
	limpiarModalPrecuentaFinal();

	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto, index) {
		var ruta = (index === 0) ? "exTicketCompra" : "exA4Compra";
		$("a:has(button:contains('" + texto + "'))").attr("href", "../reportes/" + ruta + ".php?id=" + idcompra);
	});
}

function limpiarModalImpresion() {
	$("#num_comprobante_final3").text("");

	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto) {
		$("a:has(button:contains('" + texto + "'))").removeAttr("href");
	});
}

function modalEstadoCompra(idcompra, num_comprobante) {
	limpiarModalEstadoCompra();

	$("#num_comprobante_final3").text(num_comprobante);

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").attr("onclick", "cambiarEstadoCompra('" + texto + "', " + idcompra + ");");
	});
}

function limpiarModalEstadoCompra() {
	$("#num_comprobante_final3").text("");

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").removeAttr("onclick");
	});
}

function cambiarEstadoCompra(estado, idcompra) {
	const mensajeAdicional = (estado === "ANULADO") ? " recuerde que esta opción hará que el estado de la compra no se pueda modificar de nuevo." : "";

	bootbox.confirm("¿Estás seguro de cambiar el estado de la compra a <strong>" + minusTodasLasPalabras(estado) + "</strong>?" + mensajeAdicional, function (result) {
		if (result) {
			$.post("../ajax/compra.php?op=cambiarEstado", { idcompra: idcompra, estado: capitalizarPrimeraLetra(estado) }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$('#myModal11').modal('hide');
				limpiarModalEstadoCompra();
			});
		}
	})
}

function anular(idcompra) {
	bootbox.confirm("¿Está seguro de anular la compra? recuerde que esta opción hará que el estado de la compra no se pueda modificar de nuevo.", function (result) {
		if (result) {
			$.post("../ajax/compra.php?op=anular", { idcompra: idcompra }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
					const obj = JSON.parse(data);

					let articulo = obj.articulo;
					let servicio = obj.servicio;

					listarSelectsArticulos(articulo, servicio);
					listarArticulos(articulo, servicio);
				});
			});
		}
	})
}

function eliminar(idcompra) {
	bootbox.confirm("¿Estás seguro de eliminar la compra?", function (result) {
		if (result) {
			$.post("../ajax/compra.php?op=eliminar", { idcompra: idcompra }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
					const obj = JSON.parse(data);

					let articulo = obj.articulo;
					let servicio = obj.servicio;

					listarSelectsArticulos(articulo, servicio);
					listarArticulos(articulo, servicio);
				});
			});
		}
	})
}

var cont = 0;
var detalles = 0;

// $("#btnGuardar").hide();

function agregarDetalle(tipoproducto, idarticulo, nombre, local, stock, precio_compra, codigo) {
	var cantidad = 1;
	var descuento = '0.00';

	if (idarticulo != "") {
		var minCantidad = (tipoproducto == "producto") ? "0.1" : "1";

		var fila = '<tr class="filas fila' + cont + ' principal">' +
			'<td><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '">' + codigo + '</td>' +
			'<td>' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td><input type="number" step="any" name="precio_compra[]" oninput="modificarSubototales();" id="precio_compra[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0" required value="' + (precio_compra == '' ? parseFloat(0).toFixed(2) : precio_compra) + '"></td>' +
			'<td><input type="number" step="any" name="descuento[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" min="0" required value="' + descuento + '"></td>' +
			'<td><input type="number" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="' + minCantidad + '" required value="' + cantidad + '"></td>' +
			'<td style="text-align: center;"><button type="button" class="btn btn-danger" style="height: 33.6px;" onclick="eliminarDetalle(1, ' + cont + ');"><i class="fa fa-trash"></i></button></td>' +
			'</tr>';

		var fila2 = '<tr class="filas fila' + cont + ' principal2">' +
			'<td class="nowrap-cell" style="text-align: start !important;"><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '">' + codigo + '</td>' +
			'<td style="text-align: start !important;">' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td style="text-align: start !important;"><strong>' + capitalizarTodasLasPalabras(local) + '</strong></td>' +
			'<td style="text-align: start !important;">' + (tipoproducto == "producto" ? stock : "") + '</td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="precio_compra[]" oninput="modificarSubototales2();" id="precio_compra[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0" required value="' + (precio_compra == '' ? parseFloat(0).toFixed(2) : precio_compra) + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="descuento[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" min="0" required value="' + descuento + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="' + minCantidad + '" required value="' + cantidad + '"></div></td>' +
			'<td style="text-align: center;"><button type="button" class="btn btn-danger" style="height: 33.6px;" onclick="eliminarDetalle(2, ' + cont + '); actualizarVuelto();"><i class="fa fa-trash"></i></button></td>' +
			'</tr>';

		cont++;
		detalles = detalles + 1;

		$('#detalles').append(fila);
		$('#detallesProductosPrecuenta').append(fila2);
		modificarSubototales();
		evitarCaracteresEspecialesCamposNumericos();
		aplicarRestrictATodosLosInputs();
	} else {
		bootbox.alert("Error al ingresar el detalle, revisar los datos del artículo o servicio");
	}

	mostrarOcultarColumnaAlmacen();
}

function modificarSubototales() {
	var principalRows = document.querySelectorAll('.principal');
	var totalCompra = 0;
	descuentoFinal = 0;

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioCompra = row.querySelector('[name="precio_compra[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioCompra) - descuento;
		totalCompra += subtotal;
		descuentoFinal += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Compra:", precioCompra, "Descuento:", descuento);
	});

	console.log("Total Compra: ", totalCompra);
	console.log("Total Descuento: ", descuentoFinal);

	$("#total_compra_valor").html((moneda === 'dolares') ? totalCompra.toFixed(2) + " $" : "S/. " + totalCompra.toFixed(2));
	evaluar();
}

function modificarSubototales2() {
	var principalRows = document.querySelectorAll('.principal2');
	var totalCompra = 0;
	var descuentoFinal2 = 0;
	var igvActual = $("#igv").val();

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioCompra = row.querySelector('[name="precio_compra[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioCompra) - descuento;
		totalCompra += subtotal;
		descuentoFinal2 += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Compra:", precioCompra, "Descuento:", descuento);
	});

	if (igvActual == 2) {
		totalCompra = totalCompra + (totalCompra * 0.18);
	} else {
		totalCompra = totalCompra;
	}

	console.log("IGV: ", igvActual);
	console.log("Total Compra: ", totalCompra);
	console.log("Total Descuento: ", descuentoFinal2);


	totalOriginal = totalCompra;

	$(".totalFinal1").html('TOTAL A PAGAR: ' + (moneda === 'dolares' ? totalCompra.toFixed(2) + ' $' : 'S/. ' + totalCompra.toFixed(2)));
	$(".totalFinal2").html('OP. GRAVADAS: ' + (moneda === 'dolares' ? totalCompra.toFixed(2) + ' $' : 'S/. ' + totalCompra.toFixed(2)));
	$(".descuentoFinal").html('DESCUENTOS TOTALES: ' + (moneda === 'dolares' ? descuentoFinal2.toFixed(2) + ' $' : 'S/. ' + descuentoFinal2.toFixed(2)));

	actualizarVuelto();

	$("#igv").val("0.00");
	totalTemp = 0;
	totalOriginalBackup = 0;
}

function evaluar() {
	if (detalles > 0) {
		// $("#btnGuardar").show();
	}
	else {
		// $("#btnGuardar").hide();
		cont = 0;
	}
}

function eliminarDetalle(param, indice) {
	$(".fila" + indice).remove();
	detalles = detalles - 1;
	cont = cont - 1;
	modificarSubototales();
	$("#totalItems").html(cont);
	verificarCantidadArticulos(param);
	mostrarDatosModalPrecuenta();
}

document.addEventListener('DOMContentLoaded', function () {
	init();
});