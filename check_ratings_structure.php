<?php
require_once './config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    echo "<h2>🔍 فحص بنية جدول التقييمات</h2>";
    
    // عرض بنية الجدول
    $describe = "DESCRIBE request_ratings";
    $stmt = $conn->prepare($describe);
    $stmt->execute();
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 بنية الجدول:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>الحقل</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($structure as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // عرض البيانات الموجودة
    echo "<h3>📊 البيانات الموجودة:</h3>";
    $select = "SELECT * FROM request_ratings ORDER BY created_at DESC";
    $stmt = $conn->prepare($select);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        echo "<p style='color: red;'>❌ لا توجد تقييمات في قاعدة البيانات</p>";
        
        // إضافة تقييمات تجريبية
        echo "<h3>➕ إضافة تقييمات تجريبية:</h3>";
        $insert = "INSERT INTO request_ratings (request_id, user_id, quality_rate, speed_rate, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert);
        
        $testRatings = [
            [1, 1, 5, 4],
            [2, 2, 4, 5],
            [3, 1, 5, 5],
            [4, 2, 3, 4],
            [5, 1, 4, 3]
        ];
        
        foreach ($testRatings as $rating) {
            try {
                $stmt->execute($rating);
                echo "✅ تم إضافة تقييم للطلب {$rating[0]} - الجودة: {$rating[2]}/5، السرعة: {$rating[3]}/5<br>";
            } catch (Exception $e) {
                echo "⚠️ خطأ في إضافة التقييم: " . $e->getMessage() . "<br>";
            }
        }
        
        // إعادة تحميل البيانات
        $stmt = $conn->prepare($select);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (!empty($data)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Request ID</th><th>User ID</th><th>Quality</th><th>Speed</th><th>Created At</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['request_id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['quality_rate']}/5</td>";
            echo "<td>{$row['speed_rate']}/5</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // حساب الإحصائيات
        $avgQuality = array_sum(array_column($data, 'quality_rate')) / count($data);
        $avgSpeed = array_sum(array_column($data, 'speed_rate')) / count($data);
        $avgOverall = ($avgQuality + $avgSpeed) / 2;
        
        echo "<h3>📈 الإحصائيات:</h3>";
        echo "<p>📊 إجمالي التقييمات: " . count($data) . "</p>";
        echo "<p>📊 متوسط الجودة: " . round($avgQuality, 2) . "/5</p>";
        echo "<p>📊 متوسط السرعة: " . round($avgSpeed, 2) . "/5</p>";
        echo "<p>📊 المتوسط العام: " . round($avgOverall, 2) . "/5</p>";
    }
    
    echo "<br><p><a href='admin.html' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔗 العودة إلى لوحة الإدارة</a></p>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>