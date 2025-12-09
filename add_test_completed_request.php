<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>إضافة طلب مكتمل جديد للاختبار</h3>";
    
    // إضافة طلب جديد
    $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([1, 'plumbing', 'إصلاح صنبور المياه', 'طلب إصلاح تسريب في صنبور المياه بالحمام', 'medium', 'completed']);
    
    if ($result) {
        $new_request_id = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ تم إضافة طلب جديد برقم: $new_request_id</p>";
        
        // عرض الطلبات المكتملة
        $stmt = $conn->query("
            SELECT r.*, 
                   CASE WHEN rt.id IS NOT NULL THEN 1 ELSE 0 END as has_rating,
                   rt.rating,
                   rt.comment
            FROM requests r 
            LEFT JOIN ratings rt ON r.id = rt.request_id 
            WHERE r.status = 'completed'
            ORDER BY r.id DESC
        ");
        
        echo "<h4>الطلبات المكتملة:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>رقم الطلب</th><th>الموضوع</th><th>النوع</th><th>حالة التقييم</th><th>التقييم</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ratingStatus = $row['has_rating'] ? 'تم التقييم' : 'لم يتم التقييم بعد';
            $ratingText = $row['has_rating'] ? 
                str_repeat('⭐', $row['rating']) . " ({$row['rating']}/5)" : 
                'متاح للتقييم';
            
            echo "<tr>";
            echo "<td>#" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>" . $row['request_type'] . "</td>";
            echo "<td style='font-weight: bold; color: " . ($row['has_rating'] ? '#28a745' : '#ffc107') . ";'>$ratingStatus</td>";
            echo "<td>$ratingText</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ فشل في إضافة الطلب</p>";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>