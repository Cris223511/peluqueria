var tabla;
let lastNumComp = 0;

inicializeGLightbox();

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

	$('#mCompras').addClass("treeview active");
	$('#lCompras').addClass("active");

	$('[data-toggle="popover"]').popover();
}

function actualizarCorrelativo() {
	$.post("../ajax/compra.php?op=getLastNumComprobante", function (e) {
		console.log(e);
		lastNumComp = generarSiguienteCorrelativo(e);
		$("#num_comprobante_final1").text(lastNumComp);
	});
}

function limpiar() {
	limpiarModalEmpleados();
	limpiarModalMetodoPago();
	limpiarModalProveedor();
	limpiarModalProveedor2();
	limpiarModalProveedor3();
	limpiarModalProveedor4();
	limpiarModalPrecuenta();

	listarDatos();

	$("#detalles tbody").empty();
	$("#inputsMontoMetodoPago").empty();
	$("#inputsMetodoPago").empty();

	$("#total_compra").html("S/. 0.00");
	$("#tipo_comprobante").val("BOLETA DE COMPRA");
	$("#tipo_comprobante").selectpicker('refresh');

	$("#comentario_interno_final").val("");
	$("#comentario_externo_final").val("");
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

function listarDatos() {
	$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);
		console.log(obj);

		let articulo = obj.articulo || [];
		let servicio = obj.servicio || [];
		let metodo_pago = obj.metodo_pago || [];
		let proveedores = obj.proveedores || [];
		let categoria = obj.categoria || [];
		let personales = obj.personales || [];

		$("#productos").empty();
		$("#categoria").empty();
		$("#pagos").empty();

		$("#productos1").empty();
		$("#productos2").empty();
		$("#idproveedor").empty();
		$("#idpersonal").empty();

		listarArticulos(articulo, servicio);
		listarCategoria(categoria);
		listarMetodoPago(metodo_pago);
		listarSelects(articulo, servicio, proveedores, personales);
	});
}

function listarTodosLosArticulos() {
	$.post("../ajax/compra.php?op=listarTodosLocalActivosPorUsuario", function (data) {
		const obj = JSON.parse(data);

		let articulo = obj.articulo;
		let servicio = obj.servicio;

		$("#productos").empty();
		listarArticulos(articulo, servicio);
	});
}

function listarArticulosPorCategoria(idcategoria) {
	$.post("../ajax/compra.php?op=listarArticulosPorCategoria", { idcategoria: idcategoria }, function (data) {
		const articulos = JSON.parse(data).articulo || [];
		console.log(articulos);

		$("#productos").empty();
		listarArticulos(articulos, []);
	});
}

function listarArticulos(articulos, servicios) {
	let productosContainer = $("#productos");

	if ((articulos.length > 0 || servicios.length > 0) && !(articulos.length === 0 && servicios.length === 0)) {
		articulos.forEach((articulo) => {
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
						<a style="width: 100%;" onclick="verificarEmpleado('producto','${articulo.id}','${articulo.nombre}','${articulo.stock}','${articulo.precio_compra}','${articulo.precio_venta}','${articulo.codigo}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
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
							<a style="width: 100%;" onclick="verificarEmpleado('servicio','${servicio.id}','${servicio.nombre}','${servicio.stock}','${servicio.precio_compra}','${servicio.precio_venta}','${servicio.codigo}')"><button type="button" class="btn btn-warning" style="height: 33.6px; width: 100%;">AGREGAR</button></a>
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
						<h4>No se encontraron productos y servicios.</h4>
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

function listarSelects(articulos, servicios, proveedores, personales) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.codigo.replace(/\s/g, '')} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');

	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos2.append(optionHtml);
	});

	let selectProveedores = $("#idproveedor");
	selectProveedores.empty();
	selectProveedores.append('<option value="">Buscar proveedor.</option>');

	proveedores.forEach((proveedor) => {
		let optionHtml = `<option value="${proveedor.id}">${proveedor.nombre} - ${proveedor.tipo_documento}: ${proveedor.num_documento}</option>`;
		selectProveedores.append(optionHtml);
	});

	let selectEmpleados = $("#idpersonal");
	selectEmpleados.empty();
	selectEmpleados.append('<option value="">SIN EMPLEADOS A COMISIONAR.</option>');

	personales.forEach((personal) => {
		let optionHtml = `<option value="${personal.id}">${capitalizarTodasLasPalabras(personal.nombre)} - ${capitalizarTodasLasPalabras(personal.local)}</option>`;
		selectEmpleados.append(optionHtml);
	});

	// Después de agregar todas las opciones, actualizamos el plugin selectpicker
	selectProductos1.selectpicker('refresh');
	selectProductos2.selectpicker('refresh');
	selectProveedores.selectpicker('refresh');
	selectEmpleados.selectpicker('refresh');

	$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('onkeydown', 'checkEnter(event)');
	$('#idproveedor').closest('.form-group').find('input[type="text"]').attr('oninput', 'checkDNI(this)');
	$('#idproveedor').closest('.form-group').find('.dropdown-menu.open').addClass('idproveedorInput');

	colocarNegritaStocksSelects();
}

function listarSelectsArticulos(articulos, servicios) {
	let selectProductos1 = $("#productos1");
	selectProductos1.empty();
	selectProductos1.append('<option value="">Lectora de códigos.</option>');
	selectProductos1.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.codigo.replace(/\s/g, '')} - (STOCK: ${articulo.stock})</option>`;
		selectProductos1.append(optionHtml);
	});

	selectProductos1.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
		selectProductos1.append(optionHtml);
	});

	let selectProductos2 = $("#productos2");
	selectProductos2.empty();
	selectProductos2.append('<option value="">Buscar productos.</option>');
	selectProductos2.append('<option disabled>PRODUCTOS:</option>');

	articulos.forEach((articulo) => {
		let optionHtml = `<option data-tipo-producto="producto" data-nombre="${articulo.nombre}" data-stock="${articulo.stock}" data-precio-compra="${articulo.precio_compra}" data-precio-venta="${articulo.precio_venta}" data-codigo="${articulo.codigo}" value="${articulo.id}">${articulo.nombre} - ${articulo.marca} - ${articulo.local} - (STOCK: ${articulo.stock})</option>`;
		selectProductos2.append(optionHtml);
	});

	selectProductos2.append('<option disabled>SERVICIOS:</option>');

	servicios.forEach((servicio, index) => {
		let numeroCorrelativo = ('0' + (index + 1)).slice(-2);
		let optionHtml = `<option data-tipo-producto="servicio" data-nombre="${servicio.nombre}" data-stock="${servicio.stock}" data-precio-compra="${servicio.precio_compra}" data-precio-venta="${servicio.precio_venta}" data-codigo="${servicio.codigo}" value="${servicio.id}">N° ${numeroCorrelativo}: ${capitalizarPrimeraLetra(servicio.nombre)} - Código de servicio: N° ${servicio.codigo.replace(/\s/g, '')}</option>`;
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
	verificarEmpleado(selectedOption.getAttribute('data-tipo-producto'), selectedOption.value, selectedOption.getAttribute('data-nombre'), selectedOption.getAttribute('data-stock'), selectedOption.getAttribute('data-precio-compra'), selectedOption.getAttribute('data-precio-venta'), selectedOption.getAttribute('data-codigo'))
	selectElement.value = "";
	$(selectElement).selectpicker('refresh');
	colocarNegritaStocksSelects();
}

// MODAL EMPLEADOS

let idarticuloGlobal = "";
let nombreGlobal = "";
let precioCompraGlobal = "";
let precioVentaGlobal = "";
let codigoGlobal = "";
let tipoProductoFinal = "";

function verificarEmpleado(tipoarticulo, idarticulo, nombre, stock, precio_compra, precio_venta, codigo) {
	var existeProducto = validarTablaProductos(tipoarticulo, idarticulo);

	if (stock == 0) {
		bootbox.alert("El producto seleccionado se encuentra sin stock.");
		return;
	}

	if (!existeProducto) {
		$('#myModal1').modal('show');
		limpiarModalEmpleados();

		console.log("esto traigo =) =>", tipoarticulo, idarticulo, nombre, precio_compra, precio_venta, codigo);

		idarticuloGlobal = idarticulo;
		nombreGlobal = nombre;
		precioCompraGlobal = precio_compra;
		precioVentaGlobal = precio_venta;
		codigoGlobal = codigo;
		tipoProductoFinal = tipoarticulo;

		$("#ProductoSeleccionado").html(capitalizarTodasLasPalabras(nombre));
		$("#PrecioSeleccionado").html(`S/. ${precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta}`);

		evaluarBotonEmpleado();
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
		$("#btnGuardarArticulo").attr("onclick", `agregarDetalle('${tipoProductoFinal}','${idarticuloGlobal}', '${valorEmpleado}', '${nombreGlobal}', '${precioCompraGlobal}', '${precioVentaGlobal}', '${codigoGlobal}'); limpiarModalEmpleados();`);
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

function listarProveedores() {
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
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor").prop("disabled", false);
				return;
			}
			bootbox.alert(datos);
			$('#myModal3').modal('hide');
			listarProveedores();
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
			console.log(datos);
			if (datos == "DNI no encontrado" || datos == "RUC no encontrado") {
				limpiarModalProveedor();
				bootbox.confirm(datos + ", ¿deseas crear un proveedor manualmente?", function (result) {
					if (result) {
						(datos == "DNI no encontrado") ? $("#tipo_documento4").val("DNI") : $("#tipo_documento4").val("RUC");

						$("#tipo_documento4").trigger("change");

						let inputValue = $('#sunat').val();
						$("#num_documento4").val(inputValue);

						$('#myModal3').modal('hide');
						$('#myModal6').modal('show');
					}
				})
			} else if (datos == "El DNI debe tener 8 caracteres." || datos == "El RUC debe tener 11 caracteres.") {
				bootbox.alert(datos);
				limpiarModalProveedor();
			} else {
				const obj = JSON.parse(datos);
				console.log(obj);

				$("#nombre").val(obj.nombre);
				$("#tipo_documento").val(obj.tipoDocumento == "1" ? "DNI" : "RUC");
				$("#num_documento").val(obj.numeroDocumento);
				$("#direccion").val(obj.direccion);
				$("#telefono").val(obj.telefono);
				$("#email").val(obj.email);

				// Deshabilitar los campos solo si están vacíos
				$("#nombre").prop("disabled", obj.hasOwnProperty("nombre") && obj.nombre !== "" ? true : false);
				$("#direccion").prop("disabled", obj.hasOwnProperty("direccion") && obj.direccion !== "" ? true : false);
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
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor2").prop("disabled", false);
				return;
			}
			bootbox.alert(datos);
			$('#myModal4').modal('hide');
			listarProveedores();
			limpiarModalProveedor2();
		}
	});
}

// PROVEEDORES NUEVOS (PROVEEDOR GENÉRICO)

function limpiarModalProveedor3() {
	$("#idproveedor4").val("");
	$("#nombre3").val("PÚBLICO GENERAL");
	$("#tipo_documento3").val("DNI");
	$("#num_documento3").val("");

	$("#btnGuardarProveedor3").prop("disabled", false);
}

function guardaryeditar5(e) {
	e.preventDefault();
	$("#btnGuardarProveedor3").prop("disabled", true);

	deshabilitarTodoModalProveedor2();
	var formData = new FormData($("#formulario5")[0]);
	habilitarTodoModalProveedor2();

	$.ajax({
		url: "../ajax/proveedores.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor3").prop("disabled", false);
				return;
			}
			bootbox.alert(datos);
			$('#myModal5').modal('hide');
			listarProveedores();
			limpiarModalProveedor3();
		}
	});
}

function habilitarTodoModalProveedor2() {
	$("#tipo_documento3").prop("disabled", true);
	$("#nombre3").prop("disabled", true);
}

function deshabilitarTodoModalProveedor2() {
	$("#tipo_documento3").prop("disabled", false);
	$("#num_documento3").prop("disabled", false);
	$("#nombre3").prop("disabled", false);
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
			if (datos == "El número de documento que ha ingresado ya existe.") {
				bootbox.alert(datos);
				$("#btnGuardarProveedor4").prop("disabled", false);
				return;
			}
			bootbox.alert(datos);
			$('#myModal6').modal('hide');
			listarProveedores();
			limpiarModalProveedor3();
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
		bootbox.alert("Debe agregar por lo menos un artículo o servicio.");
		return;
	}

	let detallesValidos = true;
	$('.filas').each(function (index, fila) {
		let precioCompra = $(fila).find('input[name="precio_venta[]"]').val();
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

	let totalCompra = parseFloat($("#total_compra").text().replace('S/. ', '').replace(',', ''));
	if (totalCompra <= 0) {
		bootbox.alert("El total de compra no puede ser negativo o igual a cero.");
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

	let totalFinal = $("#total_compra").text();

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

function actualizarTablaDetallesProductosCompra() {
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

function verificarCantidadArticulos() {
	if ($('.filas').length === 0) {
		bootbox.alert("Debe agregar por lo menos un artículo o servicio.");
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

	let totalCompra = 0;

	if (igv.value == 0.18) {
		totalCompra = totalOriginal + (totalOriginal * 0.18);
		totalTemp = totalCompra;
	} else {
		totalCompra = totalOriginalBackup;  // Restablece al valor original
		totalTemp = totalOriginalBackup;  // Restablece totalTemp al valor original
	}

	$(".totalFinal1").html('TOTAL A PAGAR: S/. ' + Number(totalCompra).toFixed(2));
	$(".totalFinal2").html('OP. GRAVADAS: S/. ' + Number(totalCompra).toFixed(2));

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
	let comentarioInterno = $("#comentario_interno").val();
	let comentarioExterno = $("#comentario_externo").val();
	let impuesto = $("#igv").val();
	let totalCompraFinal = $(".totalFinal1").text().match(/\d+\.\d+/)[0];
	let vueltoFinal = $("#vuelto").val();

	console.log(impuesto);

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
	$(".totalFinal1").html('TOTAL A PAGAR: S/. 0.00');
	$(".totalFinal2").html('OP. GRAVADAS: S/. 0.00');
	$(".descuentoFinal").html('DESCUENTOS TOTALES: S/. 0.00');
	$("#detallesProductosPrecuenta tbody").empty();
	$("#montoMetodoPago").empty();

	cont = 0;
	$("#totalItems").html(cont);

	$("#igv").val("0.00");
	$("#vuelto").val("0.00");
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
	// setTimeout(() => {
	// 	document.querySelector(".sidebar-toggle").click();
	// }, 500);
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
			"lengthMenu": [10, 25, 50, 100],//mostramos el menú de registros a revisar
			"aProcessing": true,//Activamos el procesamiento del datatables
			"aServerSide": true,//Paginación y filtrado realizados por el servidor
			dom: '<Bl<f>rtip>',//Definimos los elementos del control de tabla
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
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
				if (!datos) {
					console.log(datos);
					console.log("No se recibieron datos del servidor.");
					return;
				} else if (datos == "Una de las cantidades superan al stock normal del artículo o servicio." || datos == "El subtotal de uno de los artículos o servicios no puede ser menor a 0." || datos == "El precio de venta de uno de los artículos o servicios no puede ser menor al precio de compra." || "El número correlativo que ha ingresado ya existe en el local seleccionado.") {
					console.log(datos);
					console.log(typeof (datos));
					bootbox.alert(datos);
					return;
				} else {
					console.log(datos);
				}
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

// FUNCIONES Y BOTONES DE LAS VENTAS

function modalDetalles(idcompra, usuario, num_comprobante, proveedor, proveedor_tipo_documento, proveedor_num_documento, proveedor_direccion, impuesto, total_compra, vuelto) {
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
		$('#direccion_proveedor').text((proveedor_direccion != "") ? proveedor_direccion : "Sin registrar");
		$('#boleta').text("N° " + num_comprobante);

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
		$('#total_detalle').text(total_compra);

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
		$('#total_pagos').text(total_compra);

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

function modalEstadoVenta(idcompra, num_comprobante) {
	limpiarModalEstadoVenta();

	$("#num_comprobante_final3").text(num_comprobante);

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").attr("onclick", "cambiarEstadoVenta('" + texto + "', " + idcompra + ");");
	});
}

function limpiarModalEstadoVenta() {
	$("#num_comprobante_final3").text("");

	var nombresBotones = ['INICIADO', 'ENTREGADO', 'POR ENTREGAR', 'EN TRANSCURSO', 'FINALIZADO', 'ANULADO'];

	nombresBotones.forEach(function (texto) {
		$("button:contains('" + texto + "')").removeAttr("onclick");
	});
}

function cambiarEstadoVenta(estado, idcompra) {
	const mensajeAdicional = (estado === "ANULADO") ? " recuerde que esta opción hará que el estado de la venta no se pueda modificar de nuevo." : "";

	bootbox.confirm("¿Estás seguro de cambiar el estado de la venta a <strong>" + minusTodasLasPalabras(estado) + "</strong>?" + mensajeAdicional, function (result) {
		if (result) {
			$.post("../ajax/compra.php?op=cambiarEstado", { idcompra: idcompra, estado: capitalizarPrimeraLetra(estado) }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
				$('#myModal11').modal('hide');
				limpiarModalEstadoVenta();
			});
		}
	})
}

function anular(idcompra) {
	bootbox.confirm("¿Está seguro de anular la venta? recuerde que esta opción hará que el estado de la venta no se pueda modificar de nuevo.", function (result) {
		if (result) {
			$.post("../ajax/compra.php?op=anular", { idcompra: idcompra }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function eliminar(idcompra) {
	bootbox.confirm("¿Estás seguro de eliminar la venta?", function (result) {
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

function agregarDetalle(tipoproducto, idarticulo, idpersonal, nombre, precio_compra, precio_venta, codigo) {
	var cantidad = 1;
	var descuento = '0.00';

	if (idarticulo != "") {
		var fila = '<tr class="filas fila' + cont + ' principal">' +
			'<td><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '"><input type="hidden" step="any" name="precio_compra[]" value="' + precio_compra + '"><input type="hidden" name="idpersonal[]" value="' + idpersonal + '">' + codigo + '</td>' +
			'<td>' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td><input type="number" step="any" name="precio_venta[]" oninput="modificarSubototales();" id="precio_venta[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + (precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta) + '"></td>' +
			'<td><input type="number" step="any" name="descuento[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" required value="' + descuento + '"></td>' +
			'<td><input type="number" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + cantidad + '"></td>' +
			'<td style="text-align: center;"><button type="button" class="btn btn-danger" style="height: 33.6px;" onclick="eliminarDetalle(' + cont + ');"><i class="fa fa-trash"></i></button></td>' +
			'</tr>';

		var fila2 = '<tr class="filas fila' + cont + ' principal2">' +
			'<td class="nowrap-cell" style="text-align: start !important;"><input type="hidden" name="' + (tipoproducto == "producto" ? "idarticulo[]" : "idservicio[]") + '" value="' + idarticulo + '"><input type="hidden" step="any" name="precio_compra[]" value="' + precio_compra + '"><input type="hidden" name="idpersonal[]" value="' + idpersonal + '">' + codigo + '</td>' +
			'<td style="text-align: start !important;">' + capitalizarTodasLasPalabras(nombre) + '</td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="precio_venta[]" oninput="modificarSubototales2();" id="precio_venta[]" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + (precio_venta == '' ? parseFloat(0).toFixed(2) : precio_venta) + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" step="any" name="descuento[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" required value="' + descuento + '"></div></td>' +
			'<td><div style="display: flex; align-items: center; justify-content: center;"><input type="number" class="form-control" name="cantidad[]" id="cantidad[]" oninput="modificarSubototales2();" lang="en-US" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required value="' + cantidad + '"></div></td>' +
			'<td style="text-align: center;"><button type="button" class="btn btn-danger" style="height: 33.6px;" onclick="eliminarDetalle(' + cont + '); actualizarVuelto();"><i class="fa fa-trash"></i></button></td>' +
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
}

function modificarSubototales() {
	var principalRows = document.querySelectorAll('.principal');
	var totalCompra = 0;
	descuentoFinal = 0;

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioCompra = row.querySelector('[name="precio_venta[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioCompra) - descuento;
		totalCompra += subtotal;
		descuentoFinal += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Venta:", precioCompra, "Descuento:", descuento);
	});

	console.log("Total Venta: ", totalCompra);
	console.log("Total Descuento: ", descuentoFinal);

	$("#total_compra").html("S/. " + totalCompra.toFixed(2));
	evaluar();
}

function modificarSubototales2() {
	var principalRows = document.querySelectorAll('.principal2');
	var totalCompra = 0;
	var descuentoFinal2 = 0;
	var igvActual = $("#igv").val();

	principalRows.forEach(function (row) {
		var cantidad = row.querySelector('[name="cantidad[]"]').value;
		var precioCompra = row.querySelector('[name="precio_venta[]"]').value;
		var descuento = row.querySelector('[name="descuento[]"]').value;

		var subtotal = (cantidad * precioCompra) - descuento;
		totalCompra += subtotal;
		descuentoFinal2 += Number(descuento);

		// console.log("Cantidad:", cantidad, "Precio Venta:", precioCompra, "Descuento:", descuento);
	});

	if (igvActual == 2) {
		totalCompra = totalCompra + (totalCompra * 0.18);
	} else {
		totalCompra = totalCompra;
	}

	console.log("IGV: ", igvActual);
	console.log("Total Venta: ", totalCompra);
	console.log("Total Descuento: ", descuentoFinal2);


	totalOriginal = totalCompra;

	$(".totalFinal1").html('TOTAL A PAGAR: S/. ' + totalCompra.toFixed(2));
	$(".totalFinal2").html('OP. GRAVADAS: S/. ' + totalCompra.toFixed(2));
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

function eliminarDetalle(indice) {
	$(".fila" + indice).remove();
	detalles = detalles - 1;
	modificarSubototales();
	$("#totalItems").html(cont);
	verificarCantidadArticulos();
	mostrarDatosModalPrecuenta();
}

document.addEventListener('DOMContentLoaded', function () {
	init();
});