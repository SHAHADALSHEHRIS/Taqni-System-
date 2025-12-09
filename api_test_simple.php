<?php
// اختبار بسيط للAPI
require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
        exit;
    }
    
    // اختبار بسيط للإحصائيات
    $sql = "SELECT COUNT(*) as total FROM requests";
    $stmt = $conn->query($sql);
    $total = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'message' => 'الاتصال ناجح',
        'total_requests' => $total,
        'database_connected' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>