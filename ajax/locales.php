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
	if ($_SESSION['almacen'] == 1) {
		require_once "../modelos/Locales.php";

		$locales = new Local();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$idlocalSession = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
		$empresa = isset($_POST["empresa"]) ? limpiarCadena($_POST["empresa"]) : "";
		$local_ruc = isset($_POST["local_ruc"]) ? limpiarCadena($_POST["local_ruc"]) : "";
		$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
		$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";

		$param = isset($_GET["param"]) ? $_GET["param"] : 0;

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (!empty($_FILES['imagen']['name'])) {
					$uploadDirectory = "../files/locales/";

					$tempFile = $_FILES['imagen']['tmp_name'];
					$fileExtension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
					$newFileName = sprintf("%09d", rand(0, 999999999)) . '.' . $fileExtension;
					$targetFile = $uploadDirectory . $newFileName;

					// Verificar si es una imagen y mover el archivo
					$allowedExtensions = array('jpg', 'jpeg', 'png');
					if (in_array($fileExtension, $allowedExtensions) && move_uploaded_file($tempFile, $targetFile)) {
						// El archivo se ha movido correctamente, ahora $newFileName contiene el nombre del archivo
						$imagen = $newFileName;
					} else {
						// Error en la subida del archivo
						echo "Error al subir la imagen.";
						exit;
					}
				} else {
					// No se ha seleccionado ninguna imagen
					$imagen = $_POST["imagenactual"];
				}

				if (empty($idlocal)) {
					$nombreExiste = $locales->verificarNombreExiste($titulo);
					if ($nombreExiste) {
						echo "El nombre del local ya existe.";
					} else {
						$rspta = $locales->agregar($idusuario, $titulo, $empresa, $local_ruc, $descripcion, $imagen);
						echo $rspta ? "Local registrado" : "El local no se pudo registrar";
						if ($rspta) {
							$_SESSION['local'] = $titulo;
							$_SESSION['local_imagen'] = $imagen;
						}
					}
				} else {
					$nombreExiste = $locales->verificarNombreEditarExiste($titulo, $idlocal);
					if ($nombreExiste) {
						echo "El nombre del local ya existe.";
					} else {
						$rspta = $locales->editar($idlocal, $titulo, $empresa, $local_ruc, $descripcion, $imagen);
						echo $rspta ? "Local actualizado." : "El local no se pudo actualizar";
						if ($rspta) {
							$_SESSION['local'] = $titulo;
							$_SESSION['empresa'] = $empresa;
							$_SESSION['local_imagen'] = $imagen;
						}
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
				if ($param == 1)
					$rspta = $locales->listar();
				else
					$rspta = $locales->listarPorUsuario($idlocalSession);


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

					$data[] = array(
						"0" => ('<div style="display: flex; flex-wrap: nowrap; gap: 3px; justify-content: center;">' .
							'<button class="btn btn-warning" style="margin-right: 3px;" onclick="mostrar(' . $reg->idlocal . ')"><i class="fa fa-pencil"></i></button>' .
							'<button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="mostrar2(' . $reg->idlocal . ')"><i class="fa fa-eye"></i></button>' .
							'<a data-toggle="modal" href="#myModal"><button class="btn btn-bcp" style="margin-right: 3px; height: 35px;" onclick="trabajadores(' . $reg->idlocal . ',\'' . $reg->titulo . '\')"><i class="fa fa-user"></i></button></a>' .
							'</div>'),
						"1" => '<a href="../files/locales/' . $reg->imagen . '" class="galleria-lightbox" style="z-index: 10000 !important;">
									<img src="../files/locales/' . $reg->imagen . '" height="50px" width="50px" class="img-fluid">
								</a>',
						"2" => $reg->titulo,
						"3" => "N° " . $reg->local_ruc,
						"4" => $reg->empresa,
						"5" => "<textarea type='text' class='form-control' rows='2' style='background-color: white !important; cursor: default; height: 60px !important;'' readonly>" . (($reg->descripcion == '') ? 'Sin registrar.' : $reg->descripcion) . "</textarea>",
						"6" => $reg->fecha,
						"7" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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

			case 'listarUsuariosLocal':

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

					$telefono = ($reg->telefono == '') ? 'Sin registrar.' : number_format($reg->telefono, 0, '', ' ');

					$data[] = array(
						"0" => $reg->login,
						"1" => $cargo_detalle,
						"2" => $reg->nombre,
						"3" => $reg->tipo_documento,
						"4" => $reg->num_documento,
						"5" => $telefono,
						"6" => $reg->email,
						"7" => $reg->local,
						"8" => "N° " . $reg->local_ruc,
						"9" => "<img src='../files/usuarios/" . $reg->imagen . "' height='50px' width='50px' >",
						"10" => ($reg->estado) ? '<span class="label bg-green">Activado</span>' :
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

			case 'selectLocalASC':
				$rspta = $locales->listarPorUsuarioActivosASC($idlocalSession);
				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$data["locales"][] = $row;
				}

				echo json_encode($data);
				break;

			case 'selectLocalesUsuario':

				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $locales->listarActivosASC();
				} else {
					$rspta = $locales->listarPorUsuarioActivosASC($idlocalSession);
				}

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idlocal . '" data-local-ruc="' . $reg->local_ruc . '"> ' . $reg->titulo . '</option>';
				}
				break;

			case 'selectLocales':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $locales->listarActivosASC();
				} else {
					$rspta = $locales->listarPorUsuarioActivosASC($idlocalSession);
				}

				$result = mysqli_fetch_all($rspta, MYSQLI_ASSOC);

				$data = [];
				foreach ($result as $row) {
					$data["locales"][] = $row;
				}

				echo json_encode($data);
				break;

			case 'selectLocal':
				if ($cargo == "superadmin" || $cargo == "admin_total") {
					$rspta = $locales->listarActivosASC();
				} else {
					$rspta = $locales->listarPorUsuario($idlocalSession);
				}

				echo '<option value="">- Seleccione -</option>';
				while ($reg = $rspta->fetch_object()) {
					echo '<option value="' . $reg->idlocal . '" data-local-ruc="' . $reg->local_ruc . '">' . $reg->titulo . '</option>';
				}
				break;

			case 'actualizarSession':
				$info = array(
					'local' => $_SESSION['local'],
					'local_imagen' => $_SESSION['local_imagen'],
				);

				echo json_encode($info);
				break;
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
