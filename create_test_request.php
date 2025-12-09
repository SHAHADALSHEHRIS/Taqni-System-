<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    // إنشاء طلب جديد للاختبار
    $sql = "INSERT INTO requests (user_id, request_type, subject, description, priority, status) 
            VALUES (1, 'electricity', 'اختبار نظام التقييم', 'طلب اختبار لنظام التقييم الجديد', 'medium', 'completed')";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $request_id = $conn->lastInsertId();
    
    // إضافة سجل في جدول التتبع
    $sql_tracking = "INSERT INTO request_tracking (request_id, status_change, changed_by, notes) 
                     VALUES (?, 'completed', 1, 'تم الإنجاز لأغراض اختبار التقييم')";
    $stmt_tracking = $conn->prepare($sql_tracking);
    $stmt_tracking->execute([$request_id]);
    
    echo "✅ تم إنشاء طلب جديد للاختبار\n";
    echo "رقم الطلب: $request_id\n";
    echo "الحالة: مكتمل (جاهز للتقييم)\n";
    
} catch(Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>