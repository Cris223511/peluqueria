<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';
  if ($_SESSION['reportesM'] == 1) {
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

        #label {
          display: none;
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

      #contenedorPagos {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 10px;
      }

      .item_pago {
        border: 1px solid #d2d6de;
        padding: 5px 40px 5px 15px;
        display: inline-block;
        border-radius: 5px;
        position: relative;
      }

      .item_pago .borrar_pago {
        margin-left: 10px;
        font-size: 12px;
        border-radius: 5px;
        height: 24px;
        width: 24px;
        color: #002a8e;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        position: absolute;
        font-weight: bold;
        font-size: 18px;
        top: 3px;
        right: 5px;
        transition: .3s ease all;
      }

      .item_pago .borrar_pago:hover {
        background-color: #e1f1ff;
        transition: .3s ease all;
      }

      .borrar_pago:before {
        content: "𝗑";
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Reporte de métodos de pago de compras</h1>
                <a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Reporte de métodos de pago</strong>" data-html="true" data-content="Módulo para ver el monto de los métodos de pago de las compras que se hicieron en <strong>su local</strong>." style="color: #002a8e; font-size: 18px;">&nbsp;<i class="fa fa-question-circle"></i></a>
                <div class="box-tools pull-right"></div>
                <div class="panel-body table-responsive listadoregistros" style="overflow: visible; padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Fecha Inicial:</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Fecha Final:</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                  </div>
                  <div class="form-group col-lg-3 col-md-3 col-sm-4 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label>Método de pago:</label>
                    <select id="metodopagoBuscar" name="metodopagoBuscar" class="form-control selectpicker" data-live-search="true" data-size="5" onchange="agregarPago()">
                    </select>
                  </div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" style="padding: 5px; margin: 0px;">
                    <label id="labelCustom">ㅤ</label>
                    <div style="display: flex; gap: 10px;">
                      <button style="width: 100%;" class="btn btn-bcp" onclick="buscar()">Buscar</button>
                      <button style="height: 32px;" class="btn btn-success" onclick="resetear()"><i class="fa fa-repeat"></i></button>
                    </div>
                  </div>
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="contenedorPagos" style="padding: 5px; margin: 0px;">
                  </div>
                </div>
              </div>
              <div class="panel-body listadoregistros" style="background-color: #ecf0f5 !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="table-responsive" style="padding: 8px !important; padding: 20px !important; background-color: white;">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover w-100" style="width: 100% !important">
                    <thead>
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Proveedor</th>
                      <th>Método de pago</th>
                      <th>Monto</th>
                      <th>Número Ticket</th>
                      <th>Almacén</th>
                      <th>Documento</th>
                      <th>Agregado por</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Fecha y hora</th>
                      <th>DNI / RUC</th>
                      <th>Proveedor</th>
                      <th>Método de pago</th>
                      <th>Monto</th>
                      <th>Número Ticket</th>
                      <th>Almacén</th>
                      <th>Documento</th>
                      <th>Agregado por</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>
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
  <script type="text/javascript" src="scripts/reporteCompraMetodoPago.js"></script>
<?php
}
ob_end_flush();
?>