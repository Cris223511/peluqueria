var tabla;
var tabla2;

function init() {
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});

	$('#mComisiones').addClass("treeview active");
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
				$(row).find('td:eq(0), td:eq(2), td:eq(4), td:eq(5), td:eq(7), td:eq(8), td:eq(9)').addClass('nowrap-cell');
			}
		}).DataTable();
}

function generarComision(idpersonal, idlocal, nombre, cargo, tipo_documento, num_documento, local) {
	$(".trabajador_comisionar").text(capitalizarTodasLasPalabras(`${nombre} (${cargo}) - ${tipo_documento}: ${num_documento} - ${local}`));

	$.post("../ajax/comisiones.php?op=mostrarComisionesPersonal", { idpersonal: idpersonal, idlocal: idlocal }, function (data, status) {
		data = JSON.parse(data);
		console.log(data)
		$("#detallesProductosComisiones tbody").empty();

		data.forEach(function (detalle) {
			var productoTitulo = detalle.titulo_articulo || detalle.titulo_servicio;
			var inputId = detalle.idarticulo !== "0" ? 'idarticulo[]' : 'idservicio[]';

			var fila = `
                <tr>
                    <td style="width: 60%; min-width: 300px; white-space: nowrap;">
						<div style="display: flex; width: 100%; justify-content: center; align-items: center; height: 34px;">
                        	<input type="text" class="form-control" value="${capitalizarTodasLasPalabras(productoTitulo)}" disabled>
						</div>
					</td>
                    <td style="width: 40%; min-width: 130px; white-space: nowrap;">
						<div style="display: flex; width: 100%; flex-direction: row; justify-content: center; align-items: center; height: 34px; gap: 5px;">
							<input type="hidden" name="idpersonal[]" value="${detalle.idpersonal}">
							<input type="hidden" name="idcliente[]" value="${detalle.idcliente}">
							<input type="hidden" name="${inputId}" value="${detalle.idarticulo !== "0" ? detalle.idarticulo : detalle.idservicio}">
							<input style="width: 30%; min-width: 90px;" type="number" class="form-control" name="comision[]" lang="en-US" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" required>
							<select style="width: 30%; min-width: 90px;" name="tipo[]" class="form-control" required>
								<option value="1">S/.</option>
								<option value="2">%</option>
							</select>
						</div>
                    </td>
                    <td style="min-width: 130px; white-space: nowrap;">
                        <div style="display: flex; justify-content: center;">
                            <button type="button" class="btn btn-danger" style="height: 35px;" onclick="eliminarFila(this)"><i class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;

			$("#detallesProductosComisiones tbody").append(fila);
			evitarCaracteresEspecialesCamposNumericos();
			aplicarRestrictATodosLosInputs();
		});

		$("#myModal1").modal("show");
	});
}

function eliminarFila(btn) {
	if ($('#detallesProductosComisiones tbody tr').length > 1) {
		$(btn).closest("tr").remove();
	} else {
		bootbox.alert("Debe haber por lo menos una comisión en la tabla.");
	}
}

function limpiarModalComision() {
	setTimeout(() => {
		$(".trabajador_comisionar").text("");
		$("#detallesProductosComisiones tbody").empty();
	}, 800);
}

function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled", true);

	var detalles = [];
	var idpersonalUniqSet = new Set();

	$('#detallesProductosComisiones tbody tr').each(function () {
		var tipo = $(this).find('input[name="idarticulo[]"]').length ? "_producto" : "_servicio";
		var id = $(this).find('input[name="idarticulo[]"]').val() || $(this).find('input[name="idservicio[]"]').val();

		var idPersonal = $(this).find('input[name="idpersonal[]"]').val();
		idpersonalUniqSet.add(idPersonal);

		detalles.push(id + tipo);
	});

	var idpersonalUniq = Array.from(idpersonalUniqSet)[0];

	console.log(detalles);
	console.log(idpersonalUniq);

	var formData = new FormData($("#formulario")[0]);

	formData.append('detalles', JSON.stringify(detalles));
	formData.append('idpersonalUniq', idpersonalUniq);

	$.ajax({
		url: "../ajax/comisiones.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			datos = limpiarCadena(datos);
			limpiarModalComision();
			$("#myModal1").modal("hide");
			bootbox.alert(datos);
			tabla.ajax.reload();
		}
	});
}

var idpersonalGlobal = 0;

function verComision(idpersonal, nombre, cargo, tipo_documento, num_documento, local) {
	$(".trabajador_comisionar").text(capitalizarTodasLasPalabras(`${nombre} (${cargo}) - ${tipo_documento}: ${num_documento} - ${local}`));
	idpersonalGlobal = idpersonal;
	listarComision();
}

function listarComision() {
	$("#fecha_inicio").val("");
	$("#fecha_fin").val("");
	$("#estadoBuscar").val("");

	var fecha_inicio = $("#fecha_inicio").val();
	var fecha_fin = $("#fecha_fin").val();

	tabla2 = $('#tbldetalles').dataTable(
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
						doc.defaultStyle.fontSize = 10;
						doc.styles.tableHeader.fontSize = 10;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/comisiones.php?op=verComision',
				data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, idpersonal: idpersonalGlobal },
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5)').addClass('nowrap-cell');
			},
			"initComplete": function (settings, json) {
				$('#myModal2').modal('show');
			}
		}).DataTable();
}

function resetear() {
	const selects = ["fecha_inicio", "fecha_fin"];

	for (const selectId of selects) {
		$("#" + selectId).val("");
	}

	listarComision();
}

function buscarComision() {
	var fecha_inicio = $("#fecha_inicio").val();
	var fecha_fin = $("#fecha_fin").val();

	if ((fecha_inicio != "" && fecha_fin == "") || (fecha_inicio == "" && fecha_fin != "") || (fecha_inicio != "" && fecha_fin == "") || (fecha_inicio == "" && fecha_fin != "")) {
		bootbox.alert("Los campos de fecha inicial y fecha final son obligatorios.");
		return;
	} else if (fecha_inicio > fecha_fin) {
		bootbox.alert("La fecha inicial no puede ser mayor que la fecha final.");
		return;
	}

	tabla2 = $('#tbldetalles').dataTable(
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
						doc.defaultStyle.fontSize = 10;
						doc.styles.tableHeader.fontSize = 10;
					},
				},
			],
			"ajax":
			{
				url: '../ajax/comisiones.php?op=verComision',
				data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, idpersonal: idpersonalGlobal },
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
				$(row).find('td:eq(0), td:eq(1), td:eq(2), td:eq(3), td:eq(4), td:eq(5)').addClass('nowrap-cell');
			}
		}).DataTable();
}

init();