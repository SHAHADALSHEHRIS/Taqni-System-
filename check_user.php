<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<h2>فحص بيانات المستخدم</h2>';

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo '<p style="color: red;">فشل الاتصال بقاعدة البيانات</p>';
        exit;
    }
    
    // البحث عن المستخدم 1001
    $sql = "SELECT * FROM users WHERE username = '1001' OR id = 1001";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">';
        echo '<h3>✅ المستخدم موجود:</h3>';
        echo '<ul>';
        echo '<li><strong>ID:</strong> ' . $user['id'] . '</li>';
        echo '<li><strong>اسم المستخدم:</strong> ' . $user['username'] . '</li>';
        echo '<li><strong>البريد:</strong> ' . $user['email'] . '</li>';
        echo '<li><strong>الاسم الكامل:</strong> ' . $user['full_name'] . '</li>';
        echo '<li><strong>الدور:</strong> ' . $user['role'] . '</li>';
        echo '<li><strong>تاريخ الإنشاء:</strong> ' . $user['created_at'] . '</li>';
        echo '</ul>';
        echo '</div>';
        
        // اختبار كلمة المرور
        echo '<h3>اختبار كلمة المرور:</h3>';
        if (password_verify('admin123', $user['password'])) {
            echo '<p style="color: green;">✅ كلمة المرور صحيحة</p>';
        } else {
            echo '<p style="color: red;">❌ كلمة المرور غير صحيحة</p>';
            
            // إنشاء كلمة مرور جديدة
            $new_password = password_hash('admin123', PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt->execute([$new_password, $user['id']])) {
                echo '<p style="color: blue;">🔄 تم تحديث كلمة المرور</p>';
            }
        }
        
    } else {
        echo '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">';
        echo '<h3>❌ المستخدم غير موجود</h3>';
        echo '<p>سيتم إنشاء المستخدم الآن...</p>';
        echo '</div>';
        
        // إنشاء المستخدم
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if ($insert_stmt->execute(['1001', 'admin@company.com', $password_hash, 'مدير النظام', 'employee'])) {
            echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">';
            echo '<h3>✅ تم إنشاء المستخدم بنجاح</h3>';
            echo '<ul>';
            echo '<li><strong>اسم المستخدم:</strong> 1001</li>';
            echo '<li><strong>كلمة المرور:</strong> admin123</li>';
            echo '<li><strong>البريد:</strong> admin@company.com</li>';
            echo '<li><strong>الدور:</strong> employee</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<p style="color: red;">فشل في إنشاء المستخدم</p>';
        }
    }
    
    // عرض إجمالي المستخدمين
    $count_sql = "SELECT COUNT(*) as total FROM users";
    $count_stmt = $conn->query($count_sql);
    $total_users = $count_stmt->fetch()['total'];
    echo "<p><strong>إجمالي المستخدمين في النظام:</strong> $total_users</p>";
    
} catch (Exception $e) {
    echo '<p style="color: red;">خطأ: ' . $e->getMessage() . '</p>';
}
?>