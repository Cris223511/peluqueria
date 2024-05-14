<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['comisiones'] == 1) {
?>
    <style>
      #detallesProductosComisiones thead,
      #detallesProductosComisiones thead tr,
      #detallesProductosComisiones thead th,
      #detallesProductosComisiones tbody,
      #detallesProductosComisiones tbody tr,
      #detallesProductosComisiones tbody th {
        border: none;
        background-color: white;
        font-size: 14px;
        text-align: center;
      }

      #detallesProductosComisiones input,
      #detallesProductosComisiones select {
        height: 30px !important;
        text-align: center;
      }

      #detallesProductosComisiones select option {
        text-align: left !important;
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
                <h1 class="box-title">Comisiones de empleados
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Comisiones de empleados</strong>" data-html="true" data-content="Módulo para registrar las comisiones de los empleados del local.<br><br><strong>Nota 1:</strong> Puedes comisionar al empleado las veces que desee, también puede modificar y eliminar las comisiones de los empleados.<br><br><strong>Nota 2:</strong> Solo se listarán los productos que se comisionaron a los empleados desde las ventas y del local del empleado (por ejemplo, si el empleado es de <strong>los Olivos</strong>, se listará los productos que se comisionaron en las ventas desde <strong>los Olivos</strong>, ya que las ventas son por locales)." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 30%; min-width: 150px; white-space: nowrap;">Nombres</th>
                    <th>Comisión total</th>
                    <th style="width: 30%; min-width: 200px; white-space: nowrap;">Almacén</th>
                    <th style="white-space: nowrap;">Tipo Doc.</th>
                    <th style="white-space: nowrap;">Número Doc.</th>
                    <th style="white-space: nowrap;">Agregado por</th>
                    <th>Cargo</th>
                    <th style="white-space: nowrap;">Fecha y hora comisión</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombres</th>
                    <th>Comisión total</th>
                    <th>Almacén</th>
                    <th>Tipo Doc.</th>
                    <th>Número Doc.</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora comisión</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">CREAR COMISIÓN</h4>
              <h4 class="modal-title infotitulo trabajador_comisionar" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <form name="formulario" id="formulario" method="POST">
            <div class="panel-body">
              <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px 0x; background-color: white; overflow: auto;">
                <table id="detallesProductosComisiones" class="table w-100" style="width: 100% !important;">
                  <thead>
                    <th>PRODUCTOS / SERVICIOS</th>
                    <th>COMISIÓN</th>
                    <th>OPCIONES</th>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important; text-align: left !important;">
              <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalComision();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
              <button class="btn btn-bcp" type="submit" id="btnGuardarComision"><i class="fa fa-save"></i> Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Fin modal 1 -->

    <!-- Modal 2 -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">DETALLES DE COMISIÓN</h4>
              <h4 class="modal-title infotitulo trabajador_comisionar" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body table-responsive listadoregistros" style="overflow: visible; padding: 10px; padding-bottom: 0px; margin-bottom: 0px;">
            <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12" style="padding: 5px; margin: 0;">
              <label>Fecha Inicial:</label>
              <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
            </div>
            <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12" style="padding: 5px; margin: 0;">
              <label>Fecha Final:</label>
              <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding: 5px; margin: 0;">
              <label id="labelCustom">ㅤ</label>
              <div style="display: flex; gap: 10px;">
                <button style="width: 100%;" class="btn btn-bcp" onclick="buscarComision()">Buscar</button>
                <button style="height: 32px;" class="btn btn-success" onclick="resetear()"><i class="fa fa-repeat"></i></button>
              </div>
            </div>
          </div>
          <div class="panel-body" style="background-color: #ecf0f5 !important; padding: 0 !important; height: max-content;">
            <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
              <table id="tbldetalles" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                <thead>
                  <th>PRODUCTOS / SERVICIOS</th>
                  <th>CLIENTE</th>
                  <th>PERSONAL</th>
                  <th>COMISIÓN</th>
                  <th>TIPO</th>
                  <th>FECHA Y HORA</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <th>PRODUCTOS / SERVICIOS</th>
                  <th>CLIENTE</th>
                  <th>PERSONAL</th>
                  <th>COMISIÓN</th>
                  <th>TIPO</th>
                  <th>FECHA Y HORA</th>
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
    <!-- Fin Modal 2 -->
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/comisiones.js"></script>
<?php
}
ob_end_flush();
?>