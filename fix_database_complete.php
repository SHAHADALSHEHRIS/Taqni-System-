<?php
// إصلاح مشكلة قاعدة البيانات - إنشاء الجداول المفقودة
require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>";
echo "<html dir='rtl' lang='ar'>";
echo "<head><meta charset='UTF-8'><title>إصلاح قاعدة البيانات</title>";
echo "<style>body{font-family:Arial;direction:rtl;padding:20px;background:#f0f8ff;} .status{padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid;} .success{background:#d4edda;border-color:#28a745;color:#155724;} .error{background:#f8d7da;border-color:#dc3545;color:#721c24;} .warning{background:#fff3cd;border-color:#ffc107;color:#856404;} .info{background:#d1ecf1;border-color:#17a2b8;color:#0c5460;} h1{color:#2d5c8a;text-align:center;} h2{color:#2d5c8a;margin-top:30px;} .btn{background:#007bff;color:white;padding:12px 25px;border:none;border-radius:5px;cursor:pointer;margin:10px 5px;text-decoration:none;display:inline-block;} .btn:hover{background:#0056b3;}</style>";
echo "</head><body>";

echo "<h1>🔧 إصلاح قاعدة البيانات</h1>";

try {
    $database = new Database();
    $conn = $database->connect();
    echo "<div class='status success'>✅ تم الاتصال بقاعدة البيانات بنجاح</div>";
    
    // فحص الجداول الموجودة
    echo "<h2>📋 فحص الجداول الموجودة:</h2>";
    $stmt = $conn->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($existingTables as $table) {
        echo "<div class='status info'>📁 جدول موجود: $table</div>";
    }
    
    // الجداول المطلوبة
    $requiredTables = [
        'users' => "CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            employee_id VARCHAR(50) UNIQUE NOT NULL,
            username VARCHAR(100) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'requests' => "CREATE TABLE requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            request_type VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        
        'request_tracking' => "CREATE TABLE request_tracking (
            id INT PRIMARY KEY AUTO_INCREMENT,
            request_id INT NOT NULL,
            status_change VARCHAR(255) NOT NULL,
            notes TEXT,
            changed_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id)
        )",
        
        'ratings' => "CREATE TABLE ratings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            request_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_request (request_id, user_id),
            FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        
        'request_ratings' => "CREATE TABLE request_ratings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            request_id INT NOT NULL,
            user_id INT NOT NULL,
            quality_rate INT NOT NULL CHECK (quality_rate >= 1 AND quality_rate <= 5),
            speed_rate INT NOT NULL CHECK (speed_rate >= 1 AND speed_rate <= 5),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_request_rating (request_id, user_id),
            FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )"
    ];
    
    echo "<h2>🔨 إنشاء الجداول المفقودة:</h2>";
    
    foreach ($requiredTables as $tableName => $createSQL) {
        if (!in_array($tableName, $existingTables)) {
            try {
                $conn->exec($createSQL);
                echo "<div class='status success'>✅ تم إنشاء جدول: $tableName</div>";
            } catch (Exception $e) {
                echo "<div class='status error'>❌ خطأ في إنشاء جدول $tableName: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='status info'>ℹ️ جدول $tableName موجود مسبقاً</div>";
        }
    }
    
    // التأكد من وجود المستخدم الافتراضي
    echo "<h2>👤 فحص المستخدم الافتراضي:</h2>";
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE employee_id = '1001'");
    $stmt->execute();
    $userExists = $stmt->fetchColumn();
    
    if ($userExists == 0) {
        $stmt = $conn->prepare("INSERT INTO users (employee_id, username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['1001', 'admin', 'مدير النظام', 'admin@company.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
        echo "<div class='status success'>✅ تم إنشاء المستخدم الافتراضي (1001 / admin123)</div>";
    } else {
        echo "<div class='status info'>ℹ️ المستخدم الافتراضي موجود</div>";
    }
    
    // اختبار الجداول
    echo "<h2>🧪 اختبار الجداول:</h2>";
    
    foreach (array_keys($requiredTables) as $tableName) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $tableName");
            $count = $stmt->fetchColumn();
            echo "<div class='status success'>✅ جدول $tableName: $count سجل</div>";
        } catch (Exception $e) {
            echo "<div class='status error'>❌ خطأ في جدول $tableName: " . $e->getMessage() . "</div>";
        }
    }
    
    // إضافة طلبات تجريبية إذا كانت فارغة
    echo "<h2>📝 إضافة بيانات تجريبية:</h2>";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM requests");
    $requestCount = $stmt->fetchColumn();
    
    if ($requestCount == 0) {
        echo "<div class='status warning'>⚠️ لا توجد طلبات في النظام. جاري إضافة بيانات تجريبية...</div>";
        
        // الحصول على معرف المستخدم
        $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = '1001'");
        $stmt->execute();
        $user = $stmt->fetch();
        $user_id = $user['id'];
        
        // طلبات تجريبية
        $sampleRequests = [
            ['electricity', 'انقطاع الكهرباء في المكتب', 'يوجد انقطاع في التيار الكهربائي', 'high', 'completed'],
            ['plumbing', 'تسريب مياه في الحمام', 'تسريب مياه في حمام الطابق الأول', 'medium', 'completed'],
            ['ac', 'عطل في التكييف', 'التكييف لا يعمل في قاعة الاجتماعات', 'medium', 'in_progress'],
            ['it', 'مشكلة في الطابعة', 'الطابعة لا تطبع بوضوح', 'low', 'pending'],
            ['other', 'إصلاح الباب', 'الباب الرئيسي لا يُغلق بشكل صحيح', 'medium', 'completed']
        ];
        
        foreach ($sampleRequests as $req) {
            $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $req[0], $req[1], $req[2], $req[3], $req[4]]);
            
            $request_id = $conn->lastInsertId();
            
            // إضافة سجل تتبع
            $stmt = $conn->prepare("INSERT INTO request_tracking (request_id, status_change, notes, changed_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$request_id, 'تم إنشاء الطلب', 'طلب تجريبي', $user_id]);
            
            echo "<div class='status info'>📋 تم إضافة طلب: {$req[1]}</div>";
        }
        
        // إضافة تقييمات للطلبات المكتملة
        $stmt = $conn->query("SELECT id FROM requests WHERE status = 'completed'");
        $completedRequests = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($completedRequests as $requestId) {
            $rating = rand(3, 5);
            $comment = ['خدمة ممتازة', 'راضي عن الأداء', 'عمل محترف'][rand(0, 2)];
            
            $stmt = $conn->prepare("INSERT INTO ratings (request_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$requestId, $user_id, $rating, $comment]);
            
            echo "<div class='status info'>⭐ تم إضافة تقييم $rating/5 للطلب رقم $requestId</div>";
        }
        
        echo "<div class='status success'>✅ تم إضافة البيانات التجريبية بنجاح</div>";
    } else {
        echo "<div class='status info'>ℹ️ يوجد $requestCount طلب في النظام</div>";
    }
    
    // فحص نهائي
    echo "<h2>✅ فحص نهائي:</h2>";
    
    // اختبار API للتقييمات
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM ratings");
        $ratingsCount = $stmt->fetchColumn();
        echo "<div class='status success'>✅ جدول التقييمات يعمل بنجاح - عدد التقييمات: $ratingsCount</div>";
        
        $stmt = $conn->query("SELECT AVG(rating) as avg_rating FROM ratings");
        $avg = $stmt->fetch();
        if ($avg['avg_rating']) {
            echo "<div class='status info'>📊 متوسط التقييمات: " . round($avg['avg_rating'], 2) . "/5</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='status error'>❌ مشكلة في جدول التقييمات: " . $e->getMessage() . "</div>";
    }
    
    echo "<div class='status success'><strong>🎉 تم إصلاح قاعدة البيانات بنجاح!</strong></div>";
    
    echo "<h2>🔗 اختبار النظام:</h2>";
    echo "<div style='text-align:center;margin:20px 0;'>";
    echo "<a href='track.html' class='btn'>📋 صفحة تتبع الطلبات</a>";
    echo "<a href='admin.html' class='btn'>🔐 لوحة الإدارة</a>";
    echo "<a href='system_test.html' class='btn'>🧪 اختبار شامل</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='status error'>❌ خطأ في قاعدة البيانات: " . $e->getMessage() . "</div>";
    echo "<div class='status warning'>💡 تأكد من تشغيل XAMPP وأن MySQL يعمل بشكل صحيح</div>";
}

echo "</body></html>";
?>