<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>فحص الجداول المطلوبة</h3>";
    
    // فحص جدول الطلبات
    echo "<h4>جدول requests:</h4>";
    $stmt = $conn->query("SELECT * FROM requests ORDER BY created_at DESC LIMIT 3");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>النوع</th><th>الموضوع</th><th>الحالة</th><th>التاريخ</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['request_type'] . "</td>";
        echo "<td>" . substr($row['subject'], 0, 20) . "...</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // فحص جدول تتبع الطلبات
    echo "<h4>جدول request_tracking:</h4>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM request_tracking");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>عدد سجلات التتبع: <strong>$count</strong></p>";
    
    if ($count > 0) {
        $stmt = $conn->query("SELECT * FROM request_tracking ORDER BY created_at DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Request ID</th><th>التغيير</th><th>الملاحظات</th><th>بواسطة</th><th>التاريخ</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['request_id'] . "</td>";
            echo "<td>" . $row['status_change'] . "</td>";
            echo "<td>" . substr($row['notes'], 0, 30) . "...</td>";
            echo "<td>" . $row['changed_by'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // فحص جدول المستخدمين
    echo "<h4>جدول users:</h4>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>عدد المستخدمين: <strong>$count</strong></p>";
    
    if ($count > 0) {
        $stmt = $conn->query("SELECT id, username, full_name, role FROM users LIMIT 3");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الدور</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['full_name'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>