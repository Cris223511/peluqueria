<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: login.html");
} else {
  require 'header.php';

  if ($_SESSION['escritorio'] == 1) {

    require_once "../modelos/Perfiles.php";
    $consulta = new Perfiles();

    $idlocal = $_SESSION['idlocal'];
    $cargo = $_SESSION["cargo"];

    if ($cargo == "superadmin" || $cargo == "admin_total") {
      $compras10 = $consulta->comprasultimos_10dias();
      $ventas10 = $consulta->ventasultimos_10dias();
      $proformas10 = $consulta->proformasultimos_10dias();
    } else {
      $compras10 = $consulta->comprasultimos_10diasUsuario($idlocal);
      $ventas10 = $consulta->ventasultimos_10diasUsuario($idlocal);
      $proformas10 = $consulta->proformasultimos_10diasUsuario($idlocal);
    }

    if ($cargo == "superadmin" || $cargo == "admin_total") {
      $totalCompras = $consulta->totalCompras()["total"];
      $totalVentas = $consulta->totalVentas()["total"];
      $totalVentasProforma = $consulta->totalVentasProforma()["total"];
    } else {
      $totalCompras = $consulta->totalComprasUsuario($idlocal)["total"];
      $totalVentas = $consulta->totalVentasUsuario($idlocal)["total"];
      $totalVentasProforma = $consulta->totalVentasProformaUsuario($idlocal)["total"];
    }

    //Datos para mostrar el gráfico de barras de las compras
    $fechasc = '';
    $totalesc = '';
    while ($regfechac = $compras10->fetch_object()) {
      $fechasc = $fechasc . '"' . $regfechac->fecha . '",';
      $totalesc = $totalesc . $regfechac->total . ',';
    }

    //Quitamos la última coma
    $fechasc = substr($fechasc, 0, -1);
    $totalesc = substr($totalesc, 0, -1);

    //Datos para mostrar el gráfico de barras de las ventas
    $fechasv = '';
    $totalesv = '';
    while ($regfechav = $ventas10->fetch_object()) {
      $fechasv = $fechasv . '"' . $regfechav->fecha . '",';
      $totalesv = $totalesv . $regfechav->total . ',';
    }

    //Quitamos la última coma
    $fechasv = substr($fechasv, 0, -1);
    $totalesv = substr($totalesv, 0, -1);

    //Datos para mostrar el gráfico de barras de las ventas
    $fechasp = '';
    $totalesp = '';
    while ($regfechap = $proformas10->fetch_object()) {
      $fechasp = $fechasp . '"' . $regfechap->fecha . '",';
      $totalesp = $totalesp . $regfechap->total . ',';
    }

    //Quitamos la última coma
    $fechasp = substr($fechasp, 0, -1);
    $totalesp = substr($totalesp, 0, -1);
?>
    <style>
      .tarjeta1 {
        background-color: #27a844;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .tarjeta2 {
        background-color: #fec107;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .tarjeta3 {
        background-color: #17a2b7;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .ticket1 {
        color: #ffa59a;
      }

      .ticket2 {
        color: #f7c87d;
      }

      .ticket3 {
        color: #3aadea;
      }

      .tarjeta1,
      .tarjeta2,
      .tarjeta3 {
        padding: 15px;
        border-radius: 20px;
        color: white;
      }

      .tarjeta1 h1,
      .tarjeta2 h1,
      .tarjeta3 h1 {
        font-weight: bold;
        margin: 0;
        padding: 5px 0 5px 0;
      }

      @media (max-width: 520px) {

        .ticket1,
        .ticket2,
        .ticket3 {
          display: none;
        }
      }

      @media (max-width: 1199px) {
        .marco {
          padding-top: 10px !important;
          padding-bottom: 10px !important;
          padding-left: 15px !important;
          padding-right: 15px !important;
        }

        .marco:nth-child(1),
        .marco:nth-child(2) {
          padding-top: 0 !important;
        }
      }

      @media (max-width: 991px) {
        .marco:nth-child(2) {
          padding-top: 10px !important;
        }
      }
    </style>
    <div class="content-wrapper">
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Escritorio</h1>
                <div class="box-tools pull-right">
                </div>
              </div>
              <div class="panel-body formularioregistros" style="background-color: white !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
                <div class="panel-body" style="padding-top: 0; padding-bottom: 0; padding-left: 15px; padding-right: 15px;">
                  <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-right: 10px">
                      <div class="tarjeta1 bg-red">
                        <div>
                          <h1>S/. <?php echo number_format($totalVentas, 2) ?></h1>
                          <span>Total de ventas</span>
                        </div>
                        <i class="fa fa-money ticket1" style="font-size: 60px;"></i>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-left: 10px; padding-right: 10px">
                      <div class="tarjeta2 bg-yellow">
                        <div>
                          <h1>S/. <?php echo number_format($totalVentasProforma, 2) ?></h1>
                          <span>Total de proformas</span>
                        </div>
                        <i class="fa fa-usd ticket2" style="font-size: 60px;"></i>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-left: 10px;">
                      <div class="tarjeta3 bg-blue">
                        <div>
                          <h1>S/. <?php echo number_format($totalCompras, 2) ?></h1>
                          <span>Total de compras</span>
                        </div>
                        <i class="fa fa-shopping-cart ticket3" style="font-size: 60px;"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel-body formularioregistros" style="background-color: white !important; padding-left: 0 !important; padding-right: 0 !important; height: max-content;">
              <div class="panel-body">
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-body">
                      <canvas id="ventas" width="300" height="280"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-body">
                      <canvas id="proformas" width="300" height="280"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-body">
                      <canvas id="compras" width="300" height="280"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <script src="../public/plugins/node_modules/chart.js/dist/chart.min.js"></script>
    <script src="../public/plugins/node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
    <script type="text/javascript">
      Chart.register(ChartDataLabels);

      function ajustarMaximo(valor) {
        if (valor < 10) {
          return valor + 1;
        } else {
          const exponent = Math.floor(Math.log10(valor));
          const increment = Math.pow(10, exponent - 1);
          return valor + increment;
        }
      }

      let totalesc = [<?php echo $totalesc; ?>];
      let totalesv = [<?php echo $totalesv; ?>];
      let totalesp = [<?php echo $totalesp; ?>];

      let max1 = ajustarMaximo(Math.max(...totalesc));
      let max2 = ajustarMaximo(Math.max(...totalesv));
      let max3 = ajustarMaximo(Math.max(...totalesp));

      var ctx = document.getElementById("compras").getContext('2d');
      var compras = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasc; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Compras en S/ de los últimos 10 días',
            data: [<?php echo $totalesc; ?>],
            backgroundColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderWidth: 1,
            borderRadius: {
              topLeft: 10,
              topRight: 10
            }
          }]
        },
        options: {
          scales: {
            y: {
              suggestedMax: max1
            }
          },
          plugins: {
            datalabels: { //esta es la configuración de pluggin datalabels
              anchor: 'end',
              align: 'top',
              formatter: function(value, context) {
                return value.toLocaleString('es-PE', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).replace(',', '.');
              },
              font: {
                weight: 'bold'
              }
            }
          }
        }
      });

      var ctx = document.getElementById("ventas").getContext('2d');
      var ventas = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasv; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Ventas en S/ de los últimos 10 días',
            data: [<?php echo $totalesv; ?>],
            backgroundColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderWidth: 1,
            borderRadius: {
              topLeft: 10,
              topRight: 10
            }
          }]
        },
        options: {
          scales: {
            y: {
              suggestedMax: max2
            }
          },
          plugins: {
            datalabels: { //esta es la configuración de pluggin datalabels
              anchor: 'end',
              align: 'top',
              formatter: function(value, context) {
                return value.toLocaleString('es-PE', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).replace(',', '.');
              },
              font: {
                weight: 'bold'
              }
            }
          }
        }
      });

      var ctx = document.getElementById("proformas").getContext('2d');
      var proformas = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasp; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Proformas en S/ de los últimos 10 días',
            data: [<?php echo $totalesp; ?>],
            backgroundColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderColor: [
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
              'rgba(0,166,149,255)',
            ],
            borderWidth: 1,
            borderRadius: {
              topLeft: 10,
              topRight: 10
            }
          }]
        },
        options: {
          scales: {
            y: {
              suggestedMax: max3
            }
          },
          plugins: {
            datalabels: { //esta es la configuración de pluggin datalabels
              anchor: 'end',
              align: 'top',
              formatter: function(value, context) {
                return value.toLocaleString('es-PE', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).replace(',', '.');
              },
              font: {
                weight: 'bold'
              }
            }
          }
        }
      });
    </script>
<?php

  } else {
    require 'noacceso.php';
  }
}

require 'footer.php';
ob_end_flush();

?>