<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require '../config/Conexion.php';
  require 'header.php';

  if ($_SESSION['ventas'] == 1) {
?>
    <link rel="stylesheet" href="../public/css/jquery.gScrollingCarousel.css">
    <style>
      td {
        height: 30.84px !important;
      }

      @media (max-width: 991.50px) {
        .contenedor {
          flex-direction: column;
        }
      }

      .caja-categoria {
        width: 100%;
        height: min-content;
        background-color: #9daeb8;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        border: 1px solid black;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
      }

      .caja-categoria h1 {
        font-size: 14px;
        margin: 0;
        margin-bottom: 5px;
      }

      .caja-categoria h4 {
        font-size: 11px;
        margin: 0;
      }

      .productos {
        margin: 20px 50px;
        background-color: white;
        border-radius: 5px;
      }

      .caja-productos {
        width: 180px;
        height: min-content;
        background-color: white;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        border: 2px solid #eeeeee;
        word-wrap: break-word;
        display: grid;
        height: 100%;
        place-items: center;
      }

      .caja-productos img {
        width: 100px;
        margin-bottom: 20px;
        border-radius: 10px;
      }

      .caja-productos h1 {
        font-size: 16px;
        color: black;
        margin-bottom: 10px;
        font-weight: bold;
      }

      .caja-productos h4 {
        font-size: 16px;
        color: #babbbd;
        margin-bottom: 35px;
      }

      .subcaja-gris {
        background-color: #ededed;
        padding: 5px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        width: 100%;
        align-items: center;
      }

      .subcaja-gris span:nth-child(1) {
        font-size: 12px;
      }

      .subcaja-gris span:nth-child(2) {
        font-size: 12px;
        color: #737578;
      }

      .subcaja-gris span:nth-child(3) {
        font-size: 16px;
        color: black;
      }

      .caja-productos-vacia {
        width: 100%;
        height: 385px !important;
        background-color: white;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        border: 2px solid #eeeeee;
        word-wrap: break-word;
        display: grid;
        height: 100%;
        place-items: center;
      }

      .caja-productos-vacia h4 {
        font-size: 16px;
        font-style: italic;
        color: #babbbd;
        margin: 0;
      }

      #detalles input {
        color: black;
        width: 65px;
      }

      #detalles thead,
      #detalles tbody tr {
        background-color: transparent;
        color: white;
        font-size: 12px;
      }

      #detalles tbody tr {
        font-size: 10.5px;
      }

      #detalles thead th {
        color: #adc1cd;
        font-weight: bold;
        border-bottom: 1px solid #f4f4f4 !important;
      }

      #detalles thead,
      #detalles thead tr,
      #detalles thead th {
        border: none;
      }

      #detalles thead {
        position: sticky;
        top: 0;
        background-color: #2c3b42;
        user-select: none;
      }

      #detallesEmpleados thead,
      #detallesEmpleados thead tr,
      #detallesEmpleados thead th,
      #detallesEmpleados tbody,
      #detallesEmpleados tbody tr,
      #detallesEmpleados tbody th {
        border: none;
        background-color: white;
        font-size: 14px;
        text-align: center;
      }

      #detallesProductosPrecuenta thead,
      #detallesProductosPrecuenta thead tr,
      #detallesProductosPrecuenta thead th,
      #detallesProductosPrecuenta tbody,
      #detallesProductosPrecuenta tbody tr,
      #detallesProductosPrecuenta tbody th {
        border: none;
        background-color: white;
        font-size: 14px;
        text-align: center;
        align-items: center;
      }

      @media (min-width: 1199.20px) {
        #tablaPrecuenta {
          height: 290px;
        }
      }

      .pagos {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        text-align: center;
        gap: 5px;
        padding: 10px 120px;
      }

      .pagos h1 {
        color: white;
        font-size: 18px;
        font-weight: normal;
        margin-top: 10px;
      }

      .caja-pagos {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        justify-content: center;
      }

      .caja-pagos a {
        border-radius: 3px;
        margin-bottom: 10px;
        width: 40px;
        height: 30px;
        overflow: hidden;
        display: flex;
        align-items: center;
        cursor: pointer;
      }

      .grayscale {
        filter: grayscale(100%);
        transition: filter 0.3s ease;
      }

      .color {
        filter: grayscale(0%);
        transition: filter 0.3s ease;
      }

      @media (max-width: 1200px) {
        .pagos {
          padding: 10px 60px;
        }
      }

      .caja-ventas {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
      }

      .caja-ventas button {
        margin-bottom: 10px;
        width: 40px;
        height: 30px;
      }

      .g-scrolling-carousel .items>* {
        min-width: 200px !important;
        white-space: wrap !important;
      }

      .caja-datos {
        padding: 10px;
      }

      .idclienteInput {
        max-width: 400px;
        overflow: auto;
      }

      #detallesProductosPrecuenta input {
        width: 90px;
        height: 30px;
      }

      @media (max-width: 991.50px) {
        .smallModal {
          width: 90% !important;
        }
      }
    </style>

    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="overflow: auto !important;">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box caja">
              <div class="box-header with-border">
                <h1 class="box-title">Ventas
                  <button class="btn btn-bcp" id="btnagregar" onclick="validarCaja();">
                    <i class="fa fa-plus-circle"></i> Nueva venta
                  </button>
                  <?php if ($_SESSION["cargo"] == "superadmin") { ?>
                    <a href="../reportes/rptventas.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                </h1>
                <div class="box-tools pull-right">
                </div>
                <div class="panel-body table-responsive listadoregistros" style="overflow-x: visible; padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                  <div class="form-group col-lg-5 col-md-5 col-sm-6 col-xs-12">
                    <label>Fecha Inicial:</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <label>Fecha Final:</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                  </div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <label id="label">ㅤ</label>
                    <div style="display: flex; gap: 10px;">
                      <button style="width: 80%;" class="btn btn-bcp" onclick="buscar()">Buscar</button>
                      <button style="width: 20%; height: 32px" class="btn btn-success" onclick="listar()"><i class="fa fa-repeat"></i></button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th style="width: 12%;">Opciones</th>
                      <th>PDF</th>
                      <th>Cliente</th>
                      <th>Ubicación del local</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Venta (S/.)</th>
                      <th>Agregado por</th>
                      <th>Fecha y hora</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>PDF</th>
                      <th>Cliente</th>
                      <th>Ubicación del local</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Venta (S/.)</th>
                      <th>Agregado por</th>
                      <th>Fecha y hora</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            <div class="panel-body" id="formularioregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-top: 0 !important; padding-right: 0 !important;">
              <form name="formulario" id="formulario" method="POST">
                <div class="form-group col-lg-12 col-md-12 col-sm-12" style="background-color: #1d262b; padding: 10px; padding-bottom: 5px; margin: 0;">
                  <div style="display: flex; padding: 0; margin: 0; flex-wrap: wrap;">
                    <div class="form-group col-lg-2 col-md-4 col-sm-4" style="padding-bottom: 5px !important; padding-left: 0 !important; padding-right: 5px !important; margin: 0 !important;">
                      <select id="productos1" class="form-control selectpicker" data-live-search="true" data-size="5" onchange="seleccionarProducto(this)">
                        <option value="">Lectora de códigos.</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-2 col-md-4 col-sm-4" style="padding-bottom: 5px !important; padding-left: 0 !important; padding-right: 5px !important; margin: 0 !important;">
                      <select id="productos2" class="form-control selectpicker" data-live-search="true" data-size="5" onchange="seleccionarProducto(this)">
                        <option value="">Buscar productos.</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-2 col-md-4 col-sm-4" style="padding-bottom: 5px !important; padding-left: 0 !important; padding-right: 5px !important; margin: 0 !important;">
                      <select id="idcliente" name="idcliente" class="form-control selectpicker" data-live-search="true" data-size="5">
                        <option value="">Buscar cliente.</option>
                      </select>
                    </div>
                    <!-- <a data-toggle="modal" href="#"><button class="btn btn-warning" style="height: 33.6px; margin-right: 5px; margin-bottom: 5px;"><i class="fa fa-map-o"></i></button></a> -->
                    <a data-toggle="modal" href="#myModal5"><button class="btn btn-warning" style="height: 33.6px; margin-right: 5px; margin-bottom: 5px;">CLIENTE GENÉRICO</button></a>
                    <a data-toggle="modal" href="#myModal4"><button class="btn btn-primary" style="height: 33.6px; margin-right: 5px; margin-bottom: 5px;">CARNET EXTRANJERÍA</button></a>
                    <div style="padding-left: 0 !important; padding-right: 5px !important; margin: 0 !important;">
                      <select name="tipo_comprobante" id="tipo_comprobante" class="form-control selectpicker" style="padding: 0 !important; margin: 0 !important;" required>
                        <option value="NOTA DE VENTA">NOTA DE VENTA</option>
                        <option value="FACTURA">FACTURA</option>
                      </select>
                    </div>
                    <button type="button" class="btn btn-danger" style="height: 33.6px; margin-right: 5px; margin-bottom: 5px;" id="total_venta">s/. 0.00</button>
                    <button type="button" class="btn btn-success" style="height: 33.6px; margin-bottom: 5px;" onclick="listarTodosLosArticulos();"><i class="fa fa-refresh"></i></button>
                  </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 contenedor" style="display: flex; padding: 0;">
                  <div class="col-lg-7 col-md-7 col-sm-12" style="background-color: #e4e6e7; padding: 10px">
                    <div class="categoria">
                      <div class="g-scrolling-carousel carousel-three">
                        <div id="categoria" class="items">
                        </div>
                      </div>
                    </div>
                    <div class="productos">
                      <div class="g-scrolling-carousel carousel-three">
                        <div id="productos" class="items">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-5 col-md-5 col-sm-12" style="padding: 0; max-height: 100%; overflow: auto; height: 100%;">
                    <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 10px; padding-top: 0px; background-color: #2c3b42; height: 326px; max-height: 326px; overflow: auto;">
                      <table id="detalles" class="table table-dark table-striped table-hover w-100" style="width: 100% !important;">
                        <thead>
                          <th>CÓDIGO</th>
                          <th style="width: 30%; min-width: 130px; white-space: nowrap;">NOMBRE</th>
                          <th>PRECIO</th>
                          <th>DESCUENTO</th>
                          <th>CANTIDAD</th>
                          <th>ELIMINAR</th>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 pagos" style="background-color: #1e272c; height: 100%; max-height: max-content;">
                      <h1>TIPOS DE PAGO</h1>
                      <div id="pagos" class="caja-pagos">
                      </div>
                      <div id="inputsMetodoPago"></div>
                      <div id="inputsMontoMetodoPago"></div>
                      <h1>OPCIONES DE VENTA</h1>
                      <div class="caja-ventas">
                        <!-- <a href="#"><button type="button" class="btn btn-default" style="padding-top: 4px;"><strong>%</strong></button></a> -->
                        <!-- <a href="#"><button type="button" class="btn btn-default"><i class="fa fa-money"></i></button></a> -->
                        <a onclick="limpiarTodo();"><button type="button" class="btn btn-default"><i class="fa fa-trash"></i></button></a>
                        <!-- <a href="#"><button type="button" class="btn btn-default"><i class="fa fa-usd"></i></button></a> -->
                        <!-- <a href="#"><button type="button" class="btn btn-default"><i class="fa fa-cogs"></i></button></a> -->
                        <a onclick="verificarModalPrecuenta();"><button type="button" class="btn btn-warning"><i class="fa fa-book"></i></button></a>
                      </div>
                    </div>
                    <div id="comentarios" style="display: none;">
                      <textarea type="text" class="form-control" id="comentario_interno_final" name="comentario_interno" maxlength="40" rows="4" autocomplete="off"></textarea>
                      <textarea type="text" class="form-control" id="comentario_externo_final" name="comentario_externo" maxlength="40" rows="4" autocomplete="off"></textarea>
                    </div>
                    <select style="display: none;" id="igvFinal" name="impuesto" class="form-control" data-size="2">
                      <option value="0.00">0.00</option>
                      <option value="0.10">0.18</option>
                    </select>
                    <input type="hidden" id="total_venta_final" name="total_venta" value="">
                    <input type="hidden" id="vuelto_final" name="vuelto" value="">
                  </div>
                </div>
                <div class="form-group col-lg-12 col-md-12 col-sm-12" style="background-color: white !important; padding: 10px !important; margin-bottom: 0 !important;">
                  <button class="btn btn-warning" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                  <!-- <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button> -->
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: visible;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">EMPLEADOS A COMISIONAR:</h4>
          </div>
          <div class="panel-body">
            <div class="form-group col-lg-12 col-md-12 col-sm-12">
              <select id="idpersonal" class="form-control selectpicker" data-live-search="true" data-size="5" onchange="evaluarBotonEmpleado();">
                <option value="">SIN EMPLEADOS A COMISIONAR.</option>
              </select>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table id="detallesEmpleados" class="table w-100" style="width: 100% !important;">
                <thead>
                  <th>EMPLEADO</th>
                  <th>PRODUCTO</th>
                  <th>PRECIO</th>
                </thead>
                <tbody>
                  <tr>
                    <td style="width: 30%; min-width: 130px; white-space: nowrap;" id="empleadoSeleccionado">SIN SELECCIONAR</td>
                    <td style="width: 40%; min-width: 130px; white-space: nowrap;" id="ProductoSeleccionado"></td>
                    <td style="width: 30%; min-width: 130px; white-space: nowrap;" id="PrecioSeleccionado"></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
              <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalEmpleados();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
              <button class="btn btn-bcp" type="button" data-dismiss="modal" id="btnGuardarArticulo"><i class="fa fa-save"></i> Guardar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 1 -->

    <!-- Modal 2 -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">CREAR NUEVO MÉTODO DE PAGO:</h4>
          </div>
          <div class="panel-body">
            <form name="formulario2" id="formulario2" method="POST">
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Método de pago(*):</label>
                <input type="hidden" name="idmetodopago" id="idmetodopago">
                <input type="text" class="form-control" name="titulo" id="titulo" maxlength="40" placeholder="Ingrese el nombre del método de pago." autocomplete="off" required>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Imagen(*):</label>
                <input type="file" class="form-control" name="imagen" id="imagen" accept="image/x-png,image/gif,image/jpeg" required>
              </div>
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <label>Descripción:</label>
                <textarea type="text" class="form-control" name="descripcion" id="descripcion" maxlength="150" rows="4" placeholder="Ingrese una descripción."></textarea>
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalMetodoPago();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarMetodoPago"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 2 -->

    <!-- Modal 3 -->
    <div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">NO SE ENCONTRÓ AL CLIENTE, ¿DESEA AGREGAR UNO NUEVO?:</h4>
          </div>
          <div class="panel-body">
            <form name="formSunat" id="formSunat" method="POST">
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                <div style="display: flex;">
                  <input type="number" class="form-control" name="sunat" id="sunat" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="Buscar cliente por DNI o RUC a la SUNAT." required>
                  <button class="btn btn-bcp" type="submit" id="btnSunat">Buscar</button>
                </div>
              </div>
            </form>
            <form name="formulario3" id="formulario3" method="POST">
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Tipo Documento(*):</label>
                <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" onchange="changeValue(this);" required disabled>
                  <option value="">- Seleccione -</option>
                  <option value="DNI">DNI</option>
                  <option value="RUC">RUC</option>
                  <option value="CEDULA">CEDULA</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Número(*):</label>
                <input type="number" class="form-control" name="num_documento" id="num_documento" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" placeholder="Ingrese el N° de documento." required disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Nombre(*):</label>
                <input type="hidden" name="idcliente" id="idcliente2">
                <input type="text" class="form-control" name="nombre" id="nombre" maxlength="40" placeholder="Ingrese el nombre del cliente." autocomplete="off" required disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Dirección:</label>
                <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Ingrese la dirección." maxlength="40" disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Teléfono:</label>
                <input type="number" class="form-control" name="telefono" id="telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono." disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Email:</label>
                <input type="email" class="form-control" name="email" id="email" maxlength="50" placeholder="Ingrese el correo electrónico." disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Local(*):</label>
                <select id="idlocal" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC()" required disabled>
                  <option value="">- Seleccione -</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>RUC local(*):</label>
                <input type="number" class="form-control" id="local_ruc" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local." disabled>
              </div>
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <label>Descripción:</label>
                <input type="text" class="form-control" name="descripcion" id="descripcion2" maxlength="50" placeholder="Ingrese la descripción del cliente." autocomplete="off" disabled>
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalClientes();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarCliente" disabled><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 3 -->

    <!-- Modal 4 -->
    <div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">CARNET DE EXTRANJERÍA:</h4>
          </div>
          <div class="panel-body">
            <form name="formulario4" id="formulario4" method="POST">
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Tipo Documento(*):</label>
                <select class="form-control select-picker" name="tipo_documento" id="tipo_documento2" required>
                  <option value="">- Seleccione -</option>
                  <option value="CARNET DE EXTRANJERIA">CARNET DE EXTRANJERÍA</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Número(*):</label>
                <input type="number" class="form-control" name="num_documento" id="num_documento2" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20" placeholder="Ingrese el N° de documento." required>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Nombre(*):</label>
                <input type="hidden" name="idcliente" id="idcliente3">
                <input type="text" class="form-control" name="nombre" id="nombre2" maxlength="40" placeholder="Ingrese el nombre del cliente." autocomplete="off" required>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Dirección:</label>
                <input type="text" class="form-control" name="direccion" id="direccion2" placeholder="Ingrese la dirección." maxlength="40">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Teléfono:</label>
                <input type="number" class="form-control" name="telefono" id="telefono2" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono.">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Email:</label>
                <input type="email" class="form-control" name="email" id="email2" maxlength="50" placeholder="Ingrese el correo electrónico.">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Local(*):</label>
                <select id="idlocal2" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC2()" required>
                  <option value="">- Seleccione -</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>RUC local(*):</label>
                <input type="number" class="form-control" id="local_ruc2" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local." disabled>
              </div>
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <label>Descripción:</label>
                <input type="text" class="form-control" name="descripcion" id="descripcion3" maxlength="50" placeholder="Ingrese la descripción del cliente." autocomplete="off">
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalClientes2();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarCliente2"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 4 -->

    <!-- Modal 5 -->
    <div class="modal fade" id="myModal5" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">CLIENTE GENÉRICO:</h4>
          </div>
          <div class="panel-body">
            <form name="formulario5" id="formulario5" method="POST">
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Tipo Documento(*):</label>
                <select class="form-control select-picker" name="tipo_documento" id="tipo_documento3" required disabled>
                  <option value="DNI">DNI</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Número(*):</label>
                <input type="number" class="form-control" name="num_documento" id="num_documento3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" placeholder="Ingrese el N° de documento." required>
              </div>
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <label>Nombre(*):</label>
                <input type="hidden" name="idcliente" id="idcliente4">
                <input type="text" class="form-control" name="nombre" id="nombre3" maxlength="40" placeholder="Ingrese el nombre del cliente." autocomplete="off" value="PÚBLICO GENERAL" required disabled>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Local(*):</label>
                <select id="idlocal3" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC3()" required>
                  <option value="">- Seleccione -</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>RUC local(*):</label>
                <input type="number" class="form-control" id="local_ruc3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local." disabled>
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalClientes3();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarCliente3"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 5 -->

    <!-- Modal 6 -->
    <div class="modal fade" id="myModal6" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 85% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo">CREAR NUEVO CLIENTE:</h4>
          </div>
          <div class="panel-body">
            <form name="formulario6" id="formulario6" method="POST">
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Tipo Documento(*):</label>
                <select class="form-control select-picker" name="tipo_documento" id="tipo_documento4" onchange="changeValue(this);" required>
                  <option value="">- Seleccione -</option>
                  <option value="DNI">DNI</option>
                  <option value="RUC">RUC</option>
                  <option value="CEDULA">CEDULA</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Número(*):</label>
                <input type="number" class="form-control" name="num_documento" id="num_documento4" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" placeholder="Ingrese el N° de documento." required>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Nombre(*):</label>
                <input type="hidden" name="idcliente" id="idcliente4">
                <input type="text" class="form-control" name="nombre" id="nombre4" maxlength="40" placeholder="Ingrese el nombre del cliente." autocomplete="off" required>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Dirección:</label>
                <input type="text" class="form-control" name="direccion" id="direccion3" placeholder="Ingrese la dirección." maxlength="40">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Teléfono:</label>
                <input type="number" class="form-control" name="telefono" id="telefono3" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono.">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Email:</label>
                <input type="email" class="form-control" name="email" id="email3" maxlength="50" placeholder="Ingrese el correo electrónico.">
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>Local(*):</label>
                <select id="idlocal4" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC2()" required>
                  <option value="">- Seleccione -</option>
                </select>
              </div>
              <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <label>RUC local(*):</label>
                <input type="number" class="form-control" id="local_ruc4" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local." disabled>
              </div>
              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <label>Descripción:</label>
                <input type="text" class="form-control" name="descripcion" id="descripcion4" maxlength="50" placeholder="Ingrese la descripción del cliente." autocomplete="off">
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalClientes4();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarCliente4"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 6 -->

    <!-- Modal 7 -->
    <div class="modal fade" id="myModal7" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">PRECUENTA</h4>
              <h4 class="modal-title infotitulo" id="clienteFinal" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body">
            <form name="formulario7" id="formulario7" method="POST">
              <div class="col-lg-4 col-md-12 col-sm-12">
                <h4 class="modal-title infotitulo" style="margin: 0; margin-bottom: 10px; padding: 0; font-weight: bold;">DETALLE DE PRODUCTOS (Items: <span id="totalItems"></span>)</h4>
                <div id="tablaPrecuenta" class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 0; padding-top: 0px; background-color: white; max-height: 290px; overflow: auto;">
                  <table id="detallesProductosPrecuenta" class="table w-100" style="width: 100% !important;">
                    <thead>
                      <th style="text-align: start !important;">CÓDIGO</th>
                      <th style="width: 30%; min-width: 130px; white-space: nowrap; text-align: start !important;">NOMBRE</th>
                      <th>PRECIO</th>
                      <th>DESCUENTO</th>
                      <th>CANTIDAD</th>
                      <th>OPCIÓN</th>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0;">
                  <h4 class="modal-title infotitulo" style="margin: 0; margin-bottom: 10px; padding: 0; font-weight: bold;">COMENTARIO INTERNO (*):</h4>
                  <textarea type="text" class="form-control" id="comentario_interno" maxlength="40" rows="4" placeholder="Ingrese un comentario interno." autocomplete="off" required></textarea>
                </div>
                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0;">
                  <h4 class="modal-title infotitulo" style="margin: 0; margin-bottom: 10px; padding: 0; font-weight: bold;">COMENTARIO EXTERNO (*):</h4>
                  <textarea type="text" class="form-control" id="comentario_externo" maxlength="40" rows="4" placeholder="Ingrese un comentario externo." autocomplete="off" required></textarea>
                </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6" style="display: flex; flex-direction: column;">
                <h4 class="modal-title infotitulo totalFinal1" style="margin: 0; padding: 0; margin-bottom: 10px; font-weight: bold;"></h4>
                <div id="montoMetodoPago" style="max-height: 204px; overflow: auto;">
                </div>
                <div style="margin-bottom: 10px; display: flex; justify-content: start; flex-wrap: wrap; align-items: center; gap: 5px;">
                  <h4 class="modal-title infotitulo" style="margin: 0; margin-bottom: 10px; margin-top: 10px; padding: 0; font-weight: bold;">VUELTOS</h4>
                  <a href="#" data-toggle="popover" data-placement="right" title="Vuelto" data-html="true" data-content="Asegúrese que el vuelto sea <strong>mayor igual</strong> a 0." style="color: #418bb7; font-size: 16px;"><i class="fa fa-question-circle"></i></a>
                </div>
                <div style="padding: 10px; border-top: 1px solid #d2d6de; display: flex; justify-content: space-between; align-items: center;">
                  <h5 class="infotitulo" style="margin: 0; padding: 0;">VUELTO</h5>
                  <input type="number" id="vuelto" class="form-control" step="any" style="width: 120px; height: 30px;" value="0.00" disabled>
                </div>
              </div>
              <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 0; padding-top: 15px;">
                <div class="col-lg-4 col-md-4 col-sm-12" style="margin-bottom: 15px;">
                  <div style="border: 1px solid #d2d6de; border-radius: 10px; min-height: 39px; padding: 5px 10px; font-weight: bold; text-align: center; display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: center;">
                    <h5 class="infotitulo igvFinal" style="margin: 0; padding: 0; font-weight: bold; word-break: normal; text-wrap: nowrap;">IGV: S/</h5>
                    <select id="igv" style="height: 28px; padding: 4px 10px; width: 100px;" class="form-control" data-size="2" onchange="actualizarIGV(this);">
                      <option value="0.00">0.00</option>
                      <option value="0.18">0.18</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12" style="margin-bottom: 15px;">
                  <div style="border: 1px solid #d2d6de; border-radius: 10px; min-height: 39px; padding: 10px; font-weight: bold; text-align: center;">
                    <h5 class="infotitulo descuentoFinal" style="margin: 0; padding: 0; font-weight: bold;"></h5>
                  </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12" style="margin-bottom: 15px;">
                  <div style="border: 1px solid #d2d6de; border-radius: 10px; min-height: 39px; padding: 10px; font-weight: bold; text-align: center;">
                    <h5 class="infotitulo totalFinal2" style="margin: 0; padding: 0; font-weight: bold;"></h5>
                  </div>
                </div>
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
                <button class="btn btn-warning" type="button" onclick="mostrarDatosModalPrecuenta();" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                <button class="btn btn-bcp" type="submit" id="btnGuardarPrecuenta"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 7 -->

    <!-- Modal 8 -->
    <div class="modal fade" id="myModal8" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">VENTA EXITOSA, TICKET N° <span id="num_comprobante_final1"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">LISTADO DE PRECUENTAS</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">NUEVA PRECUENTA</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">REPORTE DE PRECUENTAS</button>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR TICKET</button>
              </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR PDF-A4</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 8 -->

    <!-- Modal 9 -->
    <div class="modal fade" id="myModal9" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">TICKET N° <span id="num_comprobante_final2"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR TICKET</button></a>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <a target="_blank" style="color: #333;"><button class="btn btn-secondary" style="width: 100%; text-align: center; font-weight: bold;" type="button">GENERAR PDF-A4</button></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 9 -->

    <!-- Modal 10 -->
    <div class="modal fade" id="myModal10" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 90% !important; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;"></h4>
            </div>
          </div>
          <div class="panel-body">
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 10 -->

    <!-- Modal 11 -->
    <div class="modal fade" id="myModal11" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content" style="background-color: #f2d150 !important;">
          <div class="modal-header" style="border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">MODIFICAR ESTADO DE LA VENTA N° <span id="num_comprobante_final3"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">INICIADO</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">ENTREGADO</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">POR ENTREGAR</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">EN TRANSCURSO</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">FINALIZADO</button>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6" style="padding: 0;">
              <div style="margin: 5px;">
                <button class="btn btn-secondary" style="font-weight: bold; width: 100%; text-align: center;" type="button">ANULADO</button>
              </div>
            </div>

            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; margin-top: 15px; padding: 0 !important;">
              <button class="btn btn-warning" type="button" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 11 -->

  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/venta.js"></script>
  <script src="scripts/jquery.gScrollingCarousel.js"></script>
  <script>
  </script>
<?php
}
ob_end_flush();
?>