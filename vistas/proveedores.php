<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['compras'] == 1) {
?>
    <style>
      #detallesProductosFinal thead,
      #detallesProductosFinal thead tr,
      #detallesProductosFinal thead th,
      #detallesProductosFinal tbody,
      #detallesProductosFinal tbody tr,
      #detallesProductosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      #detallesProductosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      #detallesProductosFinal tbody,
      #detallesProductosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      #detallesProductosFinal tbody tr td,
      #detallesProductosFinal tfoot tr td {
        border: none !important;
      }

      #detallesPagosFinal thead,
      #detallesPagosFinal thead tr,
      #detallesPagosFinal thead th,
      #detallesPagosFinal tbody,
      #detallesPagosFinal tbody tr,
      #detallesPagosFinal tbody th {
        border: none !important;
        background-color: white;
        font-size: 16px;
        text-align: center;
      }

      #detallesPagosFinal thead {
        border-bottom: 1.5px black solid !important;
      }

      #detallesPagosFinal tbody,
      #detallesPagosFinal tfoot {
        border-top: 1.5px black solid !important;
      }

      #detallesPagosFinal tbody tr td,
      #detallesPagosFinal tfoot tr td {
        border: none !important;
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
                <h1 class="box-title">Proveedores
                  <button class="btn btn-bcp" id="btnagregar" onclick="mostrarform(true)">
                    <i class="fa fa-plus-circle"></i> Agregar
                  </button>
                  <?php if ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total") { ?>
                    <a href="../reportes/rptproveedores.php" target="_blank">
                      <button class="btn btn-secondary" style="color: black !important;">
                        <i class="fa fa-clipboard"></i> Reporte
                      </button>
                    </a>
                  <?php } ?>
                  <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Proveedores</strong>" data-html="true" data-content="Módulo para registrar los proveedores para que sean utilizados en las compras.<br><br><strong>Nota:</strong> Los proveedores no están divididos por local, de la cual, los proveedores que registre serán visibles y utilizados por los trabajadores <strong>de todos los locales</strong> (solo puede editar, anular y eliminar los proveedores que ustéd agrega y no el de los demás)." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                </h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important;">
                  <thead>
                    <th style="width: 1%;">Opciones</th>
                    <th style="width: 30%; min-width: 150px; white-space: nowrap;">Nombres</th>
                    <th style="white-space: nowrap;">Tipo Doc.</th>
                    <th style="white-space: nowrap;">Número Doc.</th>
                    <th style="width: 30%; min-width: 200px; white-space: nowrap;">Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th style="width: 40%; min-width: 280px; white-space: nowrap;">Descripción</th>
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
                    <th>Tipo Doc.</th>
                    <th>Número Doc.</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
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
                    <label>Nombre(*):</label>
                    <input type="hidden" name="idproveedor" id="idproveedor">
                    <input type="text" class="form-control" name="nombre" id="nombre" maxlength="40" placeholder="Ingrese el nombre del proveedor." autocomplete="off" required>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Dirección:</label>
                    <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Ingrese la dirección." maxlength="40">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Tipo Documento(*):</label>
                    <select class="form-control select-picker" name="tipo_documento" id="tipo_documento" onchange="changeValue(this);" required>
                      <option value="">- Seleccione -</option>
                      <option value="DNI">DNI</option>
                      <option value="RUC">RUC</option>
                      <option value="CEDULA">CEDULA</option>
                      <option value="CARNET DE EXTRANJERIA">CARNET DE EXTRANJERÍA</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Número(*):</label>
                    <input type="number" class="form-control" name="num_documento" id="num_documento" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="8" placeholder="Ingrese el N° de documento." required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Teléfono:</label>
                    <input type="number" class="form-control" name="telefono" id="telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="9" placeholder="Ingrese el teléfono.">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <label>Email:</label>
                    <input type="email" class="form-control" name="email" id="email" maxlength="50" placeholder="Ingrese el correo electrónico.">
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label>Descripción:</label>
                    <textarea type="text" class="form-control" name="descripcion" id="descripcion" rows="4" placeholder="Ingrese una descripción."></textarea>
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

    <!-- Modal 1 -->
    <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 65%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold;">HISTORIAL DE COMPRAS DEL PROVEEDOR</h4>
              <h4 class="modal-title infotitulo proveedor_detalles" style="margin: 0; padding: 0;"></h4>
            </div>
          </div>
          <div class="panel-body" style="background-color: #ecf0f5 !important; padding: 0 !important; height: max-content;">
            <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
              <table id="tbldetalles" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                <thead>
                  <th style="width: 1%;">Opciones</th>
                  <th>Fecha y hora</th>
                  <th>Almacén</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Compra (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <th>Opciones</th>
                  <th>Fecha y hora</th>
                  <th>Almacén</th>
                  <th>Documento</th>
                  <th>Número Ticket</th>
                  <th>Total Compra (S/.)</th>
                  <th>Agregado por</th>
                  <th>Estado</th>
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
    <!-- Fin Modal 1 -->

    <!-- Modal 2 -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog smallModal" style="width: 75%; max-height: 95vh; margin: 0 !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%); overflow-x: auto;">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f2d150 !important; border-bottom: 2px solid #C68516 !important;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <div style="text-align: center; display: flex; justify-content: center; flex-direction: column; gap: 5px;">
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">BOLETA DE COMPRA: <span id="boleta_de_compra" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">PROVEEDOR: <span id="nombre_proveedor" style="font-weight: 600;"></span></h4>
              <h4 class="modal-title infotitulo" style="margin: 0; padding: 0; font-weight: bold; text-align: start;">DIRECCIÓN PROVEEDOR: <span id="direccion_proveedor" style="font-weight: 600;"></span></h4>
            </div>
          </div>
          <div class="panel-body">
            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table id="detallesProductosFinal" class="table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th style="white-space: nowrap;">DESCRIPCIÓN DEL PRODUCTO</th>
                  <th style="white-space: nowrap;">CANTIDAD</th>
                  <th style="white-space: nowrap;">PRECIO UNITARIO</th>
                  <th style="white-space: nowrap;">DESCUENTO</th>
                  <th style="white-space: nowrap;">SUBTOTAL</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">IGV</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="igv_detalle"></td>
                  </tr>
                  <tr>
                    <td style="width: 44%; min-width: 180px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap;"></td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 14%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_detalle"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="padding: 15px; padding-top: 0px; background-color: white; overflow: auto;">
              <table id="detallesPagosFinal" class="table w-100" style="width: 100% !important; margin-bottom: 0px;">
                <thead style="border-bottom: 1.5px solid black !important;">
                  <th>DESCRIPCIÓN DE PAGOS</th>
                  <th>MONTO</th>
                </thead>
                <tfoot>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="subtotal_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">VUELTO</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="vueltos_pagos"></td>
                  </tr>
                  <tr>
                    <td style="width: 80%; min-width: 180px; white-space: nowrap; text-align: end !important; font-weight: bold;">TOTAL</td>
                    <td style="width: 20%; min-width: 40px; white-space: nowrap; text-align: center !important; font-weight: bold;" id="total_pagos"></td>
                  </tr>
                </tfoot>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center;">
              <h4 style="font-weight: bold;">ATENDIDO POR: <span id="atendido_compra" style="font-weight: 600;"></span></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin modal 2 -->
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script type="text/javascript" src="scripts/proveedores.js"></script>
<?php
}
ob_end_flush();
?>