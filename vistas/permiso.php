<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['acceso'] == 1) {
?>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Permisos <button class="btn btn-success" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-plus-circle"></i> Agregar</button></h1>
                <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Permisos</strong>" data-html="true" data-content="Son los permisos que están disponibles en el sistema y sean controlados para la visualización de los módulos para cada trabajador del sistema." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                  <thead>
                    <th style="width: 20%; min-width: 260px;">Nombre</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th style="width: 20%; min-width: 260px;">Nombre</th>
                  </tfoot>
                </table>
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
  <script type="text/javascript" src="scripts/permiso2.js"></script>
<?php
}
ob_end_flush();
?>