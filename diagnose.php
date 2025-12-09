<?php
/**
 * ملف تشخيص شامل للنظام
 */

echo "<h1>🔧 تشخيص شامل للنظام</h1>";
echo "<div style='font-family: Arial; padding: 20px;'>";

// 1. فحص PHP
echo "<h2>🐘 فحص PHP</h2>";
echo "✅ إصدار PHP: " . phpversion() . "<br>";
echo "✅ امتدادات مطلوبة:<br>";

$required_extensions = ['pdo', 'pdo_mysql', 'mysqli'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "&nbsp;&nbsp;✅ $ext<br>";
    } else {
        echo "&nbsp;&nbsp;❌ <span style='color:red;'>$ext مفقود</span><br>";
    }
}

// 2. فحص الملفات
echo "<h2>📁 فحص الملفات المطلوبة</h2>";
$required_files = [
    'config/database.php',
    'api/requests.php',
    'api/auth.php',
    'js/database.js',
    'admin.html',
    'track.html'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ <span style='color:red;'>$file مفقود</span><br>";
    }
}

// 3. فحص قاعدة البيانات
echo "<h2>🗄️ فحص قاعدة البيانات</h2>";

require_once './config/database.php';

try {
    // محاولة الاتصال بقاعدة البيانات
    $conn = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ الاتصال بخادم MySQL ناجح<br>";
    
    // فحص وجود قاعدة البيانات
    $stmt = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "✅ قاعدة البيانات '" . DB_NAME . "' موجودة<br>";
        
        // الاتصال بقاعدة البيانات المحددة
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        
        // فحص الجداول
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $required_tables = ['users', 'requests', 'request_ratings'];
        foreach ($required_tables as $table) {
            if (in_array($table, $tables)) {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                echo "✅ جدول $table موجود ($count سجل)<br>";
            } else {
                echo "❌ <span style='color:red;'>جدول $table مفقود</span><br>";
            }
        }
        
    } else {
        echo "❌ <span style='color:red;'>قاعدة البيانات '" . DB_NAME . "' غير موجودة</span><br>";
    }
    
} catch (PDOException $e) {
    echo "❌ <span style='color:red;'>خطأ في قاعدة البيانات: " . $e->getMessage() . "</span><br>";
}

// 4. فحص الأذونات
echo "<h2>🔐 فحص الأذونات</h2>";
$directories = ['config', 'api', 'js'];
foreach ($directories as $dir) {
    if (is_readable($dir)) {
        echo "✅ مجلد $dir قابل للقراءة<br>";
    } else {
        echo "❌ <span style='color:red;'>مجلد $dir غير قابل للقراءة</span><br>";
    }
}

// 5. الحلول المقترحة
echo "<h2>🔧 الحلول المقترحة</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>لحل مشاكل قاعدة البيانات:</h3>";
echo "1. تأكد من تشغيل XAMPP Control Panel<br>";
echo "2. ابدأ خدمة MySQL من XAMPP<br>";
echo "3. إذا لم تكن قاعدة البيانات موجودة، اضغط على الرابط أدناه لإنشائها<br>";
echo "4. إذا كانت الجداول مفقودة، اضغط على رابط إعداد قاعدة البيانات<br>";
echo "</div>";

echo "<h3>🎯 الإجراءات المتاحة:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='setup_database.php' style='background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>إعداد قاعدة البيانات</a>";
echo "<a href='create_test_data.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>إنشاء بيانات تجريبية</a>";
echo "<a href='check_database.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>فحص قاعدة البيانات</a>";
echo "<a href='admin.html' style='background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>لوحة التحكم</a>";
echo "</div>";

echo "</div>";
?>