<?php
/**
 * إصلاح شامل لمشكلة إضافة الطلبات
 * Complete Fix for Request Creation Issue
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $database = new Database();
    $conn = $database->connect();
    
    echo "<!DOCTYPE html>
    <html lang='ar' dir='rtl'>
    <head>
        <meta charset='UTF-8'>
        <title>إصلاح مشكلة الطلبات</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
            .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
            .error { color: red; background: #f5e8e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
            .info { color: blue; background: #e8f0f5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>";
    
    echo "<h1>🔧 إصلاح مشكلة إضافة الطلبات</h1>";
    
    // 1. التأكد من وجود المستخدم الافتراضي
    echo "<h2>1️⃣ فحص المستخدم الافتراضي</h2>";
    
    $stmt = $conn->query("SELECT * FROM users WHERE id = 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<div class='info'>👤 إنشاء المستخدم الافتراضي...</div>";
        
        $stmt = $conn->prepare("INSERT INTO users (id, username, email, password_hash, full_name, role) VALUES (1, 'admin', 'admin@system.com', ?, 'المشرف العام', 'admin')");
        $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
        
        echo "<div class='success'>✅ تم إنشاء المستخدم الافتراضي</div>";
    } else {
        echo "<div class='success'>✅ المستخدم الافتراضي موجود: {$user['full_name']}</div>";
    }
    
    // 2. إصلاح جدول الطلبات إذا كان هناك مشكلة
    echo "<h2>2️⃣ فحص وإصلاح جدول الطلبات</h2>";
    
    try {
        // التأكد من أن جدول الطلبات موجود ويعمل
        $conn->query("SELECT COUNT(*) FROM requests");
        echo "<div class='success'>✅ جدول الطلبات يعمل بشكل صحيح</div>";
        
        // فحص أعمدة الجدول
        $stmt = $conn->query("DESCRIBE requests");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $required_columns = ['id', 'user_id', 'request_type', 'subject', 'description', 'priority', 'status'];
        $existing_columns = array_column($columns, 'Field');
        
        foreach ($required_columns as $col) {
            if (in_array($col, $existing_columns)) {
                echo "<div class='success'>✅ العمود $col موجود</div>";
            } else {
                echo "<div class='error'>❌ العمود $col غير موجود</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ مشكلة في جدول الطلبات: " . $e->getMessage() . "</div>";
        
        // إعادة إنشاء الجدول
        echo "<div class='info'>🔧 إعادة إنشاء جدول الطلبات...</div>";
        
        $create_sql = "
        CREATE TABLE IF NOT EXISTS requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            request_type VARCHAR(50) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($create_sql);
        echo "<div class='success'>✅ تم إعادة إنشاء جدول الطلبات</div>";
    }
    
    // 3. اختبار إضافة طلب تجريبي
    echo "<h2>3️⃣ اختبار إضافة طلب تجريبي</h2>";
    
    try {
        $test_request = [
            'user_id' => 1,
            'request_type' => 'test_fix',
            'subject' => 'اختبار إصلاح النظام - ' . date('Y-m-d H:i:s'),
            'description' => 'هذا طلب تجريبي للتأكد من أن النظام يعمل بعد الإصلاح',
            'priority' => 'medium'
        ];
        
        $sql = "INSERT INTO requests (user_id, request_type, subject, description, priority) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $test_request['user_id'],
            $test_request['request_type'],
            $test_request['subject'],
            $test_request['description'],
            $test_request['priority']
        ]);
        
        if ($result) {
            $new_id = $conn->lastInsertId();
            echo "<div class='success'>✅ تم إضافة طلب تجريبي بنجاح! ID: $new_id</div>";
            
            // إضافة سجل تتبع
            try {
                $track_sql = "INSERT INTO request_tracking (request_id, status_change, notes, changed_by) VALUES (?, ?, ?, ?)";
                $track_stmt = $conn->prepare($track_sql);
                $track_stmt->execute([$new_id, 'تم إنشاء الطلب', 'طلب تجريبي بعد الإصلاح', 1]);
                echo "<div class='success'>✅ تم إضافة سجل التتبع</div>";
            } catch (Exception $e) {
                echo "<div class='info'>ℹ️ لم يتم إضافة سجل التتبع (هذا ليس ضروري للعمل الأساسي)</div>";
            }
            
        } else {
            echo "<div class='error'>❌ فشل في إضافة الطلب التجريبي</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ خطأ في إضافة الطلب التجريبي: " . $e->getMessage() . "</div>";
    }
    
    // 4. اختبار API
    echo "<h2>4️⃣ اختبار API</h2>";
    
    $api_data = [
        'action' => 'create',
        'user_id' => 1,
        'request_type' => 'api_test',
        'subject' => 'اختبار API - ' . date('H:i:s'),
        'description' => 'اختبار API بعد الإصلاح',
        'priority' => 'medium'
    ];
    
    $postdata = json_encode($api_data);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => $postdata
        ]
    ];
    
    $context = stream_context_create($opts);
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/requests.php';
    $api_response = @file_get_contents($api_url, false, $context);
    
    if ($api_response !== false) {
        $api_result = json_decode($api_response, true);
        if ($api_result && isset($api_result['success'])) {
            if ($api_result['success']) {
                echo "<div class='success'>✅ API يعمل بنجاح: " . $api_result['message'] . "</div>";
            } else {
                echo "<div class='error'>❌ خطأ في API: " . $api_result['message'] . "</div>";
            }
        } else {
            echo "<div class='error'>❌ استجابة غير صحيحة من API</div>";
            echo "<pre>استجابة API: " . htmlspecialchars($api_response) . "</pre>";
        }
    } else {
        echo "<div class='error'>❌ لا يمكن الوصول إلى API على: $api_url</div>";
    }
    
    // 5. عرض إحصائيات نهائية
    echo "<h2>5️⃣ الإحصائيات النهائية</h2>";
    
    $total_requests = $conn->query("SELECT COUNT(*) FROM requests")->fetchColumn();
    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    echo "<div class='info'>👥 عدد المستخدمين: $total_users</div>";
    echo "<div class='info'>📄 عدد الطلبات: $total_requests</div>";
    
    // عرض آخر 3 طلبات
    if ($total_requests > 0) {
        echo "<h3>📋 آخر 3 طلبات:</h3>";
        $stmt = $conn->query("SELECT * FROM requests ORDER BY created_at DESC LIMIT 3");
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($recent as $req) {
            echo "<div class='info'>🔹 [{$req['id']}] {$req['subject']} - {$req['created_at']}</div>";
        }
    }
    
    echo "<h2>✅ تم الانتهاء من الإصلاح!</h2>";
    
    echo "<div style='margin-top:30px;'>";
    echo "<a href='request.html' style='background:#b6e2d3; color:#3b5e4d; padding:12px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin:5px;'>📝 جرب إضافة طلب الآن</a>";
    echo "<a href='track_new.html' style='background:#f5f5dc; color:#3b5e4d; padding:12px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin:5px;'>🔍 عرض الطلبات</a>";
    echo "<a href='test_requests.php' style='background:#e6f4ea; color:#3b5e4d; padding:12px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin:5px;'>🧪 اختبار متقدم</a>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ خطأ عام: " . $e->getMessage() . "</div>";
}
?>