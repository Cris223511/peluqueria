<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["cajas"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['cajas'] == 1) {
?>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Cierre de caja
                  <?php if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { ?>
                    <a href="../reportes/rptcajascerradas.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Cierre de caja</strong>" data-html="true" data-content="Módulo en donde se registran las cajas que ha sido cerradas desde el módulo de aperturas, en donde se podrá visualizar las ventas, montos, retiros y detalles de los productos que se vendieron en la caja <strong>de su local</strong> durante el rango de fecha en el que se aperturó y se cerró la caja." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
                <div class="panel-body table-responsive listadoregistros" style="overflow: visible; padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0;">
                    <label>Fecha Inicial:</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0;">
                    <label>Fecha Final:</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0;">
                    <label>Buscar por local:</label>
                    <select id="idlocal" name="idlocal" class="form-control selectpicker" data-live-search="true" data-size="5">
                      <option value="">- Seleccione -</option>
                    </select>
                  </div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" style="padding: 5px; margin: 0;">
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
                      <th>Caja</th>
                      <th>Almacén</th>
                      <th>Monto Inicial</th>
                      <th>Monto Total</th>
                      <th style="white-space: nowrap;">Cerrado por</th>
                      <th>Cargo</th>
                      <th style="white-space: nowrap;">Fecha apertura</th>
                      <th style="white-space: nowrap;">Fecha cierre</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Caja</th>
                      <th>Almacén</th>
                      <th>Monto Inicial</th>
                      <th>Monto Total</th>
                      <th>Cerrado por</th>
                      <th>Cargo</th>
                      <th>Fecha apertura</th>
                      <th>Fecha cierre</th>
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

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 75%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">
                DETALLES DE PRODUCTOS DE CIERRE DE CAJA<br>
                <span style="font-weight: 500 !important;">DEL</span> <span id="fecha_hora_caja"></span> <span style="font-weight: 500 !important;">HASTA EL</span> <span id="fecha_hora_cierre_caja"></span>
              </h4>
            </div>
          </div>
          <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding: 0 !important; height: max-content;">
            <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
              <table id="tbldetalles" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                <thead>
                  <th>PRODUCTO / SERVICIO</th>
                  <th>CÓDIGO</th>
                  <th>CANTIDAD</th>
                  <th style="white-space: nowrap;">P. UNITARIO</th>
                  <th>DESCUENTO</th>
                  <th style="white-space: nowrap;">P. TOTAL</th>
                  <th>FECHA REGISTRO</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <th>PRODUCTO / SERVICIO</th>
                  <th>CÓDIGO</th>
                  <th>CANTIDAD</th>
                  <th>P. UNITARIO</th>
                  <th>DESCUENTO</th>
                  <th>P. TOTAL</th>
                  <th>FECHA REGISTRO</th>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important;">
            <button class="btn btn-warning" type="button" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Regresar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal 1 -->
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/cierres6.js"></script>
<?php
}
ob_end_flush();
?>