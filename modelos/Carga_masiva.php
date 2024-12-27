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
		return $this->obtenerResultadosArray($sql);
	}

	public function listarLocales()
	{
		$sql = "SELECT idlocal AS id, imagen, titulo, local_ruc, empresa, descripcion FROM locales WHERE eliminado = 0";
		return $this->obtenerResultadosArray($sql);
	}

	public function listarLocalesPorUsuario($idlocalSession)
	{
		$sql = "SELECT idlocal AS id, imagen, titulo, local_ruc, empresa, descripcion FROM locales WHERE idlocal = '$idlocalSession' AND eliminado = 0";
		return $this->obtenerResultadosArray($sql);
	}

	public function listarMarcas()
	{
		$sql = "SELECT idmarca AS id, titulo, descripcion FROM marcas WHERE eliminado = 0";
		return $this->obtenerResultadosArray($sql);
	}

	public function listarMedidas()
	{
		$sql = "SELECT idmedida AS id, titulo, descripcion FROM medidas WHERE eliminado = 0";
		return $this->obtenerResultadosArray($sql);
	}

	private function obtenerResultadosArray($sql)
	{
		$resultado = ejecutarConsulta($sql);
		$data = [];
		while ($fila = mysqli_fetch_assoc($resultado)) {
			$data[] = $fila;
		}
		return $data;
	}

	/* ===================  VALIDACIONES DE LA TABLA ====================== */

	// Verificar si el código de producto ya existe en un local
	public function verificarCodigoProductoExiste($codigo_producto, $idlocal)
	{
		$sql = "SELECT * FROM articulo WHERE codigo_producto = '$codigo_producto' AND idlocal = '$idlocal' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		return mysqli_num_rows($resultado) > 0;
	}

	// Verificar si el código de barra ya existe
	public function verificarCodigoBarraExiste($codigo)
	{
		$sql = "SELECT * FROM articulo WHERE codigo = '$codigo' AND eliminado = '0'";
		$resultado = ejecutarConsulta($sql);
		return mysqli_num_rows($resultado) > 0;
	}

	/* =================== GUARDAR LOS PRODUCTOS DE LA FILA ====================== */

	public function insertarProductosCargaMasiva($idusuario, $idcategoria, $idlocal, $idmarca, $idmedida, $codigo, $codigo_producto, $nombre, $stock, $stock_minimo, $descripcion, $talla, $color, $peso, $fecha_emision, $fecha_vencimiento, $nota_1, $nota_2, $imagen, $precio_compra, $precio_venta, $precio_venta_mayor, $ganancia, $comision)
	{
		$sql = "INSERT INTO articulo (idusuario, idcategoria, idlocal, idmarca, idmedida, codigo, codigo_producto, nombre, stock, stock_minimo, descripcion, talla, color, peso, fecha_emision, fecha_vencimiento, nota_1, nota_2, imagen, precio_compra, precio_venta, precio_venta_mayor, ganancia, comision, estado, eliminado)
				VALUES ('$idusuario', '$idcategoria', '$idlocal', '$idmarca', '$idmedida','$codigo', '$codigo_producto', '$nombre', '$stock', '$stock_minimo','$descripcion', '$talla', '$color', '$peso', '$fecha_emision','$fecha_vencimiento', '$nota_1', '$nota_2', '$imagen', '$precio_compra','$precio_venta', '$precio_venta_mayor', '$ganancia', '$comision','1', '0')";

		return ejecutarConsulta($sql);
	}
}
