<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<h2>إضافة بيانات تجريبية للطلبات</h2>';

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo '<p style="color: red;">فشل الاتصال بقاعدة البيانات</p>';
        exit;
    }
    
    // التحقق من وجود طلبات
    $count_sql = "SELECT COUNT(*) as count FROM requests";
    $count_stmt = $conn->query($count_sql);
    $current_count = $count_stmt->fetch()['count'];
    
    echo "<p>عدد الطلبات الحالية: $current_count</p>";
    
    if ($current_count < 10) {
        echo '<p>جاري إضافة بيانات تجريبية...</p>';
        
        // إنشاء مستخدم تجريبي إذا لم يكن موجوداً
        $user_sql = "SELECT id FROM users WHERE username = 'demo_user'";
        $user_stmt = $conn->query($user_sql);
        
        if ($user_stmt->rowCount() == 0) {
            $password = password_hash('demo123', PASSWORD_DEFAULT);
            $insert_user = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_user);
            $stmt->execute(['demo_user', 'demo@example.com', $password, 'مستخدم تجريبي', 'customer']);
            echo '<p>تم إنشاء مستخدم تجريبي</p>';
        }
        
        // الحصول على user_id
        $user_id_sql = "SELECT id FROM users WHERE username = 'demo_user'";
        $user_id_stmt = $conn->query($user_id_sql);
        $demo_user_id = $user_id_stmt->fetch()['id'];
        
        // الطلبات التجريبية
        $sample_requests = [
            [
                'subject' => 'Star Refrigerator',
                'description' => 'طلب صيانة ثلاجة ستار',
                'request_type' => 'maintenance',
                'status' => 'completed',
                'priority' => 'high'
            ],
            [
                'subject' => 'Dell Laptop',
                'description' => 'طلب إصلاح لابتوب ديل',
                'request_type' => 'repair',
                'status' => 'pending',
                'priority' => 'medium'
            ],
            [
                'subject' => 'Apple Watch',
                'description' => 'طلب استبدال شاشة ساعة آبل',
                'request_type' => 'replacement',
                'status' => 'completed',
                'priority' => 'low'
            ],
            [
                'subject' => 'Adidas Shoes',
                'description' => 'طلب إرجاع حذاء أديداس',
                'request_type' => 'return',
                'status' => 'in_progress',
                'priority' => 'medium'
            ],
            [
                'subject' => 'Samsung TV',
                'description' => 'طلب صيانة تلفزيون سامسونج',
                'request_type' => 'maintenance',
                'status' => 'completed',
                'priority' => 'high'
            ],
            [
                'subject' => 'iPhone Repair',
                'description' => 'طلب إصلاح شاشة آيفون',
                'request_type' => 'repair',
                'status' => 'pending',
                'priority' => 'urgent'
            ],
            [
                'subject' => 'Nike Shoes',
                'description' => 'طلب استبدال حذاء نايك',
                'request_type' => 'exchange',
                'status' => 'rejected',
                'priority' => 'low'
            ],
            [
                'subject' => 'HP Printer',
                'description' => 'طلب صيانة طابعة HP',
                'request_type' => 'maintenance',
                'status' => 'in_progress',
                'priority' => 'medium'
            ]
        ];
        
        foreach ($sample_requests as $request) {
            $insert_sql = "INSERT INTO requests (user_id, request_type, subject, description, status, priority) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->execute([
                $demo_user_id,
                $request['request_type'],
                $request['subject'],
                $request['description'],
                $request['status'],
                $request['priority']
            ]);
        }
        
        echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0;">';
        echo '<h3>✅ تم إضافة البيانات التجريبية بنجاح</h3>';
        echo '<p>تم إضافة ' . count($sample_requests) . ' طلبات تجريبية</p>';
        echo '</div>';
    } else {
        echo '<div style="background: #d1ecf1; padding: 15px; border-radius: 5px; color: #0c5460; margin: 10px 0;">';
        echo '<h3>ℹ️ البيانات موجودة بالفعل</h3>';
        echo '<p>يوجد بالفعل ' . $current_count . ' طلبات في النظام</p>';
        echo '</div>';
    }
    
    // عرض إحصائيات الطلبات
    $stats_sql = "
        SELECT 
            status,
            COUNT(*) as count
        FROM requests 
        GROUP BY status
    ";
    $stats_stmt = $conn->query($stats_sql);
    
    echo '<h3>إحصائيات الطلبات:</h3>';
    echo '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
    echo '<tr><th style="padding: 8px; background: #f8f9fa;">الحالة</th><th style="padding: 8px; background: #f8f9fa;">العدد</th></tr>';
    
    while ($row = $stats_stmt->fetch()) {
        $status_text = [
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'rejected' => 'مرفوض'
        ][$row['status']] ?? $row['status'];
        
        echo '<tr>';
        echo '<td style="padding: 8px;">' . $status_text . '</td>';
        echo '<td style="padding: 8px; text-align: center;">' . $row['count'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">خطأ: ' . $e->getMessage() . '</p>';
}
?>

<div style="background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404; margin: 20px 0;">
    <h3>📋 الخطوات التالية:</h3>
    <ol>
        <li><a href="admin.html" target="_blank">افتح صفحة الإدارة المحدثة</a></li>
        <li>استخدم بيانات الدخول: 1001 / admin123</li>
        <li>ستجد البيانات التجريبية والمخططات البيانية</li>
    </ol>
</div>