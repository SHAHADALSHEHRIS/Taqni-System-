<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>إضافة طلب مكتمل جديد للاختبار</h3>";
    
    // إضافة طلب جديد
    $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([1, 'ac', 'صيانة مكيف الهواء', 'طلب صيانة مكيف الهواء في غرفة الاجتماعات', 'high', 'completed']);
    
    if ($result) {
        $new_request_id = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ تم إضافة طلب جديد برقم: $new_request_id</p>";
        
        // عرض الطلبات المكتملة
        $stmt = $conn->query("SELECT id, subject, request_type, status FROM requests WHERE status = 'completed'");
        echo "<h4>الطلبات المكتملة (يمكن تقييمها):</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>رقم الطلب</th><th>الموضوع</th><th>النوع</th><th>الحالة</th><th>التقييم</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // التحقق من وجود تقييم
            $stmt2 = $conn->prepare("SELECT rating FROM ratings WHERE request_id = ?");
            $stmt2->execute([$row['id']]);
            $rating = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            $ratingText = $rating ? str_repeat('⭐', $rating['rating']) . " ({$rating['rating']}/5)" : 'لم يتم التقييم بعد';
            
            echo "<tr>";
            echo "<td>#" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>" . $row['request_type'] . "</td>";
            echo "<td style='color: green; font-weight: bold;'>" . $row['status'] . "</td>";
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