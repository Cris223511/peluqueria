var tabla;
let lastNumComp = 0;
let idCajaFinal = 0;

var idlocal = 0;

inicializeGLightbox();

//correlativos

function actualizarCorrelativo() {
	$.post("../ajax/venta.php?op=getLastNumComprobante", function (e) {
		console.log(e);
		lastNumComp = generarSiguienteCorrelativo(e);
		$("#num_comprobante_final1").text(lastNumComp);
	});
}

function actualizarCorrelativoLocal(idlocal) {
	if (idlocal === "") {
		return;
	}

	$.post("../ajax/venta.php?op=getLastNumComprobanteLocal", { idlocal: idlocal }, function (e) {
		console.log(e);
		const obj = JSON.parse(e);
		console.log(obj);
		if (obj.idcaja == 0) {
			bootbox.alert("El local seleccionado no tiene una caja disponible.");
			$("#idlocal_session").val("");
			$("#idlocal_session").selectpicker('refresh');
			$("#num_comprobante_final1").text(lastNumComp);
		} else if (obj.estado != "aperturado") {
			bootbox.alert("La caja del local seleccionado no se encuentra aperturada.");
			$("#idlocal_session").val("");
			$("#idlocal_session").selectpicker('refresh');
			$("#num_comprobante_final1").text(lastNumComp);
		} else {
			lastNumComp = generarSiguienteCorrelativo(obj.last_num_comprobante);
			idCajaFinal = obj.idcaja;
		}
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

	$('#mVentas').addClass("treeview active");
	$('#lVentas').addClass("active");

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
	let precio_venta = $("#precio_venta").val();
	let precio_compra = $("#precio_compra").val();

	// Verificar si ambos campos están llenos
	if (precio_venta !== '' && precio_compra !== '') {
		let ganancia = precio_venta - precio_compra;
		$("#ganancia").val(ganancia.toFixed(2));
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

function actualizarRUC2() {
	const selectLocal = document.getElementById("idlocal2");
	const localRUCInput = document.getElementById("local_ruc2");
	const selectedOption = selectLocal.options[selectLocal.selectedIndex];

	if (selectedOption.value !== "") {
		const localRUC = selectedOption.getAttribute('data-local-ruc');
		localRUCInput.value = localRUC;
	} else {
		localRUCInput.value = "";
	}
}

function actualizarRUC4() {
	const selectLocal = document.getElementById("idlocal4");
	const localRUCInput = document.getElementById("local_ruc4");
	const selectedOption = selectLocal.options[selectLocal.selectedIndex];

	if (selectedOption.value !== "") {
		const localRUC = selectedOption.getAttribute('data-local-ruc');
		localRUCInput.value = localRUC;
	} else {
		localRUCInput.value = "";
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
	$("#stock").val("");
	$("#stock_minimo").val("");
	$("#imagenmuestra").attr("src", "");
	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#imagen2").val("");
	$("#precio_compra").val("");
	$("#precio_venta").val("");
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
	limpiarModalEmpleados();
	limpiarModalMetodoPago();
	limpiarModalClientes();
	limpiarModalClientes2();
	limpiarModalClientes4();
	limpiarModalPrecuenta();
	limpiarModalArticulos();

	listarDatos();

	$("#comisionar").val(1);
	$("#comisionar").selectpicker("refresh");

	$("#detalles tbody").empty();
	$("#inputsMontoMetodoPago").empty();
	$("#inputsMetodoPago").empty();

	$("#total_venta_valor").html("S/. 0.00");
	$("#tipo_comprobante").val("NOTA DE VENTA");
	$("#tipo_comprobante").selectpicker('refresh');

	$("#comentario_interno_final").val("");
	$("#comentario_externo_final").val("");
	$("#idlocal_session_final").val("");
	$("#igvFinal").val("0.00");
	$("#total_venta_final").val("");
	$("#vuelto_final").val("");
}

function limpiarTodo() {
	bootbox.confirm("¿Estás seguro de limpiar los datos de la venta?, perderá todos los datos registrados.", function (result) {
		if (result) {
			limpiar();
		}
	})
}

function frmDetalles(bool) {
	if (bool == true) { $("#frmDetalles").show(); $("#btnDetalles1").hide(); $("#btnDetalles2").show(); }
	if (bool == false) { $("#frmDetalles").hide(); $("#btnDetalles1").show(); $("#btnDetalles2").hide(); }
	// $('html, body').animate({ scrollTop: $(document).height() }, 10);
}

//Función para guardar o editar

function guardaryeditar8(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento

	var codigoBarra = $("#codigo_barra").val();

	var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);

	if (!formatoValido && codigoBarra != "") {
		bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
		$("#btnGuardarProducto").prop("disabled", false);
		return;
	}

	// var stock = parseFloat($("#stock").val());
	// var stock_minimo = parseFloat($("#stock_minimo").val());

	// if (stock_minimo > stock) {
	// 	bootbox.alert("El stock mínimo no puede ser mayor que el stock normal.");
	// 	return;
	// }

	var precio_compra = parseFloat($("#precio_compra").val());
	var precio_venta = parseFloat($("#precio_venta").val());

	if (precio_compra > precio_venta) {
		bootbox.alert("El precio de compra no puede ser mayor que el precio de venta.");
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

	$("#ganancia").prop("disabled", true);

	let detalles = frmDetallesVisible() ? obtenerDetalles() : { talla: '', color: '', idmedida: '0', peso: '0.00' };

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
			datos = limpiarCadena(datos);
			if (datos == "El código de barra del producto que ha ingresado ya existe." || datos == "El código del producto que ha ingresado ya existe en el local seleccionado.") {
				bootbox.alert(datos);
				$("#btnGuardarProducto").prop("disabled", false);
				return;
			}
			limpiarModalArticulos();
			$("#myModal12").modal("hide");
			$("#btnGuardar").prop("disabled", false);
			bootbox.alert(datos);

			$.post("../ajax/venta.php?op=listarTodosLocalActivosPorUsuario", function (data) {
				const obj = JSON.parse(data);

				let articulo = obj.articulo;
				let servicio = obj.servicio;

				listarSelectsArticulos(articulo, servicio);
				listarArticulos(articulo, servicio);
			});
		}
	});
}

function obtenerDetalles() {
	let detalles = {
		talla: $("#talla").val(),
		color: $("#color").val(),
		idmedida: $("#idmedida").val(),
		peso: $("#peso").val()
	};

	if (!detalles.talla) detalles.talla = '';
	if (!detalles.color) detalles.color = '';
	if (!detalles.idmedida) detalles.idmedida = '0';
	if (!detalles.peso) detalles.peso = '0.00';

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
	var codigo = $("#codigo_barra").val().replace(/\s/g, '').replace(/\D/g, '');
	var formattedCode = '';

	for (var i = 0; i < codigo.length; i++) {
		if (i === 1 || i === 3 || i === 7 || i === 8 || i === 12 || i === 13) {
			formattedCode += ' ';
		}

		formattedCode += codigo[i];
	}

	var maxLength = parseInt($("#codigo_barra").attr("maxlength"));
	if (formattedCode.length > maxLength) {
		formattedCode = formattedCode.substring(0, maxLength);
	}

	$("#codigo_barra").val(formattedCode);
	generarbarcode(0);
}

function borrar() {
	$("#codigo_barra").val("");
	$("#codigo_barra").focus();
	$("#print").hide();
}

//función para generar el número aleatorio del código de barra
function generar() {
	var codigo = "7 75 ";
	codigo += generarNumero(10000, 999) + " ";
	codigo += Math.floor(Math.random() * 10) + " ";
	codigo += generarNumero(100, 9) + " ";
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

	if (param == 1) {
		var codigo = $("#codigo_barra").val().replace(/\s/g, '');
		console.log(codigo.length);

		if (!/^\d+$/.test(codigo)) {
			bootbox.alert("El código de barra debe contener solo números.");
			return;
		} else if (codigo.length !== 13) {
			bootbox.alert("El código de barra debe tener 13 dígitos.");
			return;
		} else {
			codigo = codigo.slice(0, 1) + " " + codigo.slice(1, 3) + " " + codigo.slice(3, 7) + " " + codigo.slice(7, 8) + " " + codigo.slice(8, 12) + " " + codigo.slice(12, 13);
		}
	} else {
		var codigo = $("#codigo_barra").val()
	}

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

function validarCaja() {
	$.post("../ajax/venta.php?op=validarCaja", function (e) {
		e = limpiarCadena(e);
		console.log(e);
		const obj = JSON.parse(e);
		console.log(obj);

		if (e == "null") {
			bootbox.alert("Usted debe registrar una caja para realizar la venta.");
		} else if (obj.estado != "aperturado") {
			bootbox.alert("Usted necesita aperturar su caja para realizar la venta.");
		} else {
			mostrarform(true);
			actualizarCorrelativo();
			idCajaFinal = obj.idcaja;

			// setTimeout(() => {
			// 	document.querySelector(".sidebar-toggle").click();
			// }, 500);
		}
	});
}

function listarDatos() {
	$.post("../ajax/venta.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);
		console.log(obj);

		let articulo = obj.articulo || [];
		let servicio = obj.servicio || [];
		let metodo_pago = obj.metodo_pago || [];
		let clientes = obj.clientes || [];
		let categoria = obj.categoria || [];
		let personales = obj.personales || [];
		let locales = obj.locales || [];

		$("#categoria").empty();
		$("#pagos").empty();

		$("#productos1").empty();
		$("#productos2").empty();
		$("#idcliente").empty();
		$("#idpersonal").empty();
		$("#idlocal").empty();
		$("#idlocal2").empty();
		$("#idlocal3").empty();
		$("#idlocal4").empty();

		listarArticulos(articulo, servicio);
		listarCategoria(categoria);
		listarMetodoPago(metodo_pago);
		listarSelects(articulo, servicio, clientes, personales, locales);
	});
}

function listarTodosLosArticulos() {
	$.post("../ajax/venta.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);

		let articulo = obj.articulo;
		let servicio = obj.servicio;

		listarArticulos(articulo, servicio);
	});
}

function listarArticulosPorCategoria(idcategoria) {
	$.post("../ajax/venta.php?op=listarArticulosPorCategoria", { idcategoria: idcategoria }, function (data) {
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

			articulo.stock = +articulo.stock; // Convierte a número
			articulo.stock_minimo = +articulo.stock_minimo; // Convierte a número

			var stockHtml = (articulo.stock > 0 && articulo.stock < articulo.stock_minimo) ? '<span style="color: #Ea9900; font-weight: bold">' + articulo.stock + '</span>' : ((articulo.stock != '0') ? '<span style="color: #00a65a; font-weight: bold">' + articulo.stock + '</span>' : '<span style="color: red; font-weight: bold">' + articulo.stock + '</span>');
			var labelHtml = (articulo.stock > 0 && articulo.stock < articulo.stock_minimo) ? '<span class="label bg-orange" style="width: min-content;">agotandose</span>' : ((articulo.stock != '0') ? '<span class="label bg-green" style="width: min-content;">Disponible</span>' : '<span class="label bg-red" style="width: min-content;">agotado</span>');

			let html = `
				<div class="draggable" style="padding: 10px; width: 180px;">
					<div class="caja-productos">
						<a href="../files/articulos/${articulo.imagen}" class="galleria-lightbox">
							<img src="../files/articulos/${articulo.imagen}" class="img-fluid">
						</a>
						<h1>${articulo.nombre}</h1>
						<h4>${articulo.marca}</h4>
						<div class="subcaja-gris">
							<span>STOCK: <strong>${stockHtml}</strong></span>
							${labelHtml}
							<span><strong>S/ ${articulo.precio_venta}</strong></span>
						</div>
						<a style="width: 100%;" onclick="verificarEmpleado('producto','${articulo.id}','${articulo.nombre}','${articulo.local}','${articulo.stock}','${articulo.precio_compra}','${articulo.precio_venta}','${articulo.comision}','${articulo.codigo}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
					</div>
				</div>
			`;

			productosContainer.append(html);
		});

		servicios.forEach((servicio) => {
			let html = `
					<div class="draggable" style="padding: 10px; width: 180px;">
						<div class="caja-productos">
							<a href="../files/articulos/${servicio.imagen}" class="galleria-lightbox">
								<img src="../files/articulos/${servicio.imagen}" class="img-fluid">
							</a>
							<h1>${servicio.nombre}</h1>
							<h4>${servicio.marca}</h4>
							<div class="subcaja-gris">
								<span><strong>ㅤ</strong></span>
								<span class="label bg-green" style="width: min-content;">Disponible</span>
								<span><strong>S/ ${servicio.precio_venta}</strong></span>
							</div>
							<a style="width: 100%;" onclick="verificarEmpleado('servicio','${servicio.id}','${servicio.nombre}','Sin registrar','${servicio.stock}','${servicio.precio_compra}','${servicio.precio_venta}','${servicio.comision}','${servicio.codigo}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
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
                <div class="caja-categoria" data-id="${categoria.id}">
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

function listarSelects(articulos, servicios, clientes, personales, locales) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-comision="${articulo.comision}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.codigo.replace(/\s/g, '')} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-comision="${servicio.comision}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');

	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-comision="${articulo.comision}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-comision="${servicio.comision}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos2.append(optionHtml);
	});

	let selectClientes = $("#idcliente");
	selectClientes.empty();
	selectClientes.append('<option value="">Buscar cliente.</option>');

	clientes.forEach((cliente) => {
		let optionHtml = `<option value="${cliente.id}">${cliente.nombre} - ${cliente.tipo_documento}: ${cliente.num_documento} ${((cliente.local != null) ? " - " + cliente.local : "")}</option>`;
		selectClientes.append(optionHtml);
	});

	let selectEmpleados = $("#idpersonal");
	selectEmpleados.empty();
	selectEmpleados.append('<option value="">SIN EMPLEADOS A COMISIONAR.</option>');

	personales.forEach((personal) => {
		let optionHtml = `<option value="${personal.id}">${capitalizarTodasLasPalabras(personal.nombre)} - ${capitalizarTodasLasPalabras(personal.local)}</option>`;
		selectEmpleados.append(optionHtml);
	});

	let selectLocales1 = $("#idlocal");
	selectLocales1.empty();
	selectLocales1.append('<option value="">- Seleccione -</option>');

	locales.forEach((local) => {
		let optionHtml = `<option value="${local.id}" data-local-ruc="${local.local_ruc}">${local.nombre}</option>`;
		selectLocales1.append(optionHtml);
	});

	let selectLocales2 = $("#idlocal2");
	selectLocales2.empty();
	selectLocales2.append('<option value="">- Seleccione -</option>');

	locales.forEach((local) => {
		let optionHtml = `<option value="${local.id}" data-local-ruc="${local.local_ruc}">${local.nombre}</option>`;
		selectLocales2.append(optionHtml);
	});

	let selectLocales3 = $("#idlocal3");
	selectLocales3.empty();
	selectLocales3.append('<option value="">- Seleccione -</option>');

	locales.forEach((local) => {
		let optionHtml = `<option value="${local.id}" data-local-ruc="${local.local_ruc}">${local.nombre}</option>`;
		selectLocales3.append(optionHtml);
	});

	let selectLocales4 = $("#idlocal4");
	selectLocales4.empty();
	selectLocales4.append('<option value="">- Seleccione -</option>');

	locales.forEach((local) => {
		let optionHtml = `<option value="${local.id}" data-local-ruc="${local.local_ruc}">${local.nombre}</option>`;
		selectLocales4.append(optionHtml);
	});

	if ($("#idlocal_session").length) {
		let selectLocales5 = $("#idlocal_session");
		selectLocales5.empty();
		selectLocales5.append('<option value="">- Seleccione -</option>');

		locales.forEach((local) => {
			let optionHtml = `<option value="${local.id}">${local.nombre} - ${local.local_ruc}</option>`;
			selectLocales5.append(optionHtml);
		});

		selectLocales5.selectpicker('refresh');
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
	selectClientes.selectpicker('refresh');
	selectEmpleados.selectpicker('refresh');
	selectLocales1.selectpicker('refresh');
	selectLocales2.selectpicker('refresh');
	selectLocales3.selectpicker('refresh');
	selectLocales4.selectpicker('refresh');

	actualizarRUC();
	actualizarRUC2();
	actualizarRUC4();

	$('#idcliente').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'checkEnter(event)');
	$('#idcliente').closest('.form-group').find('input[type="text"]').attr('oninput', 'checkDNI(this)');
	$('#idcliente').closest('.form-group').find('.dropdown-menu.open').addClass('idclienteInput');

	colocarNegritaStocksSelects();
}

function listarSelectsArticulos(articulos, servicios) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-comision="${articulo.comision}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.codigo.replace(/\s/g, '')} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-comision="${servicio.comision}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');
	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-local="${articulo.local}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-comision="${articulo.comision}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-local="Sin registrar" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-comision="${servicio.comision}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
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
	let inputValue = $('#idcliente').closest('.form-group').find('input[type="text"]');

	if (event.key === "Enter") {
		if ($('.no-results').is(':visible') && /^\d{1,11}$/.test(inputValue.val())) {
			$('#myModal3').modal('show');
			$("#sunat").val(inputValue.val());
			limpiarModalClientes();
			console.log("di enter en idcliente =)");
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
	verificarEmpleado(selectedOption.getAttribute('data-tipo-producto'), selectedOption.value, selectedOption.getAttribute('data-nombre'), selectedOption.getAttribute('data-local'), selectedOption.getAttribute('data-stock'), selectedOption.getAttribute('data-precio-compra'), selectedOption.getAttribute('data-precio-venta'), selectedOption.getAttribute('data-comision'), selectedOption.getAttribute('data-codigo'))
	selectElement.value = "";
	$(selectElement).selectpicker('refresh');
	colocarNegritaStocksSelects();
}

// MODAL EMPLEADOS

let idarticuloGlobal = "";
let nombreGlobal = "";
let localGlobal = "";
let stockGlobal = "";
let precioCompraGlobal = "";
let precioVentaGlobal = "";
let comisionGlobal = "";
let codigoGlobal = "";
let tipoProductoFinal = "";

function verificarEmpleado(tipoarticulo, idarticulo, nombre, local, stock, precio_compra, precio_venta, comision, codigo) {
	var existeProducto = validarTablaProductos(tipoarticulo, idarticulo);

	if (stock == 0) {
		bootbox.alert("El producto seleccionado se encuentra sin stock.");
		return;
	}

	if (!existeProducto) {
		if ($("#comisionar").val() == 2 || $("#comisionar").val() == "2") {
			$('#myModal1').modal('show');
			limpiarModalEmpleados();

			console.log("esto traigo =) =>", tipoarticulo, idarticulo, nombre, local, stock, precio_compra, precio_venta, comision, codigo);

			idarticuloGlobal = idarticulo;
			nombreGlobal = nombre;
			localGlobal = local;
			stockGlobal = stock;
			precioCompraGlobal = precio_compra;
			precioVentaGlobal = precio_venta;
			comisionGlobal = comision;
			codigoGlobal = codigo;
			tipoProductoFinal = tipoarticulo;

			$("#ProductoSeleccionado").html(capitalizarTodasLasPalabras(nombre));
			$("#PrecioSeleccionado").html(`S/. ${precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta}`);
			$("#ComisionSeleccionado").html(`S/. ${comision == '' ? parseFloat(0).toFixed(2) : comision}`);

			evaluarBotonEmpleado();
		} else {
			agregarDetalle(tipoarticulo, idarticulo, '0', nombre, local, stock, precio_compra, precio_venta, comision, codigo);
		}
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

function evaluarBotonEmpleado() {
	let valorEmpleado = $("#idpersonal").val();
	console.log(valorEmpleado);

	if (valorEmpleado == "") {
		$("#btnGuardarArticulo").hide();
		$("#empleadoSeleccionado").html("SIN SELECCIONAR");
	} else {
		$("#btnGuardarArticulo").show();
		let textoSeleccionado = $("#idpersonal option:selected").text();
		$("#empleadoSeleccionado").html(capitalizarTodasLasPalabras(textoSeleccionado));
		$("#btnGuardarArticulo").attr("onclick", `agregarDetalle('${tipoProductoFinal}','${idarticuloGlobal}', '${valorEmpleado}', '${nombreGlobal}', '${localGlobal}', '${stockGlobal}', '${precioCompraGlobal}', '${precioVentaGlobal}', '${comisionGlobal}', '${codigoGlobal}'); limpiarModalEmpleados();`);
	}
}

function limpiarModalEmpleados() {
	$("#idpersonal").val("");
	$("#idpersonal").selectpicker('refresh');

	$("#empleadoSeleccionado").html("SIN SELECCIONAR");
	$("#ProductoSeleccionado").html("");
	$("#PrecioSeleccionado").html("");

	$("#btnGuardarArticulo").removeAttr("onclick");
	$("#btnGuardarArticulo").hide();

	idarticuloGlobal = "";
	nombreGlobal = "";
	precioCompraGlobal = "";
	precioVentaGlobal = "";
	codigoGlobal = "";
	tipoProductoFinal = "";
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
				<input type="number" class="form-control" step="any" style="width: 120px; height: 30px;" value="0.00" lang="en-US" oninput="actualizarVuelto();" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required>
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
	$.post("../ajax/venta.php?op=listarMetodosDePago", function (data) {
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

// CLIENTES NUEVOS (POR SUNAT)

function listarClientes(idcliente) {
	$.post("../ajax/venta.php?op=listarClientes", function (data) {
		console.log(data);
		const obj = JSON.parse(data);
		console.log(obj);

		let clientes = obj.clientes;

		let selectClientes = $("#idcliente");
		selectClientes.empty();
		selectClientes.append('<option value="">Buscar cliente.</option>');

		clientes.forEach((cliente) => {
			let optionHtml = `<option value="${cliente.id}">${cliente.nombre} - ${cliente.tipo_documento}: ${cliente.num_documento} ${((cliente.local != null) ? " - " + cliente.local : "")}</option>`;
			selectClientes.append(optionHtml);
		});

		selectClientes.val(idcliente);
		selectClientes.selectpicker('refresh');

		$('#idcliente').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'checkEnter(event)');
		$('#idcliente').closest('.form-group').find('input[type="text"]').attr('oninput', 'checkDNI(this)');
	});
}

function limpiarModalClientes() {
	$("#idcliente2").val("");
	$("#nombre").val("");
	$("#tipo_documento").val("");
	$("#num_documento").val("");
	$("#direccion").val("");
	$("#telefono").val("");
	$("#email").val("");
	$("#descripcion2").val("");

	habilitarTodoModalCliente();

	$("#idlocal").val($("#idlocal option:first").val());
	$("#idlocal").selectpicker('refresh');

	$("#btnSunat").prop("disabled", false);
	$("#btnGuardarCliente").prop("disabled", true);

	actualizarRUC();
}

function guardaryeditar3(e) {
	e.preventDefault();
	$("#btnGuardarCliente").prop("disabled", true);

	deshabilitarTodoModalCliente();
	var formData = new FormData($("#formulario3")[0]);
	habilitarTodoModalCliente();

	$.ajax({
		url: "../ajax/clientes.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El número de documento que ha ingresado ya existe." || datos == "El cliente no se pudo registrar") {
				bootbox.alert(datos);
				$("#btnGuardarCliente").prop("disabled", false);
				return;
			}
			bootbox.alert("Cliente registrado correctamente.");
			$('#myModal3').modal('hide');
			listarClientes(datos);
			limpiarModalClientes();
			$("#sunat").val("");
		}
	});
}

function buscarSunat(e) {
	e.preventDefault();
	var formData = new FormData($("#formSunat")[0]);
	limpiarModalClientes();
	$("#btnSunat").prop("disabled", true);

	$.ajax({
		url: "../ajax/venta.php?op=consultaSunat",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			console.log(datos);
			if (datos == "DNI no valido" || datos == "RUC no valido") {
				limpiarModalClientes();
				bootbox.confirm(datos + ", ¿deseas crear un cliente manualmente?", function (result) {
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
				limpiarModalClientes();
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

				$("#idlocal").prop("disabled", false);
				$("#descripcion2").prop("disabled", false);

				$("#idlocal").val($("#idlocal option:first").val());
				$("#idlocal").selectpicker('refresh');

				$("#sunat").val("");

				$("#btnSunat").prop("disabled", false);
				$("#btnGuardarCliente").prop("disabled", false);
			}
		}
	});
}

function habilitarTodoModalCliente() {
	$("#tipo_documento").prop("disabled", true);
	$("#num_documento").prop("disabled", true);
	$("#nombre").prop("disabled", true);
	$("#direccion").prop("disabled", true);
	$("#telefono").prop("disabled", true);
	$("#email").prop("disabled", true);
	$("#idlocal").prop("disabled", true);
	$("#local_ruc").prop("disabled", true);
	$("#descripcion2").prop("disabled", true);
}

function deshabilitarTodoModalCliente() {
	$("#tipo_documento").prop("disabled", false);
	$("#num_documento").prop("disabled", false);
	$("#nombre").prop("disabled", false);
	$("#direccion").prop("disabled", false);
	$("#telefono").prop("disabled", false);
	$("#email").prop("disabled", false);
	$("#idlocal").prop("disabled", false);
	$("#local_ruc").prop("disabled", false);
	$("#descripcion2").prop("disabled", false);
}

// CLIENTES NUEVOS (CARNET POR EXTRANJERÍA)

function limpiarModalClientes2() {
	$("#idcliente3").val("");
	$("#nombre2").val("");
	$("#tipo_documento2").val("");
	$("#num_documento2").val("");
	$("#direccion2").val("");
	$("#telefono2").val("");
	$("#email2").val("");
	$("#descripcion3").val("");

	$("#idlocal2").val($("#idlocal2 option:first").val());
	$("#idlocal2").selectpicker('refresh');

	$("#btnGuardarCliente2").prop("disabled", false);

	actualizarRUC2();
}

function guardaryeditar4(e) {
	e.preventDefault();
	$("#btnGuardarCliente2").prop("disabled", true);
	var formData = new FormData($("#formulario4")[0]);

	$.ajax({
		url: "../ajax/clientes.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarCliente2").prop("disabled", false);
				return;
			}
			bootbox.alert("Cliente registrado correctamente.");
			$('#myModal4').modal('hide');
			listarClientes(datos);
			limpiarModalClientes2();
		}
	});
}

// CLIENTES NUEVOS (CLIENTE GENÉRICO)

function seleccionarPublicoGeneral() {
	$("#idcliente").val(0);
	$("#idcliente").selectpicker("refresh");
}

// CLIENTES NUEVOS (POR SI NO ENCUENTRA LA SUNAT)

function limpiarModalClientes4() {
	$("#idcliente4").val("");
	$("#nombre4").val("");
	$("#tipo_documento4").val("");
	$("#num_documento4").val("");
	$("#direccion3").val("");
	$("#telefono3").val("");
	$("#email3").val("");
	$("#descripcion4").val("");

	$("#idlocal4").val($("#idlocal4 option:first").val());
	$("#idlocal4").selectpicker('refresh');

	$("#btnGuardarCliente4").prop("disabled", false);

	actualizarRUC4();
}

function guardaryeditar6(e) {
	e.preventDefault();
	$("#btnGuardarCliente4").prop("disabled", true);
	var formData = new FormData($("#formulario6")[0]);

	$.ajax({
		url: "../ajax/clientes.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarCliente4").prop("disabled", false);
				return;
			}
			bootbox.alert("Cliente registrado correctamente.");
			$('#myModal6').modal('hide');
			listarClientes(datos);
			limpiarModalClientes4();
		}
	});
}

// PRECUENTA

function verificarModalPrecuenta() {
	if ($('#idcliente').val() === "") {
		bootbox.alert("Debe seleccionar un cliente.");
		return;
	}

	if ($('.filas').length === 0) {
		bootbox.alert("Debe agregar por lo menos un producto o servicio.");
		return;
	}

	let detallesValidos = true;
	$('.filas').each(function (index, fila) {
		let precioVenta = $(fila).find('input[name="precio_venta[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		if (!precioVenta || !descuento || !cantidad) {
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

	let totalVenta = parseFloat($("#total_venta_valor").text().replace('S/. ', '').replace(',', ''));
	if (totalVenta <= 0) {
		bootbox.alert("El total de venta no puede ser negativo o igual a cero.");
		return;
	}

	totalOriginal = totalVenta;

	actualizarTablaDetallesProductosPrecuenta();
	mostrarDatosModalPrecuenta();
	actualizarVuelto();

	$("#myModal7").modal("show");
}

let descuentoFinal = 0;

function mostrarDatosModalPrecuenta() {
	let clienteSeleccionado = $("#idcliente option:selected").text();
	$("#clienteFinal").html(capitalizarTodasLasPalabras(clienteSeleccionado));

	$("#totalItems").html(cont);

	let totalFinal = $("#total_venta_valor").text();

	totalOriginal = Number(totalFinal).toFixed(2);

	$(".totalFinal1").html('TOTAL A PAGAR: ' + totalFinal);
	$(".totalFinal2").html('OP. GRAVADAS: ' + totalFinal);

	$("#igv").val("0.00");

	$(".descuentoFinal").html('DESCUENTOS TOTALES: S/. ' + descuentoFinal.toFixed(2));

	totalTemp = 0;
	totalOriginalBackup = 0;
}

function actualizarTablaDetallesProductosPrecuenta() {
	$('.filas').each(function (index, fila) {
		let precioVenta = $(fila).find('input[name="precio_venta[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		let filaPrecuenta = $('#detallesProductosPrecuenta .filas').eq(index);
		filaPrecuenta.find('input[name="precio_venta[]"]').val(precioVenta);
		filaPrecuenta.find('input[name="descuento[]"]').val(descuento);
		filaPrecuenta.find('input[name="cantidad[]"]').val(cantidad);
	});
}

function actualizarTablaDetallesProductosVenta() {
	$('#detallesProductosPrecuenta .filas').each(function (index, fila) {
		let id1 = $(fila).find('input[name="idarticulo[]"]').val();
		let id2 = $(fila).find('input[name="idservicio[]"]').val();
		let precioVenta = $(fila).find('input[name="precio_venta[]"]').val();
		let descuento = $(fila).find('input[name="descuento[]"]').val();
		let cantidad = $(fila).find('input[name="cantidad[]"]').val();

		let filaVenta = $('#detalles .filas').eq(index);
		filaVenta.find('input[name="idarticulo[]"]').val(id1);
		filaVenta.find('input[name="idservicio[]"]').val(id2);
		filaVenta.find('input[name="precio_venta[]"]').val(precioVenta);
		filaVenta.find('input[name="descuento[]"]').val(descuento);
		filaVenta.find('input[name="cantidad[]"]').val(cantidad);
	});
}

function verificarCantidadArticulos(param) {
	if ($('.filas').length === 0 && param != 1) {
		bootbox.alert("Debe agregar por lo menos un producto o servicio.");
		$('#myModal7').modal('hide');
	}
}

function actualizarVuelto() {
	var totalAPagar = parseFloat($('.totalFinal1').text().replace('TOTAL A PAGAR: S/. ', ''));
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
	let numeroTotal = textoTotal.match(/S\/\. (\d+\.\d+)/);

	if (numeroTotal && numeroTotal.length > 1) {
		totalOriginal = parseFloat(numeroTotal[1]);
	}

	// Inicializa totalOriginalBackup la primera vez que se llama a la función
	if (totalOriginalBackup === 0) {
		totalOriginalBackup = totalOriginal;
	}

	let totalVenta = 0;

	if (igv.value == 0.18) {
		totalVenta = totalOriginal + (totalOriginal * 0.18);
		totalTemp = totalVenta;
	} else {
		totalVenta = totalOriginalBackup;  // Restablece al valor original
		totalTemp = totalOriginalBackup;  // Restablece totalTemp al valor original
	}

	$(".totalFinal1").html('TOTAL A PAGAR: S/. ' + Number(totalVenta).toFixed(2));
	$(".totalFinal2").html('OP. GRAVADAS: S/. ' + Number(totalVenta).toFixed(2));

	actualizarVuelto();
}

// GUARDAR LA PRECUENTA Y VENTA

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
	let totalVenta = parseFloat(textoTotal.match(/\d+\.\d+/)[0]);

	if (totalVenta <= 0) {
		bootbox.alert("El total a pagar no puede ser negativo o igual a cero.");
		return;
	}

	// ACTUALIZAR CAMPOS DE LA VENTA

	// actualizo los inputs de los montos de los métodos de pago
	$("#montoMetodoPago div").each(function () {
		var dataId = $(this).attr("data-id");
		var monto = $(this).find("input[type='number']").val();
		$("#inputsMontoMetodoPago input[data-id='" + dataId + "']").val(monto);
	});

	// actualizo los campos de los productos de la venta por lo de la precuenta (si son modificados desde la precuenta)
	actualizarTablaDetallesProductosVenta();

	// actualizo el total final de la venta, comentarios e impuesto
	let idlocalSession = $("#idlocal_session").length ? $("#idlocal_session").val() : '';
	let comentarioInterno = $("#comentario_interno").val();
	let comentarioExterno = $("#comentario_externo").val();
	let impuesto = $("#igv").val();
	let totalVentaFinal = $(".totalFinal1").text().match(/\d+\.\d+/)[0];
	let vueltoFinal = $("#vuelto").val();

	console.log(impuesto);

	$("#idlocal_session_final").val(idlocalSession);
	$("#comentario_interno_final").val(comentarioInterno);
	$("#comentario_externo_final").val(comentarioExterno);
	$("#igvFinal").val(impuesto);
	$("#total_venta_final").val(totalVentaFinal);
	$("#vuelto_final").val(vueltoFinal);

	// ENVIAR DATOS AL SERVIDOR

	$("#formulario").submit();
}

function limpiarModalPrecuenta() {
	$("#clienteFinal").html("");
	$(".totalFinal1").html('TOTAL A PAGAR: S/. 0.00');
	$(".totalFinal2").html('OP. GRAVADAS: S/. 0.00');
	$(".descuentoFinal").html('DESCUENTOS TOTALES: S/. 0.00');
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
				url: '../ajax/venta.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
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
				url: '../ajax/venta.php?op=listar',
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
				// $(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function guardaryeditar(e) {
	e.preventDefault();

	var formData = new FormData($("#formulario")[0]);
	formData.append('num_comprobante', lastNumComp);
	formData.append('idcaja', idCajaFinal);

	var detalles = [];

	$('#detalles .filas').each(function () {
		var tipo = $(this).find('input[name="idarticulo[]"]').length ? "_producto" : "_servicio";
		var id = $(this).find('input[name="idarticulo[]"]').val() || $(this).find('input[name="idservicio[]"]').val();
		detalles.push(id + tipo);
	});

	console.log(detalles);

	formData.append('detalles', JSON.stringify(detalles));

	$.ajax({
		url: "../ajax/venta.php?op=guardaryeditar",
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


function modalPrecuentaFinal(idventa) {
	$('#myModal8').modal('show');
	limpiarModalPrecuentaFinal();

	var nombresBotones = ['LISTADO DE PRECUENTAS', 'NUEVA PRECUENTA', 'REPORTE DE PRECUENTAS', 'GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto, index) {
		$("button:contains('" + texto + "')").attr("onclick", "opcionesPrecuentaFinal(" + (index + 1) + ", " + idventa + ");");
	});
}

function opcionesPrecuentaFinal(correlativo, idventa) {
	switch (correlativo) {
		case 1:
			$("#myModal8").modal('hide');
			break;
		case 2:
			$("#myModal8").modal('hide');
			$("#btnagregar").click();
			break;
		case 3:
			window.open("./reporteVenta.php", '_blank');
			break;
		case 4:
			window.open("../reportes/exTicketVenta.php?id=" + idventa, '_blank');
			break;
		case 5:
			window.open("../reportes/exA4Venta.php?id=" + idventa, '_blank');
			break;
		default:
	}
	console.log("correlativo =) =>", correlativo);
	console.log("idventa =) =>", idventa);
}

function limpiarModalPrecuentaFinal() {
	var nombresBotones = ['LISTADO DE PRECUENTAS', 'NUEVA PRECUENTA', 'REPORTE DE PRECUENTAS', 'GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").removeAttr("onclick");
	});
}

// FUNCIONES Y BOTONES DE LAS VENTAS

function modalDetalles(idventa, usuario, num_comprobante, cliente, cliente_tipo_documento, cliente_num_documento, cliente_direccion, impuesto, total_venta, vuelto, comentario_interno) {
	$.post("../ajax/venta.php?op=listarDetallesProductoVenta", { idventa: idventa }, function (data, status) {
		console.log(data);
		data = JSON.parse(data);
		console.log(data);

		// Actualizar datos del cliente
		let nombreCompleto = cliente;

		if (cliente_tipo_documento && cliente_num_documento) {
			nombreCompleto += ' - ' + cliente_tipo_documento + ': ' + cliente_num_documento;
		}

		$('#nombre_cliente').text(nombreCompleto);
		$('#direccion_cliente').text((cliente_direccion != "") ? cliente_direccion : "Sin registrar");
		$('#nota_de_venta').text("N° " + num_comprobante);

		// Actualizar detalles de la tabla productos
		let tbody = $('#detallesProductosFinal tbody');
		tbody.empty();

		let subtotal = 0;

		data.articulos.forEach(item => {
			let descripcion = item.articulo ? item.articulo : item.servicio;
			let codigo = item.codigo_articulo ? item.codigo_articulo : item.cod_servicio;

			let row = `
                <tr>
                    <td width: 44%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(descripcion)}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.cantidad}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.precio_venta}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${item.descuento}</td>
                    <td width: 14%; min-width: 40px; white-space: nowrap;">${((item.cantidad * item.precio_venta) - item.descuento).toFixed(2)}</td>
                </tr>`;

			tbody.append(row);

			// Calcular subtotal
			subtotal += item.cantidad * item.precio_venta;
		});

		let igv = subtotal * (impuesto);

		$('#subtotal_detalle').text(subtotal.toFixed(2));
		$('#igv_detalle').text(igv.toFixed(2));
		$('#total_detalle').text(total_venta);

		// Actualizar detalles de la tabla pagos
		let tbodyPagos = $('#detallesPagosFinal tbody');
		tbodyPagos.empty();

		let subtotalPagos = 0;

		data.pagos.forEach(item => {
			let row = `
                <tr>
                    <td width: 80%; min-width: 180px; white-space: nowrap;">${capitalizarTodasLasPalabras(item.metodo_pago)}</td>
                    <td width: 20%; min-width: 40px; white-space: nowrap;">${item.monto}</td>
                </tr>`;

			tbodyPagos.append(row);

			// Calcular subtotalPagos
			subtotalPagos += parseFloat(item.monto);
		});

		$('#subtotal_pagos').text(subtotalPagos.toFixed(2));
		$('#vueltos_pagos').text(vuelto);
		$('#total_pagos').text(total_venta);

		let comentario_val = comentario_interno == "" ? "Sin registrar." : comentario_interno;

		$('#comentario_interno_detalle').text(comentario_val);
		$('#atendido_venta').text(capitalizarTodasLasPalabras(usuario));
	});
}

function modalImpresion(idventa, num_comprobante) {
	$("#num_comprobante_final2").text(num_comprobante);

	limpiarModalImpresion();
	limpiarModalPrecuentaFinal();

	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto, index) {
		var ruta = (index === 0) ? "exTicketVenta" : "exA4Venta";
		$("a:has(button:contains('" + texto + "'))").attr("href", "../reportes/" + ruta + ".php?id=" + idventa);
	});
}

function limpiarModalImpresion() {
	$("#num_comprobante_final3").text("");

	var nombresBotones = ['GENERAR TICKET', 'GENERAR PDF-A4'];

	nombresBotones.forEach(function (texto) {
		$("a:has(button:contains('" + texto + "'))").removeAttr("href");
	});
}

function modalEstadoVenta(idventa, num_comprobante) {
	limpiarModalEstadoVenta();

	$("#num_comprobante_final3").text(num_comprobante);

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").attr("onclick", "cambiarEstadoVenta('" + texto + "', " + idventa + ");");
	});
}

function limpiarModalEstadoVenta() {
	$("#num_comprobante_final3").text("");

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").removeAttr("onclick");
	});
}

function cambiarEstadoVenta(estado, idventa) {
	const mensajeAdicional = (estado === "ANULADO") ? " recuerde que esta opción hará que el estado de la venta no se pueda modificar de nuevo." : "";

	bootbox.confirm("¿Estás seguro de cambiar el estado de la venta a <strong>" + minusTodasLasPalabras(estado) + "</strong>?" + mensajeAdicional, function (result) {
		if (result) {
			$.post("../ajax/venta.php?op=cambiarEstado", { idventa: idventa, estado: capitalizarPrimeraLetra(estado) }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$('#myModal11').modal('hide');
				limpiarModalEstadoVenta();
			});
		}
	})
}

function anular(idventa) {
	bootbox.confirm("¿Está seguro de anular la venta? recuerde que esta opción hará que el estado de la venta no se pueda modificar de nuevo.", function (result) {
		if (result) {
			$.post("../ajax/venta.php?op=anular", { idventa: idventa }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$.post("../ajax/venta.php?op=listarTodosLocalActivosPorUsuario", function (data) {
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

function eliminar(idventa) {
	bootbox.confirm("¿Estás seguro de eliminar la venta?", function (result) {
		if (result) {
			$.post("../ajax/venta.php?op=eliminar", { idventa: idventa }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$.post("../ajax/venta.php?op=listarTodosLocalActivosPorUsuario", function (data) {
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

function agregarDetalle(tipoproducto, idarticulo, idpersonal, nombre, local, stock, precio_compra, precio_venta, comision, codigo) {
	var cantidad = 1;
	var descuento = '0.00';

	if (idarticulo != "") {
		var fila = '<tr class="filas fila' + cont + ' principal">' +
			'<td><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '"><input type="hidden" step="any" name="precio_compra[]" value="' + precio_compra + '"><input type="hidden" step="any" name="idpersonal[]" value="' + idpersonal + '"><input type="hidden" name="comision[]" value="' + comision + '">' + codigo + '</td>' +
			'<td>' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td><input type="number" step="any" name="precio_venta[]" oninput="modificarSubototales();" id="precio_venta[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + (precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta) + '"></td>' +
			'<td><input type="number" step="any" name="descuento[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" required value="' + descuento + '"></td>' +
			'<td><input type="number" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + cantidad + '"></td>' +
			'<td style="text-align: center;"><button type="button" class="btn btn-danger" style="height: 33.6px;" onclick="eliminarDetalle(1, ' + cont + ');"><i class="fa fa-trash"></i></button></td>' +
			'</tr>';

		var fila2 = '<tr class="filas fila' + cont + ' principal2">' +
			'<td class="nowrap-cell" style="text-align: start !important;"><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '"><input type="hidden" step="any" name="precio_compra[]" value="' + precio_compra + '"><input type="hidden" name="idpersonal[]" value="' + idpersonal + '"><input type="hidden" name="comision[]" value="' + comision + '">' + codigo + '</td>' +
			'<td style="text-align: start !important;">' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td style="text-align: start !important;"><strong>' + capitalizarTodasLasPalabras(local) + '</strong></td>' +
			'<td style="text-align: start !important;">' + (tipoproducto == "producto" ? stock : "") + '</td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="precio_venta[]" oninput="modificarSubototales2();" id="precio_venta[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + (precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta) + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="descuento[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" required value="' + descuento + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + cantidad + '"></div></td>' +
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
	var totalVenta = 0;
	descuentoFinal = 0;

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioVenta = row.querySelector('[name="precio_venta[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioVenta) - descuento;
		totalVenta += subtotal;
		descuentoFinal += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Venta:", precioVenta, "Descuento:", descuento);
	});

	console.log("Total Venta: ", totalVenta);
	console.log("Total Descuento: ", descuentoFinal);

	$("#total_venta_valor").html("S/. " + totalVenta.toFixed(2));
	evaluar();
}

function modificarSubototales2() {
	var principalRows = document.querySelectorAll('.principal2');
	var totalVenta = 0;
	var descuentoFinal2 = 0;
	var igvActual = $("#igv").val();

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioVenta = row.querySelector('[name="precio_venta[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioVenta) - descuento;
		totalVenta += subtotal;
		descuentoFinal2 += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Venta:", precioVenta, "Descuento:", descuento);
	});

	if (igvActual == 2) {
		totalVenta = totalVenta + (totalVenta * 0.18);
	} else {
		totalVenta = totalVenta;
	}

	console.log("IGV: ", igvActual);
	console.log("Total Venta: ", totalVenta);
	console.log("Total Descuento: ", descuentoFinal2);


	totalOriginal = totalVenta;

	$(".totalFinal1").html('TOTAL A PAGAR: S/. ' + totalVenta.toFixed(2));
	$(".totalFinal2").html('OP. GRAVADAS: S/. ' + totalVenta.toFixed(2));
	$(".descuentoFinal").html('DESCUENTOS TOTALES: S/. ' + descuentoFinal2.toFixed(2));

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