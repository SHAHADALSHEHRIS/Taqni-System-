<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>حذف الطلبات غير المرتبطة بالموقع</h3>";
    
    // الطلبات المراد حذفها (التي لا تخص موقعك)
    $irrelevantTypes = ['maintenance', 'repair', 'exchange', 'return', 'replacement'];
    
    // أولاً، احصل على IDs الطلبات المراد حذفها
    $placeholders = str_repeat('?,', count($irrelevantTypes) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, subject FROM requests WHERE request_type IN ($placeholders)");
    $stmt->execute($irrelevantTypes);
    $requestsToDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($requestsToDelete) > 0) {
        echo "<p>الطلبات المراد حذفها:</p>";
        echo "<ul>";
        foreach ($requestsToDelete as $req) {
            echo "<li>ID: {$req['id']} - {$req['subject']}</li>";
        }
        echo "</ul>";
        
        // حذف سجلات التتبع أولاً (بسبب foreign key constraint)
        $requestIds = array_column($requestsToDelete, 'id');
        $placeholders2 = str_repeat('?,', count($requestIds) - 1) . '?';
        
        $stmt = $conn->prepare("DELETE FROM request_tracking WHERE request_id IN ($placeholders2)");
        $trackingDeleted = $stmt->execute($requestIds);
        echo "<p>✅ تم حذف " . $stmt->rowCount() . " سجل تتبع</p>";
        
        // حذف التقييمات إن وجدت
        $stmt = $conn->prepare("DELETE FROM ratings WHERE request_id IN ($placeholders2)");
        $ratingsDeleted = $stmt->execute($requestIds);
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ تم حذف " . $stmt->rowCount() . " تقييم</p>";
        }
        
        // حذف الطلبات نفسها
        $stmt = $conn->prepare("DELETE FROM requests WHERE request_type IN ($placeholders)");
        $requestsDeleted = $stmt->execute($irrelevantTypes);
        echo "<p>✅ تم حذف " . $stmt->rowCount() . " طلب</p>";
        
        echo "<p style='color: green; font-weight: bold;'>🎉 تم تنظيف قاعدة البيانات بنجاح!</p>";
        
    } else {
        echo "<p>لا توجد طلبات مراد حذفها.</p>";
    }
    
    // عرض الطلبات المتبقية
    echo "<hr><h3>الطلبات المتبقية (تخص موقعك)</h3>";
    $stmt = $conn->query("SELECT id, subject, request_type, status, created_at FROM requests ORDER BY id DESC");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>الموضوع</th><th>النوع</th><th>الحالة</th><th>التاريخ</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
        echo "<td>" . $row['request_type'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>