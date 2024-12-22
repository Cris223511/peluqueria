<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['carga_masiva'] == 1) {
?>
    <style>
      @media (max-width: 991px) {
        .contenedor_articulos {
          display: flex;
          flex-direction: column-reverse !important;
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

      .contenedor_articulos .form-control,
      .contenedor_articulos .form-control button {
        height: 45px !important;
        font-size: 16px !important;
        align-content: center;
      }

      .contenedor_articulos textarea.form-control {
        height: fit-content !important;
        font-size: 16px !important;
        align-content: start !important;
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Carga masiva de productos
                  <?php // if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { 
                  ?>
                  <button class="btn btn-success" id="btndescargar" onclick="mostrarform(true)"><i class="fa fa-download"></i> Descargar plantilla</button>
                  <button class="btn btn-warning" id="btnimportar" onclick="mostrarform(true)"><i class="fa fa-download"></i> Importar productos</button>
                  <?php // } 
                  ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Carga masiva de productos</strong>" data-html="true" data-content="Este módulo permite la importación masiva de productos al sistema mediante un archivo Excel. Para utilizarlo, descargue la plantilla de Excel haciendo clic en el botón <strong>Descargar plantilla</strong>. Complete los campos requeridos en la plantilla y, una vez listo, utilice el botón <strong>Importar productos</strong> para cargar los datos al sistema." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right"></div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th style="width: 1%;">Opciones</th>
                      <th>Imagen</th>
                      <th style="width: 20%; min-width: 180px;">Nombre</th>
                      <th style="white-space: nowrap;">U. medida</th>
                      <th>Categoría</th>
                      <th style="width: 15%; min-width: 180px;">Almacén</th>
                      <th>Marca</th>
                      <th>C. producto</th>
                      <th>Stock</th>
                      <th>Stock min.</th>
                      <th>P. venta</th>
                      <th>P. compra</th>
                      <th>Ganancia</th>
                      <th>P. venta por mayor</th>
                      <th>Comisión</th>
                      <th>C. de barra</th>
                      <th style="width: 20%; min-width: 180px;">Descripción</th>
                      <th style="width: 20%; min-width: 160px;">Talla</th>
                      <th style="width: 20%; min-width: 160px;">Color</th>
                      <th>Peso</th>
                      <th>Fecha emisión</th>
                      <th>Fecha ven.</th>
                      <th style="width: 20%; min-width: 160px;">Item 1</th>
                      <th style="width: 20%; min-width: 160px;">Item 2</th>
                      <th>Agregado por</th>
                      <th>Cargo</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Imagen</th>
                      <th>Nombre</th>
                      <th>U. medida</th>
                      <th>Categoría</th>
                      <th>Almacén</th>
                      <th>Marca</th>
                      <th>C. producto</th>
                      <th>Stock</th>
                      <th>Stock min.</th>
                      <th>P. venta</th>
                      <th>P. compra</th>
                      <th>Ganancia</th>
                      <th>P. venta por mayor</th>
                      <th>Comisión</th>
                      <th>C. de barra</th>
                      <th>Descripción</th>
                      <th>Talla</th>
                      <th>Color</th>
                      <th>Peso</th>
                      <th>Fecha emisión</th>
                      <th>Fecha ven.</th>
                      <th>Item 1</th>
                      <th>Item 2</th>
                      <th>Agregado por</th>
                      <th>Cargo</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="box-header with-border" style="border-top: 3px #002a8e solid !important;">
                <h1 class="box-title">
                  <?php // if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { 
                  ?>
                  <button class="btn btn-bcp" id="btnguardar" onclick="mostrarform(true)"><i class="fa fa-save"></i> Guardar productos</button>
                  <?php // } 
                  ?>
                </h1>
                <div class="box-tools pull-right"></div>
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
  <script type="text/javascript" src="scripts/carga_masiva.js"></script>
<?php
}
ob_end_flush();
?>