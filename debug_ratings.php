<?php
require_once './config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $conn = $database->connect();
    
    // التحقق من التقييمات الموجودة
    $sql = "SELECT 
                rr.id,
                rr.request_id,
                rr.quality_rate,
                rr.speed_rate,
                (rr.quality_rate + rr.speed_rate) / 2 as overall_rating,
                rr.created_at,
                r.subject as request_subject,
                u.full_name as requester_name,
                u.employee_id
            FROM request_ratings rr
            JOIN requests r ON rr.request_id = r.id
            JOIN users u ON rr.user_id = u.id
            ORDER BY rr.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $ratings,
        'count' => count($ratings),
        'message' => 'Debug: جميع التقييمات من قاعدة البيانات'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>