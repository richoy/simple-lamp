<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Lectura de variables de entorno (sin credenciales expuestas en texto plano)
$host = getenv('MYSQL_HOST') ?: 'mysql';
$dbname = getenv('MYSQL_DATABASE') ?: 'hospital';
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

// Verificación de credenciales antes de intentar la conexión
if (!$username || !$password) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuración de base de datos incompleta en el servidor']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

$data = json_decode(file_get_contents('php://input'), true) ?? [];

// ============================================
// ENDPOINTS
// ============================================
if ($method === 'GET' && $path === 'medicos') {
    // Obtener catálogo de personal médico
    $stmt = $pdo->query("SELECT * FROM medicos");
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($medicos);

} elseif ($method === 'GET' && $path === 'registros') {
    // Obtener historial de ingresos y salidas
    $stmt = $pdo->query("
        SELECT r.*, m.nombre as medico_nombre, m.especialidad 
        FROM registros r 
        JOIN medicos m ON r.medico_id = m.id 
        ORDER BY r.fecha DESC
    ");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($registros);

} elseif ($method === 'POST' && $path === 'registros') {
    // Registrar un nuevo ingreso o salida
    $medico_id = $data['medico_id'] ?? 0;
    $tipo = $data['tipo'] ?? ''; // ENTRADA / SALIDA
 
    if ($medico_id <= 0 || !in_array($tipo, ['ENTRADA', 'SALIDA'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros o tipo de movimiento inválidos']);
        exit;
    }
 
    $stmt = $pdo->prepare("INSERT INTO registros (medico_id, tipo) VALUES (?, ?)");
    $stmt->execute([$medico_id, $tipo]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado']);
}