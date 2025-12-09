<?php
/**
 * ملف فحص اتصال قاعدة البيانات
 */

require_once './config/database.php';

echo "<h2>🔍 فحص اتصال قاعدة البيانات</h2>";
echo "<div style='font-family: Arial; padding: 20px;'>";

try {
    echo "<h3>📋 معلومات الاتصال:</h3>";
    echo "🏠 الخادم: " . DB_HOST . "<br>";
    echo "🗄️ قاعدة البيانات: " . DB_NAME . "<br>";
    echo "👤 المستخدم: " . DB_USER . "<br>";
    echo "🔒 كلمة المرور: " . (DB_PASS ? "محددة" : "فارغة") . "<br><br>";
    
    // محاولة الاتصال
    echo "<h3>🔗 محاولة الاتصال...</h3>";
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ <span style='color: green; font-weight: bold;'>تم الاتصال بقاعدة البيانات بنجاح!</span><br><br>";
    
    // فحص الجداول الموجودة
    echo "<h3>📊 فحص الجداول:</h3>";
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>📋 $table</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ <span style='color: orange;'>لا توجد جداول في قاعدة البيانات</span><br>";
    }
    
    // فحص الجداول المطلوبة
    echo "<h3>🔍 فحص الجداول المطلوبة:</h3>";
    $required_tables = ['users', 'requests', 'request_ratings'];
    
    foreach ($required_tables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ جدول $table موجود<br>";
            
            // عدد السجلات
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "&nbsp;&nbsp;&nbsp;📊 عدد السجلات: $count<br>";
        } else {
            echo "❌ <span style='color: red;'>جدول $table مفقود</span><br>";
        }
    }
    
    echo "<br><h3>🎯 الإجراءات المتاحة:</h3>";
    
    if (in_array('users', $tables) && in_array('requests', $tables)) {
        echo "<a href='create_test_data.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>إنشاء بيانات تجريبية</a>";
    } else {
        echo "<a href='setup_database.php' style='background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>إنشاء قاعدة البيانات</a>";
    }
    
    echo "<a href='admin.html' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>لوحة التحكم</a>";
    echo "<a href='track.html' style='background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>تتبع الطلبات</a>";
    
} catch (PDOException $e) {
    echo "❌ <span style='color: red; font-weight: bold;'>خطأ في الاتصال بقاعدة البيانات:</span><br>";
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>رسالة الخطأ:</strong> " . $e->getMessage() . "<br><br>";
    
    // اقتراحات الحلول
    echo "<strong>الحلول المقترحة:</strong><br>";
    echo "1. تأكد من تشغيل XAMPP و MySQL<br>";
    echo "2. تأكد من وجود قاعدة البيانات '" . DB_NAME . "'<br>";
    echo "3. تحقق من بيانات الاتصال في config/database.php<br>";
    echo "4. يمكنك إنشاء قاعدة البيانات من خلال <a href='setup_database.php'>هذا الرابط</a><br>";
    echo "</div>";
}

echo "</div>";
?>