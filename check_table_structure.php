<?php
$host = 'localhost';
$dbname = 'shahad_clean_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ الاتصال بقاعدة البيانات نجح\n\n";
    
    // فحص هيكل جدول requests
    echo "🔍 فحص هيكل جدول requests:\n";
    $stmt = $pdo->query('DESCRIBE requests');
    $columns = $stmt->fetchAll();
    foreach($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n📊 عدد الطلبات: ";
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM requests');
    $count = $stmt->fetch()['count'];
    echo "$count\n\n";
    
    // عرض آخر 3 طلبات (باستخدام الأعمدة الموجودة فعلاً)
    echo "📋 آخر 3 طلبات:\n";
    $stmt = $pdo->query('SELECT * FROM requests ORDER BY id DESC LIMIT 3');
    $requests = $stmt->fetchAll();
    
    if (count($requests) > 0) {
        foreach($requests as $req) {
            echo "- ID: {$req['id']}\n";
            foreach($req as $key => $value) {
                if ($key !== 'id' && !is_numeric($key)) {
                    echo "  $key: $value\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "لا توجد طلبات\n";
    }
    
} catch(Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?>