<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 إدارة الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #eaf2fb 0%, #b6e2d3 60%, #f5f5dc 100%);
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            color: #333;
            padding: 20px;
            position: relative;
        }
        
        body:before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
            background: url('https://www.transparenttextures.com/patterns/hexellence.png'),
                        radial-gradient(circle at 80% 10%, #b6e2d3aa 0%, transparent 60%),
                        radial-gradient(circle at 10% 80%, #f5f5dcaa 0%, transparent 60%);
            opacity: 0.25;
        }
        
        body:after {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
            background: url('https://www.transparenttextures.com/patterns/hexellence.png'),
                        radial-gradient(circle at 80% 10%, #b6e2d3aa 0%, transparent 60%),
                        radial-gradient(circle at 10% 80%, #f5f5dcaa 0%, transparent 60%);
            opacity: 0.4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .header {
            background: linear-gradient(90deg, #b6e2d3 60%, #f5f5dc 100%);
            color: #2d5c8a;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .header .subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .nav-section {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            background: linear-gradient(90deg, #b6e2d3, #f5f5dc);
            color: #3b5e4d;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 2px 12px rgba(182, 226, 211, 0.3);
        }

        .nav-btn:hover {
            background: linear-gradient(90deg, #f5f5dc, #b6e2d3);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(182, 226, 211, 0.4);
            color: #3b5e4d;
            text-decoration: none;
        }

        .nav-btn.home {
            background: linear-gradient(90deg, #b6e2d3, #f5f5dc);
            color: #3b5e4d;
        }

        .nav-btn.home:hover {
            background: linear-gradient(90deg, #f5f5dc, #b6e2d3);
            color: #3b5e4d;
        }

        .filter-section {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        select, input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }

        .status-pending { background: #fff3cd; color: #d68910; border: 1px solid #f39c12; }
        .status-in_progress { background: #fffbf0; color: #b8860b; border: 1px solid #ffd700; }
        .status-completed { background: #d4edda; color: #155724; border: 1px solid #28a745; }
        .status-rejected { background: #fadbd8; color: #c0392b; border: 1px solid #e74c3c; }

        .content-section {
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d5c8a;
            margin-bottom: 10px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(182, 226, 211, 0.2);
        }

        .orders-table thead {
            background: linear-gradient(90deg, #b6e2d3 60%, #f5f5dc 100%);
        }

        .orders-table th,
        .orders-table td {
            padding: 15px 10px;
            text-align: center;
            border-bottom: 1px solid rgba(182, 226, 211, 0.2);
        }

        .orders-table th {
            color: #3b5e4d;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(255,255,255,0.3);
            font-size: 0.9rem;
            border: none;
        }

        .orders-table td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .orders-table tr:hover {
            background: rgba(182, 226, 211, 0.1);
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #007bff;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #b6e2d3;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: #3b5e4d;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .nav-buttons {
                justify-content: center;
            }
            
            .filter-controls {
                justify-content: center;
            }
            
            .orders-table {
                font-size: 0.8rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> إدارة الطلبات</h1>
            <div class="subtitle">عرض وإدارة جميع الطلبات في النظام</div>
        </div>

        <!-- Navigation -->
        <div class="nav-section">
            <div class="nav-buttons">
                <a href="admin.html" class="nav-btn home">
                    <i class="fas fa-home"></i>
                    العودة للوحة الإدارة
                </a>
                <a href="track.html" class="nav-btn">
                    <i class="fas fa-search"></i>
                    تتبع الطلبات
                </a>
                <a href="request.html" class="nav-btn">
                    <i class="fas fa-plus"></i>
                    طلب جديد
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="statusFilter">فلترة حسب الحالة:</label>
                    <select id="statusFilter" onchange="filterOrders()">
                        <option value="">جميع الحالات</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="in_progress">قيد التنفيذ</option>
                        <option value="completed">تم التنفيذ</option>
                        <option value="rejected">لم يتم التنفيذ</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="typeFilter">فلترة حسب النوع:</label>
                    <select id="typeFilter" onchange="filterOrders()">
                        <option value="">جميع الأنواع</option>
                        <option value="electricity">كهرباء</option>
                        <option value="plumbing">سباكة</option>
                        <option value="ac">تكييف</option>
                        <option value="it">تقنية معلومات</option>
                        <option value="maintenance">صيانة</option>
                        <option value="cleaning">تنظيف</option>
                    </select>
                </div>
                <button class="nav-btn" onclick="clearFilters()">
                    <i class="fas fa-times"></i>
                    مسح الفلاتر
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="content-section">
            <!-- Statistics Summary -->
            <div class="stats-summary" id="statsSummary">
                <!-- سيتم ملؤها عبر JavaScript -->
            </div>

            <div class="section-header">
                <h2 class="section-title" id="sectionTitle">جميع الطلبات</h2>
            </div>

            <!-- Orders Table -->
            <div class="table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الموضوع</th>
                            <th>اسم العميل</th>
                            <th>نوع الطلب</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>المدة</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <tr>
                            <td colspan="8" class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                جاري تحميل الطلبات...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/api.js"></script>
    <script>
        // متغيرات عامة
        let allOrders = [];
        let currentFilter = {};

        // دوال مساعدة
        function getRequestTypeText(type) {
            const types = {
                'electricity': 'كهرباء ⚡',
                'plumbing': 'سباكة 🚰', 
                'ac': 'تكييف ❄️',
                'it': 'تقنية معلومات 💻',
                'maintenance': 'صيانة عامة 🔧',
                'cleaning': 'تنظيف 🧽',
                'security': 'أمن وسلامة 🛡️',
                'other': 'أخرى ❓'
            };
            
            return types[type] || type || '❓ غير محدد';
        }

        function getPriorityText(priority) {
            const priorities = {
                'low': 'منخفض',
                'medium': 'متوسط',
                'high': 'عالي',
                'urgent': 'عاجل'
            };
            
            return priorities[priority] || priority || 'متوسط';
        }

        function getStatusText(status) {
            const statuses = {
                'pending': 'قيد الانتظار',
                'in_progress': 'قيد التنفيذ',
                'completed': 'تم التنفيذ',
                'rejected': 'لم يتم التنفيذ'
            };
            
            return statuses[status] || status || 'غير محدد';
        }

        // حساب المدة
        function calculateDuration(createdDate, status, updatedDate = null) {
            try {
                const createdTime = new Date(createdDate);
                let endTime;
                
                if (status === 'completed' && updatedDate) {
                    endTime = new Date(updatedDate);
                } else {
                    endTime = new Date();
                }
                
                const diffTime = Math.abs(endTime - createdTime);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                let durationText = '';
                if (diffDays === 0) {
                    durationText = 'اليوم';
                } else if (diffDays === 1) {
                    durationText = 'يوم واحد';
                } else if (diffDays === 2) {
                    durationText = 'يومان';
                } else if (diffDays >= 3 && diffDays <= 10) {
                    durationText = diffDays + ' أيام';
                } else {
                    durationText = diffDays + ' يوم';
                }
                
                // تحديد اللون
                let color = '#666';
                if (status === 'completed') {
                    if (diffDays <= 3) color = '#27ae60';
                    else if (diffDays <= 7) color = '#f39c12';
                    else color = '#e74c3c';
                } else {
                    if (diffDays <= 7) color = '#3498db';
                    else if (diffDays <= 14) color = '#f39c12';
                    else color = '#e74c3c';
                }
                
                return `<span style="color: ${color}; font-weight: bold;"><i class="fa fa-clock" style="margin-left: 5px;"></i> ${durationText}</span>`;
            } catch (error) {
                return '<span style="color: #999;">غير محدد</span>';
            }
        }

        // تحميل الطلبات
        async function loadOrders() {
            try {
                const response = await fetch('api/requests.php?action=get_all');
                const result = await response.json();
                
                if (result.success && result.requests) {
                    allOrders = result.requests;
                    
                    // تطبيق الفلتر من URL
                    applyURLFilter();
                    
                    // عرض الطلبات
                    displayOrders(allOrders);
                    
                    // تحديث الإحصائيات
                    updateStatistics(allOrders);
                } else {
                    showError('فشل في تحميل الطلبات: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                showError('خطأ في الاتصال بالخادم: ' + error.message);
            }
        }

        // تطبيق فلتر URL
        function applyURLFilter() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const type = urlParams.get('type');
            
            if (status) {
                document.getElementById('statusFilter').value = status;
                currentFilter.status = status;
                updatePageTitle(status);
            }
            
            if (type) {
                document.getElementById('typeFilter').value = type;
                currentFilter.type = type;
            }
        }

        // تحديث عنوان الصفحة
        function updatePageTitle(status) {
            const statusNames = {
                'pending': 'الطلبات قيد الانتظار',
                'in_progress': 'الطلبات قيد التنفيذ',
                'completed': 'الطلبات المكتملة',
                'rejected': 'الطلبات المرفوضة'
            };
            
            const title = statusNames[status] || 'جميع الطلبات';
            document.getElementById('sectionTitle').textContent = title;
            document.title = title + ' - إدارة الطلبات';
        }

        // عرض الطلبات
        function displayOrders(orders) {
            const tbody = document.getElementById('ordersTableBody');
            
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">لا توجد طلبات تطابق الفلتر المحدد</td></tr>';
                return;
            }
            
            tbody.innerHTML = orders.map(order => {
                const customerName = order.display_name || order.customer_name || order.user_name || order.full_name || order.username || 'غير محدد';
                const duration = order.duration_info ? order.duration_info.html : calculateDuration(order.created_at, order.status, order.updated_at);
                
                return `<tr>
                    <td><strong>#${order.id}</strong></td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${order.subject || 'طلب خدمة'}</td>
                    <td style="color: #2c5aa0; font-weight: bold;">${customerName}</td>
                    <td>
                        <span class="status-badge" style="background: #e6f4ea; color: #3b5e4d;">
                            ${getRequestTypeText(order.request_type)}
                        </span>
                    </td>
                    <td>
                        <span style="color: ${order.priority === 'high' ? '#dc3545' : order.priority === 'medium' ? '#ffc107' : '#28a745'};">
                            ${getPriorityText(order.priority)}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-${order.status}">
                            ${getStatusText(order.status)}
                        </span>
                    </td>
                    <td style="font-size: 0.85rem;">
                        ${new Date(order.created_at).toLocaleDateString('ar-SA', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        })}
                    </td>
                    <td>${duration}</td>
                </tr>`;
            }).join('');
        }

        // تحديث الإحصائيات
        function updateStatistics(orders) {
            const stats = {
                total: orders.length,
                pending: orders.filter(o => o.status === 'pending').length,
                in_progress: orders.filter(o => o.status === 'in_progress').length,
                completed: orders.filter(o => o.status === 'completed').length,
                rejected: orders.filter(o => o.status === 'rejected').length
            };
            
            const statsSummary = document.getElementById('statsSummary');
            statsSummary.innerHTML = `
                <div class="stat-item">
                    <div class="stat-number">${stats.total}</div>
                    <div class="stat-label">إجمالي الطلبات</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #ffc107;">${stats.pending}</div>
                    <div class="stat-label">قيد الانتظار</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #17a2b8;">${stats.in_progress}</div>
                    <div class="stat-label">قيد التنفيذ</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #28a745;">${stats.completed}</div>
                    <div class="stat-label">مكتملة</div>
                </div>
            `;
        }

        // فلترة الطلبات
        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            
            let filteredOrders = allOrders;
            
            if (statusFilter) {
                filteredOrders = filteredOrders.filter(order => order.status === statusFilter);
                updatePageTitle(statusFilter);
            } else {
                document.getElementById('sectionTitle').textContent = 'جميع الطلبات';
            }
            
            if (typeFilter) {
                filteredOrders = filteredOrders.filter(order => order.request_type === typeFilter);
            }
            
            displayOrders(filteredOrders);
            updateStatistics(filteredOrders);
        }

        // مسح الفلاتر
        function clearFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value = '';
            currentFilter = {};
            
            displayOrders(allOrders);
            updateStatistics(allOrders);
            
            document.getElementById('sectionTitle').textContent = 'جميع الطلبات';
            
            // تحديث URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // عرض خطأ
        function showError(message) {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = `<tr><td colspan="8" class="error">${message}</td></tr>`;
        }

        // تحميل البيانات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });
    </script>
</body>
</html>