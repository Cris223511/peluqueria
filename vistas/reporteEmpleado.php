<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['reportesE'] == 1) {
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

      td {
        height: 30.84px !important;
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
                <h1 class="box-title">Reporte de ventas por empleados</h1>
                <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Reporte de ventas por empleados</strong>" data-html="true" data-content="Módulo para ver todos los empleados que recibieron productos comisionados desde el módulo de ventas." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
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
                      <option value="NOTA DE VENTA">NOTA DE VENTA</option>
                      <option value="FACTURA">FACTURA</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Local:</label>
                    <select id="localBuscar" name="localBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                    </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Usuario:</label>
                    <select id="usuarioBuscar" name="usuarioBuscar" class="form-control selectpicker" data-live-search="true" data-size="5">
                    </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
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
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Cliente</th>
                      <th>Empleado</th>
                      <th>Producto / servicio</th>
                      <th>Almacén</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Venta (S/.)</th>
                      <th>Agregado por</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Cliente</th>
                      <th>Empleado</th>
                      <th>Producto / servicio</th>
                      <th>Almacén</th>
                      <th>Caja</th>
                      <th>Documento</th>
                      <th>Número Ticket</th>
                      <th>Total Venta (S/.)</th>
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

  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/reporteEmpleado.js"></script>
<?php
}
ob_end_flush();
?>