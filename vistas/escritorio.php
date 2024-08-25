<?php
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

    // Moneda por defecto (soles)
    $moneda = 'soles';

    if ($cargo == "superadmin" || $cargo == "admin_total") {
      $compras10 = $consulta->comprasultimos_10dias($moneda);
      $ventas10 = $consulta->ventasultimos_10dias($moneda);
      $proformas10 = $consulta->proformasultimos_10dias($moneda);
      $totalCompras = $consulta->totalCompras($moneda)["total"];
      $totalVentas = $consulta->totalVentas($moneda)["total"];
      $totalVentasProforma = $consulta->totalVentasProforma($moneda)["total"];
    } else {
      $compras10 = $consulta->comprasultimos_10diasUsuario($idlocal, $moneda);
      $ventas10 = $consulta->ventasultimos_10diasUsuario($idlocal, $moneda);
      $proformas10 = $consulta->proformasultimos_10diasUsuario($idlocal, $moneda);
      $totalCompras = $consulta->totalComprasUsuario($idlocal, $moneda)["total"];
      $totalVentas = $consulta->totalVentasUsuario($idlocal, $moneda)["total"];
      $totalVentasProforma = $consulta->totalVentasProformaUsuario($idlocal, $moneda)["total"];
    }

    // Datos para los gráficos en soles
    $fechasc = '';
    $totalesc = '';
    while ($regfechac = $compras10->fetch_object()) {
      $fechasc .= '"' . $regfechac->fecha . '",';
      $totalesc .= $regfechac->total . ',';
    }

    $fechasc = substr($fechasc, 0, -1);
    $totalesc = substr($totalesc, 0, -1);

    $fechasv = '';
    $totalesv = '';
    while ($regfechav = $ventas10->fetch_object()) {
      $fechasv .= '"' . $regfechav->fecha . '",';
      $totalesv .= $regfechav->total . ',';
    }

    $fechasv = substr($fechasv, 0, -1);
    $totalesv = substr($totalesv, 0, -1);

    $fechasp = '';
    $totalesp = '';
    while ($regfechap = $proformas10->fetch_object()) {
      $fechasp .= '"' . $regfechap->fecha . '",';
      $totalesp .= $regfechap->total . ',';
    }

    $fechasp = substr($fechasp, 0, -1);
    $totalesp = substr($totalesp, 0, -1);

    // Datos para los gráficos en dólares
    $moneda = 'dolares';
    if ($cargo == "superadmin" || $cargo == "admin_total") {
      $compras10_usd = $consulta->comprasultimos_10dias($moneda);
      $ventas10_usd = $consulta->ventasultimos_10dias($moneda);
      $proformas10_usd = $consulta->proformasultimos_10dias($moneda);
    } else {
      $compras10_usd = $consulta->comprasultimos_10diasUsuario($idlocal, $moneda);
      $ventas10_usd = $consulta->ventasultimos_10diasUsuario($idlocal, $moneda);
      $proformas10_usd = $consulta->proformasultimos_10diasUsuario($idlocal, $moneda);
    }

    $fechasc_usd = '';
    $totalesc_usd = '';
    while ($regfechac_usd = $compras10_usd->fetch_object()) {
      $fechasc_usd .= '"' . $regfechac_usd->fecha . '",';
      $totalesc_usd .= $regfechac_usd->total . ',';
    }

    $fechasc_usd = substr($fechasc_usd, 0, -1);
    $totalesc_usd = substr($totalesc_usd, 0, -1);

    $fechasv_usd = '';
    $totalesv_usd = '';
    while ($regfechav_usd = $ventas10_usd->fetch_object()) {
      $fechasv_usd .= '"' . $regfechav_usd->fecha . '",';
      $totalesv_usd .= $regfechav_usd->total . ',';
    }

    $fechasv_usd = substr($fechasv_usd, 0, -1);
    $totalesv_usd = substr($totalesv_usd, 0, -1);

    $fechasp_usd = '';
    $totalesp_usd = '';
    while ($regfechap_usd = $proformas10_usd->fetch_object()) {
      $fechasp_usd .= '"' . $regfechap_usd->fecha . '",';
      $totalesp_usd .= $regfechap_usd->total . ',';
    }

    $fechasp_usd = substr($fechasp_usd, 0, -1);
    $totalesp_usd = substr($totalesp_usd, 0, -1);
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
                  <div class="row" style="margin-bottom: 10px;">
                    <form name="formulario" id="formulario" method="POST" enctype="multipart/form-data">
                      <div class="form-group col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <label>Filtrar por tipo de cambio:<a href="#" data-toggle="popover" data-placement="bottom" title="<strong>Filtrar por tipo de cambio</strong>" data-html="true" data-content="Filtra la sumatoria de los totales de las ventas, compras y proformas que se hicieron en soles o en dólares." style="color: #002a8e; font-size: 18px; position: absolute; top: -3px; width: fit-content !important;">&nbsp;<i class="fa fa-question-circle"></i></a></label>
                        <select name="moneda" id="moneda" class="form-control" required onchange="manejarTipoCambio()">
                          <option value="soles">Soles</option>
                          <option value="dolares">Dólares</option>
                        </select>
                      </div>
                    </form>
                  </div>
                  <div class="row" style="margin-bottom: 10px;">
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-right: 10px">
                      <div class="tarjeta1 bg-red">
                        <div>
                          <h1 id="totalVentas">S/. <?php echo number_format($totalVentas, 2) ?></h1>
                          <span>Total de ventas</span>
                        </div>
                        <i class="fa fa-money ticket1" style="font-size: 60px;"></i>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-left: 10px; padding-right: 10px">
                      <div class="tarjeta2 bg-yellow">
                        <div>
                          <h1 id="totalVentasProforma">S/. <?php echo number_format($totalVentasProforma, 2) ?></h1>
                          <span>Total de proformas</span>
                        </div>
                        <i class="fa fa-usd ticket2" style="font-size: 60px;"></i>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 marco" style="padding-left: 10px;">
                      <div class="tarjeta3 bg-blue">
                        <div>
                          <h1 id="totalCompras">S/. <?php echo number_format($totalCompras, 2) ?></h1>
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
                <!-- gráficos en soles -->
                <div id="graficos-soles">
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
                <!-- gráficos en dólares -->
                <div id="graficos-dolares" style="display:none;">
                  <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                    <div class="box box-primary">
                      <div class="box-body">
                        <canvas id="ventas-usd" width="300" height="280"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                    <div class="box box-primary">
                      <div class="box-body">
                        <canvas id="proformas-usd" width="300" height="280"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                    <div class="box box-primary">
                      <div class="box-body">
                        <canvas id="compras-usd" width="300" height="280"></canvas>
                      </div>
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

      // Gráficos en soles
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
            label: 'Compras en S/ de los últimos 30 días',
            data: [<?php echo $totalesc; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
            datalabels: {
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
            label: 'Ventas en S/ de los últimos 30 días',
            data: [<?php echo $totalesv; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
            datalabels: {
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
            label: 'Proformas en S/ de los últimos 30 días',
            data: [<?php echo $totalesp; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
            datalabels: {
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

      // Gráficos en dólares
      let totalesc_usd = [<?php echo $totalesc_usd; ?>];
      let totalesv_usd = [<?php echo $totalesv_usd; ?>];
      let totalesp_usd = [<?php echo $totalesp_usd; ?>];

      let max1_usd = ajustarMaximo(Math.max(...totalesc_usd));
      let max2_usd = ajustarMaximo(Math.max(...totalesv_usd));
      let max3_usd = ajustarMaximo(Math.max(...totalesp_usd));

      var ctx_usd = document.getElementById("compras-usd").getContext('2d');
      var compras_usd = new Chart(ctx_usd, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasc_usd; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Compras en $ de los últimos 30 días',
            data: [<?php echo $totalesc_usd; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
              suggestedMax: max1_usd
            }
          },
          plugins: {
            datalabels: {
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

      var ctx_usd = document.getElementById("ventas-usd").getContext('2d');
      var ventas_usd = new Chart(ctx_usd, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasv_usd; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Ventas en $ de los últimos 30 días',
            data: [<?php echo $totalesv_usd; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
              suggestedMax: max2_usd
            }
          },
          plugins: {
            datalabels: {
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

      var ctx_usd = document.getElementById("proformas-usd").getContext('2d');
      var proformas_usd = new Chart(ctx_usd, {
        type: 'bar',
        data: {
          labels: [<?php echo $fechasp_usd; ?>],
          datasets: [{
            barPercentage: 0.5,
            label: 'Proformas en $ de los últimos 30 días',
            data: [<?php echo $totalesp_usd; ?>],
            backgroundColor: 'rgba(0,166,149,255)',
            borderColor: 'rgba(0,166,149,255)',
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
              suggestedMax: max3_usd
            }
          },
          plugins: {
            datalabels: {
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

      function manejarTipoCambio() {
        var moneda = $('#moneda').val();

        if (moneda === 'dolares') {
          $('#graficos-soles').hide();
          $('#graficos-dolares').show();
        } else {
          $('#graficos-dolares').hide();
          $('#graficos-soles').show();
        }

        $.ajax({
          url: '../ajax/escritorio.php?op=filtrarPorMoneda',
          type: 'POST',
          data: {
            moneda: moneda
          },
          success: function(response) {
            var data = JSON.parse(response);

            let simboloMoneda = moneda === 'dolares' ? '$' : 'S/.';

            $('#totalVentas').text(simboloMoneda + ' ' + data.totalVentas);
            $('#totalVentasProforma').text(simboloMoneda + ' ' + data.totalVentasProforma);
            $('#totalCompras').text(simboloMoneda + ' ' + data.totalCompras);
          }
        });
      }
    </script>
<?php

  } else {
    require 'noacceso.php';
  }
}

require 'footer.php';
ob_end_flush();
?>