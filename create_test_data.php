<?php
/**
 * ملف إنشاء بيانات تجريبية للنظام
 */

require_once './config/database.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 إنشاء بيانات تجريبية للنظام</h2>";
    
    // 1. إنشاء مستخدمين تجريبيين
    echo "<h3>👥 إنشاء مستخدمين تجريبيين...</h3>";
    
    $users = [
        ['أحمد محمد', 'ahmed123', 'ahmed@example.com', 'password123'],
        ['فاطمة علي', 'fatima456', 'fatima@example.com', 'password123'],
        ['خالد سعد', 'khalid789', 'khalid@example.com', 'password123'],
    ];
    
    foreach ($users as $user) {
        $checkSql = "SELECT id FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$user[1]]);
        
        if ($checkStmt->rowCount() == 0) {
            $sql = "INSERT INTO users (full_name, username, email, password, user_type, is_active) 
                    VALUES (?, ?, ?, ?, 'customer', 1)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user[0], $user[1], $user[2], password_hash($user[3], PASSWORD_DEFAULT)]);
            echo "✅ تم إنشاء المستخدم: {$user[0]}<br>";
        } else {
            echo "⚠️ المستخدم موجود مسبقاً: {$user[0]}<br>";
        }
    }
    
    // 2. إنشاء طلبات تجريبية
    echo "<h3>📋 إنشاء طلبات تجريبية...</h3>";
    
    $requests = [
        [1, 'electricity', 'مشكلة في الكهرباء', 'انقطاع في التيار الكهربائي في المكتب الرئيسي', 'high'],
        [2, 'plumbing', 'تسريب في المياه', 'يوجد تسريب في دورة المياه بالطابق الثاني', 'medium'],
        [3, 'it', 'مشكلة في الكمبيوتر', 'الكمبيوتر لا يعمل بشكل صحيح', 'low'],
        [1, 'cleaning', 'تنظيف المكتب', 'يحتاج المكتب إلى تنظيف شامل', 'medium'],
        [2, 'maintenance', 'صيانة المكيف', 'المكيف لا يبرد بشكل جيد', 'high'],
    ];
    
    foreach ($requests as $request) {
        $sql = "INSERT INTO requests (user_id, request_type, subject, description, priority, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute($request);
        $requestId = $conn->lastInsertId();
        echo "✅ تم إنشاء الطلب: {$request[2]} (ID: {$requestId})<br>";
    }
    
    // 3. إنشاء تقييمات تجريبية
    echo "<h3>⭐ إنشاء تقييمات تجريبية...</h3>";
    
    // تحديث بعض الطلبات إلى مكتملة أولاً
    $sql = "UPDATE requests SET status = 'completed' WHERE id IN (1, 3, 5)";
    $conn->exec($sql);
    
    $ratings = [
        [1, 1, 5, 4],  // طلب 1, مستخدم 1, جودة 5, سرعة 4
        [3, 3, 4, 5],  // طلب 3, مستخدم 3, جودة 4, سرعة 5
        [5, 2, 5, 5],  // طلب 5, مستخدم 2, جودة 5, سرعة 5
    ];
    
    foreach ($ratings as $rating) {
        $checkSql = "SELECT id FROM request_ratings WHERE request_id = ? AND user_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$rating[0], $rating[1]]);
        
        if ($checkStmt->rowCount() == 0) {
            $sql = "INSERT INTO request_ratings (request_id, user_id, quality_rate, speed_rate, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute($rating);
            echo "✅ تم إنشاء تقييم للطلب {$rating[0]} - جودة: {$rating[2]}, سرعة: {$rating[3]}<br>";
        }
    }
    
    // 4. إحصائيات
    echo "<h3>📊 إحصائيات البيانات:</h3>";
    
    $sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users_count = $stmt->fetch()['total'];
    echo "👥 عدد المستخدمين: {$users_count}<br>";
    
    $sql = "SELECT COUNT(*) as total FROM requests";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $requests_count = $stmt->fetch()['total'];
    echo "📋 عدد الطلبات: {$requests_count}<br>";
    
    $sql = "SELECT COUNT(*) as total FROM request_ratings";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ratings_count = $stmt->fetch()['total'];
    echo "⭐ عدد التقييمات: {$ratings_count}<br>";
    
    echo "<h3>✅ تم إنشاء البيانات التجريبية بنجاح!</h3>";
    echo "<p><a href='admin.html' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>← العودة إلى لوحة التحكم</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='color:red; padding:20px; border:1px solid red; border-radius:5px; margin:20px;'>";
    echo "<h3>❌ خطأ في إنشاء البيانات:</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}
?>