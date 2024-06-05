<?php
if (strlen(session_id()) < 1)
  session_start();

$nombre_login = $_SESSION['nombre'];
$cargo_login = $_SESSION['cargo_detalle'];
$local_login = $_SESSION['local'];
?>

<style>
  .content-wrapper {
    min-height: 919px !important;
  }

  .skin-blue-light .main-header .navbar .sidebar-toggle {
    transition: .3s ease all;
  }

  .skin-blue-light .main-header .navbar .sidebar-toggle:hover {
    transition: .3s ease all;
    background: #0234ac !important;
  }

  .skin-blue-light .main-header .navbar .nav>li>a {
    transition: .3s ease all;
  }

  .skin-blue-light .main-header .navbar .nav>li>a:hover {
    transition: .3s ease all;
    background: #0234ac !important;
  }

  .sidebar-menu .fa {
    color: #3e79fd;
  }

  .sidebar-menu .pull-right {
    color: #Fa7d1e;
    font-weight: bold;
    transition: .3s ease all;
  }

  .btn-bcp,
  .btn-danger,
  .btn-warning,
  .btn-info,
  .btn-success,
  .btn-secondary {
    transition: .3s ease all;
    border: 0px !important;
  }

  .btn-bcp.focus,
  .btn-danger.focus,
  .btn-warning.focus,
  .btn-info.focus,
  .btn-success.focus {
    color: white !important;
    text-decoration: none;
  }

  .btn-bcp:focus,
  .btn-danger:focus,
  .btn-warning:focus,
  .btn-info:focus,
  .btn-success:focus {
    color: white !important;
    text-decoration: none;
  }

  .main-header .navbar-custom-menu a,
  .main-header .navbar-right a {
    color: white !important;
  }

  .btn-warning {
    background-color: #Fa7d1e !important;
    color: while !important;
  }

  .btn-warning:hover {
    background-color: #ff961f !important;
  }

  .btn-default {
    background-color: #ffffff !important;
    transition: .3s ease all;
    border-color: #ccc;
  }

  .btn-default.disabled {
    background-color: #eeeeee !important;
    transition: .3s ease all;
    opacity: 1 !important;
  }

  .modal-footer .btn+.btn,
  .btn-bcp {
    background-color: #3e79fd !important;
    outline: none !important;
    box-shadow: none !important;
    border: none !important;
    transition: .3s ease all;
    color: white;
  }

  .modal-footer .btn+.btn:hover,
  .btn-bcp:hover {
    background-color: #5288ff !important;
    transition: .3s ease all !important;
    color: white !important;
  }

  .btn-bcp.disabled {
    background-color: #5288ff !important;
    transition: .3s ease all;
    opacity: 1 !important;
  }

  .nowrap-cell {
    white-space: nowrap;
  }

  .two-row {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  body {
    padding-right: 0 !important
  }

  .table-responsive {
    border: none !important;
  }

  #total2,
  #total {
    font-weight: bold;
  }

  .box {
    box-shadow: none !important;
    border-top: 3px #002a8e solid !important;
  }

  input,
  .form-control,
  button {
    border-radius: 5px !important;
  }

  label {
    text-transform: uppercase;
  }

  textarea {
    resize: none !important;
  }

  @media (max-width: 991.50px) {
    #labelCustom {
      display: none;
    }
  }

  .popover {
    z-index: 99999 !important;
  }

  .box-title2 {
    display: inline-block;
    font-size: 18px;
    margin: 0;
    line-height: 1;
  }


  @media (max-width: 991.50px) {
    .smallModal {
      width: 90% !important;
    }
  }
</style>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- avoid cache -->
  <meta http-equiv="Expires" content="0">
  <meta http-equiv="Last-Modified" content="0">
  <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <title>Sistema de ventas | www.SistemaDeVentas.com</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="../public/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../public/css/font-awesome.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../public/css/AdminLTE.min.css">
  <!-- Lightbox style -->
  <link href="../public/glightbox/css/glightbox.min.css" rel="stylesheet" asp-append-version="true">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="../public/css/_all-skins.min.css">
  <link rel="apple-touch-icon" href="../public/img/apple-touch-icon.png">
  <link rel="shortcut icon" href="../public/img/favicon.ico">

  <!-- DATATABLES -->
  <link rel="stylesheet" type="text/css" href="../public/datatables/jquery.dataTables.min.css">
  <link href="../public/datatables/buttons.dataTables.min.css" rel="stylesheet" />
  <link href="../public/datatables/responsive.dataTables.min.css" rel="stylesheet" />

  <link rel="stylesheet" type="text/css" href="../public/css/bootstrap-select.min.css">
</head>

<body class="hold-transition skin-blue-light sidebar-mini" style="padding: 0 !important;">
  <div class="wrapper">

    <header class="main-header" style="box-shadow: 0px 0px 15px -7px; position: sticky !important; width: 100%">
      <a href="escritorio.php" class="logo" style="color: white !important; background-color: #002a8e !important;">
        <span class="logo-mini"><b>S.V.</b></span>
        <span class="logo-lg" style="font-size: 15px;"><b>Sistema de ventas</b></span>
      </a>
      <nav class="navbar" role="navigation" style="background-color: #002a8e !important;">
        <div style="display: flex; align-items: center; float: left;">
          <a href="#" class="sidebar-toggle" style="background: #002a8e; color: white !important;" data-toggle="offcanvas" role="button">
            <span class="sr-only">Navegación</span>
          </a>
        </div>
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li class="dropdown user user-menu" style="background: #002a8e !important; display: inline-flex; align-items: center; height: 50px;">
              <span style="color: white !important;" class="hidden-xs user-info local"><?php echo '<strong> Local: ' . $local_login . '</strong>' ?></span>
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: white !important; height: 50px;">
                <img src="../files/usuarios/<?php echo $_SESSION['imagen']; ?>" class="user-image" alt="User Image">
                <span class="hidden-xs user-info user"><?php echo $nombre_login; ?> - <?php echo '<strong> Rol: ' . $cargo_login . '</strong>' ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header" style="background: #002a8e !important;">
                  <img src="../files/usuarios/<?php echo $_SESSION['imagen']; ?>" class="img-circle" alt="User Image">
                  <p style="color: white !important;">
                    Sistema de ventas
                    <small>nuestro contacto: +51 937 075 845</small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-right">
                    <a href="../ajax/usuario.php?op=salir" class="btn btn-warning btn-flat" onclick="destruirSession()">Cerrar sesión</a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>
    <aside class="main-sidebar">
      <section class="sidebar">
        <ul class="sidebar-menu">
          <li class="header"></li>
          <?php
          if ($_SESSION['escritorio'] == 1) {
            echo '<li id="mEscritorio">
              <a href="escritorio.php">
                <i class="fa fa-tasks"></i> <span>Escritorio</span>
              </a>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['servicios'] == 1) {
            echo '<li id="mServicios" class="treeview">
              <a href="servicios.php">
                <i class="fa fa-cogs"></i>
                <span>Servicios</span>
              </a>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['almacen'] == 1) {
            echo '<li id="mAlmacen" class="treeview">
              <a href="#">
                <i class="fa fa-laptop"></i>
                <span>Almacén</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lArticulos"><a href="articulo.php"><i class="fa fa-circle-o"></i> Productos</a></li>
                ';
            if (($_SESSION['cargo'] == "superadmin" || $_SESSION['cargo'] == "admin_total") && $_SESSION['PExternos'] == 1) {
              echo '<li id="lArticulosExternos"><a href="articuloExterno.php"><i class="fa fa-circle-o"></i> Productos Externos</a></li>';
            }
            echo '
                <li id="lCategorias"><a href="categoria.php"><i class="fa fa-circle-o"></i> Categorías</a></li>
                <li id="lLocales"><a href="locales.php"><i class="fa fa-circle-o"></i> Locales</a></li>
                <li id="lMilocal"><a href="miLocal.php"><i class="fa fa-circle-o"></i> Mi local</a></li>
                <li id="lMarcas"><a href="marcas.php"><i class="fa fa-circle-o"></i> Marcas</a></li>
                <li id="lMedidas"><a href="medidas.php"><i class="fa fa-circle-o"></i> Unidades de medidas</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['compras'] == 1) {
            echo '<li id="mCompras" class="treeview">
              <a href="#">
                <i class="fa fa-cart-plus"></i>
                <span>Compras</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lCompras"><a href="compra.php"><i class="fa fa-circle-o"></i> Compras</a></li>
                <li id="lProveedor"><a href="proveedores.php"><i class="fa fa-circle-o"></i> Proveedores</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['ventas'] == 1) {
            echo '<li id="mVentas" class="treeview">
              <a href="#">
                <i class="fa fa-shopping-cart"></i>
                <span>Ventas</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lVentas"><a href="venta.php"><i class="fa fa-circle-o"></i> Ventas</a></li>
                <li id="lProformas"><a href="proforma.php"><i class="fa fa-circle-o"></i> Proformas</a></li>
                <li id="lClientes"><a href="clientes.php"><i class="fa fa-circle-o"></i> Clientes</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['cajas'] == 1) {
            echo '<li id="mCajas" class="treeview">
              <a href="#">
                <i class="fa fa-archive"></i>
                <span>Flujo de caja</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lAperturas"><a href="aperturas.php"><i class="fa fa-circle-o"></i> Aperturas</a></li>
                <li id="lCierres"><a href="cierres.php"><i class="fa fa-circle-o"></i> Cierres</a></li>
                <li id="lRetiros"><a href="retiros.php"><i class="fa fa-circle-o"></i> Retiros</a></li>
                <li id="lGastos"><a href="gastos.php"><i class="fa fa-circle-o"></i> Gastos</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['pagos'] == 1) {
            echo '<li id="mPagos" class="treeview">
              <a href="metodo_pago.php">
                <i class="fa fa-credit-card"></i>
                <span>Métodos de pago</span>
              </a>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['personas'] == 1) {
            echo '<li id="mPersonas" class="treeview">
              <a href="#">
                <i class="fa fa-users"></i>
                <span>Personas</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
              <li id="lTrabajadores"><a href="trabajadores.php"><i class="fa fa-circle-o"></i> Trabajadores</a></li>
              <li id="lPersonales"><a href="personales.php"><i class="fa fa-circle-o"></i> Empleados</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['comisiones'] == 1) {
            echo '<li id="mComisiones" class="treeview">
              <a href="comisiones.php">
                <i style="text-align: center; margin-left: -3px; margin-right: 1px" class="fa fa-dollar"></i>
                <span>Comisiones</span>
              </a>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['perfilu'] == 1) {
            echo '
          <li id="mPerfilUsuario" class="treeview">
            <a href="#">
              <i class="fa fa-user"></i> <span>Perfil de usuario</span>
              <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <li id="lConfUsuario"><a href="confUsuario.php"><i class="fa fa-circle-o"></i> Configuración de perfil</a></li>
              ';
            if ($_SESSION['cargo'] == "superadmin" || $_SESSION['cargo'] == "admin_total" || $_SESSION['cargo'] == "admin") {
              echo '
                <li id="lConfPortada"><a href="confPortada.php"><i class="fa fa-circle-o"></i> Configuración de portada</a></li>
                <li id="lConfBoleta"><a href="confBoleta.php"><i class="fa fa-circle-o"></i> Configuración de boletas</a></li>
                <li id="lLocalesExternos"><a href="localesExternos.php"><i class="fa fa-circle-o"></i> Locales externos</a></li>
                <li id="lLocalesDisponibles"><a href="localesDisponibles.php"><i class="fa fa-circle-o"></i> Locales disponibles</a></li>
              ';
            }
            echo '
            </ul>
          </li>';
          }
          ?>

          <?php
          if ($_SESSION['reportes'] == 1) {
            echo '<li id="mReportes" class="treeview">
              <a href="#">
                <i class="fa fa-bar-chart"></i>
                <span>Reportes generales</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
              <li id="lReporteVenta"><a href="reporteVenta.php"><i class="fa fa-circle-o"></i> Reporte de ventas</a></li>
              <li id="lReporteCotizacion"><a href="reporteProforma.php"><i class="fa fa-circle-o"></i> Reporte de cotizaciones</a></li>
              <li id="lReporteCompra"><a href="reporteCompra.php"><i class="fa fa-circle-o"></i> Reporte de compras</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['reportesP'] == 1) {
            echo '<li id="mReportesP" class="treeview">
              <a href="#">
                <i class="fa fa-area-chart"></i>
                <span>Reportes de productos</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
              <li id="lReporteProductosV"><a href="reporteProductoMasVendido.php"><i class="fa fa-circle-o"></i> Productos más vendidos</a></li>
              <li id="lReporteProductosC"><a href="reporteProductoMasComprado.php"><i class="fa fa-circle-o"></i> Productos más comprados</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['reportesM'] == 1) {
            echo '<li id="mReportesM" class="treeview">
              <a href="#">
                <i class="fa fa-line-chart"></i>
                <span>Reportes de pagos</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
              <li id="lReporteVentaMetodoPago"><a href="reporteVentaMetodoPago.php"><i class="fa fa-circle-o"></i> Métodos de pago (ventas)</a></li>
              <li id="lReporteProformaMetodoPago"><a href="reporteProformaMetodoPago.php"><i class="fa fa-circle-o"></i> Métodos de pago (cotizaciones)</a></li>
              <li id="lReporteCompraMetodoPago"><a href="reporteCompraMetodoPago.php"><i class="fa fa-circle-o"></i> Métodos de pago (compras)</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['reportesE'] == 1) {
            echo '<li id="mReportesE" class="treeview">
              <a href="#">
                <i class="fa fa-balance-scale"></i>
                <span>Reportes de empleados</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
              <li id="lReporteVentaEmpleados"><a href="reporteEmpleado.php"><i class="fa fa-circle-o"></i> Reporte empleados (ventas)</a></li>
              </ul>
            </li>';
          }
          ?>

          <?php
          if ($_SESSION['acceso'] == 1) {
            echo '<li id="mAcceso" class="treeview">
              <a href="#">
                <i class="fa fa-folder"></i> <span>Acceso</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li id="lUsuarios"><a href="usuario.php"><i class="fa fa-circle-o"></i> Usuarios</a></li>
                <li id="lPermisos"><a href="permiso.php"><i class="fa fa-circle-o"></i> Permisos</a></li>
              </ul>
            </li>';
          }
          ?>
          <li>
            <a href="ayuda.php">
              <i class="fa fa-plus-square"></i> <span>Ayuda</span>
              <small class="label pull-right bg-red">PDF</small>
            </a>
          </li>
          <?php
          // if ($_SESSION['cargo'] == "superadmin" || $_SESSION['cargo'] == "admin_total") {
          ?>
          <!-- <li id="sql_export">
              <a>
                <?php
                // if ($_POST) {
                //   if ($_POST["backup"]) {
                //     require("backup/backup.php");
                //     $backupdb = new backupdb();
                //   }
                // }
                ?>
                <form method="post" style="margin: 0 !important;">
                  <input type="hidden" value="true" name="backup">
                  <i class="fa fa-file"></i>
                  <input id="sql" type="submit" value="Exportar DB." style="border: none; background-color: transparent; outline: none;">
                </form>
                <small class="label pull-right bg-green">SQL</small>
              </a>
            </li>
            <div style="display: none;" id="rolUsuario"><?php // echo $_SESSION['cargo'] 
                                                        ?></div> -->
          <?php
          // }
          ?>
        </ul>
      </section>
    </aside>
    <script>
      function destruirSession() {
        sessionStorage.clear();
      }
    </script>