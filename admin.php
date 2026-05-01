<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// Kiểm tra quyền Admin (Đã bật lại bảo mật)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Delete Product
if (isset($_GET['action']) && $_GET['action'] == 'delete_product' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

// Handle Delete Order
if (isset($_GET['action']) && $_GET['action'] == 'delete_order' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

// Handle Toggle Order Status
if (isset($_GET['action']) && $_GET['action'] == 'toggle_order' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = $_GET['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    header("Location: admin.php");
    exit();
}

// Handle Add / Edit Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_product') {
    $id = $_POST['productId'] ?? '';
    $name = $_POST['productName'];
    $category_id = $_POST['productCategory'];
    $price = $_POST['productPrice'];
    $stock = $_POST['productStock'];
    $image = $_POST['productImage'] ?? '';

    // Xử lý upload ảnh
    if (isset($_FILES['productImageFile']) && $_FILES['productImageFile']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['productImageFile']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['productImageFile']['tmp_name'], $target_file)) {
            $image = $target_file;
        }
    }

    if (empty($image)) {
        $image = 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b';
    }

    if (empty($id)) {
        // Insert
        $id = 'p' . time();
        $stmt = $conn->prepare("INSERT INTO products (id, category_id, name, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $category_id, $name, $price, $stock, $image]);
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, stock_quantity = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$category_id, $name, $price, $stock, $image, $id]);
    }
    header("Location: admin.php");
    exit();
}

// Lấy danh sách sản phẩm (Dùng LEFT JOIN để không mất dữ liệu nếu thiếu category)
$stmtProducts = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$stmtProducts->execute();
$products = $stmtProducts->fetchAll();

// Lấy danh sách đơn hàng (Dùng LEFT JOIN để luôn hiện đơn hàng kể cả khi khách bị xóa)
$stmtOrders = $conn->prepare("SELECT o.*, u.full_name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$stmtOrders->execute();
$orders = $stmtOrders->fetchAll();

// Fetch Categories
$stmtCat = $conn->prepare("SELECT * FROM categories");
$stmtCat->execute();
$categories = $stmtCat->fetchAll();

// Fetch Customers
$stmtCust = $conn->prepare("SELECT * FROM users WHERE role='customer' ORDER BY created_at DESC");
$stmtCust->execute();
$customers = $stmtCust->fetchAll();

// Dashboard Stats
$totalRev = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status='delivered'")->fetchColumn();
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaStyle Admin - Quản Trị Hệ Thống</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function switchTab(tabId, element) {
            console.log("Switching to:", tabId);
            const tabContents = document.querySelectorAll('.admin-tab-content');
            const tabLinks = document.querySelectorAll('.admin-tab-link');
            
            tabContents.forEach(c => c.style.display = 'none');
            tabLinks.forEach(l => l.classList.remove('active'));
            
            const targetTab = document.getElementById(tabId);
            if(targetTab) {
                targetTab.style.display = 'block';
                if(element) element.classList.add('active');
            }
        }
    </script>
    <style>
        body {
            background-color: var(--bg-dark);
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
            padding-top: 80px;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: calc(100vh - 80px);
            z-index: 10;
        }

        .admin-menu {
            list-style: none;
            padding: 0 15px;
        }

        .admin-menu li {
            margin-bottom: 10px;
        }

        .admin-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            transition: var(--transition-smooth);
            font-weight: 500;
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-main);
            box-shadow: inset 3px 0 0 var(--accent-blue);
        }

        .admin-menu a i {
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
            color: var(--accent-purple);
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--glass-border);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 15px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            transition: var(--transition-smooth);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 65, 108, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .stat-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            color: rgba(255, 255, 255, 0.03);
            transform: rotate(-15deg);
        }

        .stat-value {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 800;
            color: #333333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666666;
            font-weight: 500;
        }
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Chart Area Placeholder */
        .chart-section {
            background: #ffffff;
            border: 1px solid var(--glass-border);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            border: 2px dashed rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-muted);
            font-style: italic;
        }

        .table-section {
            background: #ffffff;
            border: 1px solid var(--glass-border);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 15px;
            padding: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h2 {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            font-weight: 600;
            color: #333333;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th, .admin-table td {
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
            color: #333333; /* Đổi sang màu đen để nhìn thấy được trên nền trắng */
        }

        .admin-table th {
            color: #666666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .admin-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-success {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        .status-pending {
            background: rgba(255, 170, 0, 0.1);
            color: #ffaa00;
            border: 1px solid rgba(255, 170, 0, 0.3);
        }
        
        /* Modal Styles */
        .product-modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition-smooth);
        }
        .product-modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        .product-modal {
            background: #ffffff;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transform: translateY(-20px);
            transition: var(--transition-smooth);
        }
        .product-modal-overlay.active .product-modal {
            transform: translateY(0);
        }
        .product-modal h3 {
            font-family: var(--font-heading);
            margin-bottom: 20px;
            color: var(--text-main);
        }
        .form-group-modal {
            margin-bottom: 15px;
        }
        .form-group-modal label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-main);
        }
        .form-group-modal input, .form-group-modal select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-family: var(--font-body);
            background: rgba(0,0,0,0.02);
            color: var(--text-main);
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }
    </style>
</head>

<body>

    <!-- Background Orbs -->
    <div class="orb orb-1" style="background: #ff416c; width: 500px; height: 500px; top: -200px;"></div>
    <div class="orb orb-2" style="background: #ff4b2b; bottom: 0; right: 0;"></div>

    <!-- Top Navigation -->
    <nav class="glass-header" style="border-bottom: 1px solid rgba(255, 65, 108, 0.3);">
        <div class="logo">
            <i class="fa-solid fa-microchip" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i> 
            NovaStyle <span style="font-size: 1rem; color: #ff4b2b; font-weight: 400; margin-left: 5px;">Admin</span>
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-icon" title="Về trang chủ"><i class="fa-solid fa-house"></i></a>
            <a href="#" class="nav-icon"><i class="fa-solid fa-bell"></i><span class="badge" style="background: #ff416c;">5</span></a>
            <a href="#" class="nav-icon" style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 600;">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Admin" style="width: 35px; height: 35px; border-radius: 50%; border: 2px solid #ff4b2b;">
                Admin_Root
            </a>
        </div>
    </nav>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <ul class="admin-menu">
                <li><a href="#" class="admin-tab-link active" onclick="switchTab('dashboard-tab', this)"><i class="fa-solid fa-chart-line"></i> Bảng Điều Khiển</a></li>
                <li><a href="#" class="admin-tab-link" onclick="switchTab('products-tab', this)"><i class="fa-solid fa-box"></i> Quản Lý Sản Phẩm</a></li>
                <li><a href="#" class="admin-tab-link" onclick="switchTab('orders-tab', this)"><i class="fa-solid fa-file-invoice-dollar"></i> Quản Lý Đơn Hàng</a></li>
                <li><a href="#" class="admin-tab-link" onclick="switchTab('customers-tab', this)"><i class="fa-solid fa-users"></i> Quản Lý Khách Hàng</a></li>
                <li><a href="#" class="admin-tab-link" onclick="switchTab('settings-tab', this)"><i class="fa-solid fa-robot"></i> Cấu Hình AI</a></li>
                <li style="margin-top: auto;"><a href="logout.php" style="color: #ff4d4d;"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <!-- Tiêu đề đã được lược bỏ theo yêu cầu -->
                </div>
                <div>
                    <button class="btn btn-primary btn-glow" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                        <i class="fa-solid fa-download"></i> Xuất Báo Cáo
                    </button>
                </div>
            </div>

            <!-- TAB: Dashboard -->
            <div class="admin-tab-content" id="dashboard-tab" style="display: block;">
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fa-solid fa-sack-dollar stat-icon"></i>
                        <div class="stat-value">$<?= number_format($totalRev ?: 0, 2) ?></div>
                        <div class="stat-label">Doanh thu tháng này</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa-solid fa-users stat-icon"></i>
                        <div class="stat-value"><?= number_format($totalUsers ?: 0) ?></div>
                        <div class="stat-label">Người dùng đăng ký</div>
                    </div>
                </div>
            </div>

            <!-- TAB: Quản Lý Sản Phẩm -->
            <div class="admin-tab-content" id="products-tab" style="display: none;">
                <div class="table-section">
                    <div class="table-header">
                        <h2 style="color: #333;">QUẢN LÝ KHO HÀNG (<?= count($products) ?>)</h2>
                        <button class="btn btn-primary" onclick="showAddProductModal()">+ Thêm Sản Phẩm Mới</button>
                    </div>
                    <table class="admin-table" id="productTable">
                        <thead>
                            <tr><th>Mã SP</th><th>Tên Sản Phẩm</th><th>Giá</th><th>Tồn Kho</th><th>Hành Động</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td>#P-<?= $p['id'] ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></td>
                                <td style="color: #ff416c; font-weight: bold;">$<?= number_format($p['price'], 2) ?></td>
                                <td><span style="background: #f0f0f0; padding: 4px 10px; border-radius: 10px;"><?= $p['stock_quantity'] ?></span></td>
                                <td>
                                    <button class="btn-action" onclick="editProduct(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>', <?= $p['category_id'] ?>, <?= $p['price'] ?>, <?= $p['stock_quantity'] ?>, '<?= $p['image_url'] ?>')"><i class="fa-solid fa-edit"></i></button>
                                    <a href="admin.php?action=delete_product&id=<?= $p['id'] ?>" class="btn-action" style="color:red;" onclick="return confirm('Xóa sản phẩm này?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: Quản Lý Đơn Hàng -->
            <div class="admin-tab-content" id="orders-tab" style="display: none;">
                <div class="table-section">
                    <div class="table-header">
                        <h2 style="color: #333;">QUẢN LÝ ĐƠN HÀNG & DUYỆT ĐƠN</h2>
                    </div>
                    <table class="admin-table" id="orderTable">
                        <thead>
                            <tr><th>Mã Đơn</th><th>Khách Hàng</th><th>Tổng Tiền</th><th>Trạng Thái</th><th>Hành Động</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td style="font-weight:700; color:#3498db;">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($o['customer_name'] ?? 'Ẩn danh') ?></td>
                                <td style="font-weight:700;">$<?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <form action="admin.php" method="GET">
                                        <input type="hidden" name="action" value="toggle_order">
                                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd;">
                                            <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Chờ duyệt</option>
                                            <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>Đang xử lý</option>
                                            <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?>>Đang giao</option>
                                            <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>Đã giao</option>
                                            <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn-action" title="Xem Chi Tiết Đơn Hàng"><i class="fa-solid fa-eye"></i> Xem Chi Tiết</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: Quản Lý Khách Hàng -->
            <div class="admin-tab-content" id="customers-tab" style="display: none;">
                <div class="table-section">
                    <div class="table-header">
                        <h2 style="color: #333;">THÔNG TIN KHÁCH HÀNG & GIAO DỊCH</h2>
                    </div>
                    <table class="admin-table" id="customerTable">
                        <thead>
                            <tr><th>Khách Hàng</th><th>Email</th><th>Lịch Sử</th><th>Hành Động</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $c): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($c['full_name']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td>
                                    <span style="font-size: 0.85rem; color: #666;">Mua: <strong>5 đơn</strong> | Chi: <strong>$1,200</strong></span>
                                </td>
                                <td>
                                    <button class="btn-action" onclick="showCustomerDetails(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name']) ?>', '<?= htmlspecialchars($c['email']) ?>', '<?= $c['phone'] ?>', '<?= htmlspecialchars($c['address']) ?>')"><i class="fa-solid fa-address-card"></i> Hồ Sơ</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: Cấu Hình Trợ Lý AI -->
            <div class="admin-tab-content" id="settings-tab" style="display: none;">
                <div class="table-section">
                    <div class="table-header">
                        <h2>Cấu Hình AI</h2>
                    </div>
                    <div style="color: var(--text-muted); padding: 20px;">Tính năng cấu hình mô hình học máy đang được phát triển...</div>
                </div>
            </div>

        </main>
    </div>

    <!-- Product Modal -->
    <div class="product-modal-overlay" id="productModalOverlay">
        <div class="product-modal">
            <h3 id="modalTitle">Thêm Sản Phẩm Mới</h3>
            <form id="productForm" method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_product">
                <input type="hidden" name="productId" id="productId">
                
                <div class="form-group-modal">
                    <label>Tên Sản Phẩm</label>
                    <input type="text" name="productName" id="productName" required>
                </div>
                <div class="form-group-modal">
                    <label>Danh Mục</label>
                    <select name="productCategory" id="productCategory" required>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-modal" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Tồn Kho</label>
                        <input type="number" name="productStock" id="productStock" required>
                    </div>
                    <div style="flex:1;">
                        <label>Giá Bán ($)</label>
                        <input type="number" step="0.01" name="productPrice" id="productPrice" required>
                    </div>
                </div>
                <div class="form-group-modal">
                    <label>Tải Lên Hình Ảnh (Ưu tiên)</label>
                    <input type="file" name="productImageFile" id="productImageFile" accept="image/*" style="background: white;">
                </div>
                <div class="form-group-modal">
                    <label>Hoặc nhập URL Hình Ảnh</label>
                    <input type="text" name="productImage" id="productImage" placeholder="https://...">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" style="padding: 8px 15px;" onclick="closeProductModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 15px; background: var(--primary-gradient);">Lưu Sản Phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="product-modal-overlay" id="customerModalOverlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center;">
        <div class="product-modal" style="background: white; padding: 40px; border-radius: 20px; width: 90%; max-width: 500px; position: relative;">
            <h3 id="customerModalTitle">Chi Tiết Khách Hàng</h3>
            <div id="customerModalBody" style="margin-top: 20px; line-height: 1.8; color: var(--text-main);">
                <!-- Nội dung được nạp bởi JS -->
            </div>
            <div class="modal-actions" style="margin-top: 30px; display: flex; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeCustomerModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        // Modal Logic
        const modalOverlay = document.getElementById('productModalOverlay');
        const modalTitle = document.getElementById('modalTitle');
        const productForm = document.getElementById('productForm');

        function openProductModal(edit = false, id = '', name = '', category = '', price = '', stock = '', image = '') {
            modalTitle.innerText = edit ? "Chỉnh Sửa Sản Phẩm" : "Thêm Sản Phẩm Mới";
            
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('productCategory').value = category;
            document.getElementById('productPrice').value = price;
            document.getElementById('productStock').value = stock;
            document.getElementById('productImage').value = image;

            modalOverlay.classList.add('active');
        }

        function closeProductModal() {
            modalOverlay.classList.remove('active');
        }

        // Hàm chuyển Tab trực tiếp - Luôn hoạt động
        function switchTab(tabId, element) {
            console.log("Switching to tab:", tabId);
            const tabContents = document.querySelectorAll('.admin-tab-content');
            const tabLinks = document.querySelectorAll('.admin-tab-link');
            
            tabContents.forEach(c => c.style.display = 'none');
            tabLinks.forEach(l => l.classList.remove('active'));
            
            const targetTab = document.getElementById(tabId);
            if(targetTab) {
                targetTab.style.display = 'block';
                if(element) element.classList.add('active');
            }
        }

        // Khởi tạo tìm kiếm
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Chart.js with Error Handling
            try {
                const chartEl = document.getElementById('revenueChart');
                if (chartEl && typeof Chart !== 'undefined') {
                    const ctx = chartEl.getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5', 'Tuần 6'],
                            datasets: [{
                                label: 'Doanh thu ($)',
                                data: [1200, 1900, 1500, 2200, 2800, <?= $totalRev ? $totalRev : 3500 ?>],
                                borderColor: '#ff416c',
                                backgroundColor: 'rgba(255, 65, 108, 0.2)',
                                borderWidth: 3,
                                pointBackgroundColor: '#ff4b2b',
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            } catch(e) {
                console.error("Chart.js Error:", e);
            }
        });

        function setupSearch(inputId, tableId, searchColumns) {
            const searchInput = document.getElementById(inputId);
            if (!searchInput) return;
            searchInput.oninput = function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll(`#${tableId} tbody tr`);
                rows.forEach(row => {
                    if (row.cells.length < 2) return;
                    let match = false;
                    searchColumns.forEach(index => {
                        if (row.cells[index] && row.cells[index].textContent.toLowerCase().includes(query)) match = true;
                    });
                    row.style.display = match ? '' : 'none';
                });
            };
        }

        // Customer Modal Logic
        function showCustomerDetails(id, name, email, phone, address) {
            const modal = document.getElementById('customerModalOverlay');
            const body = document.getElementById('customerModalBody');
            document.getElementById('customerModalTitle').innerText = 'Thông Tin: ' + name;
            
            body.innerHTML = `
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 10px; color: #333;">
                    <strong>ID:</strong> <span>#USER-${id.toString().padStart(4, '0')}</span>
                    <strong>Email:</strong> <span>${email}</span>
                    <strong>SĐT:</strong> <span>${phone || '<i>Chưa cập nhật</i>'}</span>
                    <strong>Địa chỉ:</strong> <span>${address || '<i>Chưa cập nhật</i>'}</span>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid rgba(0,0,0,0.1);">
                <p style="font-weight: 600; color: #ff416c; margin-bottom: 10px;"><i class="fa-solid fa-chart-pie"></i> Tóm tắt hoạt động:</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="background: rgba(0,0,0,0.03); padding: 15px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 0.8rem; color: #666;">Tổng đơn hàng</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: #333;">5 đơn</div>
                    </div>
                    <div style="background: rgba(0,0,0,0.03); padding: 15px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 0.8rem; color: #666;">Tổng chi tiêu</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: #00ff88;">$450.00</div>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function closeCustomerModal() {
            document.getElementById('customerModalOverlay').style.display = 'none';
        }

        function editProduct(id, name, category, price, stock, image) {
            const modalOverlay = document.getElementById('productModalOverlay');
            document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Sản Phẩm';
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('productCategory').value = category;
            document.getElementById('productPrice').value = price;
            document.getElementById('productStock').value = stock;
            document.getElementById('productImage').value = image;

            modalOverlay.classList.add('active');
        }

        function showAddProductModal() {
            const modalOverlay = document.getElementById('productModalOverlay');
            document.getElementById('modalTitle').innerText = 'Thêm Sản Phẩm Mới';
            document.getElementById('productId').value = '';
            document.getElementById('productForm').reset();
            modalOverlay.classList.add('active');
        }

        function closeProductModal() {
            document.getElementById('productModalOverlay').classList.remove('active');
        }
    </script>
</body>
</html>
