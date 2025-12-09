<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<h2>فحص جداول قاعدة البيانات</h2>';

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo '<p style="color: red;">فشل الاتصال بقاعدة البيانات</p>';
        exit;
    }
    
    // فحص الجداول المطلوبة
    $tables = ['users', 'requests', 'user_sessions', 'request_tracking', 'request_ratings'];
    
    foreach ($tables as $table) {
        echo "<h3>فحص جدول $table:</h3>";
        
        try {
            // فحص وجود الجدول
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ الجدول موجود</p>";
                
                // عرض عدد السجلات
                $count_stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch()['count'];
                echo "<p>عدد السجلات: $count</p>";
                
                // عرض بنية الجدول
                echo "<details><summary>بنية الجدول</summary>";
                $structure = $conn->query("DESCRIBE $table");
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>العمود</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $structure->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['Field']}</td>";
                    echo "<td>{$row['Type']}</td>";
                    echo "<td>{$row['Null']}</td>";
                    echo "<td>{$row['Key']}</td>";
                    echo "<td>{$row['Default']}</td>";
                    echo "</tr>";
                }
                echo "</table></details>";
                
                // عرض عينة من البيانات للجداول المهمة
                if ($table == 'users' && $count > 0) {
                    echo "<h4>عينة من المستخدمين:</h4>";
                    $sample = $conn->query("SELECT id, username, email, full_name, role, created_at FROM users LIMIT 5");
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr><th>ID</th><th>اسم المستخدم</th><th>البريد</th><th>الاسم الكامل</th><th>الدور</th><th>تاريخ الإنشاء</th></tr>";
                    while ($row = $sample->fetch()) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['username']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['role']}</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ الجدول غير موجود</p>";
                
                // إنشاء الجدول المفقود
                if ($table == 'user_sessions') {
                    echo "<p>جاري إنشاء جدول user_sessions...</p>";
                    $create_sql = "
                        CREATE TABLE user_sessions (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            session_token VARCHAR(255) UNIQUE NOT NULL,
                            ip_address VARCHAR(45),
                            user_agent TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            expires_at TIMESTAMP NOT NULL,
                            INDEX idx_user_id (user_id),
                            INDEX idx_session_token (session_token),
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                    ";
                    
                    if ($conn->exec($create_sql)) {
                        echo "<p style='color: green;'>✅ تم إنشاء جدول user_sessions</p>";
                    } else {
                        echo "<p style='color: red;'>❌ فشل إنشاء جدول user_sessions</p>";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>خطأ في فحص الجدول $table: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo '<p style="color: red;">خطأ: ' . $e->getMessage() . '</p>';
}
?>