<?php
$host = 'localhost';
$dbname = 'shahad_clean_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ الاتصال بقاعدة البيانات نجح\n";
    
    // فحص جدول requests
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM requests');
    $count = $stmt->fetch()['count'];
    echo "عدد الطلبات في جدول requests: $count\n";
    
    // فحص آخر 5 طلبات
    $stmt = $pdo->query('SELECT id, service_type, status, created_at FROM requests ORDER BY id DESC LIMIT 5');
    $requests = $stmt->fetchAll();
    echo "آخر 5 طلبات:\n";
    foreach($requests as $req) {
        echo "- ID: {$req['id']}, النوع: {$req['service_type']}, الحالة: {$req['status']}, التاريخ: {$req['created_at']}\n";
    }
    
    // فحص API
    echo "\n🔍 فحص API:\n";
    $api_url = 'http://localhost/projeect/api/requests.php?action=getAllRequests';
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        $requests_count = 0;
        if (isset($data['data']) && is_array($data['data'])) {
            $requests_count += count($data['data']);
        }
        if (isset($data['requests']) && is_array($data['requests'])) {
            $requests_count += count($data['requests']);
        }
        echo "API يعمل بنجاح - عدد الطلبات المُرجعة: $requests_count\n";
    } else {
        echo "❌ مشكلة في API\n";
        echo "الاستجابة: " . $response . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>