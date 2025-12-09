<?php
/**
 * إضافة حقل اسم العميل إلى جدول الطلبات
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 إضافة حقل اسم العميل</h2>";

try {
    $conn = new PDO("mysql:host=localhost;dbname=shahad_clean_db;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>✅ اتصال قاعدة البيانات ناجح</h3>";
    
    // فحص إذا كان الحقل موجود
    $stmt = $conn->query("DESCRIBE requests");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('customer_name', $columns)) {
        echo "<p>✅ حقل customer_name موجود بالفعل</p>";
    } else {
        // إضافة الحقل
        $sql = "ALTER TABLE requests ADD COLUMN customer_name VARCHAR(255) DEFAULT '' AFTER description";
        $conn->exec($sql);
        echo "<p>✅ تم إضافة حقل customer_name بنجاح</p>";
    }
    
    // تحديث البيانات الموجودة - نقل اسم العميل من subject إلى customer_name
    echo "<h4>🔄 تحديث البيانات الموجودة:</h4>";
    
    $stmt = $conn->query("SELECT id, subject FROM requests WHERE customer_name IS NULL OR customer_name = ''");
    $requests = $stmt->fetchAll();
    
    foreach ($requests as $request) {
        // إذا كان subject يحتوي على اسم (وليس وصف طويل)
        $subject = trim($request['subject']);
        if (strlen($subject) <= 50 && !preg_match('/\b(إصلاح|صيانة|مشكلة|طلب|خدمة)\b/', $subject)) {
            // انقل subject إلى customer_name وضع موضوع افتراضي
            $updateStmt = $conn->prepare("UPDATE requests SET customer_name = ?, subject = 'طلب خدمة' WHERE id = ?");
            $updateStmt->execute([$subject, $request['id']]);
            echo "✅ تم تحديث الطلب #{$request['id']}: العميل = {$subject}<br>";
        } else {
            // ضع اسم افتراضي إذا كان subject وصف طويل
            $updateStmt = $conn->prepare("UPDATE requests SET customer_name = 'عميل غير محدد' WHERE id = ?");
            $updateStmt->execute([$request['id']]);
            echo "⚠️ تم وضع اسم افتراضي للطلب #{$request['id']}<br>";
        }
    }
    
    // عرض النتيجة النهائية
    echo "<h4>📋 البيانات المحدثة:</h4>";
    
    $stmt = $conn->query('
        SELECT id, customer_name, subject, request_type, status, created_at
        FROM requests 
        ORDER BY id DESC 
        LIMIT 10
    ');
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f8ff;'>";
    echo "<th>ID</th><th>اسم العميل</th><th>الموضوع</th><th>النوع</th><th>الحالة</th><th>التاريخ</th>";
    echo "</tr>";
    
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td><strong>#{$row['id']}</strong></td>";
        echo "<td style='color: #2c5aa0; font-weight: bold;'>{$row['customer_name']}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['request_type']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>🎯 تم التحديث بنجاح!</h3>";
    echo "<p><strong>التحديثات المطلوبة:</strong></p>";
    echo "<ul>";
    echo "<li>✅ إضافة حقل customer_name إلى جدول requests</li>";
    echo "<li>✅ تحديث البيانات الموجودة</li>";
    echo "<li>✅ تحديث API لدعم الحقل الجديد</li>";
    echo "</ul>";
    echo "<p><strong>الخطوة التالية:</strong> تحديث النماذج لاستخدام الحقل الجديد</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>❌ خطأ:</h3>";
    echo "<p><strong>الخطأ:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>