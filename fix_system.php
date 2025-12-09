<?php
/**
 * ملف إصلاح سريع لمشاكل قاعدة البيانات
 */

echo "<h1>🚀 إصلاح سريع للنظام</h1>";
echo "<div style='font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto;'>";

// 1. فحص MySQL
echo "<h2>1. فحص خدمة MySQL</h2>";
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    echo "✅ <span style='color: green;'>خدمة MySQL تعمل بشكل صحيح</span><br>";
    
    // 2. إنشاء قاعدة البيانات إذا لم تكن موجودة
    echo "<h2>2. إنشاء قاعدة البيانات</h2>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS shahad_clean_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ قاعدة البيانات 'shahad_clean_db' جاهزة<br>";
    
    // 3. الاتصال بقاعدة البيانات
    $conn = new PDO("mysql:host=localhost;dbname=shahad_clean_db;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. إنشاء الجداول
    echo "<h2>3. إنشاء الجداول</h2>";
    
    // جدول users
    $sql = "CREATE TABLE IF NOT EXISTS users (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✅ جدول users<br>";
    
    // جدول requests
    $sql = "CREATE TABLE IF NOT EXISTS requests (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✅ جدول requests<br>";
    
    // إضافة عمود المدة إذا لم يكن موجوداً
    try {
        $conn->exec("ALTER TABLE requests ADD COLUMN duration_days INT AS (DATEDIFF(CURDATE(), DATE(created_at))) VIRTUAL");
        echo "✅ تم إضافة عمود المدة<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ عمود المدة موجود مسبقاً<br>";
        } else {
            echo "⚠️ تحذير: " . $e->getMessage() . "<br>";
        }
    }
    
    // جدول request_ratings
    $sql = "CREATE TABLE IF NOT EXISTS request_ratings (
        id int(11) NOT NULL AUTO_INCREMENT,
        request_id int(11) NOT NULL,
        user_id int(11) NOT NULL,
        quality_rate int(1) NOT NULL CHECK (quality_rate >= 1 AND quality_rate <= 5),
        speed_rate int(1) NOT NULL CHECK (speed_rate >= 1 AND speed_rate <= 5),
        comments text,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_rating (request_id, user_id),
        KEY request_id (request_id),
        KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✅ جدول request_ratings<br>";
    
    // 5. إنشاء مستخدم admin افتراضي
    echo "<h2>4. إنشاء المستخدمين</h2>";
    
    // فحص وجود المستخدم admin
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', 'admin@system.com', password_hash('admin123', PASSWORD_DEFAULT), 'مدير النظام']);
        echo "✅ تم إنشاء مستخدم المدير (admin / admin123)<br>";
    } else {
        echo "✅ مستخدم المدير موجود مسبقاً<br>";
    }
    
    // إنشاء بعض المستخدمين التجريبيين
    $users = [
        ['user1', 'user1@test.com', 'أحمد محمد'],
        ['user2', 'user2@test.com', 'فاطمة علي'],
        ['user3', 'user3@test.com', 'خالد سعد']
    ];
    
    foreach ($users as $user) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user[0]]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user[0], $user[1], password_hash('123456', PASSWORD_DEFAULT), $user[2]]);
            echo "✅ تم إنشاء المستخدم: {$user[2]}<br>";
        }
    }
    
    // 6. إنشاء طلبات تجريبية
    echo "<h2>5. إنشاء بيانات تجريبية</h2>";
    
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
    echo "✅ تم إنشاء " . count($requests) . " طلب تجريبي<br>";
    
    // 7. إنشاء تقييمات للطلبات المكتملة
    $ratings = [
        [3, 3, 5, 4],  // طلب 3, مستخدم 3, جودة 5, سرعة 4
        [4, 2, 4, 5]   // طلب 4, مستخدم 2, جودة 4, سرعة 5
    ];
    
    foreach ($ratings as $rating) {
        $stmt = $conn->prepare("INSERT IGNORE INTO request_ratings (request_id, user_id, quality_rate, speed_rate) VALUES (?, ?, ?, ?)");
        $stmt->execute($rating);
    }
    echo "✅ تم إنشاء " . count($ratings) . " تقييم<br>";
    
    // 8. عرض الإحصائيات النهائية
    echo "<h2>6. إحصائيات النظام</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $users_count = $stmt->fetch()['count'];
    echo "👥 عدد المستخدمين: $users_count<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM requests");
    $requests_count = $stmt->fetch()['count'];
    echo "📋 عدد الطلبات: $requests_count<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM request_ratings");
    $ratings_count = $stmt->fetch()['count'];
    echo "⭐ عدد التقييمات: $ratings_count<br>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 10px; margin: 30px 0;'>";
    echo "<h2 style='color: #155724; margin-top: 0;'>🎉 تم إصلاح النظام بنجاح!</h2>";
    echo "<p style='color: #155724; font-size: 16px;'>جميع المشاكل تم حلها والنظام جاهز للاستخدام</p>";
    echo "</div>";
    
    echo "<h2>🔗 روابط سريعة</h2>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='admin.html' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold;'>🏠 لوحة التحكم</a>";
    echo "<a href='track.html' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold;'>📋 تتبع الطلبات</a>";
    echo "<a href='request.html' style='background: #ffc107; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold;'>➕ طلب جديد</a>";
    echo "<a href='login.html' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold;'>🔐 تسجيل دخول</a>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #856404; margin-top: 0;'>📝 معلومات تسجيل الدخول:</h3>";
    echo "<strong style='color: #856404;'>مدير النظام:</strong> admin / admin123<br>";
    echo "<strong style='color: #856404;'>مستخدم تجريبي:</strong> user1 / 123456";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2 style='color: #721c24; margin-top: 0;'>❌ خطأ في قاعدة البيانات</h2>";
    echo "<p style='color: #721c24;'><strong>الخطأ:</strong> " . $e->getMessage() . "</p>";
    echo "<h3 style='color: #721c24;'>الحلول:</h3>";
    echo "<ol style='color: #721c24;'>";
    echo "<li>تأكد من تشغيل XAMPP Control Panel</li>";
    echo "<li>ابدأ خدمة MySQL من XAMPP</li>";
    echo "<li>تأكد من أن MySQL يعمل على المنفذ 3306</li>";
    echo "<li>إذا استمر الخطأ، أعد تشغيل XAMPP</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</div>";
?>