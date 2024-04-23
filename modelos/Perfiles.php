<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Perfiles
{
	//Implementamos nuestro constructor
	public function __construct()
	{
	}

	/* ===================  ESCRITORIO ====================== */

	public function comprasultimos_10dias()
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_compra) AS total 
				FROM compra 
				WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function comprasultimos_10diasUsuario($idlocal)
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_compra) AS total 
				FROM compra 
				WHERE idlocal = '$idlocal' AND fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function ventasultimos_10dias()
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_venta) AS total 
				FROM venta 
				WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function ventasultimos_10diasUsuario($idlocal)
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_venta) AS total 
				FROM venta 
				WHERE idlocal = '$idlocal' AND fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function proformasultimos_10dias()
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_venta) AS total 
				FROM proforma 
				WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function proformasultimos_10diasUsuario($idlocal)
	{
		$sql = "SELECT CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora)) AS fecha, SUM(total_venta) AS total 
				FROM proforma 
				WHERE idlocal = '$idlocal' AND fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND eliminado = '0'
				GROUP BY CONCAT(DAY(fecha_hora), '-', MONTH(fecha_hora))
				ORDER BY fecha_hora ASC";

		return ejecutarConsulta($sql);
	}

	public function totalVentas()
	{
		$sql = "SELECT SUM(total_venta) AS total 
            	FROM venta
				WHERE eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}

	public function totalVentasUsuario($idlocal)
	{
		$sql = "SELECT SUM(total_venta) AS total 
				FROM venta 
				WHERE idlocal = '$idlocal' AND eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}

	public function totalVentasProforma()
	{
		$sql = "SELECT SUM(total_venta) AS total 
				FROM proforma
				WHERE eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}

	public function totalVentasProformaUsuario($idlocal)
	{
		$sql = "SELECT SUM(total_venta) AS total 
				FROM proforma 
				WHERE idlocal = '$idlocal' AND eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}

	public function totalCompras()
	{
		$sql = "SELECT SUM(total_compra) AS total 
            	FROM compra
				WHERE eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}

	public function totalComprasUsuario($idlocal)
	{
		$sql = "SELECT SUM(total_compra) AS total 
            	FROM compra 
            	WHERE idlocal = '$idlocal' AND eliminado = '0'";

		return ejecutarConsultaSimpleFila($sql);
	}


	/* ===================  PERFILES DE USUARIO ====================== */
	public function mostrarUsuario($idusuario)
	{
		$sql = "SELECT * FROM usuario WHERE idusuario='$idusuario'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function actualizarPerfilUsuario($idusuario, $idlocal, $nombre, $tipo_documento, $num_documento, $direccion, $telefono, $email, $login, $clave, $imagen)
	{
		$sql = "UPDATE usuario SET idlocal='$idlocal',nombre='$nombre',tipo_documento='$tipo_documento',num_documento='$num_documento',direccion='$direccion',telefono='$telefono',email='$email',login='$login',clave='$clave',imagen='$imagen' WHERE idusuario='$idusuario'";
		return ejecutarConsulta($sql);
	}

	/* ===================  PORTADA DE LOGIN ====================== */
	public function actualizarPortadaLogin($imagen)
	{
		$sql = "UPDATE portada_login SET imagen='$imagen'";
		return ejecutarConsulta($sql);
	}

	public function obtenerPortadaLogin()
	{
		$sql = "SELECT * FROM portada_login";
		return ejecutarConsultaSimpleFila($sql);
	}

	/* ===================  REPORTES ====================== */
	public function mostrarReporte()
	{
		$sql = "SELECT * FROM reportes";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function actualizarBoleta($idreporte, $titulo, $ruc, $direccion, $telefono, $email, $auspiciado, $imagen)
	{
		$sql = "UPDATE reportes SET titulo='$titulo',ruc='$ruc',direccion='$direccion',telefono='$telefono',email='$email',auspiciado='$auspiciado',imagen='$imagen' WHERE idreporte='$idreporte'";
		return ejecutarConsulta($sql);
	}
}
