<?php
/**
 * ملف التحقق من حالة النظام
 * System Status Check
 */

// تشغيل عرض الأخطاء للمساعدة في التشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><meta charset='UTF-8'><title>فحص حالة النظام</title></head><body>";
echo "<h2>فحص حالة نظام إدارة الطلبات</h2>";

// التحقق من إصدار PHP
echo "<h3>1. فحص إصدار PHP</h3>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "✅ إصدار PHP: " . PHP_VERSION . " (مدعوم)<br>";
} else {
    echo "❌ إصدار PHP: " . PHP_VERSION . " (يتطلب 7.4 أو أحدث)<br>";
}

// التحقق من امتدادات PHP المطلوبة
echo "<h3>2. فحص امتدادات PHP</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ امتداد $ext: متوفر<br>";
    } else {
        echo "❌ امتداد $ext: غير متوفر<br>";
    }
}

// التحقق من ملفات التكوين
echo "<h3>3. فحص ملفات التكوين</h3>";
$config_files = [
    'config/database.php' => 'ملف إعدادات قاعدة البيانات',
    'api/auth.php' => 'API المصادقة',
    'api/requests.php' => 'API الطلبات',
    'js/api.js' => 'ملف JavaScript'
];

foreach ($config_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: موجود<br>";
    } else {
        echo "❌ $description: غير موجود<br>";
    }
}

// التحقق من الاتصال بقاعدة البيانات
echo "<h3>4. فحص الاتصال بقاعدة البيانات</h3>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->connect();
        
        if ($conn) {
            echo "✅ الاتصال بقاعدة البيانات: نجح<br>";
            
            // التحقق من وجود الجداول
            $tables = ['users', 'requests', 'request_tracking', 'user_sessions'];
            foreach ($tables as $table) {
                $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() > 0) {
                    echo "✅ جدول $table: موجود<br>";
                } else {
                    echo "❌ جدول $table: غير موجود<br>";
                }
            }
            
            // التحقق من المستخدم الإداري
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                echo "✅ المستخدم الإداري: موجود<br>";
            } else {
                echo "❌ المستخدم الإداري: غير موجود<br>";
            }
            
        } else {
            echo "❌ الاتصال بقاعدة البيانات: فشل<br>";
        }
    } else {
        echo "❌ ملف إعدادات قاعدة البيانات غير موجود<br>";
    }
} catch (Exception $e) {
    echo "❌ خطأ في الاتصال: " . $e->getMessage() . "<br>";
}

// التحقق من صلاحيات الملفات
echo "<h3>5. فحص صلاحيات الملفات</h3>";
$directories = ['config', 'api', 'js'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_readable($dir)) {
            echo "✅ مجلد $dir: قابل للقراءة<br>";
        } else {
            echo "❌ مجلد $dir: غير قابل للقراءة<br>";
        }
    } else {
        echo "❌ مجلد $dir: غير موجود<br>";
    }
}

echo "<h3>6. إجراءات مقترحة</h3>";
echo "<ul>";
echo "<li>إذا كانت هناك أخطاء في قاعدة البيانات، قم بتشغيل <a href='setup_database.php'>setup_database.php</a></li>";
echo "<li>تأكد من أن إعدادات قاعدة البيانات في config/database.php صحيحة</li>";
echo "<li>تأكد من أن خادم MySQL يعمل</li>";
echo "<li>تحقق من صلاحيات المجلدات والملفات</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>ملاحظة:</strong> هذا الملف للتشخيص فقط. احذفه من الخادم بعد التأكد من عمل النظام.</p>";
echo "</body></html>";
?>