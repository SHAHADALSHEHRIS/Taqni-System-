<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>إضافة طلب جديد للاختبار</h3>";
    
    // إضافة طلب جديد
    $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        1, 
        'electricity', 
        'إضاءة المكتب', 
        'طلب إصلاح إضاءة المكتب الرئيسي - المصابيح لا تعمل', 
        'high', 
        'pending'
    ]);
    
    if ($result) {
        $new_request_id = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ تم إضافة طلب جديد برقم: $new_request_id</p>";
        
        // إضافة سجل تتبع
        $stmt = $conn->prepare("INSERT INTO request_tracking (request_id, status_change, notes, changed_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$new_request_id, 'تم إنشاء الطلب', 'طلب جديد تم تقديمه للنظام', 1]);
        
        echo "<p style='color: green;'>✅ تم إضافة سجل التتبع</p>";
        
        // عرض آخر 5 طلبات
        $stmt = $conn->query("SELECT id, subject, request_type, status, created_at FROM requests ORDER BY created_at DESC LIMIT 5");
        echo "<h4>آخر 5 طلبات في النظام:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>رقم الطلب</th><th>الموضوع</th><th>النوع</th><th>الحالة</th><th>التاريخ</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statusColor = '';
            switch($row['status']) {
                case 'completed': $statusColor = 'color: #28a745; font-weight: bold;'; break;
                case 'in_progress': $statusColor = 'color: #ffc107; font-weight: bold;'; break;
                case 'pending': $statusColor = 'color: #dc3545; font-weight: bold;'; break;
            }
            
            echo "<tr>";
            echo "<td>#" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>" . $row['request_type'] . "</td>";
            echo "<td style='$statusColor'>" . $row['status'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // عرض إجمالي الطلبات
        $stmt = $conn->query("SELECT COUNT(*) as total FROM requests");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>إجمالي الطلبات في النظام: $total</strong></p>";
        
    } else {
        echo "<p style='color: red;'>❌ فشل في إضافة الطلب</p>";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>