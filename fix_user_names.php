<?php
/**
 * فحص وإصلاح بيانات المستخدمين والطلبات
 */

header('Content-Type: text/html; charset=utf-8');

require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h2>🔍 فحص وإصلاح بيانات المستخدمين والطلبات</h2>";
    
    // 1. فحص المستخدمين الموجودين
    echo "<h3>1️⃣ المستخدمون الموجودون حالياً</h3>";
    $stmt = $conn->query('SELECT id, username, full_name, email FROM users ORDER BY id');
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f8ff;'><th>ID</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الإيميل</th></tr>";
    
    $users = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[$row['id']] = $row;
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td><strong>{$row['full_name']}</strong></td>";
        echo "<td>{$row['email']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. إصلاح/إضافة مستخدمين بأسماء حقيقية
    echo "<h3>2️⃣ إضافة/تحديث المستخدمين بأسماء حقيقية</h3>";
    
    $realUsers = [
        ['id' => 1, 'username' => 'ahmed.salem', 'full_name' => 'أحمد سالم المطيري', 'email' => 'ahmed.salem@company.com'],
        ['id' => 2, 'username' => 'sara.mohammed', 'full_name' => 'سارة محمد الأحمد', 'email' => 'sara.mohammed@company.com'],
        ['id' => 3, 'username' => 'omar.abdullah', 'full_name' => 'عمر عبدالله الخالد', 'email' => 'omar.abdullah@company.com'],
        ['id' => 4, 'username' => 'fatima.ali', 'full_name' => 'فاطمة علي السعيد', 'email' => 'fatima.ali@company.com'],
        ['id' => 5, 'username' => 'khalid.hassan', 'full_name' => 'خالد حسن الزهراني', 'email' => 'khalid.hassan@company.com']
    ];
    
    foreach ($realUsers as $user) {
        // التحقق من وجود المستخدم
        $stmt = $conn->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        if ($stmt->fetch()) {
            // تحديث المستخدم الموجود
            $stmt = $conn->prepare('UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?');
            $stmt->execute([$user['username'], $user['full_name'], $user['email'], $user['id']]);
            echo "✅ تم تحديث المستخدم: {$user['full_name']}<br>";
        } else {
            // إضافة مستخدم جديد
            $stmt = $conn->prepare('INSERT INTO users (id, username, full_name, email, password) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $user['username'], $user['full_name'], $user['email'], password_hash('123456', PASSWORD_DEFAULT)]);
            echo "✅ تم إضافة المستخدم الجديد: {$user['full_name']}<br>";
        }
    }
    
    // 3. إصلاح الطلبات الموجودة
    echo "<h3>3️⃣ إصلاح الطلبات الموجودة</h3>";
    
    // تحديث الطلبات لتوزيعها على المستخدمين المختلفين
    $requestUpdates = [
        ['id' => 1, 'user_id' => 1, 'request_type' => 'electricity', 'subject' => 'إصلاح عطل كهربائي في المكتب'],
        ['id' => 2, 'user_id' => 2, 'request_type' => 'plumbing', 'subject' => 'صيانة تسريب في دورة المياه'],
        ['id' => 3, 'user_id' => 3, 'request_type' => 'ac', 'subject' => 'صيانة مكيف الهواء'],
        ['id' => 4, 'user_id' => 4, 'request_type' => 'it', 'subject' => 'مشكلة في الشبكة والإنترنت'],
        ['id' => 5, 'user_id' => 5, 'request_type' => 'maintenance', 'subject' => 'صيانة عامة للمكتب']
    ];
    
    foreach ($requestUpdates as $update) {
        $stmt = $conn->prepare('UPDATE requests SET user_id = ?, request_type = ?, subject = ? WHERE id = ?');
        $stmt->execute([$update['user_id'], $update['request_type'], $update['subject'], $update['id']]);
        echo "✅ تم تحديث الطلب #{$update['id']}<br>";
    }
    
    // 4. إضافة طلبات جديدة بأنواع مختلفة
    echo "<h3>4️⃣ إضافة طلبات جديدة بأنواع مختلفة</h3>";
    
    $newRequests = [
        [
            'user_id' => 1,
            'request_type' => 'cleaning',
            'subject' => 'تنظيف شامل للمكاتب',
            'description' => 'طلب تنظيف شامل لجميع المكاتب في الطابق الثاني',
            'priority' => 'medium'
        ],
        [
            'user_id' => 2,
            'request_type' => 'security',
            'subject' => 'فحص أنظمة الأمان',
            'description' => 'مراجعة وفحص كاميرات المراقبة وأنظمة الإنذار',
            'priority' => 'high'
        ],
        [
            'user_id' => 3,
            'request_type' => 'supplies',
            'subject' => 'طلب مستلزمات مكتبية',
            'description' => 'طلب أوراق طباعة وأقلام ومستلزمات مكتبية أخرى',
            'priority' => 'low'
        ]
    ];
    
    foreach ($newRequests as $request) {
        $stmt = $conn->prepare('INSERT INTO requests (user_id, request_type, subject, description, priority) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $request['user_id'],
            $request['request_type'],
            $request['subject'],
            $request['description'],
            $request['priority']
        ]);
        echo "✅ تم إضافة طلب جديد: {$request['subject']}<br>";
    }
    
    // 5. عرض النتيجة النهائية
    echo "<h3>5️⃣ النتيجة النهائية - الطلبات مع أسماء المستخدمين</h3>";
    
    $stmt = $conn->query('
        SELECT r.id, r.subject, r.request_type, r.priority, r.status, r.created_at,
               u.full_name as user_name, u.username 
        FROM requests r 
        LEFT JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC 
        LIMIT 15
    ');
    
    $typeMap = [
        'electricity' => 'كهرباء',
        'plumbing' => 'سباكة',
        'ac' => 'تكييف',
        'it' => 'تقنية معلومات',
        'maintenance' => 'صيانة عامة',
        'cleaning' => 'تنظيف',
        'security' => 'أمن وسلامة',
        'supplies' => 'مستلزمات'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #e8f5e8;'>
            <th>ID</th>
            <th>اسم الطالب الحقيقي</th>
            <th>نوع الطلب</th>
            <th>الموضوع</th>
            <th>الأولوية</th>
            <th>الحالة</th>
          </tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>#{$row['id']}</strong></td>";
        echo "<td style='color: #2c5aa0; font-weight: bold;'>{$row['user_name']} ({$row['username']})</td>";
        echo "<td style='background: #f0f8ff; font-weight: bold;'>" . ($typeMap[$row['request_type']] ?? $row['request_type']) . "</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['priority']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>✅ تم إصلاح المشكلة بنجاح!</h3>";
    echo "<p><strong>الآن:</strong></p>";
    echo "<ul>";
    echo "<li>✅ المستخدمون لديهم أسماء حقيقية بدلاً من 'مدير العام'</li>";
    echo "<li>✅ أنواع الطلبات محددة ومترجمة للعربية</li>";
    echo "<li>✅ البيانات مرتبطة بشكل صحيح بين الجداول</li>";
    echo "</ul>";
    echo "<p><a href='admin.html'>اذهب إلى صفحة الإدارة لرؤية النتائج</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>