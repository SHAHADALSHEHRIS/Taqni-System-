<?php
require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h3>فحص جدول التقييمات (ratings)</h3>";
    
    $stmt = $conn->query('DESCRIBE ratings');
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>اسم العمود</th><th>نوع البيانات</th><th>NULL</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // فحص عدد السجلات
    $stmt = $conn->query('SELECT COUNT(*) as count FROM ratings');
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>عدد التقييمات المسجلة: <strong>$count</strong></p>";
    
    // فحص الطلبات المكتملة
    $stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'completed'");
    $completed = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>عدد الطلبات المكتملة: <strong>$completed</strong></p>";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>