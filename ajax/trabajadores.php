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
		require_once "../modelos/Trabajadores.php";

		$trabajadores = new Trabajador();

		// Variables de sesión a utilizar.
		$idusuario = $_SESSION["idusuario"];
		$cargo = $_SESSION["cargo"];

		$idtrabajador = isset($_POST["idtrabajador"]) ? limpiarCadena($_POST["idtrabajador"]) : "";
		$idlocal = isset($_POST["idlocal"]) ? limpiarCadena($_POST["idlocal"]) : "";
		$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
		$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
		$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
		$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
		$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
		$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";
		$fecha_nac = isset($_POST["fecha_nac"]) ? limpiarCadena($_POST["fecha_nac"]) : "";

		switch ($_GET["op"]) {
			case 'guardaryeditar':
				if (empty($idtrabajador)) {
					$nombreExiste = $trabajadores->verificarNombreExiste($nombre);
					if ($nombreExiste) {
						echo "El nombre del trabajador ya existe.";
					} else {
						$rspta = $trabajadores->agregar($idusuario, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email, $fecha_nac);
						echo $rspta ? "Trabajador registrado" : "El trabajador no se pudo registrar";
					}
				} else {
					$nombreExiste = $trabajadores->verificarNombreEditarExiste($nombre, $idtrabajador);
					if ($nombreExiste) {
						echo "El nombre del trabajador ya existe.";
					} else {
						$rspta = $trabajadores->editar($idtrabajador, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email, $fecha_nac);
						echo $rspta ? "Trabajador actualizado" : "El trabajador no se pudo actualizar";
					}
				}
				break;

			case 'desactivar':
				$rspta = $trabajadores->desactivar($idtrabajador);
				echo $rspta ? "Trabajador desactivado" : "El trabajador no se pudo desactivar";
				break;

			case 'activar':
				$rspta = $trabajadores->activar($idtrabajador);
				echo $rspta ? "Trabajador activado" : "El trabajador no se pudo activar";
				break;

			case 'eliminar':
				$rspta = $trabajadores->eliminar($idtrabajador);
				echo $rspta ? "Trabajador eliminado" : "El trabajador no se pudo eliminar";
				break;

			case 'mostrar':
				$rspta = $trabajadores->mostrar($idtrabajador);
				echo json_encode($rspta);
				break;

			case 'listar':

				if ($cargo == "superadmin" || $cargo == "admin") {
					$rspta = $trabajadores->listarTrabajadores();
				} else {
					$rspta = $trabajadores->listarTrabajadoresPorUsuario($idusuario);
				}

				$data = array();

				while ($reg = $rspta->fetch_object()) {
					$data[] = array(
						"0" => ucwords($reg->nombre),
						"1" => $reg->tipo_documento,
						"2" => $reg->num_documento,
						"3" => $reg->direccion,
						"4" => $reg->telefono,
						"5" => $reg->email,
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
		}
	} else {
		require 'noacceso.php';
	}
}
ob_end_flush();
