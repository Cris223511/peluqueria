<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['reportes'] == 1) {
?>
    <style>
      @media (max-width: 991px) {
        .caja1 {
          padding-right: 0 !important;
        }

        .caja1 .contenedor {
          display: flex;
          flex-direction: column;
          justify-content: center;
          text-align: center;
          gap: 15px;
        }

        .caja1 .contenedor img {
          width: 25% !important;
        }

        #label {
          display: none;
        }
      }

      @media (max-width: 767px) {
        .botones {
          width: 100% !important;
        }
      }

      tbody td:nth-child(12) {
        white-space: nowrap !important;
      }

      #detallesProductosFinal thead,
      #detallesProductosFinal thead tr,
      #detallesProductosFinal thead th,
      #detallesProductosFinal tbody,
      #detallesProductosFinal tbody tr,
      #detallesProductosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      #detallesProductosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      #detallesProductosFinal tbody,
      #detallesProductosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      #detallesProductosFinal tbody tr td,
      #detallesProductosFinal tfoot tr td {
        border: none !important;
      }

      #detallesPagosFinal thead,
      #detallesPagosFinal thead tr,
      #detallesPagosFinal thead th,
      #detallesPagosFinal tbody,
      #detallesPagosFinal tbody tr,
      #detallesPagosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      #detallesPagosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      #detallesPagosFinal tbody,
      #detallesPagosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      #detallesPagosFinal tbody tr td,
      #detallesPagosFinal tfoot tr td {
        border: none !important;
      }

      @media (max-width: 991.50px) {
        .smallModal {
          width: 90% !important;
        }
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Reporte de cotizaciones generales</h1>
                <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Reporte de cotizaciones generales</strong>" data-html="true" data-content="Módulo para ver todas las cotizaciones (proformas) realizadas en <strong>su local</strong> y los detalles de los productos." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                <div class="box-tools pull-right"></div>
                <div class="panel-body table-responsive listadoregistros" style="overflow: visible; padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Fecha Inicial:</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Fecha Final:</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Tipo documento:</label>
                    <select id="tipoDocBuscar" name="tipoDocBuscar" class="form-control selectpicker" data-size="5">
                      <option value="">- Seleccione -</option>
                      <option value="COTIZACIÓN">COTIZACIÓN</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Local:</label>
                    <select id="localBuscar" name="localBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                    </select>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Usuario:</label>
                    <select id="usuarioBuscar" name="usuarioBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                    </select>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Estado:</label>
                    <select id="estadoBuscar" name="estadoBuscar" class="form-control selectpicker" data-size="5">
                      <option value="">- Seleccione -</option>
                      <option value="FINALIZADO">FINALIZADO</option>
                      <option value="ENTREGADO">ENTREGADO</option>
                      <option value="ANULADO">ANULADO</option>
                      <option value="INICIADO">INICIADO</option>
                      <option value="POR ENTREGAR">POR ENTREGAR</option>
                      <option value="EN TRANSCURSO">EN TRANSCURSO</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Método de pago:</label>
                    <select id="metodopagoBuscar" name="metodopagoBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                    </select>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Cliente:</label>
                    <input type="text" class="form-control" name="clienteBuscar" id="clienteBuscar" maxlength="100" placeholder="Ingrese el nombre del cliente." required>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>DNI / RUC:</label>
                    <input type="number" class="form-control" name="numDocBuscar" id="numDocBuscar" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="Ingrese el N° de documento." required>
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>N° ticket:</label>
                    <input type="number" class="form-control" name="numTicketBuscar" id="numTicketBuscar" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" placeholder="Ingrese el N° de ticket." required>
                  </div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label id="labelCustom">ㅤ</label>
                    <div style="display: flex; gap: 10px;">
                      <button style="width: 100%;" class="btn btn-bcp" onclick="buscar()">Buscar</button>
                      <button style="height: 32px;" class="btn btn-success" onclick="resetear()"><i class="fa fa-repeat"></i></button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th style="width: 1%;">Opciones</th>
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Cliente</th>
                      <th>Almacén</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Proforma (S/.)</th>
                      <th>Agregado por</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Cliente</th>
                      <th>Almacén</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Proforma (S/.)</th>
                      <th>Agregado por</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 75%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">COTIZACIÓN: <span id="nota_de_venta" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">CLIENTE: <span id="nombre_cliente" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">DIRECCIÓN CLIENTE: <span id="direccion_cliente" style="font-weight: 600;"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table id="detallesProductosFinal" class="table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th style="white-space: nowrap;">DESCRIPCIÓN DEL PRODUCTO</th>
                  <th style="white-space: nowrap;">CANTIDAD</th>
                  <th style="white-space: nowrap;">PRECIO UNITARIO</th>
                  <th style="white-space: nowrap;">DESCUENTO</th>
                  <th style="white-space: nowrap;">SUBTOTAL</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">IGV</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="igv_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_detalle"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table id="detallesPagosFinal" class="table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DE PAGOS</th>
                  <th>MONTO</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">VUELTO</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="vueltos_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_pagos"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center;">
              <h4 style="font-weight: bold;">ATENDIDO POR: <span id="atendido_venta" style="font-weight: 600;"></span></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal -->

  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/reporteProforma.js"></script>
<?php
}
ob_end_flush();
?>