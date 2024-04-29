<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['personas'] == 1) {
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
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Comisiones de empleados</strong>" data-html="true" data-content="Módulo para registrar las comisiones de los empleados del local.<br><strong>Nota:</strong> Puedes comisionar al empleado las veces que desee, también puede modificar y eliminar las comisiones de los empleados." style="color: #002a8e; font-size: 18px;"><i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 30%; min-width: 150px; white-space: nowrap;">Nombres</th>
                    <th>Cargo</th>
                    <th>Comisión total</th>
                    <th style="width: 30%; min-width: 200px; white-space: nowrap;">Ubicación del local</th>
                    <th style="white-space: nowrap;">Tipo Doc.</th>
                    <th style="white-space: nowrap;">Número Doc.</th>
                    <th style="white-space: nowrap;">Agregado por</th>
                    <th>Cargo</th>
                    <th style="white-space: nowrap;">Fecha y hora</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombres</th>
                    <th>Cargo</th>
                    <th>Comisión total</th>
                    <th>Ubicación del local</th>
                    <th>Tipo Doc.</th>
                    <th>Número Doc.</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
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
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow: auto;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">CREAR COMISIÓN</h4>
              <h4 class="modal-title infotitulo" id="trabajador_comisionar" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px 0x; background-color: white; overflow: auto;">
              <table id="detallesProductosComisiones" class="table w-100" style="width: 100% !important;">
                <thead>
                  <th>PRODUCTOS / SERVICIOS</th>
                  <th>COMISIONES</th>
                  <th>OPCIONES</th>
                </thead>
                <tbody>
                  <tr>
                    <td style="width: 60%; min-width: 130px; white-space: nowrap;">
                      <input type="text" class="form-control" disabled>
                    </td>
                    <td style="width: 100%; gap: 5px; min-width: 130px; white-space: nowrap; display: flex; justify-content: start">
                      <input type="hidden" class="form-control" name="idpersonal[]" id="idpersonal[]">
                      <input type="hidden" class="form-control" name="idarticulo[]" id="idarticulo[]">
                      <input type="number" class="form-control" name="comision[]" id="comision[]" lang="en-US" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="1" required>
                      <select id="tipo[]" name="tipo[]" class="form-control" required>
                        <option value="1">S/.</option>
                        <option value="2">%</option>
                      </select>
                    </td>
                    <td style="width: 10%; min-width: 130px; white-space: nowrap;">
                      <div style="display: flex; justify-content: center;">
                        <button class="btn btn-danger" style="height: 35px;" onclick="eliminar()"><i class="fa fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0 !important; padding: 0 !important;">
              <button class="btn btn-warning" type="button" data-dismiss="modal" onclick="limpiarModalComision();"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
              <button class="btn btn-bcp" type="button" data-dismiss="modal" id="btnGuardarComision"><i class="fa fa-save"></i> Guardar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 1 -->
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