<?php
/**
 * ملف إصلاح مبسط وسريع
 */

echo "<h1>🔧 إصلاح سريع ومبسط</h1>";
echo "<div style='font-family: Arial; padding: 20px;'>";

try {
    // 1. الاتصال بـ MySQL
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ الاتصال بـ MySQL ناجح<br>";
    
    // 2. إنشاء قاعدة البيانات
    $conn->exec("CREATE DATABASE IF NOT EXISTS shahad_clean_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ قاعدة البيانات جاهزة<br>";
    
    // 3. الاتصال بقاعدة البيانات
    $conn = new PDO("mysql:host=localhost;dbname=shahad_clean_db;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. حذف الجداول القديمة وإعادة إنشائها
    echo "<h2>إعادة إنشاء الجداول...</h2>";
    
    $conn->exec("DROP TABLE IF EXISTS request_ratings");
    $conn->exec("DROP TABLE IF EXISTS requests");
    $conn->exec("DROP TABLE IF EXISTS users");
    echo "✅ تم حذف الجداول القديمة<br>";
    
    // جدول المستخدمين
    $sql = "CREATE TABLE users (
        id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(50) NOT NULL UNIQUE,
        email varchar(100) NOT NULL UNIQUE,
        password varchar(255) NOT NULL,
        full_name varchar(100) NOT NULL,
        user_type enum('admin','customer') DEFAULT 'customer',
        is_active tinyint(1) DEFAULT 1,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->exec($sql);
    echo "✅ جدول users<br>";
    
    // جدول الطلبات (بدون عمود محسوب)
    $sql = "CREATE TABLE requests (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        request_type varchar(50) NOT NULL,
        subject varchar(200) NOT NULL,
        description text,
        priority enum('low','medium','high','urgent') DEFAULT 'medium',
        status enum('pending','in_progress','completed','rejected') DEFAULT 'pending',
        admin_notes text,
        admin_id int(11) DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->exec($sql);
    echo "✅ جدول requests<br>";
    
    // جدول التقييمات
    $sql = "CREATE TABLE request_ratings (
        id int(11) NOT NULL AUTO_INCREMENT,
        request_id int(11) NOT NULL,
        user_id int(11) NOT NULL,
        quality_rate int(1) NOT NULL,
        speed_rate int(1) NOT NULL,
        comments text,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_rating (request_id, user_id),
        KEY request_id (request_id),
        KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->exec($sql);
    echo "✅ جدول request_ratings<br>";
    
    // 5. إنشاء البيانات الأساسية
    echo "<h2>إنشاء البيانات...</h2>";
    
    // مستخدم المدير
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute(['admin', 'admin@system.com', password_hash('admin123', PASSWORD_DEFAULT), 'مدير النظام']);
    echo "✅ مستخدم المدير (admin / admin123)<br>";
    
    // مستخدمين تجريبيين
    $users = [
        ['user1', 'user1@test.com', 'أحمد محمد'],
        ['user2', 'user2@test.com', 'فاطمة علي'],
        ['user3', 'user3@test.com', 'خالد سعد']
    ];
    
    foreach ($users as $user) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user[0], $user[1], password_hash('123456', PASSWORD_DEFAULT), $user[2]]);
        echo "✅ المستخدم: {$user[2]}<br>";
    }
    
    // طلبات تجريبية
    $requests = [
        [2, 'electricity', 'مشكلة في الكهرباء', 'انقطاع في التيار الكهربائي', 'high', 'pending'],
        [3, 'plumbing', 'تسريب في المياه', 'تسريب في دورة المياه', 'medium', 'in_progress'],
        [4, 'it', 'مشكلة في الكمبيوتر', 'الكمبيوتر لا يعمل', 'low', 'completed'],
        [2, 'cleaning', 'تنظيف المكتب', 'المكتب يحتاج تنظيف', 'medium', 'completed'],
        [3, 'maintenance', 'صيانة المكيف', 'المكيف لا يبرد', 'high', 'pending']
    ];
    
    foreach ($requests as $req) {
        $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 10) DAY))");
        $stmt->execute($req);
    }
    echo "✅ تم إنشاء " . count($requests) . " طلب<br>";
    
    // تقييمات
    $ratings = [
        [3, 3, 5, 4],  
        [4, 2, 4, 5]   
    ];
    
    foreach ($ratings as $rating) {
        $stmt = $conn->prepare("INSERT INTO request_ratings (request_id, user_id, quality_rate, speed_rate) VALUES (?, ?, ?, ?)");
        $stmt->execute($rating);
    }
    echo "✅ تم إنشاء " . count($ratings) . " تقييم<br>";
    
    // إحصائيات نهائية
    echo "<h2>📊 إحصائيات النظام:</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $users_count = $stmt->fetch()['count'];
    echo "👥 المستخدمين: $users_count<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM requests");
    $requests_count = $stmt->fetch()['count'];
    echo "📋 الطلبات: $requests_count<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM request_ratings");
    $ratings_count = $stmt->fetch()['count'];
    echo "⭐ التقييمات: $ratings_count<br>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 10px; margin: 30px 0;'>";
    echo "<h2 style='color: #155724; margin-top: 0;'>🎉 تم إنشاء النظام بنجاح!</h2>";
    echo "<p style='color: #155724; font-size: 16px;'>النظام جاهز للاستخدام الآن</p>";
    echo "</div>";
    
    echo "<h3>🔗 اختبر النظام:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='admin.html' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🏠 لوحة التحكم</a>";
    echo "<a href='track.html' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📋 تتبع الطلبات</a>";
    echo "<a href='test_system.php' style='background: #ffc107; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🧪 اختبار النظام</a>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #856404; margin-top: 0;'>🔐 بيانات تسجيل الدخول:</h4>";
    echo "<strong style='color: #856404;'>المدير:</strong> admin / admin123<br>";
    echo "<strong style='color: #856404;'>مستخدم تجريبي:</strong> user1 / 123456";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 10px;'>";
    echo "<h2 style='color: #721c24;'>❌ خطأ في قاعدة البيانات</h2>";
    echo "<p style='color: #721c24;'><strong>الخطأ:</strong> " . $e->getMessage() . "</p>";
    echo "<h3 style='color: #721c24;'>الحلول:</h3>";
    echo "<ol style='color: #721c24;'>";
    echo "<li>تأكد من تشغيل XAMPP</li>";
    echo "<li>ابدأ خدمة MySQL</li>";
    echo "<li>تأكد من أن MySQL يعمل على المنفذ 3306</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</div>";
?>