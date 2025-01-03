<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['servicios'] == 1) {
?>
    <style>
      #formulario .form-control,
      #formulario .form-control button {
        height: 45px !important;
        font-size: 16px !important;
        align-content: center;
      }

      #camera video {
        width: 250px;
        height: auto;
        border-radius: 15px;
        margin-top: 10px;
      }

      #camera canvas.drawingBuffer {
        height: auto;
        position: absolute;
      }
    </style>

    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Servicios
                  <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)">
                    <i class="fa fa-plus-circle"></i> Agregar
                  </button>
                  <?php if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { ?>
                    <a href="../reportes/rptservicios.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Servicios</strong>" data-html="true" data-content="Módulo en donde se registran los servicios para que sean utilizados en las ventas, proformas y compras.<br><br><strong>Nota:</strong> El código de servicio incrementa automáticamente y no se puede repetir. También, los servicios que usted registre serán visible y utilizados por todos (solo puede editar, anular y eliminar los servicios que ustéd agrega y no el de los demás)." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 20%; min-width: 260px;">Nombre</th>
                    <th>Código servicio</th>
                    <th>C. de barra</th>
                    <th style="width: 30%; min-width: 350px;">Descripción</th>
                    <th>Costo</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombre</th>
                    <th>Código servicio</th>
                    <th>C. de barra</th>
                    <th>Descripción</th>
                    <th>Costo</th>
                    <th>Agregado por</th>
                    <th>Cargo</th>
                    <th>Fecha y hora</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" style="height: max-content;" id="formularioregistros">
                <form name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Servicio(*):</label>
                    <input type="hidden" name="idservicio" id="idservicio">
                    <input type="text" class="form-control" name="titulo" id="titulo" maxlength="40" placeholder="Ingrese el nombre del servicio." autocomplete="off" required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Código de servicio(*):</label>
                    <input type="text" class="form-control" name="codigo" id="codigo" oninput="onlyNumbersAndMaxLenght(this)" onblur="formatearNumeroCorrelativo(this)" maxlength="10" placeholder="Ingrese el código de servicio." required />
                  </div>
                  <div class="form-group col-lg-6 col-md-12 col-sm-12">
                    <div>
                      <label>Código de barra:</label>
                      <input type="text" class="form-control" name="codigo_barra" id="codigo_barra" maxlength="13" placeholder="Ingrese el código de barra.">
                    </div>
                    <div style="margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;">
                      <button class="btn btn-info" type="button" onclick="generar()">Generar</button>
                      <button class="btn btn-warning" type="button" onclick="imprimir()">Imprimir</button>
                      <button class="btn btn-danger" type="button" onclick="borrar()">Borrar</button>
                      <button class="btn btn-success btn1" type="button" onclick="escanear()">Escanear</button>
                      <button class="btn btn-danger btn2" type="button" onclick="detenerEscaneo()">Detener</button>
                    </div>
                    <div id="print" style="overflow-y: hidden;">
                      <img id="barcode">
                    </div>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12" style="padding: 0; margin: 0;">
                      <div style="display: flex; justify-content: start;">
                        <div id="camera"></div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label>Costo de servicio(*):</label>
                    <input type="number" class="form-control" name="costo" id="costo" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" placeholder="Ingrese el costo de servicio." required>
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
  <script type="text/javascript" src="../public/js/JsBarcode.all.min.js"></script>
  <script type="text/javascript" src="../public/js/jquery.PrintArea.js"></script>
  <script type="text/javascript" src="scripts/servicios3.js"></script>
<?php
}
ob_end_flush();
?>