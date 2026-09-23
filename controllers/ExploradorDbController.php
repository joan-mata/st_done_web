<?php
// controllers/ExploradorDbController.php
// Controlador de la zona educativa de base de datos.

require_once __DIR__ . '/../models/ExploradorDb.php';

class ExploradorDbController {

    public function mostrar(): void {
        $modelo = new ExploradorDb();
        $tablas = $modelo->getTablas();
        $tablaActual = $_GET['tabla'] ?? 'productos';

        if (!isset($tablas[$tablaActual])) {
            $tablaActual = 'productos';
        }

        $resumen = $modelo->getResumen();
        $filas = $modelo->getFilas($tablaActual);
        require __DIR__ . '/../views/explorador_db.php';
    }
}
