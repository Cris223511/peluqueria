var tabla;

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})

	$("#imagenmuestra").hide();
	$('#mAlmacen').addClass("treeview active");
	$('#lArticulos').addClass("active");

	$.post("../ajax/articulo.php?op=listarTodosActivos", function (data) {
		// console.log(data)
		const obj = JSON.parse(data);
		console.log(obj);

		const selects = {
			"idmarca": $("#idmarca"),
			"idcategoria": $("#idcategoria"),
			"idlocal": $("#idlocal"),
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
							select.append('<option value="' + opcion.id + '">' + opcion.titulo + ' - ' + opcion.usuario + '</option>');
						} else {
							select.append('<option value="' + opcion.id + '" data-local-ruc="' + opcion.ruc + '">' + opcion.titulo + ' - ' + opcion.usuario + '</option>');
						}
					});
					select.selectpicker('refresh');
				}
			}
		}

		$("#idlocal").val($("#idlocal option:first").val());
		$("#idlocal").selectpicker('refresh');
		actualizarRUC();
	});
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
	$("#codigo").val("");
	$("#codigo_producto").val("");
	$("#nombre").val("");
	$("#local_ruc").val("");
	$("#descripcion").val("");
	$("#stock").val("");
	$("#stock_minimo").val("");
	$("#imagenmuestra").attr("src", "");
	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#imagen").val("");
	$("#print").hide();
	$("#idarticulo").val("");

	$("#idcategoria").val($("#idcategoria option:first").val());
	$("#idcategoria").selectpicker('refresh');
	$("#idlocal").val($("#idlocal option:first").val());
	$("#idlocal").selectpicker('refresh');
	$("#idmarcas").val($("#idmarcas option:first").val());
	$("#idmarcas").selectpicker('refresh');
	actualizarRUC();
}

//Función mostrar formulario
function mostrarform(flag) {
	limpiar();
	if (flag) {
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
		$(".caja1").hide();
		$(".caja2").removeClass("col-lg-10 col-md-8 col-sm-12").addClass("col-lg-12 col-md-12 col-sm-12");
		$(".botones").removeClass("col-lg-10 col-md-8 col-sm-12").addClass("col-lg-12 col-md-12 col-sm-12");
	}
	else {
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
		$(".caja1").show();
		$(".caja2").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");
		$(".botones").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");
	}
}

//Función cancelarform
function cancelarform() {
	limpiar();
	mostrarform(false);
}

//Función Listar
function listar() {
	tabla = $('#tbllistado').dataTable(
		{
			"lengthMenu": [5, 10, 25, 75, 100],//mostramos el menú de registros a revisar
			"aProcessing": true,//Activamos el procesamiento del datatables
			"aServerSide": true,//Paginación y filtrado realizados por el servidor
			dom: '<Bl<f>rtip>',//Definimos los elementos del control de tabla
			buttons: [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5',
			],
			"ajax":
			{
				url: '../ajax/articulo.php?op=listar',
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(4), td:eq(5), td:eq(6), td:eq(7), td:eq(8), td:eq(9), td:eq(10)').addClass('nowrap-cell');
			}
		}).DataTable();
}
//Función para guardar o editar

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	var codigoBarra = $("#codigo").val();
	var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);
	if (!formatoValido && codigoBarra != "") {
		bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
		$("#btnGuardar").prop("disabled", false);
		return;
	}

	$.ajax({
		url: "../ajax/articulo.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			if (datos == "El código de barra del producto que ha ingresado ya existe." || datos == "El código del producto que ha ingresado ya existe.") {
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

function mostrar(idarticulo) {
	mostrarform(true);
	
	$(".caja1").show();
	$(".caja2").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");
	$(".botones").removeClass("col-lg-12 col-md-12 col-sm-12").addClass("col-lg-10 col-md-8 col-sm-12");

	$.post("../ajax/articulo.php?op=mostrar", { idarticulo: idarticulo }, function (data, status) {
		data = JSON.parse(data);
		console.log(data);

		$("#idcategoria").val(data.idcategoria);
		$('#idcategoria').selectpicker('refresh');
		$("#idlocal").val(data.idlocal);
		$('#idlocal').selectpicker('refresh');
		$("#idmarcas").val(data.idmarcas);
		$('#idmarcas').selectpicker('refresh');
		$("#codigo").val(data.codigo);
		$("#codigo_producto").val(data.codigo_producto);
		$("#nombre").val(data.nombre);
		$("#stock").val(data.stock);
		$("#stock_minimo").val(data.stock_minimo);
		$("#descripcion").val(data.descripcion);
		$("#imagenmuestra").show();
		$("#imagenmuestra").attr("src", "../files/articulos/" + data.imagen);
		$("#imagenactual").val(data.imagen);
		$("#idarticulo").val(data.idarticulo);
		generarbarcode();
		actualizarRUC();
	})
}

//Función para desactivar registros
function desactivar(idarticulo) {
	bootbox.confirm("¿Está Seguro de desactivar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articulo.php?op=desactivar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//Función para activar registros
function activar(idarticulo) {
	bootbox.confirm("¿Está Seguro de activar el producto?", function (result) {
		if (result) {
			$.post("../ajax/articulo.php?op=activar", { idarticulo: idarticulo }, function (e) {
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
			$.post("../ajax/articulo.php?op=eliminar", { idarticulo: idarticulo }, function (e) {
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//función para generar el número aleatorio del código de barra
function generar() {
	var codigo = "7 75 ";
	codigo += generarNumero(10000, 999) + " ";
	codigo += Math.floor(Math.random() * 10) + " ";
	codigo += generarNumero(100, 9) + " ";
	codigo += Math.floor(Math.random() * 10);
	$("#codigo").val(codigo);
	generarbarcode();
}

function generarNumero(max, min) {
	var numero = Math.floor(Math.random() * (max - min + 1)) + min;
	var numeroFormateado = ("0000" + numero).slice(-4);
	return numeroFormateado;
}


//función para generar el código de barras
function generarbarcode() {
	codigo = $("#codigo").val();

	var codigoBarra = $("#codigo").val();
	var formatoValido = /^[0-9]{1} [0-9]{2} [0-9]{4} [0-9]{1} [0-9]{4} [0-9]{1}$/.test(codigoBarra);
	if (!formatoValido && codigoBarra != "") {
		bootbox.alert("El formato del código de barra no es válido. El formato correcto es: X XX XXXX X XXXX X");
		$("#btnGuardar").prop("disabled", false);
		return;
	}

	JsBarcode("#barcode", codigo);
	$("#print").show();
}

//Función para imprimir el código de barras
function imprimir() {
	$("#print").printArea();
}

init();