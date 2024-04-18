<?php

$bd = [
    'host' => 'localhost',
    'nombreUsuario' => 'root',
    'contraseña' => '',
    'bd' => 'bdproductos'
];

function conectar($bd)
{
    try {
        $conexionBD = new PDO("mysql:host={$bd['host']};dbname={$bd['bd']}", $bd['nombreUsuario'], $bd['contraseña']);
        $conexionBD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexionBD;
    } catch (PDOException $exception) {
        exit($exception->getMessage());
    }
}

function obtenerRutas() {
    return array(
        'GET' => array(
            '/listarProductos' => 'obtenerListadoProductos',
            '/detalleProducto' => 'obtenerProductoPorId',
            '/listadoPaginadoYOrdenado' => 'obtenerListadoProductosPaginadoYOrdenado',
            '/listarCategorias' => 'obtenerCategorias'
        ),
        'POST' => array(
            '/agregarProducto' => 'crearNuevoProducto'
        ),
        'PUT' => array(
            '/actualizarProducto' => 'actualizarProducto'
        ),
        'DELETE' => array(
            '/eliminarProducto' => 'eliminarProducto'
        )
    );
}

function obtenerListadoProductos() {
    $conexionBD = conectar($GLOBALS['bd']);
    $sql = "SELECT * FROM productos";
    $stmt = $conexionBD->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($productos) {
        return $productos;
    } else {
        return array("message" => "No se encontraron productos");
    }
}

function obtenerProductoPorId($parametros) {
    $conexionBD = conectar($GLOBALS['bd']);
    $id = isset($parametros['id']) ? $parametros['id'] : null;
    if ($id !== null) {
        $sql = "SELECT * FROM productos WHERE codprod = :id";
        $stmt = $conexionBD->prepare($sql);
        $stmt->execute(['id' => $id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            return $producto;
        } else {
            return array("message" => "No se encontró ningún producto con el ID proporcionado");
        }
    } else {
        return array("error" => "ID de producto no proporcionado");
    }
}

function obtenerListadoProductosPaginadoYOrdenado($parametros) {
    $conexionBD = conectar($GLOBALS['bd']);
    $pagina = isset($parametros['pagina']) ? intval($parametros['pagina']) : 1;
    $productosPorPagina = isset($parametros['productosPorPag']) ? intval($parametros['productosPorPag']) : 5;
    $offset = ($pagina - 1) * $productosPorPagina;

    $orden = isset($parametros['orden']) ? strtoupper($parametros['orden']) : 'DESC';

    $ordenValido = in_array($orden, ['ASC', 'DESC']) ? $orden : 'DESC';

    $sql = "SELECT p.*, c.nombre AS nombre_categoria FROM productos p 
            INNER JOIN categorias c ON p.categoria = c.id
            ORDER BY c.id $ordenValido 
            LIMIT :offset, :productosPorPag";
    $stmt = $conexionBD->prepare($sql);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':productosPorPag', $productosPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlTotal = "SELECT COUNT(*) AS total FROM productos p 
                 INNER JOIN categorias c ON p.categoria = c.id";
    $stmtTotal = $conexionBD->query($sqlTotal);
    $totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    return array(
        "productos" => $productos,
        "total_registros" => $totalRegistros
    );
}

function crearNuevoProducto() {
    $conexionBD = conectar($GLOBALS['bd']);
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO productos (nombre, categoria, pvp, stock, imagen, Observaciones) 
            VALUES (:nombre, :categoria, :pvp, :stock, :imagen, :observaciones)";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute([
        'nombre' => $data['nombre'],
        'categoria' => $data['categoria'],
        'pvp' => $data['pvp'],
        'stock' => $data['stock'],
        'imagen' => $data['imagen'],
        'observaciones' => $data['observaciones']
    ]);

    if ($stmt->rowCount() > 0) {
        return array("message" => "Producto creado correctamente");
    } else {
        return array("error" => "No se pudo crear el producto");
    }
}

function actualizarProducto($parametros) {
    $conexionBD = conectar($GLOBALS['bd']);
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($parametros['id']) ? $parametros['id'] : null;
    if ($id !== null) {
        $sql = "UPDATE productos 
                SET nombre = :nombre, categoria = :categoria, pvp = :pvp, stock = :stock, imagen = :imagen, Observaciones = :observaciones 
                WHERE codprod = :id";
        $stmt = $conexionBD->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'categoria' => $data['categoria'],
            'pvp' => $data['pvp'],
            'stock' => $data['stock'],
            'imagen' => $data['imagen'],
            'observaciones' => $data['observaciones']
        ]);

        if ($stmt->rowCount() > 0) {
            return array("message" => "Producto actualizado correctamente");
        } else {
            return array("message" => "No se encontró ningún producto con el ID proporcionado");
        }
    } else {
        return array("error" => "ID de producto no proporcionado");
    }
}

function eliminarProducto($parametros) {
    $conexionBD = conectar($GLOBALS['bd']);
    $id = isset($parametros['id']) ? $parametros['id'] : null;
    if (!isset($id) || !is_numeric($id)) {
        return array("error" => "ID de producto no válido");
    }
    $sql = "DELETE FROM productos WHERE codprod = :id";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() > 0) {
        return array("message" => "Producto eliminado correctamente");
    } else {
        return array("message" => "No se encontró ningún producto con el ID proporcionado");
    }
}

function obtenerCategorias() {
    $conexionBD = conectar($GLOBALS['bd']);
    $sql = "SELECT id, nombre FROM categorias";
    $stmt = $conexionBD->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($categorias) {
        return $categorias;
    } else {
        return array("message" => "No se encontraron categorías");
    }
}

?>
