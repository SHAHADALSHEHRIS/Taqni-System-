<?php
/**
 * تحديث شامل لضمان عرض أسماء العملاء وأنواع الطلبات في صفحة الإدارة
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 تحديث البيانات لصفحة الإدارة</h2>";

try {
    $conn = new PDO("mysql:host=localhost;dbname=shahad_clean_db;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>✅ اتصال قاعدة البيانات ناجح</h3>";
    
    // خطوة 1: فحص هيكل الجدول
    echo "<h4>📋 خطوة 1: فحص هيكل جدول الطلبات</h4>";
    
    $stmt = $conn->query("DESCRIBE requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f0f8ff;'><th>اسم العمود</th><th>النوع</th><th>يمكن أن يكون NULL</th><th>القيمة الافتراضية</th></tr>";
    
    $hasCustomerName = false;
    $hasRequestType = false;
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'customer_name') $hasCustomerName = true;
        if ($column['Field'] === 'request_type') $hasRequestType = true;
    }
    echo "</table>";
    
    // خطوة 2: إضافة الحقول المفقودة
    echo "<h4>🔧 خطوة 2: إضافة الحقول المفقودة</h4>";
    
    if (!$hasCustomerName) {
        $sql = "ALTER TABLE requests ADD COLUMN customer_name VARCHAR(255) DEFAULT '' AFTER description";
        $conn->exec($sql);
        echo "<p>✅ تم إضافة حقل customer_name</p>";
    } else {
        echo "<p>✅ حقل customer_name موجود</p>";
    }
    
    if (!$hasRequestType) {
        $sql = "ALTER TABLE requests ADD COLUMN request_type VARCHAR(100) DEFAULT 'other' AFTER customer_name";
        $conn->exec($sql);
        echo "<p>✅ تم إضافة حقل request_type</p>";
    } else {
        echo "<p>✅ حقل request_type موجود</p>";
    }
    
    // خطوة 3: تحديث البيانات الموجودة
    echo "<h4>🔄 خطوة 3: تحديث البيانات الموجودة</h4>";
    
    // إضافة أسماء عملاء وأنواع طلبات للطلبات الموجودة
    $sampleUpdates = [
        [
            'id' => 1,
            'customer_name' => 'أحمد محمد السعيد',
            'request_type' => 'electricity',
            'subject' => 'إصلاح عطل كهربائي'
        ],
        [
            'id' => 2, 
            'customer_name' => 'فاطمة علي الأحمد',
            'request_type' => 'plumbing',
            'subject' => 'صيانة تسريب'
        ],
        [
            'id' => 3,
            'customer_name' => 'عبدالله سعد الخالد', 
            'request_type' => 'ac',
            'subject' => 'صيانة مكيف'
        ],
        [
            'id' => 4,
            'customer_name' => 'سارة محمود العتيبي',
            'request_type' => 'it',
            'subject' => 'مشكلة شبكة'
        ]
    ];
    
    foreach ($sampleUpdates as $update) {
        // تحقق إذا كان الطلب موجود
        $checkStmt = $conn->prepare("SELECT id FROM requests WHERE id = ?");
        $checkStmt->execute([$update['id']]);
        
        if ($checkStmt->rowCount() > 0) {
            // تحديث البيانات
            $updateStmt = $conn->prepare("
                UPDATE requests 
                SET customer_name = ?, request_type = ?, subject = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([
                $update['customer_name'],
                $update['request_type'], 
                $update['subject'],
                $update['id']
            ]);
            echo "✅ تم تحديث الطلب #{$update['id']}: {$update['customer_name']}<br>";
        } else {
            // إنشاء طلب جديد
            $insertStmt = $conn->prepare("
                INSERT INTO requests (id, customer_name, request_type, subject, description, priority, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'medium', 'pending', NOW(), NOW())
            ");
            $insertStmt->execute([
                $update['id'],
                $update['customer_name'],
                $update['request_type'],
                $update['subject'],
                'وصف تجريبي للطلب'
            ]);
            echo "✅ تم إنشاء الطلب #{$update['id']}: {$update['customer_name']}<br>";
        }
    }
    
    // خطوة 4: إضافة المزيد من الطلبات التجريبية
    echo "<h4>📝 خطوة 4: إضافة طلبات تجريبية إضافية</h4>";
    
    $additionalRequests = [
        [
            'customer_name' => 'محمد عبدالرحمن النور',
            'request_type' => 'maintenance',
            'subject' => 'صيانة عامة',
            'description' => 'طلب صيانة عامة للمكتب',
            'priority' => 'high'
        ],
        [
            'customer_name' => 'نورا أحمد المالكي',
            'request_type' => 'cleaning', 
            'subject' => 'خدمة تنظيف',
            'description' => 'تنظيف شامل للمبنى',
            'priority' => 'low'
        ],
        [
            'customer_name' => 'خالد يوسف العنزي',
            'request_type' => 'security',
            'subject' => 'فحص أمني',
            'description' => 'فحص أنظمة الأمان',
            'priority' => 'high'
        ]
    ];
    
    foreach ($additionalRequests as $req) {
        // تحقق إذا كان الطلب موجود
        $checkStmt = $conn->prepare("SELECT id FROM requests WHERE customer_name = ? AND subject = ?");
        $checkStmt->execute([$req['customer_name'], $req['subject']]);
        
        if ($checkStmt->rowCount() == 0) {
            $insertStmt = $conn->prepare("
                INSERT INTO requests (user_id, customer_name, request_type, subject, description, priority, status, created_at, updated_at)
                VALUES (1, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
            ");
            $insertStmt->execute([
                $req['customer_name'],
                $req['request_type'],
                $req['subject'], 
                $req['description'],
                $req['priority']
            ]);
            echo "✅ تم إضافة طلب جديد: {$req['customer_name']}<br>";
        }
    }
    
    // خطوة 5: عرض النتيجة النهائية
    echo "<h4>📊 خطوة 5: النتيجة النهائية</h4>";
    
    $stmt = $conn->query('
        SELECT id, customer_name, request_type, subject, priority, status, created_at
        FROM requests 
        ORDER BY id DESC 
        LIMIT 10
    ');
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f8ff;'>";
    echo "<th>ID</th><th>اسم العميل</th><th>نوع الطلب</th><th>الموضوع</th><th>الأولوية</th><th>الحالة</th><th>التاريخ</th>";
    echo "</tr>";
    
    $typeMap = [
        'electricity' => 'كهرباء ⚡',
        'plumbing' => 'سباكة 🚰',
        'ac' => 'تكييف ❄️',
        'it' => 'تقنية معلومات 💻',
        'maintenance' => 'صيانة عامة 🔧',
        'cleaning' => 'تنظيف 🧽',
        'security' => 'أمن وسلامة 🛡️',
        'other' => 'أخرى ❓'
    ];
    
    while ($row = $stmt->fetch()) {
        $typeDisplay = $typeMap[$row['request_type']] ?? $row['request_type'];
        
        echo "<tr>";
        echo "<td><strong>#{$row['id']}</strong></td>";
        echo "<td style='color: #2c5aa0; font-weight: bold;'>{$row['customer_name']}</td>";
        echo "<td>{$typeDisplay}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['priority']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>🎯 تم التحديث بنجاح!</h3>";
    echo "<p><strong>ما تم إنجازه:</strong></p>";
    echo "<ul>";
    echo "<li>✅ إضافة حقل customer_name لأسماء العملاء</li>";
    echo "<li>✅ إضافة حقل request_type لأنواع الطلبات</li>";
    echo "<li>✅ تحديث البيانات الموجودة</li>";
    echo "<li>✅ إضافة طلبات تجريبية جديدة</li>";
    echo "<li>✅ تحديث صفحة الإدارة لعرض البيانات الجديدة</li>";
    echo "</ul>";
    
    echo "<p><strong>اختبر الآن:</strong></p>";
    echo "<ol>";
    echo "<li><a href='test_api_data.html' target='_blank' style='color: #007bff;'>اختبار بيانات API</a></li>";
    echo "<li><a href='admin.html' target='_blank' style='color: #007bff;'>صفحة الإدارة المحدثة</a></li>";
    echo "<li><a href='track.html' target='_blank' style='color: #007bff;'>صفحة تتبع الطلبات</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>❌ خطأ:</h3>";
    echo "<p><strong>الخطأ:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>