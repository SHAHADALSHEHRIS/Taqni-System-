<?php
// إضافة طلبات تجريبية إلى النظام
require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>";
echo "<html dir='rtl' lang='ar'>";
echo "<head><meta charset='UTF-8'><title>إضافة طلبات تجريبية</title>";
echo "<style>body{font-family:Arial;direction:rtl;padding:20px;background:#f0f8ff;} .status{padding:10px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;} .error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;} .info{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;} .btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;} .btn:hover{background:#0056b3;}</style>";
echo "</head><body>";

echo "<h1>📝 إضافة طلبات تجريبية</h1>";

try {
    $database = new Database();
    $conn = $database->connect();
    echo "<div class='status success'>✅ تم الاتصال بقاعدة البيانات بنجاح</div>";
    
    // التحقق من وجود المستخدم الافتراضي
    $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = '1001'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<div class='status error'>❌ لم يتم العثور على المستخدم الافتراضي. جاري إنشاؤه...</div>";
        
        // إنشاء المستخدم الافتراضي
        $stmt = $conn->prepare("INSERT INTO users (employee_id, username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['1001', 'admin', 'مدير النظام', 'admin@company.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = '1001'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        echo "<div class='status success'>✅ تم إنشاء المستخدم الافتراضي</div>";
    }
    
    $user_id = $user['id'];
    echo "<div class='status info'>ℹ️ معرف المستخدم: $user_id</div>";
    
    // طلبات تجريبية متنوعة
    $sampleRequests = [
        // طلبات كهرباء
        [
            'type' => 'electricity',
            'subject' => 'انقطاع الكهرباء في المكتب رقم 205',
            'description' => 'يوجد انقطاع في التيار الكهربائي في المكتب رقم 205 بالطابق الثاني منذ صباح اليوم. الأجهزة لا تعمل والإضاءة مطفأة.',
            'priority' => 'high',
            'status' => 'pending'
        ],
        [
            'type' => 'electricity',
            'subject' => 'تذبذب في الكهرباء يؤثر على الأجهزة',
            'description' => 'هناك تذبذب في التيار الكهربائي في قسم المحاسبة يسبب إعادة تشغيل الأجهزة بشكل متكرر.',
            'priority' => 'medium',
            'status' => 'in_progress'
        ],
        [
            'type' => 'electricity',
            'subject' => 'عطل في المولد الاحتياطي',
            'description' => 'المولد الاحتياطي لا يعمل عند انقطاع الكهرباء. نحتاج لفحص وإصلاح فوري.',
            'priority' => 'high',
            'status' => 'completed'
        ],
        
        // طلبات سباكة
        [
            'type' => 'plumbing',
            'subject' => 'تسريب مياه في دورة المياه',
            'description' => 'يوجد تسريب مياه شديد في دورة المياه بالطابق الأول. المياه تتجمع على الأرض وتحتاج إصلاح عاجل.',
            'priority' => 'high',
            'status' => 'in_progress'
        ],
        [
            'type' => 'plumbing',
            'subject' => 'انسداد في المجاري',
            'description' => 'انسداد في مجاري الطابق الثالث يسبب رائحة كريهة وعدم تصريف المياه بشكل طبيعي.',
            'priority' => 'medium',
            'status' => 'pending'
        ],
        [
            'type' => 'plumbing',
            'subject' => 'إصلاح صنبور المطبخ',
            'description' => 'صنبور المطبخ في قسم الاستراحة يقطر باستمرار ويحتاج لاستبدال الحشوات.',
            'priority' => 'low',
            'status' => 'completed'
        ],
        
        // طلبات تكييف
        [
            'type' => 'ac',
            'subject' => 'عطل في تكييف قاعة الاجتماعات',
            'description' => 'تكييف قاعة الاجتماعات الكبرى لا يعمل. درجة الحرارة مرتفعة جداً ولا يمكن عقد الاجتماعات.',
            'priority' => 'high',
            'status' => 'pending'
        ],
        [
            'type' => 'ac',
            'subject' => 'صوت غريب من وحدة التكييف',
            'description' => 'وحدة التكييف في الطابق الثاني تصدر أصواتاً غريبة وصوت اهتزاز عالي.',
            'priority' => 'medium',
            'status' => 'in_progress'
        ],
        [
            'type' => 'ac',
            'subject' => 'تنظيف فلاتر التكييف',
            'description' => 'حان وقت تنظيف وتغيير فلاتر أجهزة التكييف في جميع الطوابق حسب الجدولة الدورية.',
            'priority' => 'low',
            'status' => 'completed'
        ],
        
        // طلبات تقنية معلومات
        [
            'type' => 'it',
            'subject' => 'عطل في خادم النظام الرئيسي',
            'description' => 'خادم النظام الرئيسي يواجه مشاكل في الأداء وبطء شديد في الاستجابة. يؤثر على جميع الموظفين.',
            'priority' => 'high',
            'status' => 'in_progress'
        ],
        [
            'type' => 'it',
            'subject' => 'مشكلة في الطابعة الشبكية',
            'description' => 'الطابعة الشبكية في قسم الموارد البشرية لا تطبع بوضوح وتحتاج لتنظيف أو استبدال الحبر.',
            'priority' => 'medium',
            'status' => 'pending'
        ],
        [
            'type' => 'it',
            'subject' => 'تحديث برامج الحماية',
            'description' => 'تحديث برامج مكافحة الفيروسات وأنظمة الحماية على جميع أجهزة الكمبيوتر في المؤسسة.',
            'priority' => 'medium',
            'status' => 'completed'
        ],
        
        // طلبات أخرى
        [
            'type' => 'other',
            'subject' => 'إصلاح الباب الرئيسي',
            'description' => 'الباب الرئيسي للمبنى يواجه صعوبة في الإغلاق والقفل لا يعمل بشكل صحيح.',
            'priority' => 'medium',
            'status' => 'pending'
        ],
        [
            'type' => 'other',
            'subject' => 'تنظيف النوافذ الخارجية',
            'description' => 'النوافذ الخارجية للمبنى تحتاج تنظيف شامل. الأتربة والغبار يؤثر على دخول الضوء الطبيعي.',
            'priority' => 'low',
            'status' => 'completed'
        ],
        [
            'type' => 'other',
            'subject' => 'صيانة المصعد',
            'description' => 'المصعد يعمل ببطء ويحدث أصواتاً غريبة. نحتاج فحص دوري للتأكد من السلامة.',
            'priority' => 'high',
            'status' => 'in_progress'
        ]
    ];
    
    echo "<h2>📋 إضافة الطلبات التجريبية:</h2>";
    
    // فحص الطلبات الموجودة
    $stmt = $conn->query("SELECT COUNT(*) FROM requests");
    $currentCount = $stmt->fetchColumn();
    echo "<div class='status info'>📊 عدد الطلبات الحالية: $currentCount</div>";
    
    // إضافة الطلبات الجديدة
    $addedCount = 0;
    $skippedCount = 0;
    
    foreach ($sampleRequests as $index => $request) {
        try {
            // تحقق من عدم وجود طلب مشابه
            $stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE subject = ?");
            $stmt->execute([$request['subject']]);
            $exists = $stmt->fetchColumn();
            
            if ($exists > 0) {
                echo "<div class='status info'>⏭️ تم تخطي: {$request['subject']} (موجود مسبقاً)</div>";
                $skippedCount++;
                continue;
            }
            
            // إضافة الطلب
            $stmt = $conn->prepare("
                INSERT INTO requests (user_id, request_type, subject, description, priority, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW() - INTERVAL ? HOUR)
            ");
            
            // إضافة فترة زمنية عشوائية للطلبات لتبدو واقعية
            $hoursAgo = rand(1, 72); // بين ساعة و 3 أيام
            
            $stmt->execute([
                $user_id,
                $request['type'],
                $request['subject'],
                $request['description'],
                $request['priority'],
                $request['status'],
                $hoursAgo
            ]);
            
            $request_id = $conn->lastInsertId();
            
            // إضافة سجل تتبع للطلب
            $stmt = $conn->prepare("
                INSERT INTO request_tracking (request_id, status_change, notes, changed_by, created_at) 
                VALUES (?, ?, ?, ?, NOW() - INTERVAL ? HOUR)
            ");
            
            $statusText = [
                'pending' => 'قيد الانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل',
                'rejected' => 'مرفوض'
            ];
            
            $stmt->execute([
                $request_id,
                "تم إنشاء الطلب - الحالة: " . $statusText[$request['status']],
                "طلب تجريبي تم إنشاؤه تلقائياً",
                $user_id,
                $hoursAgo
            ]);
            
            echo "<div class='status success'>✅ تم إضافة: {$request['subject']}</div>";
            $addedCount++;
            
        } catch (Exception $e) {
            echo "<div class='status error'>❌ خطأ في إضافة: {$request['subject']} - {$e->getMessage()}</div>";
        }
    }
    
    echo "<h2>📈 ملخص العملية:</h2>";
    echo "<div class='status info'>";
    echo "<strong>📊 إحصائيات الإضافة:</strong><br>";
    echo "• تم إضافة: $addedCount طلب جديد<br>";
    echo "• تم تخطي: $skippedCount طلب (موجود مسبقاً)<br>";
    echo "• إجمالي الطلبات في النظام: " . ($currentCount + $addedCount) . " طلب<br>";
    echo "</div>";
    
    // إضافة تقييمات للطلبات المكتملة
    echo "<h2>⭐ إضافة تقييمات للطلبات المكتملة:</h2>";
    
    $stmt = $conn->query("SELECT id, subject FROM requests WHERE status = 'completed'");
    $completedRequests = $stmt->fetchAll();
    
    $ratingsAdded = 0;
    foreach ($completedRequests as $request) {
        // تحقق من عدم وجود تقييم مسبق
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ratings WHERE request_id = ?");
        $stmt->execute([$request['id']]);
        $hasRating = $stmt->fetchColumn();
        
        if ($hasRating == 0) {
            $rating = rand(3, 5); // تقييم بين 3 و 5
            $comments = [
                'خدمة ممتازة وسريعة',
                'راضي جداً عن الأداء',
                'تم إنجاز المهمة بكفاءة',
                'عمل احترافي ومتقن',
                'سرعة في الاستجابة والتنفيذ',
                'جودة عالية في الخدمة',
                'تعامل مهذب ومحترف'
            ];
            $comment = $comments[array_rand($comments)];
            
            $stmt = $conn->prepare("INSERT INTO ratings (request_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW() - INTERVAL ? HOUR)");
            $stmt->execute([$request['id'], $user_id, $rating, $comment, rand(1, 24)]);
            
            echo "<div class='status success'>⭐ تم إضافة تقييم {$rating}/5 للطلب: {$request['subject']}</div>";
            $ratingsAdded++;
        }
    }
    
    echo "<div class='status info'>📈 تم إضافة $ratingsAdded تقييم جديد</div>";
    
    echo "<h2>🔗 روابط سريعة:</h2>";
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<a href='track.html' class='btn'>📋 عرض الطلبات</a>";
    echo "<a href='admin.html' class='btn'>🔐 لوحة الإدارة</a>";
    echo "<a href='request.html' class='btn'>➕ إضافة طلب جديد</a>";
    echo "<a href='system_test.html' class='btn'>🧪 اختبار النظام</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='status error'>❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>