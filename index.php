<?php

include_once 'controladorProductos.php';

$ruta = $_SERVER['REQUEST_URI'];
$metodo = $_SERVER['REQUEST_METHOD'];

$rutaEncontrada = buscarRuta($ruta, $metodo);

if ($rutaEncontrada === null) {
    http_response_code(400);
    $respuesta = array("error" => "Ruta no encontrada");
} else {
    $respuesta = ejecutarFuncion($rutaEncontrada);
    if (isset($respuesta['error'])) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }
}

header('Content-Type: application/json');
echo json_encode($respuesta);

?>
