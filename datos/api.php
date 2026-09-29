<?php
// api.php - Endpoint principal para obtener datos

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Permitir acceso desde cualquier origen (para pruebas)

require_once 'db.php'; // Incluimos la conexión

// 1. Obtener parámetros de la URL (GET)
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$id   = isset($_GET['id']) ? $_GET['id'] : null;

// 2. Validaciones básicas
if (!$tipo || !$id) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros. Uso: api.php?tipo=persona&id=12345678'
    ]);
    exit;
}

try {
    $data = null;
    $ubicacion = null;

    // 3. Lógica según el tipo de búsqueda
    if ($tipo === 'persona') {
        // Buscar en tabla personas
        $stmt = $pdo->prepare("SELECT * FROM personas WHERE dni = ? LIMIT 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            // Buscar última ubicación de esta persona
            $stmtLoc = $pdo->prepare("SELECT * FROM ubicaciones_historial WHERE entidad_id = ? AND tipo_entidad = 'persona' ORDER BY fecha_registro DESC LIMIT 1");
            $stmtLoc->execute([$data['id']]);
            $ubicacion = $stmtLoc->fetch();
        }

    } elseif ($tipo === 'maquina') {
        // Buscar en tabla maquinas
        $stmt = $pdo->prepare("SELECT * FROM maquinas WHERE codigo_maquina = ? LIMIT 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            // Buscar última ubicación de esta máquina
            $stmtLoc = $pdo->prepare("SELECT * FROM ubicaciones_historial WHERE entidad_id = ? AND tipo_entidad = 'maquina' ORDER BY fecha_registro DESC LIMIT 1");
            $stmtLoc->execute([$data['id']]);
            $ubicacion = $stmtLoc->fetch();
        }
        
        // Si la máquina tiene coordenadas guardadas en su propia tabla, usarlas como fallback
        if (!$ubicacion && $data && $data['ubicacion_lat']) {
            $ubicacion = [
                'latitud' => $data['ubicacion_lat'],
                'longitud' => $data['ubicacion_lng'],
                'fecha_registro' => 'Ubicación fija en tabla máquina'
            ];
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo inválido. Use "persona" o "maquina".']);
        exit;
    }

    // 4. Respuesta Final
    if ($data) {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'ubicacion_actual' => $ubicacion
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron registros con ese identificador.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>