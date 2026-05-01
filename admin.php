<?php
require_once 'db.php';

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

// Fetch Products
$stmtProducts = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$stmtProducts->execute();
$products = $stmtProducts->fetchAll();

// Fetch Orders
$stmtOrders = $conn->prepare("SELECT o.*, u.full_name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
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
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
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
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th, .admin-table td {
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .admin-table th {
            color: var(--text-muted);
            font-weight: 500;
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
                <li><a href="#" class="admin-tab-link active" data-tab="dashboard-tab"><i class="fa-solid fa-chart-line"></i> Bảng Điều Khiển</a></li>
                <li><a href="#" class="admin-tab-link" data-tab="products-tab"><i class="fa-solid fa-box-open"></i> Quản Lý Sản Phẩm</a></li>
                <li><a href="#" class="admin-tab-link" data-tab="orders-tab"><i class="fa-solid fa-file-invoice-dollar"></i> Quản Lý Đơn Hàng</a></li>
                <li><a href="#" class="admin-tab-link" data-tab="customers-tab"><i class="fa-solid fa-users"></i> Quản Lý Khách Hàng</a></li>
                <li><a href="#" class="admin-tab-link" data-tab="settings-tab"><i class="fa-solid fa-robot"></i> Cấu Hình Trợ Lý AI</a></li>
                <li><a href="#"><i class="fa-solid fa-shield-halved"></i> Bảo Mật & Phân Quyền</a></li>
                <li style="margin-top: 50px;"><a href="login.php" style="color: #ff4d4d;"><i class="fa-solid fa-right-from-bracket" style="color: #ff4d4d;"></i> Đăng Xuất</a></li>
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

            <!-- TAB: Bảng Điều Khiển -->
            <div class="admin-tab-content" id="dashboard-tab" style="display: block;">
                <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-solid fa-sack-dollar stat-icon"></i>
                    <div class="stat-value">$<?= number_format($totalRev ?: 0, 2) ?></div>
                    <div class="stat-label">Doanh thu tháng này</div>
                    <div style="color: #00ff88; font-size: 0.85rem; margin-top: 10px;"><i class="fa-solid fa-arrow-trend-up"></i> +14.5% so với tháng trước</div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-users stat-icon"></i>
                    <div class="stat-value"><?= number_format($totalUsers ?: 0) ?></div>
                    <div class="stat-label">Người dùng đăng ký</div>
                    <div style="color: #00ff88; font-size: 0.85rem; margin-top: 10px;"><i class="fa-solid fa-arrow-trend-up"></i> +5.2% người dùng mới</div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-box stat-icon"></i>
                    <div class="stat-value"><?= number_format($totalOrders ?: 0) ?></div>
                    <div class="stat-label">Đơn hàng AI xử lý</div>
                    <div style="color: #ffaa00; font-size: 0.85rem; margin-top: 10px;"><i class="fa-solid fa-clock"></i> Đang xử lý các đơn hàng mới</div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-brain stat-icon"></i>
                    <div class="stat-value">94.8%</div>
                    <div class="stat-label">Độ chính xác AI Model</div>
                    <div style="color: #00ff88; font-size: 0.85rem; margin-top: 10px;"><i class="fa-solid fa-check-circle"></i> Đã tối ưu hóa thuật toán</div>
                </div>
            </div>

            <!-- Revenue Chart Area -->
            <div class="table-section" style="margin-bottom: 40px; padding: 30px;">
                <div class="table-header">
                    <h2>Biểu Đồ Doanh Thu</h2>
                </div>
                <div style="width: 100%; height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            </div>
            <!-- TAB: Quản Lý Sản Phẩm -->
            <div class="admin-tab-content" id="products-tab" style="display: none;">
                <!-- Product Management Section -->
                <div class="table-section" style="margin-bottom: 40px;">
                <div class="table-header">
                    <h2>Quản Lý Sản Phẩm (Kho Hàng)</h2>
                    <div style="display: flex; gap: 15px;">
                        <input type="text" id="adminProductSearch" placeholder="Tìm kiếm sản phẩm..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.02); color: var(--text-main); font-family: var(--font-body); min-width: 250px;">
                        <button class="btn btn-primary btn-glow" style="background: var(--primary-gradient); font-size: 0.85rem; padding: 10px 20px;" onclick="openProductModal()">
                            <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
                        </button>
                    </div>
                </div>
                <div style="max-height: 600px; overflow-y: auto;">
                <table class="admin-table" id="productTable">
                    <thead>
                        <tr>
                            <th>Hình Ảnh</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Tồn Kho</th>
                            <th>Giá Bán</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($p['image_url']) ?>" style="border-radius: 8px; width:50px; height:50px; object-fit:cover;"></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['cat_name']) ?></td>
                            <td><span style="color: #00ff88;"><?= $p['stock_quantity'] ?></span></td>
                            <td style="font-weight: 600;">$<?= number_format($p['price'], 2) ?></td>
                            <td>
                                <button style="background: transparent; border: none; color: var(--accent-blue); cursor: pointer; margin-right: 10px;" onclick="openProductModal(true, '<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>', '<?= $p['category_id'] ?>', <?= $p['price'] ?>, <?= $p['stock_quantity'] ?>, '<?= htmlspecialchars($p['image_url'], ENT_QUOTES) ?>')"><i class="fa-solid fa-pen"></i></button>
                                <a href="admin.php?action=delete_product&id=<?= urlencode($p['id']) ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" style="background: transparent; border: none; color: #ff4d4d; cursor: pointer; text-decoration:none;"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- TAB: Đơn Hàng & Doanh Thu -->
            <div class="admin-tab-content" id="orders-tab" style="display: none;">
                <!-- Recent Orders Table -->
                <div class="table-section">
                <div class="table-header">
                    <h2>Quản Lý Đơn Hàng</h2>
                    <input type="text" id="adminOrderSearch" placeholder="Tìm mã đơn hoặc tên KH..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.02); color: var(--text-main); font-family: var(--font-body); min-width: 250px;">
                </div>
                <div style="max-height: 600px; overflow-y: auto;">
                <table class="admin-table" id="orderTable">
                    <thead>
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Khách Hàng</th>
                            <th>Ngày Đặt</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td style="font-weight:600; color:var(--accent-blue);">#ORD-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--primary-gradient); color: white; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; font-weight: bold; text-transform:uppercase;">
                                        <?= substr($o['customer_name'], 0, 2) ?>
                                    </div>
                                    <?= htmlspecialchars($o['customer_name']) ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td style="font-weight: 600;">$<?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <?php 
                                    $bg = 'rgba(0,0,0,0.05)'; $col = 'var(--text-main)';
                                    if($o['status'] == 'delivered') { $bg = 'rgba(0, 255, 136, 0.1)'; $col = '#00ff88'; }
                                    if($o['status'] == 'pending') { $bg = 'rgba(255, 170, 0, 0.1)'; $col = '#ffaa00'; }
                                    if($o['status'] == 'processing') { $bg = 'rgba(138, 43, 226, 0.1)'; $col = '#8a2be2'; }
                                    if($o['status'] == 'shipped') { $bg = 'rgba(9, 132, 227, 0.1)'; $col = '#0984e3'; }
                                    if($o['status'] == 'cancelled') { $bg = 'rgba(255, 77, 77, 0.1)'; $col = '#ff4d4d'; }
                                ?>
                                <form action="admin.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_order">
                                    <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 5px 10px; border-radius: 20px; border: 1px solid <?= $col ?>; background: <?= $bg ?>; color: <?= $col ?>; font-weight: 600; font-size: 0.8rem; cursor: pointer; outline: none;">
                                        <option value="pending" <?= $o['status']=='pending'?'selected':'' ?> style="color:var(--text-main);">Chờ duyệt</option>
                                        <option value="processing" <?= $o['status']=='processing'?'selected':'' ?> style="color:var(--text-main);">Đang xử lý</option>
                                        <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?> style="color:var(--text-main);">Đang giao</option>
                                        <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?> style="color:var(--text-main);">Đã giao</option>
                                        <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?> style="color:var(--text-main);">Đã hủy</option>
                                    </select>
                                </form>
                            </td>
                             <td>
                                <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" style="background: transparent; border: none; color: var(--accent-blue); cursor: pointer; text-decoration: none; padding: 5px; margin-right: 5px;" title="Xem chi tiết hóa đơn"><i class="fa-solid fa-eye"></i></a>
                                <?php if($o['status'] == 'pending'): ?>
                                    <a href="admin.php?action=toggle_order&id=<?= $o['id'] ?>&status=processing" style="background: transparent; border: none; color: #00ff88; cursor: pointer; text-decoration: none; padding: 5px; margin-right: 5px;" title="Duyệt đơn ngay"><i class="fa-solid fa-check"></i></a>
                                <?php endif; ?>
                                <?php if($o['status'] != 'cancelled' && $o['status'] != 'delivered'): ?>
                                    <a href="admin.php?action=toggle_order&id=<?= $o['id'] ?>&status=cancelled" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')" style="background: transparent; border: none; color: #ffaa00; cursor: pointer; text-decoration: none; padding: 5px; margin-right: 5px;" title="Hủy đơn hàng"><i class="fa-solid fa-ban"></i></a>
                                <?php endif; ?>
                                <a href="admin.php?action=delete_order&id=<?= $o['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn đơn hàng này?')" style="background: transparent; border: none; color: #ff4d4d; cursor: pointer; text-decoration: none; padding: 5px;" title="Xóa vĩnh viễn"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            </div>

            <!-- TAB: Quản Lý Khách Hàng -->
            <div class="admin-tab-content" id="customers-tab" style="display: none;">
                <div class="table-section">
                    <div class="table-header">
                        <h2>Quản Lý Tài Khoản Khách Hàng</h2>
                        <input type="text" id="adminCustomerSearch" placeholder="Tìm tên hoặc email..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.02); color: var(--text-main); font-family: var(--font-body); min-width: 250px;">
                    </div>
                    <div style="max-height: 600px; overflow-y: auto;">
                    <table class="admin-table" id="customerTable">
                        <thead>
                            <tr>
                                <th>Tên Khách Hàng</th>
                                <th>Email</th>
                                <th>Ngày Đăng Ký</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $c): ?>
                            <tr>
                                <td style="font-weight:500;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--primary-gradient); color: white; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; font-weight: bold; text-transform:uppercase;">
                                            <?= substr($c['full_name'], 0, 2) ?>
                                        </div>
                                        <?= htmlspecialchars($c['full_name']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                                 <td>
                                    <button onclick="alert('Tính năng xem chi tiết khách hàng đang được phát triển!')" style="background: transparent; border: none; color: var(--accent-blue); cursor: pointer; text-decoration: none; padding: 5px; margin-right: 10px;" title="Xem thông tin chi tiết"><i class="fa-solid fa-address-card"></i> Chi tiết</button>
                                    <a href="admin.php?action=delete_user&id=<?= $c['id'] ?>" onclick="return confirm('Xóa khách hàng này sẽ xóa toàn bộ đơn hàng của họ. Bạn có chắc chắn?')" style="background: transparent; border: none; color: #ff4d4d; cursor: pointer; text-decoration: none;" title="Xóa tài khoản"><i class="fa-solid fa-user-slash"></i> Xóa</a>
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

        // Admin Search Logic
        function setupSearch(inputId, tableId, searchColumns) {
            const searchInput = document.getElementById(inputId);
            if (!searchInput) return;
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll(`#${tableId} tbody tr`);
                rows.forEach(row => {
                    let match = false;
                    searchColumns.forEach(index => {
                        if (row.cells[index].textContent.toLowerCase().includes(query)) match = true;
                    });
                    row.style.display = match ? '' : 'none';
                });
            });
        }
        setupSearch('adminProductSearch', 'productTable', [1, 2]); // Tên, Danh mục
        setupSearch('adminOrderSearch', 'orderTable', [0, 1]); // Mã đơn, Khách hàng
        setupSearch('adminCustomerSearch', 'customerTable', [0, 1]); // Tên KH, Email

        // Tab Navigation Logic
        const tabLinks = document.querySelectorAll('.admin-tab-link');
        const tabContents = document.querySelectorAll('.admin-tab-content');

        tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.style.display = 'none');
                
                link.classList.add('active');
                const targetId = link.getAttribute('data-tab');
                document.getElementById(targetId).style.display = 'block';
            });
        });

        // Initialize Chart.js
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
