<?php
// فحص قاعدة البيانات وإصلاح المشاكل
require_once __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html>";
echo "<html dir='rtl' lang='ar'>";
echo "<head><meta charset='UTF-8'><title>فحص وإصلاح قاعدة البيانات</title>";
echo "<style>body{font-family:Arial;direction:rtl;padding:20px;background:#f0f8ff;} .status{padding:10px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;} .error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;} .warning{background:#fff3cd;border:1px solid #ffeaa7;color:#856404;} .info{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;}</style>";
echo "</head><body>";

echo "<h1>🔍 فحص وإصلاح قاعدة البيانات</h1>";

try {
    $database = new Database();
    $conn = $database->connect();
    echo "<div class='status success'>✅ تم الاتصال بقاعدة البيانات بنجاح</div>";
    
    // فحص الجداول الموجودة
    echo "<h2>📋 الجداول الموجودة:</h2>";
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['users', 'requests', 'request_tracking', 'ratings'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "<div class='status success'>✅ جدول $table موجود</div>";
        } else {
            echo "<div class='status error'>❌ جدول $table مفقود</div>";
            $missingTables[] = $table;
        }
    }
    
    // إنشاء الجداول المفقودة
    if (!empty($missingTables)) {
        echo "<h2>🔧 إنشاء الجداول المفقودة:</h2>";
        
        // جدول المستخدمين
        if (in_array('users', $missingTables)) {
            $sql = "CREATE TABLE users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                employee_id VARCHAR(50) UNIQUE NOT NULL,
                username VARCHAR(100) NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                password VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($sql);
            echo "<div class='status success'>✅ تم إنشاء جدول users</div>";
            
            // إدراج بيانات المدير الافتراضي
            $stmt = $conn->prepare("INSERT INTO users (employee_id, username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['1001', 'admin', 'مدير النظام', 'admin@company.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
            echo "<div class='status info'>ℹ️ تم إدراج المدير الافتراضي</div>";
        }
        
        // جدول الطلبات
        if (in_array('requests', $missingTables)) {
            $sql = "CREATE TABLE requests (
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
            )";
            $conn->exec($sql);
            echo "<div class='status success'>✅ تم إنشاء جدول requests</div>";
        }
        
        // جدول تتبع الطلبات
        if (in_array('request_tracking', $missingTables)) {
            $sql = "CREATE TABLE request_tracking (
                id INT PRIMARY KEY AUTO_INCREMENT,
                request_id INT NOT NULL,
                status_change VARCHAR(255) NOT NULL,
                notes TEXT,
                changed_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
                FOREIGN KEY (changed_by) REFERENCES users(id)
            )";
            $conn->exec($sql);
            echo "<div class='status success'>✅ تم إنشاء جدول request_tracking</div>";
        }
        
        // جدول التقييمات
        if (in_array('ratings', $missingTables)) {
            $sql = "CREATE TABLE ratings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                request_id INT NOT NULL,
                user_id INT NOT NULL,
                rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_request (request_id, user_id),
                FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )";
            $conn->exec($sql);
            echo "<div class='status success'>✅ تم إنشاء جدول ratings</div>";
        }
    }
    
    // فحص البيانات الموجودة
    echo "<h2>📊 إحصائيات البيانات:</h2>";
    foreach ($tables as $table) {
        if (in_array($table, $requiredTables)) {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<div class='status info'>📈 جدول $table: $count سجل</div>";
        }
    }
    
    // إضافة بيانات تجريبية إذا كانت فارغة
    echo "<h2>🎲 إضافة بيانات تجريبية:</h2>";
    
    // فحص الطلبات
    $stmt = $conn->query("SELECT COUNT(*) FROM requests");
    $requestCount = $stmt->fetchColumn();
    
    if ($requestCount == 0) {
        // إضافة بعض الطلبات التجريبية
        $sampleRequests = [
            [1, 'electricity', 'انقطاع الكهرباء في المكتب', 'يوجد انقطاع في التيار الكهربائي في الطابق الثاني', 'high', 'completed'],
            [1, 'plumbing', 'تسريب في الحمام', 'يوجد تسريب مياه في حمام الموظفين', 'medium', 'completed'],
            [1, 'ac', 'عطل في التكييف', 'التكييف لا يعمل في غرفة الاجتماعات', 'medium', 'in_progress'],
            [1, 'it', 'مشكلة في الطابعة', 'الطابعة لا تطبع بشكل واضح', 'low', 'pending']
        ];
        
        foreach ($sampleRequests as $req) {
            $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($req);
        }
        echo "<div class='status success'>✅ تم إضافة " . count($sampleRequests) . " طلبات تجريبية</div>";
        
        // إضافة تقييمات للطلبات المكتملة
        $stmt = $conn->query("SELECT id FROM requests WHERE status = 'completed'");
        $completedRequests = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($completedRequests as $requestId) {
            $rating = rand(3, 5); // تقييم بين 3 و 5
            $comments = ['خدمة ممتازة', 'سرعة في الاستجابة', 'عمل احترافي', 'راضي عن الخدمة'];
            $comment = $comments[array_rand($comments)];
            
            $stmt = $conn->prepare("INSERT INTO ratings (request_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$requestId, 1, $rating, $comment]);
        }
        echo "<div class='status success'>✅ تم إضافة تقييمات للطلبات المكتملة</div>";
    } else {
        echo "<div class='status info'>ℹ️ يوجد $requestCount طلب في النظام</div>";
    }
    
    echo "<h2>✅ تم الانتهاء من فحص وإصلاح قاعدة البيانات</h2>";
    echo "<p><a href='track.html' style='color:blue;'>🔗 انتقل إلى صفحة تتبع الطلبات</a></p>";
    echo "<p><a href='admin.html' style='color:blue;'>🔗 انتقل إلى لوحة الإدارة</a></p>";
    
} catch (Exception $e) {
    echo "<div class='status error'>❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>