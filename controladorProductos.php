<?php

include_once 'modeloProductos.php';

function buscarRuta($ruta, $metodo) {
    $rutas = obtenerRutas();
    foreach ($rutas[$metodo] as $endpoint => $funcion) {
        if (strpos($ruta, $endpoint) !== false) {
            return $funcion;
        }
    }
    return null;
}

function ejecutarFuncion($funcion) {
    return call_user_func($funcion, $_GET);
}

?>
