<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['almacen'] == 1) {
?>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Unidades de medida
                  <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)">
                    <i class="fa fa-plus-circle"></i> Agregar
                  </button>
                  <?php if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { ?>
                    <a href="../reportes/rptmedidas.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Medidas</strong>" data-html="true" data-content="Módulo para registrar las medidas de los productos.<br><br><strong>Nota:</strong> Las medidas no están divididos por local, de la cual, las medidas que registre serán visibles y utilizados por los trabajadores <strong>de todos los locales</strong> (solo puede editar, anular y eliminar las medidas que ustéd agrega y no el de los demás)." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 20%; min-width: 260px;">Nombre</th>
                    <th style="width: 30%; min-width: 280px;">Descripción</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th style="width: 20%; min-width: 260px;">Nombre</th>
                    <th>Descripción</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" style="height: max-content;" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Medida(*):</label>
                    <input type="hidden" name="idmedida" id="idmedida">
                    <input type="text" class="form-control" name="titulo" id="titulo" maxlength="40" placeholder="Ingrese el nombre de la medida." autocomplete="off" required>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Descripción:</label>
                    <textarea type="text" class="form-control" name="descripcion" id="descripcion" maxlength="10000" rows="4" placeholder="Ingrese una descripción."></textarea>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/medidas1.js"></script>
<?php
}
ob_end_flush();
?>