<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm Yêu Thích - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="glass-header">
        <div class="logo">
            <a href="index.php" style="color: inherit; text-decoration: none;">
                <i class="fa-solid fa-microchip"></i> NovaStyle
            </a>
        </div>
        <div class="nav-links" style="margin-left: auto;">
            <a href="products.php" class="nav-item" style="text-decoration:none; color:var(--text-main); font-weight:600; margin-right:15px;">Sản Phẩm</a>
            <a href="wishlist.php" class="nav-icon" title="Yêu Thích"><i class="fa-solid fa-heart" style="color: #ff416c;"></i><span class="badge" id="wishlistBadge">0</span></a>
            <a href="#" class="nav-icon" onclick="alert('Bạn không có thông báo mới nào.'); return false;" title="Thông Báo"><i class="fa-solid fa-bell"></i><span class="badge">0</span></a>
            <a href="profile.php" class="nav-icon" title="Hồ Sơ Của Tôi">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: var(--accent-purple);"></i>
            </a>
        </div>
    </nav>
    
    <div style="padding: 120px 5% 50px; min-height: 80vh;">
        <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 20px;"><i class="fa-solid fa-heart" style="color:#ff416c;"></i> Sản Phẩm Yêu Thích</h2>
        <div id="wishlistContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px;">
            <!-- Render via JS -->
        </div>
        <div id="emptyWishlist" style="display: none; text-align: center; padding: 50px;">
            <i class="fa-solid fa-heart-crack" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-muted);">Danh sách yêu thích trống</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Bạn chưa lưu bất kỳ sản phẩm nào. Hãy khám phá và lưu lại những món đồ bạn yêu thích nhé!</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 20px; padding: 12px 25px;">Khám Phá Sản Phẩm Ngay</a>
        </div>
    </div>
    
    <script>
        function loadWishlist() {
            const list = JSON.parse(localStorage.getItem('novaWishlist') || '[]');
            const container = document.getElementById('wishlistContainer');
            const empty = document.getElementById('emptyWishlist');
            
            if(list.length === 0) {
                container.style.display = 'none';
                empty.style.display = 'block';
                return;
            }
            
            container.style.display = 'grid';
            empty.style.display = 'none';
            container.innerHTML = '';
            
            list.forEach(item => {
                container.innerHTML += `
                    <div class="product-card" style="position: relative; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 20px; overflow: hidden; transition: 0.3s;">
                        <button class="btn-wishlist active" style="position: absolute; top: 15px; right: 15px; z-index: 10; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.1); cursor: pointer; color: #ff416c;" onclick="removeFromWishlist('${item.id}')"><i class="fa-solid fa-heart"></i></button>
                        <div class="product-img-wrapper" style="height: 250px; overflow: hidden;">
                            <img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;">
                        </div>
                        <div class="product-info" style="padding: 20px;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--text-main);">${item.name}</h3>
                            <div style="font-weight: 700; color: #ff416c; font-size: 1.2rem;">$${item.price}</div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 15px; background: var(--primary-gradient);" onclick="window.location.href='product_detail.php?id=${item.id}'">Xem Chi Tiết</button>
                        </div>
                    </div>
                `;
            });
        }
        
        function removeFromWishlist(id) {
            let list = JSON.parse(localStorage.getItem('novaWishlist') || '[]');
            list = list.filter(i => i.id !== id);
            localStorage.setItem('novaWishlist', JSON.stringify(list));
            loadWishlist();
            
            const badge = document.getElementById('wishlistBadge');
            if(badge) badge.innerText = list.length;
        }
        
        document.addEventListener('DOMContentLoaded', loadWishlist);
    </script>
    <script src="script.js"></script>
    <?php include 'chat_widget.php'; ?>
</body>
</html>
