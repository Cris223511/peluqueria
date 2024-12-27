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

    <style>
      .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
        margin: 0 10px;
        margin-bottom: 8px;
      }

      .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 10px 12px;
        transition: 0.3s;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 0px !important;
      }

      .tab button:hover {
        background-color: #ddd;
      }

      .tab button.active {
        background-color: #ccc;
        font-weight: 600;
      }

      .tabcontent {
        display: none;
        -webkit-animation: fadeEffect .7s;
        animation: fadeEffect .7s;
      }

      /* Fade in tabs */
      @-webkit-keyframes fadeEffect {
        from {
          opacity: 0;
        }

        to {
          opacity: 1;
        }
      }

      @keyframes fadeEffect {
        from {
          opacity: 0;
        }

        to {
          opacity: 1;
        }
      }

      #tablaErrores thead,
      #tablaErrores thead tr,
      #tablaErrores thead th,
      #tablaErrores tbody,
      #tablaErrores tbody tr,
      #tablaErrores tbody th {
        border: none;
        background-color: white;
        font-size: 14px;
        text-align: center;
      }

      .spinner-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      }

      .spinner {
        border: 9px solid #f3f3f3;
        border-top: 9px solid #002a8e;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1.2s linear infinite;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
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
                  <button class="btn btn-success" id="btndescargar" onclick="descargarPlantilla()"><i class="fa fa-download"></i> Descargar plantilla</button>
                  <button class="btn btn-warning" id="btnimportar" onclick="importarProductos()"><i class="fa fa-upload"></i> Importar productos</button>
                  <input type="file" id="fileInput" accept=".xlsx,.xls" style="display: none;" onchange="handleFile(event)">
                  <a data-toggle="modal" href="#myModal">
                    <button id="btninformacion" class="btn btn-info">
                      <i class="fa fa-info-circle"></i> Información de datos
                    </button>
                  </a>
                  <?php // } 
                  ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Carga masiva de productos</strong>" data-html="true" data-content="Este módulo permite la importación masiva de productos al sistema mediante un archivo Excel. Para utilizarlo, descargue la plantilla de Excel haciendo clic en el botón <strong>Descargar plantilla</strong>. Complete los campos requeridos en la plantilla y, una vez listo, utilice el botón <strong>Importar productos</strong> para cargar los datos al sistema. El botón <strong>Información de datos</strong> proporciona detalles sobre los nombres de los campos que requieran de un ID, como <em>idlocal</em>, <em>idmedida</em>, <em>idmarca</em> e <em>idcategoria</em>." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right"></div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th style="width: 1%;">Estado</th>
                      <th>Imagen</th>
                      <th style="width: 20%; min-width: 160px;">Nombre</th>
                      <th style="width: 20%; min-width: 60px;">IDMEDIDA</th>
                      <th style="width: 20%; min-width: 60px;">IDCATEGORIA</th>
                      <th style="width: 20%; min-width: 60px;">IDLOCAL</th>
                      <th style="width: 20%; min-width: 60px;">IDMARCA</th>
                      <th style="width: 20%; min-width: 140px;">C. producto</th>
                      <th style="width: 20%; min-width: 100px;">Stock</th>
                      <th style="width: 20%; min-width: 100px;">Stock min.</th>
                      <th style="width: 20%; min-width: 100px;">P. venta</th>
                      <th style="width: 20%; min-width: 100px;">P. compra</th>
                      <th style="width: 20%; min-width: 100px;">Ganancia</th>
                      <th style="width: 20%; min-width: 100px;">P. venta por mayor</th>
                      <th style="width: 20%; min-width: 100px;">Comisión</th>
                      <th style="width: 20%; min-width: 160px;">C. de barra</th>
                      <th style="width: 20%; min-width: 180px;">Descripción</th>
                      <th style="width: 20%; min-width: 160px;">Talla</th>
                      <th style="width: 20%; min-width: 160px;">Color</th>
                      <th style="width: 20%; min-width: 100px;">Peso</th>
                      <th>Fecha emisión</th>
                      <th>Fecha ven.</th>
                      <th style="width: 20%; min-width: 160px;">Item 1</th>
                      <th style="width: 20%; min-width: 160px;">Item 2</th>
                      <th style="width: 20%; min-width: 160px;">Agregado por</th>
                      <th style="width: 20%; min-width: 160px;">Cargo</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Estado</th>
                      <th>Imagen</th>
                      <th>Nombre</th>
                      <th>IDMEDIDA</th>
                      <th>IDCATEGORIA</th>
                      <th>IDLOCAL</th>
                      <th>IDMARCA</th>
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
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="box-header with-border" id="section_btnguardar" style="border-top: 3px #002a8e solid !important;">
                <h1 class="box-title">
                  <?php // if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { 
                  ?>
                  <button class="btn btn-bcp" id="btnguardar" onclick="validarYGuardarProductos()"><i class="fa fa-save"></i> Guardar productos</button>
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

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 70%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo" style="text-align: center; font-weight: bold;">INFORMACIÓN DE DATOS</h4>
          </div>
          <div class="panel-body">
            <!-- Tabs -->
            <div class="tab">
              <button class="tablinks active" onclick="changeTables(event, 'categorias')">Categorías</button>
              <button class="tablinks" onclick="changeTables(event, 'locales')">Locales</button>
              <button class="tablinks" onclick="changeTables(event, 'marcas')">Marcas</button>
              <button class="tablinks" onclick="changeTables(event, 'medidas')">Medidas</button>
            </div>
            <!-- Tab content -->
            <div id="categorias" class="tabcontent">
              <div class="modal-body table-responsive">
                <table id="tbllistado_categorias" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th>ID CATEGORÍA</th>
                    <th style="width: 20%; min-width: 180px;">TÍTULO</th>
                    <th style="width: 30%; min-width: 280px;">DESCRIPCIÓN</th>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <th>ID CATEGORÍA</th>
                    <th>TÍTULO</th>
                    <th>DESCRIPCIÓN</th>
                  </tfoot>
                </table>
              </div>
            </div>
            <div id="locales" class="tabcontent" style="display: none;">
              <div class="modal-body table-responsive">
                <table id="tbllistado_locales" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th>ID LOCAL</th>
                    <th>LOGO</th>
                    <th>UBICACIÓN DEL LOCAL</th>
                    <th style="white-space: nowrap;">N° RUC</th>
                    <th>EMPRESA DEL LOCAL</th>
                    <th style="width: 40%; min-width: 280px; white-space: nowrap;">DESCRIPCIÓN DEL LOCAL</th>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <th>ID LOCAL</th>
                    <th>LOGO</th>
                    <th>UBICACIÓN DEL LOCAL</th>
                    <th>N° RUC</th>
                    <th>EMPRESA DEL LOCAL</th>
                    <th>DESCRIPCIÓN DEL LOCAL</th>
                  </tfoot>
                </table>
              </div>
            </div>
            <div id="marcas" class="tabcontent" style="display: none;">
              <div class="modal-body table-responsive">
                <table id="tbllistado_marcas" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th>ID MARCA</th>
                    <th style="width: 20%; min-width: 180px;">TÍTULO</th>
                    <th style="width: 30%; min-width: 280px;">DESCRIPCIÓN</th>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <th>ID MARCA</th>
                    <th>TÍTULO</th>
                    <th>DESCRIPCIÓN</th>
                  </tfoot>
                </table>
              </div>
            </div>
            <div id="medidas" class="tabcontent" style="display: none;">
              <div class="modal-body table-responsive">
                <table id="tbllistado_medidas" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th>ID MEDIDA</th>
                    <th style="width: 20%; min-width: 180px;">TÍTULO</th>
                    <th style="width: 30%; min-width: 280px;">DESCRIPCIÓN</th>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <th>ID MEDIDA</th>
                    <th>TÍTULO</th>
                    <th>DESCRIPCIÓN</th>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important;">
            <button class="btn btn-warning" type="button" data-dismiss="modal">
              <i class="fa fa-arrow-circle-left"></i> Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal 1 -->

    <!-- Modal 2 -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 70%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title infotitulo" style="text-align: center; font-weight: bold;">INFORMACIÓN DE ERRORES</h4>
          </div>
          <div class="modal-body">
            <div class="table-responsive" style="background-color: white; overflow: auto;">
              <table id="tablaErrores" class="table w-100" style="width: 100% !important;">
                <thead>
                  <tr>
                    <th>N°</th>
                    <th>COLUMNA</th>
                    <th>MENSAJE</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Los errores serán insertados dinámicamente aquí -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f2d150 !important; border-top: 2px solid #C68516 !important;">
            <button class="btn btn-warning" type="button" data-dismiss="modal">
              <i class="fa fa-arrow-circle-left"></i> Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal 2 -->

    <!-- Spinner -->
    <div id="spinnerLoader" style="display: none;">
      <div class="spinner-background">
        <div class="spinner"></div>
      </div>
    </div>
    <!-- Fin Spinner -->

  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script>
    function changeTables(e, table) {
      var i, tabcontent, tablinks;

      tabcontent = document.getElementsByClassName("tabcontent");
      tablinks = document.getElementsByClassName("tablinks");

      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }

      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }

      document.getElementById(table).style.display = "block";
      e.currentTarget.className += " active";
    }

    document.getElementById("categorias").style.display = "block";
  </script>
  <script type="text/javascript" src="scripts/carga_masiva.js"></script>
<?php
}
ob_end_flush();
?>