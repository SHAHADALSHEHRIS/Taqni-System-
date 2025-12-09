<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>فحص البيانات في قاعدة البيانات</h3>";
    
    // فحص جدول الطلبات
    $stmt = $conn->query("SELECT COUNT(*) as count FROM requests");
    $requestCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>عدد الطلبات في جدول requests: <strong>$requestCount</strong></p>";
    
    if ($requestCount > 0) {
        $stmt = $conn->query("SELECT id, subject, request_type, status, created_at FROM requests ORDER BY created_at DESC LIMIT 5");
        echo "<h4>آخر 5 طلبات:</h4>";
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
    }
    
    // اختبار API
    echo "<hr><h3>اختبار API</h3>";
    
    // محاكاة استدعاء API
    $result = file_get_contents('http://localhost/projeect/api/requests.php?action=get_all');
    $data = json_decode($result, true);
    
    if ($data) {
        echo "<p>نتيجة API: ";
        if ($data['success']) {
            $requests = $data['data'] ?? $data['requests'] ?? [];
            echo "<span style='color: green;'>نجح - " . count($requests) . " طلب</span></p>";
        } else {
            echo "<span style='color: red;'>فشل - " . ($data['message'] ?? 'خطأ غير معروف') . "</span></p>";
        }
        
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<p style='color: red;'>فشل في الحصول على استجابة من API</p>";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>