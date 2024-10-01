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

        #label {
          display: none;
        }
      }

      tbody td:nth-child(12) {
        white-space: nowrap !important;
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

      #formulario .form-control,
      #formulario .form-control button {
        height: 45px !important;
        font-size: 16px !important;
        align-content: center;
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Agregar Productos</h1>
                <button class="btn btn-danger" type="button" onclick="window.history.back()"><i class="fa fa-arrow-circle-left"></i> Volver</button>
              </div>
              <div class="panel-body" id="formularioregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important;">
                <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data">
                  <div class="form-group col-lg-10 col-md-8 col-sm-12 caja2" style="background-color: white; border-top: 3px #002a8e solid !important; padding: 20px;">
                    <div class="form-group col-lg-4 col-md-6 col-sm-12" style="margin: 0; padding: 0;">
                      <div class="form-group col-lg-6 col-md-6 col-sm-6">
                        <label>Código(*):</label>
                        <input type="text" class="form-control" id="cod_part_1" maxlength="10" placeholder="PRO" onblur="convertirMayus()" required>
                      </div>
                      <div class="form-group col-lg-6 col-md-6 col-sm-6">
                        <label id="label">ㅤ</label>
                        <input type="text" class="form-control" id="cod_part_2" maxlength="10" placeholder="0001" oninput="onlyNumbersAndMaxLenght(this)" onblur="formatearNumeroCorrelativo()" required>
                      </div>
                    </div>
                    <div class="form-group col-lg-8 col-md-6 col-sm-12">
                      <label>Nombre(*):</label>
                      <input type="hidden" name="idarticulo" id="idarticulo">
                      <input type="text" class="form-control" name="nombre" id="nombre" maxlength="100" placeholder="Ingrese el nombre del producto." required>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Categoría:</label>
                      <select id="idcategoria" name="idcategoria" class="form-control selectpicker" data-live-search="true" data-size="5"></select>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Marca:</label>
                      <select id="idmarca" name="idmarca" class="form-control selectpicker" data-live-search="true" data-size="5"></select>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Local(*):</label>
                      <select id="idlocal" name="idlocal" class="form-control selectpicker idlocal" data-live-search="true" data-size="5" onchange="actualizarRUC()" required>
                        <option value="">- Seleccione -</option>
                      </select>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>RUC local(*):</label>
                      <input type="number" class="form-control" id="local_ruc" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" placeholder="RUC del local" disabled>
                    </div>
                    <div class="form-group col-lg-8 col-md-12 col-sm-12" style="padding: 0; margin: 0;">
                      <div class="form-group col-lg-4 col-md-4 col-sm-12">
                        <label>Precio compra:</label>
                        <input type="number" class="form-control" name="precio_compra" id="precio_compra" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); changeGanancia();" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0" placeholder="Ingrese el precio de compra.">
                      </div>
                      <div class="form-group col-lg-4 col-md-4 col-sm-12">
                        <label>Precio venta:</label>
                        <input type="number" class="form-control" name="precio_venta" id="precio_venta" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength); changeGanancia();" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0" placeholder="Ingrese el precio de venta.">
                      </div>
                      <div class="form-group col-lg-4 col-md-4 col-sm-12">
                        <label>Ganancia:</label>
                        <input type="number" class="form-control" name="ganancia" id="ganancia" step="any" value="0.00" disabled>
                      </div>
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Stock:</label>
                      <input type="number" class="form-control" name="stock" id="stock" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0.1" placeholder="Ingrese el stock.">
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Stock mínimo:</label>
                      <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" step="any" min="0.1" placeholder="Ingrese el stock mínimo.">
                    </div>
                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                      <label>Imagen:</label>
                      <input type="file" class="form-control" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.jfif,.bmp">
                      <input type="hidden" name="imagenactual" id="imagenactual">
                    </div>
                    <div class="form-group col-lg-6 col-md-6 col-sm-12">
                      <label>Descripción:</label>
                      <input type="text" class="form-control" name="descripcion" id="descripcion" maxlength="10000" placeholder="Ingrese la descripción del producto." autocomplete="off">
                      <div style="display: flex; justify-content: end;">
                        <div id="camera"></div>
                      </div>
                    </div>
                    <div class="form-group col-lg-6 col-md-12 col-sm-12">
                      <div>
                        <label>Código de barra:</label>
                        <input type="text" class="form-control" name="codigo" id="codigo_barra" maxlength="13" placeholder="Ingrese el código de barra.">
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
                    </div>
                    <div class="form-group col-lg-12 col-md-12" style="display: flex; justify-content: center;">
                      <button class="btn btn-success" type="button" id="btnDetalles1" onclick="frmDetalles(true)"><i class="fa fa-plus"></i> Más detalles</button>
                      <button class="btn btn-danger" type="button" id="btnDetalles2" onclick="frmDetalles(false)"><i class="fa fa-minus"></i> Cerrar</button>
                    </div>
                    <!-- form detalles -->
                    <div id="frmDetalles" class="col-lg-12 col-md-12" style="margin: 0 !important; padding: 0 !important;">
                      <div class="form-group col-lg-6 col-md-12">
                        <label>Comisión:</label>
                        <input type="number" class="form-control" name="comision" id="comision" step="any" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" onkeydown="evitarNegativo(event)" onpaste="return false;" onDrop="return false;" min="0" placeholder="Ingrese la comisión del producto.">
                      </div>
                      <div class="form-group col-lg-6 col-md-12">
                        <label>Talla:</label>
                        <input type="text" class="form-control" name="talla" id="talla" maxlength="15" placeholder="Ingrese la talla del producto." autocomplete="off">
                      </div>
                      <div class="form-group col-lg-6 col-md-12">
                        <label>Color:</label>
                        <input type="text" class="form-control" name="color" id="color" maxlength="30" placeholder="Ingrese el color del producto." autocomplete="off">
                      </div>
                      <div class="form-group col-lg-6 col-md-12">
                        <label>Unidad de medida(*):</label>
                        <select id="idmedida" name="idmedida" class="form-control selectpicker" data-live-search="true" required></select>
                      </div>
                      <div class="form-group col-lg-6 col-md-12">
                        <label>Peso:</label>
                        <input type="number" class="form-control" name="peso" id="peso" step="any" onkeydown="evitarNegativo(event)" oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" min="0" placeholder="Ingrese el peso.">
                      </div>
                    </div>
                    <!-- end form detalles -->
                  </div>
                  <div class="form-group col-lg-2 col-md-4 col-sm-12 caja1" style="padding-right: 0 !important; padding-left: 20px;">
                    <div class="contenedor" style="background-color: white; border-top: 3px #002a8e solid !important; padding: 10px 20px 20px 20px;">
                      <label>Imagen de muestra:</label>
                      <div>
                        <img src="" width="100%" id="imagenmuestra" style="display: none;">
                      </div>
                    </div>
                  </div>
                  <div class="form-group col-lg-10 col-md-8 col-sm-12 botones" style="background-color: white !important; padding: 10px !important; float: left;">
                    <div style="float: left;">
                      <button class="btn btn-bcp" type="submit" id="btnGuardar"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Form categoría -->
    <form name="formularioCategoria" id="formularioCategoria" method="POST" style="display: none;">
      <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label>Nombre(*):</label>
        <input type="hidden" name="idcategoria" id="idcategoria2">
        <input type="text" class="form-control" name="titulo" id="titulo2" maxlength="50" placeholder="Nombre" required>
      </div>
      <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <label>Descripción:</label>
        <input type="text" class="form-control" name="descripcion" id="descripcion2" maxlength="10000" placeholder="Descripción">
      </div>
    </form>
    <!-- Fin form categoría -->

    <!-- Form marcas -->
    <form name="formularioMarcas" id="formularioMarcas" method="POST" style="display: none;">
      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <label>Marca:</label>
        <input type="hidden" name="idmarca" id="idmarca3">
        <input type="text" class="form-control" name="titulo" id="titulo3" maxlength="50" placeholder="Nombre de la marca" required>
      </div>
      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <label>Descripción:</label>
        <textarea type="text" class="form-control" name="descripcion" id="descripcion3" maxlength="10000" rows="4" placeholder="Descripción"></textarea>
      </div>
    </form>
    <!-- Fin form marcas -->

    <!-- Form medidas -->
    <form name="formularioMedidas" id="formularioMedidas" method="POST" style="display: none;">
      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <label>Medida(*):</label>
        <input type="hidden" name="idmedida" id="idmedida4">
        <input type="text" class="form-control" name="titulo" id="titulo4" maxlength="50" placeholder="Nombre de la medida" required>
      </div>
      <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <label>Descripción:</label>
        <textarea type="text" class="form-control" name="descripcion" id="descripcion4" maxlength="10000" rows="4" placeholder="Descripción"></textarea>
      </div>
    </form>
    <!-- Fin form medidas -->
  <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php';
  ?>
  <script type="text/javascript" src="../public/js/JsBarcode.all.min.js"></script>
  <script type="text/javascript" src="../public/js/jquery.PrintArea.js"></script>
  <script type="text/javascript" src="scripts/articulo_form.js"></script>
<?php
}
ob_end_flush();
?>