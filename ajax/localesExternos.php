<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

// si no está logeado o no tiene ningún cargo...
if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	// opciones a las que NO pueden tener acceso... si no colocamos ninguno, quiere decir
	// que tiene acceso a todas las opciones si es que está logeado o tiene un cargo.
	if (($_GET["op"] == 'selectLocal' || $_GET["op"] == 'selectLocalUsuario' || $_GET["op"] == 'selectLocalDisponible')) {
		echo 'No está autorizado para realizar esta acción.';
		exit();
	}
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html");
} else {
	if ($_SESSION['perfilu'] == 1) {
		require_once "../modelos/LocalesExternos.php";

		$locales = new LocalExterno();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$idlocal_session = $_SESSION['idlocal'];
		$cargo = $_SESSION["cargo"];

		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$idusuariolocal = isset($_POST["idusuariolocal"]) ? limpiarCadena($_POST["idusuariolocal"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$local_ruc = isset($_POST["local_ruc"]) ? limpiarCadena($_POST["local_ruc"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idlocal)) {
					$nombreExiste = $locales->verificarNombreExiste($titulo);
					if ($nombreExiste) {
						echo "El nombre del local ya existe.";
					} else {
						$rspta = $locales->agregar($idusuario, $titulo, $local_ruc, $descripcion);
						echo $rspta ? "Local registrado" : "El local no se pudo registrar";
					}
				} else {
					$nombreExiste = $locales->verificarNombreEditarExiste($titulo, $idlocal);
					if ($nombreExiste) {
						echo "El nombre del local ya existe.";
					} else {
						$rspta = $locales->editar($idlocal, $titulo, $local_ruc, $descripcion);
						echo $rspta ? "Local actualizado" : "El local no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $locales->desactivar($idlocal);
				echo $rspta ? "Local desactivado" : "El local no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $locales->activar($idlocal);
				echo $rspta ? "Local activado" : "El local no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $locales->eliminar($idlocal);
				echo $rspta ? "Local eliminado" : "El local no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $locales->mostrar($idlocal);
				echo json_encode($rspta);
				break;

			case 'listar':

				$rspta = $locales->listar($idlocal_session);

				$data = array();

				function mostrarBoton($cargo, $buttonType)
				{
					if ($cargo == "superadmin" || $cargo == "admin" || $cargo == "admin_total") {
						return $buttonType;
					} else {
						return '';
					}
				}

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";

					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
							break;
						case 'admin_total':
							$cargo_detalle = "Admin Total";
							break;
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'cajero':
							$cargo_detalle = "Cajero";
							break;
						default:
							break;
					}

					$reg->descripcion = (strlen($reg->descripcion) > 70) ? substr($reg->descripcion, 0, 70) . "..." : $reg->descripcion;

					$data[] = array(
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px;">' .
							mostrarBoton($cargo, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idlocal . ')"><i class="fa fa-pencil"></i></button>') .
							mostrarBoton($cargo, '<a data-toggle="modal" href="#myModal"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="trabajadores(' . $reg->idlocal . ',\'' . $reg->titulo . '\')"><i class="fa fa-user"></i></button></a>') .
							'<button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="mostrar2(' . $reg->idlocal . ')"><i class="fa fa-eye"></i></button>' .
							(($reg->estado == 'activado') ?
								(mostrarBoton($cargo, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idlocal . ')"><i class="fa fa-close"></i></button>')) : (mostrarBoton($cargo, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idlocal . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							'</div>',
						"1" => $reg->titulo,
						"2" => "N° " . $reg->local_ruc,
						"3" => ($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion,
						"4" => $reg->fecha,
						"5" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
							'<span class="label bg-red">Desactivado</span>'
					);
				}
				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);

				echo json_encode($results);
				break;

			case 'listarTrabajadores':

				$idlocal2 = isset($_GET["idlocal"]) ? limpiarCadena($_GET["idlocal"]) : "";

				$rspta = $locales->listarUsuariosPorLocal($idlocal2);

				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$cargo_detalle = "";
					switch ($reg->cargo) {
						case 'superadmin':
							$cargo_detalle = "Superadministrador";
							break;
						case 'admin_total':
							$cargo_detalle = "Admin Total";
							break;
						case 'admin':
							$cargo_detalle = "Administrador";
							break;
						case 'cajero':
							$cargo_detalle = "Cajero";
							break;
						default:
							break;
					}

					$telefono = ($reg->telefono == '') ? 'Sin registrar' : number_format($reg->telefono, 0, '', ' ');

					$data[] = array(
						"0" => $reg->login,
						"1" => $cargo_detalle,
						"2" => $reg->nombre,
						"3" => $reg->tipo_documento,
						"4" => $reg->num_documento,
						"5" => $telefono,
						"6" => $reg->email,
						"7" => '<a href="../files/usuarios/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
									<img src="../files/usuarios/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
								</a>',
						"8" => ($reg->estado) ? '<span class="label bg-green">Activado</span>' :
							'<span class="label bg-red">Desactivado</span>'
					);
				}
				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);

				echo json_encode($results);
				break;

			case 'selectLocal':
				$rspta = $locales->listarPorUsuarioActivos($idlocal_session);
				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$data["locales"][] = $row;
				}

				echo json_encode($data);
				break;

			case 'selectLocalASC':
				$rspta = $locales->listarPorUsuarioActivosASC($idlocal_session);
				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$data["locales"][] = $row;
				}

				echo json_encode($data);
				break;

			case 'selectLocales':
				$rspta = $locales->listarActivos($idlocal_session);

				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idlocal . '"> ' . $reg->titulo . '</option>';
				}
				break;

			case 'selectLocalesUsuario':

				$rspta = $locales->listarActivosASC($idlocal_session);

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idlocal . '"> ' . $reg->titulo . '</option>';
				}
				break;

			case 'selectLocalUsuario':
				$rspta = $locales->listarPorUsuarioActivos($idusuariolocal);

				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idlocal . '" data-local-ruc="' . $reg->local_ruc . '"> ' . $reg->titulo . '</option>';
				}
				break;

			case 'selectLocalDisponible':
				$rspta = $locales->listarLocalesDisponiblesActivos($idlocal_session);
				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$data["locales"][] = $row;
				}

				echo json_encode($data);
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
