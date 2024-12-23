<?php
require_once "../config/Conexion.php";

class CargaMasiva
{
	/* ===================  OBTENER CÓDIGO PARA MODIFICAR CELDA DE PLANTILLA ====================== */

	public function getLastCodigo($idlocal)
    {
        $sql = "SELECT codigo_producto AS last_codigo 
                FROM articulo 
                WHERE idlocal = '$idlocal' AND eliminado = '0' 
                ORDER BY idarticulo DESC 
                LIMIT 1";
        return ejecutarConsulta($sql);
    }

	/* ===================  CARGAR DATOS DEL MODAL DE INFORMACIÓN ====================== */

	public function listarCategorias()
	{
		$sql = "SELECT idcategoria AS id, titulo, descripcion FROM categoria WHERE eliminado = 0";
		return ejecutarConsulta($sql);
	}

	public function listarLocales()
	{
		$sql = "SELECT idlocal AS id, imagen, titulo, local_ruc, empresa, descripcion FROM locales WHERE eliminado = 0";
		return ejecutarConsulta($sql);
	}

	public function listarLocalesPorUsuario($idlocalSession)
	{
		$sql = "SELECT idlocal AS id, imagen, titulo, local_ruc, empresa, descripcion FROM locales WHERE idlocal = '$idlocalSession' AND eliminado = 0";
		return ejecutarConsulta($sql);
	}

	public function listarMarcas()
	{
		$sql = "SELECT idmarca AS id, titulo, descripcion FROM marcas WHERE eliminado = 0";
		return ejecutarConsulta($sql);
	}

	public function listarMedidas()
	{
		$sql = "SELECT idmedida AS id, titulo, descripcion FROM medidas WHERE eliminado = 0";
		return ejecutarConsulta($sql);
	}
}
