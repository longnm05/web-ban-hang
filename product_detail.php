<?php
session_start();
require_once 'db.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    header("Location: products.php");
    exit;
}

$stmt = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Sản phẩm không tồn tại.";
    exit;
}

// Mock variations since our DB doesn't have them yet
$sizes = ['S', 'M', 'L', 'XL', 'XXL'];
if (strpos(strtolower($product['cat_name']), 'giày') !== false || strpos(strtolower($product['cat_name']), 'sneaker') !== false) {
    $sizes = ['39', '40', '41', '42', '43', '44'];
}
$colors = ['#000000', '#ffffff', '#8a2be2', '#00d2ff'];

// Lấy sản phẩm tương tự
$stmtRelated = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmtRelated->execute([$product['category_id'], $id]);
$relatedProducts = $stmtRelated->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-detail-container {
            display: flex;
            flex-wrap: wrap;
            padding: 120px 5% 50px;
            gap: 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Lưới hình ảnh phong cách Adidas */
        .pd-gallery {
            flex: 1;
            min-width: 50%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            align-content: start;
        }

        .pd-gallery img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            cursor: zoom-in;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--glass-border);
            transition: var(--transition-smooth);
        }
        
        .pd-gallery img:hover {
            border-color: var(--accent-purple);
        }

        /* Cột thông tin Sticky */
        .pd-info-wrapper {
            flex: 0 1 400px;
            position: relative;
        }

        .pd-info {
            position: sticky;
            top: 100px;
            background: rgba(10, 10, 12, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .pd-category {
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-blue);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        .pd-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .pd-price {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .pd-stock-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        /* Colors */
        .pd-section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .color-options {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .color-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: 0.2s;
        }

        .color-circle.selected {
            border-color: var(--accent-blue);
            transform: scale(1.1);
        }

        /* Sizes */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }

        .size-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            padding: 12px 0;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .size-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .size-btn.selected {
            background: var(--text-main);
            color: var(--bg-dark);
            border-color: var(--text-main);
        }

        /* Actions */
        .pd-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn-add-cart {
            flex: 1;
            background: var(--text-main);
            color: var(--bg-dark);
            border: none;
            padding: 18px;
            border-radius: 10px;
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn-wishlist {
            width: 60px;
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .btn-wishlist:hover, .btn-wishlist.active {
            border-color: #ff416c;
            color: #ff416c;
        }

        /* Accordion / Specs */
        .pd-accordion {
            border-top: 1px solid var(--glass-border);
        }

        .accordion-item {
            border-bottom: 1px solid var(--glass-border);
        }

        .accordion-header {
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .accordion-content {
            padding-bottom: 20px;
            color: var(--text-muted);
            line-height: 1.8;
            display: none;
        }
        
        .accordion-content.active {
            display: block;
        }

        .accordion-content ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        @media (max-width: 900px) {
            .pd-gallery {
                grid-template-columns: 1fr;
            }
            .pd-info-wrapper {
                flex: 100%;
            }
            .pd-info {
                position: static;
            }
        }
    </style>
</head>
<body>

    <!-- Header (Tương tự index.php) -->
    <nav class="glass-header">
        <div class="logo">
            <a href="index.php" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-microchip"></i> NovaStyle</a>
        </div>
        <div class="nav-links">
            <a href="products.php" class="nav-item" style="text-decoration:none; color:var(--text-main); font-weight:600; margin-right:15px;">Sản Phẩm</a>
            <a href="wishlist.php" class="nav-icon" title="Yêu Thích"><i class="fa-solid fa-heart"></i><span class="badge" id="wishlistBadge">0</span></a>
            <a href="#" class="nav-icon" onclick="alert('Bạn không có thông báo mới nào.'); return false;" title="Thông Báo"><i class="fa-solid fa-bell"></i><span class="badge">0</span></a>
            <a href="#" class="nav-icon" id="openCartBtn" title="Giỏ Hàng"><i class="fa-solid fa-cart-shopping"></i><span class="badge" id="cartBadge">0</span></a>
            <a href="profile.php" class="nav-icon"><i class="fa-solid fa-user"></i></a>
        </div>
    </nav>

    <div class="product-detail-container">
        <!-- Trái: Thư viện ảnh -->
        <div class="pd-gallery">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?> 2" style="filter: brightness(0.8) sepia(0.2);">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?> 3" style="filter: grayscale(0.5);">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?> 4" style="transform: scaleX(-1);">
        </div>

        <!-- Phải: Thông tin chốt đơn -->
        <div class="pd-info-wrapper">
            <div class="pd-info">
                <span class="pd-category"><?= htmlspecialchars($product['cat_name']) ?></span>
                <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="pd-price">
                    $<?= number_format($product['price'], 2) ?>
                    <span class="pd-stock-badge"><i class="fa-solid fa-check"></i> Còn hàng (<?= $product['stock_quantity'] ?>)</span>
                </div>

                <div class="pd-section-title">Màu Sắc</div>
                <div class="color-options">
                    <?php foreach($colors as $idx => $color): ?>
                        <div class="color-circle <?= $idx == 0 ? 'selected' : '' ?>" style="background-color: <?= $color ?>;" onclick="selectColor(this)"></div>
                    <?php endforeach; ?>
                </div>

                <div class="pd-section-title">Kích Thước</div>
                <div class="size-grid">
                    <?php foreach($sizes as $size): ?>
                        <div class="size-btn" onclick="selectSize(this)"><?= $size ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="pd-actions">
                    <button class="btn-add-cart add-to-cart" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['price'] ?>" data-image="<?= htmlspecialchars($product['image_url']) ?>">
                        THÊM VÀO GIỎ HÀNG <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button class="btn-wishlist" onclick="toggleWishlist(this, '<?= $product['id'] ?>', '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= $product['image_url'] ?>')"><i class="fa-regular fa-heart"></i></button>
                </div>

                <div class="pd-accordion">
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            Mô tả sản phẩm <i class="fa-solid fa-plus"></i>
                        </div>
                        <div class="accordion-content active">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                            <br><br>
                            Phân tích AI: Phong cách <?= $product['ai_tags'] ?>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            Chi tiết thông số <i class="fa-solid fa-plus"></i>
                        </div>
                        <div class="accordion-content">
                            <ul>
                                <li>Chất liệu: Premium Cyber-Fabric 2.0</li>
                                <li>Kiểu dáng: Regular Fit / Tương lai</li>
                                <li>Bảo hành: Đổi trả trong 30 ngày</li>
                                <li>Mã sản phẩm: SKU-<?= strtoupper($product['id']) ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            Giao hàng & Đổi trả <i class="fa-solid fa-plus"></i>
                        </div>
                        <div class="accordion-content">
                            Miễn phí giao hàng tiêu chuẩn cho thành viên hội viên NovaStyle. 
                            Giao hàng nhanh 2h tại khu vực trung tâm.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SẢN PHẨM LIÊN QUAN -->
    <?php if(!empty($relatedProducts)): ?>
    <section style="padding: 50px 5%; max-width: 1400px; margin: 0 auto;">
        <h2 style="font-family: var(--font-heading); margin-bottom: 30px; font-size: 2rem;">Sản Phẩm Tương Tự</h2>
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px;">
            <?php foreach ($relatedProducts as $row): ?>
            <div class="product-card">
                <div class="card-image">
                    <a href="product_detail.php?id=<?= $row['id'] ?>"><img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></a>
                </div>
                <div class="card-info">
                    <h3><a href="product_detail.php?id=<?= $row['id'] ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($row['name']) ?></a></h3>
                    <div class="price-row">
                        <span class="price">$<?= number_format($row['price'], 2) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ĐÁNH GIÁ (REVIEWS) MOCKUP -->
    <section style="padding: 50px 5%; max-width: 1400px; margin: 0 auto; background: rgba(255,255,255,0.02); border-radius: 20px; margin-bottom: 50px;">
        <h2 style="font-family: var(--font-heading); margin-bottom: 20px; font-size: 2rem;">Đánh Giá Từ Khách Hàng (4.8/5) <i class="fa-solid fa-star" style="color:gold;"></i></h2>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="padding: 20px; background: rgba(0,0,0,0.2); border-radius: 15px; border: 1px solid var(--glass-border);">
                <div style="display:flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong>@minhtuan99</strong> <span style="color:gold;"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                </div>
                <p style="color: var(--text-muted);">Sản phẩm tuyệt vời, chất liệu đúng như mô tả. Sẽ ủng hộ shop dài dài!</p>
            </div>
            <div style="padding: 20px; background: rgba(0,0,0,0.2); border-radius: 15px; border: 1px solid var(--glass-border);">
                <div style="display:flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong>@linhnguyen</strong> <span style="color:gold;"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></span>
                </div>
                <p style="color: var(--text-muted);">Giao hàng nhanh, form lên dáng rất chuẩn. AI tư vấn size rất vừa vặn.</p>
            </div>
        </div>
        <button class="btn btn-secondary" style="margin-top: 20px;">Viết Đánh Giá</button>
    </section>

    <!-- Giỏ hàng Sidebar (tái sử dụng cấu trúc, được load bởi script.js) -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-sidebar glass-panel cart-glass" id="cartSidebar">
        <div class="cart-header">
            <h3><i class="fa-solid fa-cart-shopping"></i> Giỏ Hàng</h3>
            <button id="closeCartBtn" class="close-cart"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="cart-body">
            <div class="cart-empty" id="cartEmpty" style="display: none; text-align: center; padding: 40px 20px;">
                <p>Giỏ hàng trống.</p>
            </div>
            <div class="cart-items-container" id="cartItemsContainer"></div>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span style="color: var(--text-muted)">Tổng cộng:</span>
                <span class="total-price gradient-text" id="cartTotal" style="font-size: 1.5rem; font-weight:800;">$0.00</span>
            </div>
            <button class="btn btn-primary w-100 btn-glow" id="checkoutBtn">Tiến Hành Thanh Toán</button>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Lấy thông tin gốc
        const originalName = "<?= htmlspecialchars(addslashes($product['name'])) ?>";
        const originalId = "<?= $product['id'] ?>";
        const cartBtn = document.querySelector('.btn-add-cart');

        let selectedColorName = 'Mặc định';
        function selectColor(el) {
            document.querySelectorAll('.color-circle').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            // Mock color name based on background
            selectedColorName = el.style.backgroundColor; 
            updateCartData();
        }

        let selectedSize = null;
        function selectSize(el) {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            selectedSize = el.innerText;
            updateCartData();
        }

        function updateCartData() {
            if (selectedSize) {
                cartBtn.setAttribute('data-name', originalName + ' (Size: ' + selectedSize + ')');
                cartBtn.setAttribute('data-id', originalId + '_' + selectedSize);
            }
        }

        function toggleAccordion(el) {
            const content = el.nextElementSibling;
            const icon = el.querySelector('i');
            
            if (content.classList.contains('active')) {
                content.classList.remove('active');
                icon.className = 'fa-solid fa-plus';
            } else {
                document.querySelectorAll('.accordion-content').forEach(c => c.classList.remove('active'));
                document.querySelectorAll('.accordion-header i').forEach(i => i.className = 'fa-solid fa-plus');
                
                content.classList.add('active');
                icon.className = 'fa-solid fa-minus';
            }
        }

        // Dùng capturing (true) để chặn sự kiện của script.js nếu chưa chọn size
        cartBtn.addEventListener('click', function(e) {
            if (!selectedSize) {
                e.stopImmediatePropagation(); // Chặn script.js thực thi
                e.preventDefault();
                alert('Vui lòng chọn Kích Thước (Size) trước khi thêm vào giỏ hàng!');
            }
        }, true);
    </script>

    <footer style="background: rgba(10,10,12,0.9); border-top: 1px solid var(--glass-border); padding: 50px 5% 20px; margin-top: 50px;">
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
