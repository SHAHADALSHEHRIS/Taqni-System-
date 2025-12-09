<?php
/**
 * اختبار شامل ومحسن لاتصال قاعدة البيانات والنظام
 * Enhanced Database Connection and System Test
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء تسجيل الأخطاء
$logFile = __DIR__ . '/logs/test_' . date('Y-m-d_H-i-s') . '.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function displayResult($type, $title, $message, $details = null) {
    $icons = [
        'success' => '✅',
        'error' => '❌', 
        'warning' => '⚠️',
        'info' => 'ℹ️'
    ];
    
    $colors = [
        'success' => '#d4edda',
        'error' => '#f8d7da',
        'warning' => '#fff3cd',
        'info' => '#d1ecf1'
    ];
    
    echo "<div style='background: {$colors[$type]}; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid " . ($type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : ($type === 'warning' ? '#ffc107' : '#17a2b8'))) . ";'>";
    echo "<h3 style='margin: 0 0 10px 0; color: #333;'>{$icons[$type]} $title</h3>";
    echo "<p style='margin: 0; color: #555;'>$message</p>";
    
    if ($details && is_array($details)) {
        echo "<ul style='margin: 10px 0 0 20px; color: #666;'>";
        foreach ($details as $detail) {
            echo "<li>$detail</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    logMessage("[$type] $title: $message");
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 اختبار النظام الشامل المحسن</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(120deg, #eaf2fb 0%, #b6e2d3 60%, #f5f5dc 100%);
            direction: rtl;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            background: linear-gradient(90deg, #b6e2d3 60%, #f5f5dc 100%);
            color: #2d5c8a;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2d5c8a;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .nav-buttons {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(90deg, #b6e2d3, #f5f5dc);
            color: #3b5e4d;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: linear-gradient(90deg, #f5f5dc, #b6e2d3);
            transform: translateY(-2px);
            color: #3b5e4d;
            text-decoration: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> اختبار النظام الشامل المحسن</h1>
            <p>تاريخ ووقت الاختبار: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <?php
        $startTime = microtime(true);
        $totalTests = 0;
        $passedTests = 0;
        $allResults = [];

        try {
            // التحقق من وجود ملف إعدادات قاعدة البيانات
            $configFiles = [
                __DIR__ . '/config/database.php',
                __DIR__ . '/database.php',
                __DIR__ . '/config.php'
            ];
            
            $configFound = false;
            $configFile = null;
            
            foreach ($configFiles as $file) {
                if (file_exists($file)) {
                    $configFound = true;
                    $configFile = $file;
                    break;
                }
            }
            
            if ($configFound) {
                require_once $configFile;
                displayResult('success', 'ملف الإعدادات', "تم العثور على ملف الإعدادات: " . basename($configFile));
                $passedTests++;
            } else {
                displayResult('error', 'ملف الإعدادات', 'لم يتم العثور على ملف إعدادات قاعدة البيانات');
            }
            $totalTests++;

            // اختبار الاتصال بقاعدة البيانات
            echo "<div class='test-section'>";
            echo "<h2><i class='fas fa-plug'></i> اختبار الاتصال بقاعدة البيانات</h2>";
            
            if (class_exists('Database')) {
                $database = new Database();
                $conn = $database->connect();
                
                if ($conn) {
                    displayResult('success', 'اتصال قاعدة البيانات', 'تم الاتصال بقاعدة البيانات بنجاح');
                    $passedTests++;
                    
                    // معلومات قاعدة البيانات
                    $dbInfo = $conn->query("SELECT VERSION() as version")->fetch();
                    $dbName = $conn->query("SELECT DATABASE() as dbname")->fetch();
                    
                    echo "<table class='data-table'>";
                    echo "<tr><th>المعلومة</th><th>القيمة</th></tr>";
                    echo "<tr><td>إصدار MySQL</td><td>{$dbInfo['version']}</td></tr>";
                    echo "<tr><td>اسم قاعدة البيانات</td><td>{$dbName['dbname']}</td></tr>";
                    echo "<tr><td>حالة الاتصال</td><td><span style='color: green;'>متصل</span></td></tr>";
                    echo "</table>";
                    
                } else {
                    displayResult('error', 'اتصال قاعدة البيانات', 'فشل في الاتصال بقاعدة البيانات');
                }
            } else {
                displayResult('error', 'فئة قاعدة البيانات', 'فئة Database غير موجودة');
            }
            $totalTests++;
            echo "</div>";

            // فحص الجداول
            if (isset($conn) && $conn) {
                echo "<div class='test-section'>";
                echo "<h2><i class='fas fa-table'></i> فحص الجداول</h2>";
                
                $requiredTables = [
                    'users' => 'جدول المستخدمين',
                    'requests' => 'جدول الطلبات', 
                    'request_tracking' => 'جدول تتبع الطلبات',
                    'request_ratings' => 'جدول التقييمات',
                    'user_sessions' => 'جدول جلسات المستخدمين'
                ];
                
                $existingTables = [];
                $tableCounts = [];
                
                foreach ($requiredTables as $table => $description) {
                    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $existingTables[] = $table;
                        
                        // عد السجلات
                        try {
                            $countStmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                            $count = $countStmt->fetch()['count'];
                            $tableCounts[$table] = $count;
                            
                            displayResult('success', $description, "الجدول موجود ويحتوي على $count سجل");
                            $passedTests++;
                        } catch (Exception $e) {
                            displayResult('warning', $description, "الجدول موجود لكن لا يمكن عد السجلات: " . $e->getMessage());
                        }
                    } else {
                        displayResult('error', $description, "الجدول $table غير موجود");
                    }
                    $totalTests++;
                }
                
                // عرض إحصائيات الجداول
                if (!empty($tableCounts)) {
                    echo "<div class='stats-grid'>";
                    foreach ($tableCounts as $table => $count) {
                        echo "<div class='stat-card'>";
                        echo "<i class='fas fa-table' style='font-size: 2rem; color: #007bff;'></i>";
                        echo "<div class='stat-number'>$count</div>";
                        echo "<div class='stat-label'>" . $requiredTables[$table] . "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }

            // اختبار APIs
            echo "<div class='test-section'>";
            echo "<h2><i class='fas fa-code'></i> اختبار APIs</h2>";
            
            $apiEndpoints = [
                'api/requests.php' => 'API الطلبات',
                'api/ratings.php' => 'API التقييمات', 
                'api/stats.php' => 'API الإحصائيات',
                'api/auth.php' => 'API المصادقة'
            ];
            
            foreach ($apiEndpoints as $endpoint => $description) {
                $fullPath = __DIR__ . '/' . $endpoint;
                if (file_exists($fullPath)) {
                    displayResult('success', $description, "ملف API موجود: $endpoint");
                    $passedTests++;
                    
                    // اختبار استجابة API
                    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $endpoint;
                    
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'timeout' => 10,
                            'ignore_errors' => true
                        ]
                    ]);
                    
                    $response = @file_get_contents($apiUrl, false, $context);
                    if ($response !== false) {
                        $httpCode = isset($http_response_header) ? $http_response_header[0] : 'غير معروف';
                        displayResult('info', 'استجابة ' . $description, "الاستجابة: $httpCode");
                    } else {
                        displayResult('warning', 'استجابة ' . $description, 'لا يمكن الوصول للـ API عبر HTTP');
                    }
                } else {
                    displayResult('error', $description, "ملف API غير موجود: $endpoint");
                }
                $totalTests++;
            }
            echo "</div>";

            // اختبار الملفات الأساسية
            echo "<div class='test-section'>";
            echo "<h2><i class='fas fa-file-code'></i> فحص الملفات الأساسية</h2>";
            
            $requiredFiles = [
                'admin.html' => 'لوحة الإدارة',
                'request.html' => 'صفحة إضافة الطلبات',
                'track.html' => 'صفحة تتبع الطلبات',
                'js/api.js' => 'JavaScript API',
                'css/style.css' => 'ملف التنسيق',
                'orders.php' => 'صفحة إدارة الطلبات'
            ];
            
            foreach ($requiredFiles as $file => $description) {
                $fullPath = __DIR__ . '/' . $file;
                if (file_exists($fullPath)) {
                    $fileSize = formatBytes(filesize($fullPath));
                    displayResult('success', $description, "الملف موجود ($fileSize)");
                    $passedTests++;
                } else {
                    displayResult('error', $description, "الملف غير موجود: $file");
                }
                $totalTests++;
            }
            echo "</div>";

            // معلومات النظام
            echo "<div class='test-section'>";
            echo "<h2><i class='fas fa-server'></i> معلومات النظام</h2>";
            
            $systemInfo = [
                'إصدار PHP' => phpversion(),
                'نظام التشغيل' => php_uname('s') . ' ' . php_uname('r'),
                'خادم الويب' => $_SERVER['SERVER_SOFTWARE'] ?? 'غير محدد',
                'المنطقة الزمنية' => date_default_timezone_get(),
                'الذاكرة المتاحة' => ini_get('memory_limit'),
                'حد زمن التنفيذ' => ini_get('max_execution_time') . ' ثانية',
                'رفع الملفات' => ini_get('file_uploads') ? 'مفعل' : 'معطل',
                'حجم الرفع الأقصى' => ini_get('upload_max_filesize')
            ];
            
            echo "<table class='data-table'>";
            echo "<tr><th>المعلومة</th><th>القيمة</th></tr>";
            foreach ($systemInfo as $key => $value) {
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
            echo "</table>";
            echo "</div>";

        } catch (Exception $e) {
            displayResult('error', 'خطأ في النظام', $e->getMessage());
            logMessage("CRITICAL ERROR: " . $e->getMessage());
        }

        // النتيجة النهائية
        $successRate = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        
        echo "<div class='test-section'>";
        echo "<h2><i class='fas fa-chart-pie'></i> النتيجة النهائية</h2>";
        
        echo "<div class='stats-grid'>";
        echo "<div class='stat-card'>";
        echo "<i class='fas fa-check-circle' style='font-size: 2rem; color: #28a745;'></i>";
        echo "<div class='stat-number'>$passedTests</div>";
        echo "<div class='stat-label'>اختبارات ناجحة</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<i class='fas fa-times-circle' style='font-size: 2rem; color: #dc3545;'></i>";
        echo "<div class='stat-number'>" . ($totalTests - $passedTests) . "</div>";
        echo "<div class='stat-label'>اختبارات فاشلة</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<i class='fas fa-percentage' style='font-size: 2rem; color: #007bff;'></i>";
        echo "<div class='stat-number'>" . round($successRate, 1) . "%</div>";
        echo "<div class='stat-label'>معدل النجاح</div>";
        echo "</div>";
        
        echo "<div class='stat-card'>";
        echo "<i class='fas fa-clock' style='font-size: 2rem; color: #6f42c1;'></i>";
        echo "<div class='stat-number'>{$executionTime}s</div>";
        echo "<div class='stat-label'>وقت التنفيذ</div>";
        echo "</div>";
        echo "</div>";
        
        // شريط التقدم
        echo "<div class='progress-bar'>";
        echo "<div class='progress-fill' style='width: {$successRate}%;'></div>";
        echo "</div>";
        
        if ($successRate >= 80) {
            displayResult('success', 'تقييم النظام', 'النظام جاهز للاستخدام! جميع الاختبارات الأساسية نجحت.');
        } elseif ($successRate >= 60) {
            displayResult('warning', 'تقييم النظام', 'النظام يعمل بشكل جيد لكن يحتاج بعض التحسينات.');
        } else {
            displayResult('error', 'تقييم النظام', 'هناك مشاكل كبيرة تحتاج إلى إصلاح قبل استخدام النظام.');
        }
        echo "</div>";
        
        // دوال مساعدة
        function formatBytes($size, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB'];
            $base = log($size, 1024);
            return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
        }
        ?>
        
        <div class="nav-buttons">
            <h3>🔗 روابط سريعة</h3>
            <a href="admin.html" class="btn">
                <i class="fas fa-tachometer-alt"></i> لوحة الإدارة
            </a>
            <a href="request.html" class="btn">
                <i class="fas fa-plus"></i> إضافة طلب
            </a>
            <a href="track.html" class="btn">
                <i class="fas fa-search"></i> تتبع الطلبات
            </a>
            <a href="database_test_center.html" class="btn">
                <i class="fas fa-vial"></i> مركز الاختبار
            </a>
            <a href="<?php echo $logFile; ?>" class="btn" target="_blank">
                <i class="fas fa-file-alt"></i> عرض سجل الاختبار
            </a>
        </div>
    </div>
</body>
</html>