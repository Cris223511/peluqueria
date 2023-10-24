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
    <style>
      @media (max-width: 991px) {
        .caja1 {
          padding-right: 0 !important;
        }

        .caja1 .contenedor {
          display: flex;
          flex-direction: column;
          justify-content: center;
          text-align: center;
          gap: 15px;
        }

        .caja1 .contenedor img {
          width: 25% !important;
        }
      }

      @media (max-width: 767px) {
        .botones {
          width: 100% !important;
        }
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Productos
                  <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)"><i class="fa fa-plus-circle"></i> Agregar</button>
                  <a href="../reportes/rptarticulos.php" target="_blank"><button class="btn btn-secondary" style="color: black !important;"><i class="fa fa-clipboard"></i> Reporte</button></a>
                </h1>
                <div class="box-tools pull-right"></div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                  <thead>
                    <th>Opciones</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th style="width: 20%; min-width: 220px; white-space: nowrap;">Ubicación del local</th>
                    <th>Marca</th>
                    <th style="white-space: nowrap;">C. producto</th>
                    <th style="white-space: nowrap;">Stock normal</th>
                    <th style="white-space: nowrap;">Stock mínimo</th>
                    <th style="white-space: nowrap;">Precio de venta</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Ubicación del local</th>
                    <th>Marca</th>
                    <th>C. producto</th>
                    <th>Stock normal</th>
                    <th>Stock mínimo</th>
                    <th>Precio de venta</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" id="formularioregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important;">
                <form name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-2 col-md-4 col-sm-12 caja1" style="padding-left: 0 !important; padding-right: 20px;">
                    <div class="contenedor" style="background-color: white; border-top: 3px #3686b4 solid; padding: 10px 20px 20px 20px;">
                      <label>Imagen de muestra:</label>
                      <div>
                        <img src="" width="100%" id="imagenmuestra">
                      </div>
                    </div>
                  </div>
                  <div class="form-group col-lg-10 col-md-8 col-sm-12 caja2" style="background-color: white; border-top: 3px #3686b4 solid; padding: 20px;">
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Nombre(*):</label>
                      <input type="hidden" name="idarticulo" id="idarticulo">
                      <input type="text" class="form-control" name="nombre" id="nombre" maxlength="100" placeholder="Nombre" required>
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Categoría(*):</label>
                      <select id="idcategoria" name="idcategoria" class="form-control selectpicker" data-live-search="true" required></select>
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>Local(*):</label>
                      <select id="idlocal" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC()">
                        <option value="">- Seleccione -</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                      <label>RUC local(*):</label>
                      <input type="number" class="form-control" id="local_ruc" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local" disabled>
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Marca(*):</label>
                      <select id="idmarca" name="idmarca" class="form-control selectpicker" data-live-search="true" required></select>
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Stock(*):</label>
                      <input type="number" class="form-control" name="stock" id="stock" onkeydown="evitarNegativo(event)" oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="3" min="0" placeholder="Stock" required>
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Stock mínimo(*):</label>
                      <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" onkeydown="evitarNegativo(event)" oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="3" min="0" placeholder="Stock mínimo" required>
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <label>Descripción:</label>
                      <input type="text" class="form-control" name="descripcion" id="descripcion" maxlength="50" placeholder="Descripción del artículo" autocomplete="off">
                    </div>
                    <div class="form-group col-lg-12 col-md-12">
                      <label>Imagen:</label>
                      <input type="file" class="form-control" name="imagen" id="imagen" accept="image/x-png,image/gif,image/jpeg">
                      <input type="hidden" name="imagenactual" id="imagenactual">
                    </div>
                    <div class="form-group col-lg-6 col-md-12">
                      <div>
                        <label>Código de barra(*):</label>
                        <input type="text" class="form-control" name="codigo" id="codigo" maxlength="18" placeholder="Código de barra">
                      </div>
                      <div style="margin-top: 10px;">
                        <button class="btn btn-bcp" type="button" onclick="generarbarcode()">Visualizar</button>
                        <button class="btn btn-info" type="button" onclick="generar()">Generar</button>
                        <button class="btn btn-warning" type="button" onclick="imprimir()">Imprimir</button>
                      </div>
                      <div id="print" style="overflow-y: hidden;">
                        <img id="barcode">
                      </div>
                    </div>
                    <div class="form-group col-lg-6 col-md-6">
                      <label>Código del producto(*):</label>
                      <input type="text" class="form-control" name="codigo_producto" id="codigo_producto" maxlength="10" placeholder="Código del producto" required>
                    </div>
                  </div>
                  <div class="form-group col-lg-10 col-md-8 col-sm-12 botones" style="background-color: white !important; padding: 10px 10px 10px 0 !important; float: right;">
                    <div style="float: right;">
                      <button class="btn btn-warning" onclick="cancelarform()" type="button"><i class="fa fa-arrow-circle-left"></i> Cancelar</button>
                      <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                  </div>
                </form>
              </div>
              <!--Fin centro -->
            </div><!-- /.box -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->
    <!--Fin-Contenido-->
  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="../public/js/JsBarcode.all.min.js"></script>
  <script type="text/javascript" src="../public/js/jquery.PrintArea.js"></script>
  <script type="text/javascript" src="scripts/articulo3.js"></script>
<?php
}
ob_end_flush();
?>