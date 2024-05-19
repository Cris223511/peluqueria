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
                <h1 class="box-title">Aperturas de caja
                  <?php // if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin") { 
                  ?>
                  <button class="btn btn-bcp" id="btnagregar" onclick="validarCaja();">
                    <i class="fa fa-plus-circle"></i> Crear nueva caja
                  </button>
                  <?php // } 
                  ?>
                  <?php if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { ?>
                    <a href="../reportes/rptcajas.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Aperturas de caja</strong>" data-html="true" data-content="Módulo en donde se registran las cajas para que sean utilizadas en las ventas, proformas y compras.<br><br><strong>Nota:</strong> Sólo puede agregar 1 caja por local. También, todos los trabajadores de su local puede editar y cerrar la caja de su local.<br><br><strong>Restricciones:</strong> El monto de la caja puede ser editado sólo 3 veces y mientras tanto no se haya realizado una venta con la caja. También no puede editar el monto si la caja está cerrada, de la cuál tendría que abrirlo de nuevo." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
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
                    <select id="idlocal2" name="idlocal2" class="form-control selectpicker" data-live-search="true" data-size="5">
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
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                    <thead>
                      <th style="width: 1%;">Opciones</th>
                      <th>Caja</th>
                      <th>Almacén</th>
                      <th>Monto Inicial</th>
                      <th>Monto Total</th>
                      <th style="white-space: nowrap;">Empleado</th>
                      <th>Cargo</th>
                      <th style="white-space: nowrap;">Fecha apertura</th>
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
                      <th>Empleado</th>
                      <th>Cargo</th>
                      <th>Fecha apertura</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="panel-body" style="height: max-content;" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-6 col-md-6 col-sm-12">
                    <label>Empleado(*):</label>
                    <select name="idusuario" id="idusuario" class="form-control" required>
                      <option value="">- Seleccione -</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-12">
                    <label>Local(*):</label>
                    <select name="idlocal" id="idlocal" class="form-control" required>
                      <option value="">- Seleccione -</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-12">
                    <label>Caja(*):</label>
                    <input type="hidden" name="idcaja" id="idcaja">
                    <input type="text" class="form-control" name="titulo" id="titulo" maxlength="40" placeholder="Ingrese el nombre de la caja." autocomplete="off" required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-12">
                    <label>Monto inicial(*):</label>
                    <div style="display: flex;">
                      <input type="number" class="form-control" name="monto" id="monto" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" placeholder="Ingrese el monto inicial de la caja." required>
                      <a id="desbloquearMonto" class="btn btn-bcp" style="display: flex; align-items: center;"><i class="fa fa-lock"></i></a>
                    </div>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12">
                    <label>Comentario:</label>
                    <textarea type="text" class="form-control" name="descripcion" id="descripcion" maxlength="150" rows="4" placeholder="Ingrese un comentario."></textarea>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12">
                    <button class="btn btn-warning" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                    <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 55%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">APERTURA DE CAJA</h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 15px; background-color: white; overflow: auto; font-size: 16px; overflow: auto;">
              <div style="display: flex; gap: 5px; flex-direction: column;">
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">ALMACÉN / TIENDA:</div>
                  <div class="nowrap-cell" id="local_caja"></div>
                </div>
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">EMPLEADO:</div>
                  <div class="nowrap-cell" id="usuario_caja"></div>
                </div>
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">FECHA DE CREACIÓN:</div>
                  <div class="nowrap-cell" id="fecha_caja"></div>
                </div>
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">HORA:</div>
                  <div class="nowrap-cell" id="hora_caja"></div>
                </div>
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">MONTO:</div>
                  <div class="nowrap-cell" id="monto_caja"></div>
                </div>
                <div style="display: flex; justify-content: start;">
                  <div style="width: 200px; min-width: 200px; font-weight: bold;">COMENTARIO:</div>
                  <div style="min-width: 300px;" id="descripcion_caja"></div>
                </div>
              </div>
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
  <script type="text/javascript" src="scripts/aperturas6.js"></script>
<?php
}
ob_end_flush();
?>