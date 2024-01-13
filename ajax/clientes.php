<?php
ob_start();
if (strlen(session_id()) < 1) {
	session_start(); //Validamos si existe o no la sesión
}

if (empty($_SESSION['idusuario']) || empty($_SESSION['cargo'])) {
	echo 'No está autorizado para realizar esta acción.';
	exit();
}

if (!isset($_SESSION["nombre"])) {
	header("Location: ../vistas/login.html");
} else {
	if ($_SESSION['personas'] == 1) {
		require_once "../modelos/Clientes.php";

		$clientes = new Cliente();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$idlocal_session = $_SESSION["idlocal"];
		$cargo = $_SESSION["cargo"];

		$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
		$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
		$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
		$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
		$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idcliente)) {
					$nombreExiste = $clientes->verificarDniExiste($num_documento);
					if ($nombreExiste && $num_documento != '') {
						echo "El número de documento que ha ingresado ya existe.";
					} else {
						$rspta = $clientes->agregar($idusuario, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email);
						echo $rspta ? "Cliente registrado" : "El cliente no se pudo registrar";
					}
				} else {
					$nombreExiste = $clientes->verificarDniEditarExiste($nombre, $idcliente);
					if ($nombreExiste && $num_documento != '') {
						echo "El número de documento que ha ingresado ya existe.";
					} else {
						$rspta = $clientes->editar($idcliente, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email);
						echo $rspta ? "Cliente actualizado" : "El cliente no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $clientes->desactivar($idcliente);
				echo $rspta ? "Cliente desactivado" : "El cliente no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $clientes->activar($idcliente);
				echo $rspta ? "Cliente activado" : "El cliente no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $clientes->eliminar($idcliente);
				echo $rspta ? "Cliente eliminado" : "El cliente no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $clientes->mostrar($idcliente);
				echo json_encode($rspta);
				break;

			case 'listar':
				if ($cargo == "superadmin") {
					$rspta = $clientes->listarClientes();
				} else {
					$rspta = $clientes->listarClientesPorUsuario($idlocal_session);
				}

				$data = array();

				function mostrarBoton($reg, $cargo, $idusuario, $buttonType)
				{
					if ($reg == "admin" && $cargo == "admin" && $idusuario == $_SESSION["idusuario"]) {
						return $buttonType;
					} elseif ($cargo == "superadmin" || $cargo == "cajero" && $idusuario == $_SESSION["idusuario"]) {
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
						"0" => '<div style="display: flex; flex-wrap: nowrap; gap: 3px">' .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-warning" style="margin-right: 3px; height: 35px;" onclick="mostrar(' . $reg->idcliente . ')"><i class="fa fa-pencil"></i></button>') .
							(($reg->estado == 'activado') ?
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="margin-right: 3px; height: 35px;" onclick="desactivar(' . $reg->idcliente . ')"><i class="fa fa-close"></i></button>')) :
								(mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-success" style="margin-right: 3px; width: 35px; height: 35px;" onclick="activar(' . $reg->idcliente . ')"><i style="margin-left: -2px" class="fa fa-check"></i></button>'))) .
							mostrarBoton($reg->cargo, $cargo, $reg->idusuario, '<button class="btn btn-danger" style="height: 35px;" onclick="eliminar(' . $reg->idcliente . ')"><i class="fa fa-trash"></i></button>') .
							'</div>',
						"1" => ucwords($reg->nombre),
						"2" => $reg->local,
						"3" => $reg->tipo_documento,
						"4" => $reg->num_documento,
						"5" => ($reg->direccion == "") ? "Sin registrar" : $reg->direccion,
						"6" => $telefono,
						"7" => ($reg->email == "") ? "Sin registrar" : $reg->email,
						"8" => ucwords($reg->nombre),
						"9" => ucwords($cargo_detalle),
						"10" => $reg->fecha,
						"11" => ($reg->estado == 'activado') ? '<span class="label bg-green">Activado</span>' :
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
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
