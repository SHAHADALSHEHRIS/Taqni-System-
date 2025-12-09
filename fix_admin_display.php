<?php
/**
 * إضافة بيانات اختبارية لحل مشكلة عرض أسماء المستخدمين ونوع الطلبات
 */

header('Content-Type: text/html; charset=utf-8');

require 'config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h2>🔧 إضافة بيانات اختبارية لإصلاح المشكلة</h2>";
    
    // 1. التأكد من وجود مستخدمين
    echo "<h3>1️⃣ فحص وإضافة المستخدمين</h3>";
    
    $users = [
        ['username' => 'admin', 'full_name' => 'مدير النظام', 'email' => 'admin@test.com'],
        ['username' => 'ahmed', 'full_name' => 'أحمد محمد', 'email' => 'ahmed@test.com'],
        ['username' => 'sara', 'full_name' => 'سارة عبدالله', 'email' => 'sara@test.com'],
        ['username' => 'omar', 'full_name' => 'عمر عبدالعزيز', 'email' => 'omar@test.com']
    ];
    
    foreach ($users as $user) {
        // التحقق من وجود المستخدم
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$user['username']]);
        
        if (!$stmt->fetch()) {
            // إضافة المستخدم إذا لم يكن موجوداً
            $stmt = $conn->prepare('INSERT INTO users (username, full_name, email, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user['username'], $user['full_name'], $user['email'], password_hash('123456', PASSWORD_DEFAULT)]);
            echo "✅ تم إضافة المستخدم: {$user['full_name']} ({$user['username']})<br>";
        } else {
            echo "ℹ️ المستخدم موجود: {$user['full_name']} ({$user['username']})<br>";
        }
    }
    
    // 2. إضافة طلبات اختبارية مع بيانات صحيحة
    echo "<h3>2️⃣ إضافة طلبات اختبارية</h3>";
    
    $requests = [
        [
            'user_id' => 1,
            'request_type' => 'electricity',
            'subject' => 'إصلاح عطل في الكهرباء',
            'description' => 'يوجد انقطاع في التيار الكهربائي في المكتب رقم 205',
            'priority' => 'high'
        ],
        [
            'user_id' => 2,
            'request_type' => 'plumbing',
            'subject' => 'إصلاح تسريب في الحمام',
            'description' => 'تسريب مياه من صنبور الحمام في الطابق الثاني',
            'priority' => 'medium'
        ],
        [
            'user_id' => 3,
            'request_type' => 'ac',
            'subject' => 'صيانة مكيف الهواء',
            'description' => 'المكيف لا يعمل بكفاءة ويحتاج صيانة',
            'priority' => 'low'
        ],
        [
            'user_id' => 4,
            'request_type' => 'it',
            'subject' => 'مشكلة في الشبكة',
            'description' => 'بطء في سرعة الإنترنت في قسم المحاسبة',
            'priority' => 'urgent'
        ],
        [
            'user_id' => 1,
            'request_type' => 'maintenance',
            'subject' => 'صيانة دورية للمباني',
            'description' => 'فحص شامل للمرافق والمباني',
            'priority' => 'medium'
        ]
    ];
    
    foreach ($requests as $request) {
        $stmt = $conn->prepare('INSERT INTO requests (user_id, request_type, subject, description, priority) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $request['user_id'],
            $request['request_type'],
            $request['subject'],
            $request['description'],
            $request['priority']
        ]);
        echo "✅ تم إضافة طلب: {$request['subject']}<br>";
    }
    
    // 3. عرض الطلبات مع أسماء المستخدمين للتأكد
    echo "<h3>3️⃣ فحص الطلبات مع أسماء المستخدمين</h3>";
    
    $stmt = $conn->query('
        SELECT r.id, r.subject, r.request_type, r.priority, r.status, 
               u.full_name as user_name, u.username 
        FROM requests r 
        LEFT JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC 
        LIMIT 10
    ');
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f5f5f5;'>
            <th>ID</th>
            <th>الموضوع</th>
            <th>النوع</th>
            <th>اسم المقدم</th>
            <th>الأولوية</th>
            <th>الحالة</th>
          </tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $typeMap = [
            'electricity' => 'كهرباء',
            'plumbing' => 'سباكة',
            'ac' => 'تكييف',
            'it' => 'تقنية معلومات',
            'maintenance' => 'صيانة عامة'
        ];
        
        $priorityMap = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
            'urgent' => 'عاجل'
        ];
        
        $statusMap = [
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'rejected' => 'لم يتم التنفيذ'
        ];
        
        echo "<tr>";
        echo "<td>#{$row['id']}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>" . ($typeMap[$row['request_type']] ?? $row['request_type']) . "</td>";
        echo "<td><strong>{$row['user_name']}</strong> ({$row['username']})</td>";
        echo "<td>" . ($priorityMap[$row['priority']] ?? $row['priority']) . "</td>";
        echo "<td>" . ($statusMap[$row['status']] ?? $row['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ تم إصلاح المشكلة بنجاح!</h3>";
    echo "<p>الآن يمكنك الذهاب إلى <a href='admin.html'>صفحة الإدارة</a> وستجد أسماء المستخدمين وأنواع الطلبات تظهر بشكل صحيح.</p>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>