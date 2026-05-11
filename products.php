<?php
session_start();
require_once 'db.php';

// Lấy danh mục để hiển thị ở Sidebar
$stmtCat = $conn->prepare("SELECT * FROM categories");
$stmtCat->execute();
$categories = $stmtCat->fetchAll();

// Xử lý Lọc & Sắp xếp
$where = [];
$params = [];

if (!empty($_GET['category'])) {
    $where[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}
if (!empty($_GET['min_price'])) {
    $where[] = "p.price >= ?";
    $params[] = $_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
}

$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$orderSQL = "ORDER BY p.created_at DESC";
if (!empty($_GET['sort'])) {
    if ($_GET['sort'] == 'price-asc') $orderSQL = "ORDER BY p.price ASC";
    elseif ($_GET['sort'] == 'price-desc') $orderSQL = "ORDER BY p.price DESC";
    elseif ($_GET['sort'] == 'popular') $orderSQL = "ORDER BY RAND()"; // Mock for popular
}

$sql = "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id $whereSQL $orderSQL";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--bg-light);
            padding-top: 100px;
        }

        .products-layout {
            display: flex;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 5%;
        }

        /* Sidebar Filter */
        .filter-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .filter-group {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .filter-title {
            font-family: var(--font-heading);
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--text-main);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-list {
            list-style: none;
            padding: 0;
        }

        .filter-list li {
            margin-bottom: 10px;
        }

        .filter-list label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .filter-list label:hover {
            color: var(--accent-blue);
        }

        .filter-list input[type="checkbox"] {
            accent-color: var(--accent-purple);
            width: 16px;
            height: 16px;
        }

        /* Price Range */
        .price-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .price-inputs input {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            background: rgba(0,0,0,0.02);
            color: var(--text-main);
            font-family: var(--font-body);
        }

        /* Main Content */
        .products-main {
            flex: 1;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .sort-select {
            padding: 10px 15px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background: rgba(0,0,0,0.02);
            font-family: var(--font-body);
            color: var(--text-main);
            cursor: pointer;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.7);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition-smooth);
            font-weight: 600;
        }

        .page-btn.active, .page-btn:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }
    </style>
</head>

<body>
    <!-- Background Elements -->
    <div class="orb orb-1" style="background: rgba(138, 43, 226, 0.15);"></div>
    <div class="orb orb-2" style="background: rgba(255, 65, 108, 0.15);"></div>

    <!-- Navigation -->
    <nav class="glass-header" style="background: rgba(255,255,255,0.8);">
        <div class="logo">
            <i class="fa-solid fa-microchip" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i> NovaStyle
        </div>
        <div class="nav-links">
            <a href="products.php" class="nav-item active">Khám Phá</a>
            <a href="products.php?category=nam" class="nav-item">Nam</a>
            <a href="wishlist.php" class="nav-icon" title="Yêu Thích"><i class="fa-solid fa-heart"></i><span class="badge" id="wishlistBadge">0</span></a>
            <a href="#" class="nav-icon" onclick="alert('Bạn không có thông báo mới nào.'); return false;" title="Thông Báo"><i class="fa-solid fa-bell"></i><span class="badge">0</span></a>
            <a href="#" class="nav-icon" id="openCartBtn" title="Giỏ Hàng"><i class="fa-solid fa-cart-shopping"></i><span class="badge" id="cartBadge">0</span></a>
            <a href="history.php" class="nav-icon" title="Lịch Sử Đơn Hàng"><i class="fa-solid fa-clock-rotate-left"></i></a>
            <a href="profile.php" class="nav-icon" title="Hồ Sơ Của Tôi"><i class="fa-solid fa-user"></i></a>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="products-layout">
        
        <!-- Sidebar Filter -->
        <aside class="filter-sidebar">
            <div class="filter-group">
                <h3 class="filter-title">Danh Mục <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i></h3>
                <ul class="filter-list">
                    <li><label><input type="radio" name="cat" value="" <?= empty($_GET['category']) ? 'checked' : '' ?> onchange="applyFilter('category', '')"> Tất cả sản phẩm</label></li>
                    <?php foreach($categories as $cat): ?>
                    <li><label><input type="radio" name="cat" value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'checked' : '' ?> onchange="applyFilter('category', '<?= $cat['id'] ?>')"> <?= htmlspecialchars($cat['name']) ?></label></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-group">
                <h3 class="filter-title">Khoảng Giá <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i></h3>
                <div class="price-inputs">
                    <input type="number" id="min_price" value="<?= $_GET['min_price'] ?? '' ?>" placeholder="Từ ($)">
                    <span>-</span>
                    <input type="number" id="max_price" value="<?= $_GET['max_price'] ?? '' ?>" placeholder="Đến ($)">
                </div>
                <button class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 10px;" onclick="applyPriceFilter()">Lọc Giá</button>
            </div>

            <div class="filter-group">
                <h3 class="filter-title">Màu Sắc <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i></h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div style="width: 25px; height: 25px; border-radius: 50%; background: #000; cursor: pointer; border: 2px solid var(--glass-border);"></div>
                    <div style="width: 25px; height: 25px; border-radius: 50%; background: #fff; cursor: pointer; border: 2px solid var(--glass-border);"></div>
                    <div style="width: 25px; height: 25px; border-radius: 50%; background: #ff416c; cursor: pointer; border: 2px solid var(--glass-border);"></div>
                    <div style="width: 25px; height: 25px; border-radius: 50%; background: #8a2be2; cursor: pointer; border: 2px solid var(--glass-border);"></div>
                    <div style="width: 25px; height: 25px; border-radius: 50%; background: #00ff88; cursor: pointer; border: 2px solid var(--glass-border);"></div>
                </div>
            </div>
        </aside>

        <!-- Product Grid Area -->
        <main class="products-main">
            <div class="products-header">
                <div>
                    <h2 style="font-family: var(--font-heading); font-size: 1.5rem;">Tất cả sản phẩm</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Hiển thị 1 - 9 trong số 45 kết quả</p>
                </div>
                <div>
                    <select class="sort-select" onchange="applyFilter('sort', this.value)">
                        <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="price-asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price-asc') ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                        <option value="price-desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price-desc') ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                        <option value="popular" <?= (isset($_GET['sort']) && $_GET['sort'] == 'popular') ? 'selected' : '' ?>>Phổ biến nhất</option>
                    </select>
                </div>
            </div>

            <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <div class="card-glow"></div>
                    <div class="card-image">
                        <a href="product_detail.php?id=<?= $row['id'] ?>"><img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></a>
                        <button class="quick-view"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="card-info">
                        <span class="category"><?= htmlspecialchars($row['cat_name']) ?></span>
                        <h3><a href="product_detail.php?id=<?= $row['id'] ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($row['name']) ?></a></h3>
                        <div class="price-row">
                            <span class="price">$<?= number_format($row['price'], 2) ?></span>
                            <button class="add-to-cart" data-id="<?= htmlspecialchars($row['id']) ?>" data-name="<?= htmlspecialchars($row['name']) ?>" data-price="<?= $row['price'] ?>" data-image="<?= htmlspecialchars($row['image_url']) ?>">
                                <i class="fa-solid fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">3</a>
                <a href="#" class="page-btn"><i class="fa-solid fa-arrow-right"></i></a>
            </div>

        </main>
    </div>

    <!-- Quick View Modal (Reused from index.html) -->
    <div class="modal-overlay" id="quickViewOverlay">
        <div class="modal-content" id="quickViewModal" style="width: 90%; max-width: 800px; display: flex; flex-wrap: wrap; gap: 30px; position: relative;">
            <button id="closeQuickView" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-main); z-index: 10;"><i class="fa-solid fa-times"></i></button>
            <div style="flex: 1; min-width: 300px;">
                <img id="qvImage" src="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: center;">
                <span id="qvCategory" class="category" style="margin-bottom: 10px; display: inline-block;"></span>
                <h2 id="qvTitle" style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 15px; color: var(--text-main);"></h2>
                <div style="font-size: 2rem; font-weight: 800; color: var(--accent-blue); margin-bottom: 20px;" id="qvPrice"></div>
                <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.8;">Sản phẩm thiết kế độc quyền, được AI phân tích có độ tương thích 95% với phong cách hiện tại của bạn. Chất liệu cao cấp, đường may tỉ mỉ mang lại trải nghiệm tuyệt vời.</p>
                <div style="display: flex; gap: 15px; margin-top: auto;">
                    <input type="number" value="1" min="1" id="qvQty" style="width: 80px; padding: 10px; border: 1px solid var(--glass-border); border-radius: 10px; text-align: center; background: rgba(0,0,0,0.02); color: var(--text-main);">
                    <button class="btn btn-primary" id="qvAddToCart" style="flex: 1; justify-content: center;"><i class="fa-solid fa-cart-plus"></i> Thêm Vào Giỏ Hàng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Giỏ Hàng <i class="fa-solid fa-bag-shopping"></i></h3>
            <button class="close-cart" id="closeCartBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cart-items" id="cartItemsContainer">
            <!-- Items rendered via JS -->
        </div>
        <div class="cart-empty" id="cartEmpty" style="display:none; text-align:center; padding: 30px 0; color: var(--text-muted);">
            Giỏ hàng trống.
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Tổng cộng:</span>
                <span class="total-price" id="cartTotal">$0.00</span>
            </div>
            <button class="btn btn-primary" id="checkoutBtn" style="width: 100%; margin-top: 15px;">Tiến Hành Thanh Toán</button>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        function applyFilter(key, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            window.location.href = url.toString();
        }

        function applyPriceFilter() {
            const min = document.getElementById('min_price').value;
            const max = document.getElementById('max_price').value;
            const url = new URL(window.location.href);
            
            if (min) url.searchParams.set('min_price', min);
            else url.searchParams.delete('min_price');
            
            if (max) url.searchParams.set('max_price', max);
            else url.searchParams.delete('max_price');
            
            window.location.href = url.toString();
        }
    </script>

    <!-- Newsletter Section -->
    <section style="background: var(--primary-gradient); padding: 60px 5%; text-align: center; color: white; margin-top: 50px;">
        <h2 style="font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 20px;">Nhận Ưu Đãi & Xu Hướng Mới</h2>
        <p style="margin-bottom: 30px; font-size: 1.1rem; opacity: 0.9;">AI của chúng tôi sẽ gửi những bộ sưu tập cá nhân hóa trực tiếp đến email của bạn.</p>
        <form style="display: flex; max-width: 500px; margin: 0 auto; gap: 10px;" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký! Bạn sẽ nhận được email xác nhận sớm.');">
            <input type="email" placeholder="Địa chỉ email của bạn..." required style="flex: 1; padding: 15px 20px; border: none; border-radius: 30px; font-size: 1rem; outline: none; font-family: var(--font-body);">
            <button type="submit" style="padding: 15px 30px; background: #0a0a0c; color: white; border: none; border-radius: 30px; font-weight: 600; cursor: pointer; transition: 0.3s;">Đăng Ký</button>
        </form>
    </section>

    <footer style="background: rgba(10,10,12,0.9); border-top: 1px solid var(--glass-border); padding: 50px 5% 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 30px;">
            <div>
                <h3 style="font-family: var(--font-heading); margin-bottom: 15px;"><i class="fa-solid fa-microchip" style="color: var(--accent-blue);"></i> NovaStyle</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Trải nghiệm mua sắm e-commerce đỉnh cao với trợ lý AI phân tích phong cách.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 15px;">Chính sách</h4>
                <ul style="list-style: none; padding: 0; color: var(--text-muted); font-size: 0.9rem; line-height: 2;">
                    <li><a href="#" style="color: inherit; text-decoration: none;">Giao hàng & Nhận hàng</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">Chính sách đổi trả</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">Bảo mật thông tin</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 15px;">Liên hệ</h4>
                <ul style="list-style: none; padding: 0; color: var(--text-muted); font-size: 0.9rem; line-height: 2;">
                    <li><i class="fa-solid fa-phone"></i> 1900 6868</li>
                    <li><i class="fa-solid fa-envelope"></i> support@novastyle.ai</li>
                    <li><i class="fa-solid fa-location-dot"></i> 123 Tech Street, HCMC</li>
                </ul>
            </div>
        </div>
        <p style="text-align: center; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">&copy; 2026 NovaStyle AI. Bản quyền thuộc về tương lai.</p>
    </footer>

    <?php include 'chat_widget.php'; ?>
</body>
</html>
