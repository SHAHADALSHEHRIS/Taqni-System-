<?php
/**
 * اختبار شامل للنظام - نسخة نهائية
 * Complete System Test - Final Version
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>اختبار النظام النهائي</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
        .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f5e8e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e8f0f5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .btn { background: #b6e2d3; color: #3b5e4d; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px; display: inline-block; }
        .btn:hover { background: #a0d6c1; }
    </style>
</head>
<body>";

echo "<h1>🔍 اختبار النظام النهائي</h1>";
echo "<p>تاريخ ووقت الاختبار: " . date('Y-m-d H:i:s') . "</p>";

$all_tests_passed = true;

try {
    // 1. اختبار الاتصال بقاعدة البيانات
    echo "<h2>1️⃣ اختبار الاتصال بقاعدة البيانات</h2>";
    
    $database = new Database();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<div class='success'>✅ الاتصال بقاعدة البيانات ناجح</div>";
    } else {
        echo "<div class='error'>❌ فشل في الاتصال بقاعدة البيانات</div>";
        $all_tests_passed = false;
    }
    
    // 2. فحص الجداول المطلوبة
    echo "<h2>2️⃣ فحص الجداول المطلوبة</h2>";
    
    $required_tables = ['users', 'requests', 'request_tracking', 'request_ratings'];
    foreach ($required_tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ الجدول $table موجود</div>";
        } else {
            echo "<div class='error'>❌ الجدول $table غير موجود</div>";
            $all_tests_passed = false;
        }
    }
    
    // 3. فحص البيانات
    echo "<h2>3️⃣ فحص البيانات</h2>";
    
    $users_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $requests_count = $conn->query("SELECT COUNT(*) FROM requests")->fetchColumn();
    
    echo "<div class='info'>👥 عدد المستخدمين: $users_count</div>";
    echo "<div class='info'>📄 عدد الطلبات: $requests_count</div>";
    
    if ($users_count > 0) {
        echo "<div class='success'>✅ يوجد مستخدمين في النظام</div>";
    } else {
        echo "<div class='warning'>⚠️ لا يوجد مستخدمين - سيتم إنشاء مستخدم افتراضي</div>";
        
        // إنشاء مستخدم افتراضي
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES ('admin', 'admin@system.com', ?, 'المشرف العام', 'admin')");
        $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
        echo "<div class='success'>✅ تم إنشاء المستخدم الافتراضي (admin/admin123)</div>";
    }
    
    // 4. اختبار API
    echo "<h2>4️⃣ اختبار API</h2>";
    
    // محاولة جلب الطلبات عبر API
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/requests.php';
    
    $postdata = json_encode(['action' => 'get_all']);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => $postdata
        ]
    ];
    
    $context = stream_context_create($opts);
    $api_response = @file_get_contents($api_url, false, $context);
    
    if ($api_response !== false) {
        $api_result = json_decode($api_response, true);
        if ($api_result && isset($api_result['success']) && $api_result['success']) {
            $api_count = count($api_result['requests'] ?? []);
            echo "<div class='success'>✅ API يعمل بنجاح - عدد الطلبات: $api_count</div>";
        } else {
            echo "<div class='error'>❌ API لا يعمل بشكل صحيح</div>";
            $all_tests_passed = false;
        }
    } else {
        echo "<div class='error'>❌ لا يمكن الوصول إلى API</div>";
        $all_tests_passed = false;
    }
    
    // 5. فحص الملفات الأساسية
    echo "<h2>5️⃣ فحص الملفات الأساسية</h2>";
    
    $required_files = [
        'request.html' => 'صفحة إضافة الطلبات',
        'track_new.html' => 'صفحة تتبع الطلبات',
        'admin.html' => 'لوحة الإدارة',
        'api/requests.php' => 'API الطلبات',
        'js/api.js' => 'JavaScript API',
        'config/database.php' => 'إعدادات قاعدة البيانات'
    ];
    
    foreach ($required_files as $file => $description) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<div class='success'>✅ $description ($file)</div>";
        } else {
            echo "<div class='error'>❌ $description ($file) غير موجود</div>";
            $all_tests_passed = false;
        }
    }
    
    // 6. إضافة بيانات تجريبية إذا لم تكن موجودة
    if ($requests_count == 0) {
        echo "<h2>6️⃣ إضافة بيانات تجريبية</h2>";
        
        $sample_requests = [
            [
                'request_type' => 'electricity',
                'subject' => 'إصلاح مقبس كهربائي',
                'description' => 'يوجد مقبس كهربائي لا يعمل في المكتب رقم 201',
                'priority' => 'medium',
                'status' => 'pending'
            ],
            [
                'request_type' => 'plumbing',
                'subject' => 'تسريب في الحمام',
                'description' => 'يوجد تسريب مياه في حمام الطابق الثاني',
                'priority' => 'high',
                'status' => 'completed'
            ],
            [
                'request_type' => 'ac',
                'subject' => 'صيانة المكيف',
                'description' => 'المكيف في القاعة الرئيسية يحتاج إلى صيانة دورية',
                'priority' => 'low',
                'status' => 'completed'
            ]
        ];
        
        $created_count = 0;
        foreach ($sample_requests as $request) {
            try {
                $sql = "INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (1, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $request['request_type'],
                    $request['subject'],
                    $request['description'],
                    $request['priority'],
                    $request['status']
                ]);
                
                if ($result) {
                    $created_count++;
                }
            } catch (Exception $e) {
                // تجاهل الأخطاء
            }
        }
        
        echo "<div class='success'>✅ تم إنشاء $created_count طلب تجريبي</div>";
    }
    
    // 7. النتيجة النهائية
    echo "<h2>🎯 النتيجة النهائية</h2>";
    
    if ($all_tests_passed) {
        echo "<div class='success' style='font-size: 1.2em; font-weight: bold;'>🎉 جميع الاختبارات نجحت! النظام جاهز للاستخدام</div>";
    } else {
        echo "<div class='error' style='font-size: 1.2em; font-weight: bold;'>⚠️ هناك بعض المشاكل تحتاج إلى إصلاح</div>";
    }
    
    // روابط سريعة
    echo "<h2>🔗 روابط سريعة</h2>";
    echo "<a href='request.html' class='btn'>➕ إضافة طلب جديد</a>";
    echo "<a href='track_new.html' class='btn'>🔍 تتبع الطلبات</a>";
    echo "<a href='admin.html' class='btn'>⚙️ لوحة الإدارة</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ خطأ في اختبار النظام: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>