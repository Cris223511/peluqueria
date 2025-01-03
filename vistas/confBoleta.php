<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['perfilu'] == 1) {
?>
    <style>
      .marco {
        background-color: white;
        border-top: 3px #002a8e solid !important;
      }
    </style>

    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Configuración de boletas</h1>
                <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Configuración de boletas</strong>" data-html="true" data-content="Módulo para configurar los datos de la empresa que aparecerán en los tickets y reportes A4 (ventas, proformas, compras y los flujos de cajas)." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                <div class="box-tools pull-right">
                </div>
              </div>
            </div>
            <div class="d-flex">
              <div class="box" style="border-top: none !important">
                <div class="panel-body marco" id="formularioregistros">
                  <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data">
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>Desarrollado por:</label>
                      <input type="hidden" name="idreporte" id="idreporte">
                      <input type="text" class="form-control" name="auspiciado" id="auspiciado" maxlength="25" placeholder="Ingrese el nombre de la empresa desarrolladora.">
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>RUC(*):</label>
                      <input type="number" class="form-control" name="ruc" id="ruc" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="Ingrese el RUC." required>
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>Dirección:</label>
                      <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Ingrese la dirección." maxlength="95">
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>Teléfono:</label>
                      <input type="number" class="form-control" name="telefono" id="telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono.">
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>Email:</label>
                      <input type="email" class="form-control" name="email" id="email" maxlength="50" placeholder="Ingrese el email.">
                    </div>
                    <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                      <label>Tipo de cambio(*):</label>
                      <select name="moneda" id="moneda" class="form-control" required onchange="manejarTipoCambio()">
                        <option value="">- Seleccione -</option>
                        <option value="soles">Soles</option>
                        <option value="dolares">Dólares</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                      <label>Valor de conversión(*):<a href="#" data-toggle="popover" data-placement="top" title="<strong>Valor de conversión</strong>" data-html="true" data-content="Digite el valor de la conversión de soles a dólares, es decir, cuánto vale un sol en un dólar, para que se haga las conversiones de los precios en soles a dólares (el valor de conversión va a dividir el precio que está en soles). Sólo aplicable para el tipo de cambio en dólares." style="color: #002a8e; font-size: 18px; position: absolute; top: -3px;">&nbsp;<i class="fa fa-question-circle"></i></a></label>
                      <input type="number" class="form-control" name="cambio" id="cambio" step="any" placeholder="Ingrese el valor de conversión." required>
                    </div>
                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 0;">
                      <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                  </form>
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
  <script type="text/javascript" src="scripts/confBoleta.js"></script>
<?php
}
ob_end_flush();
?>