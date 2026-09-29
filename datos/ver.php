<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debe iniciar sesión.'
    ]);
    exit;
}

// ✅ Usar la conexión existente en lugar de config/database.php
require_once 'conexion.php';

try {
    // ✅ $pdo ya viene definido desde conexion.php
    // Obtener parámetros
    $dni = isset($_GET['dni']) ? trim($_GET['dni']) : null;
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'estudiante';

    // Validar DNI (8 dígitos)
    if (!$dni || !preg_match('/^\d{8}$/', $dni)) {
        echo json_encode([
            'success' => false,
            'message' => 'DNI inválido. Debe contener exactamente 8 dígitos.'
        ]);
        exit;
    }

    // Determinar la tabla según el tipo
    $tabla = '';
    switch ($tipo) {
        case 'estudiante':
            $tabla = 'estudiantes';
            break;
        case 'docente':
            $tabla = 'docentes';
            break;
        case 'persona':
            $tabla = 'personas';
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de búsqueda no válido.'
            ]);
            exit;
    }

    // Preparar consulta
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.nombre as carrera_nombre,
               r.nombre as rol_nombre
        FROM {$tabla} e
        INNER JOIN personas p ON e.persona_id = p.id
        LEFT JOIN carreras c ON e.carrera_id = c.id
        LEFT JOIN roles r ON p.rol_id = r.id
        WHERE p.dni = :dni
        LIMIT 1
    ");

    $stmt->execute([':dni' => $dni]);
    $resultado = $stmt->fetch();

    if ($resultado) {
        // Ocultar datos sensibles si no es administrador
        if ($_SESSION['rol'] !== 'administrador') {
            unset($resultado['password']);
            unset($resultado['dni']);
        }

        echo json_encode([
            'success' => true,
            'data' => $resultado
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró ningún registro con el DNI proporcionado.'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error en ver.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud. Intente nuevamente.'
    ]);
}
?>