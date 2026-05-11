<?php
require_once 'db.php';

// Các danh mục đa dạng
$categories = [
    ['name' => 'Thời trang Nam', 'slug' => 'thoi-trang-nam'],
    ['name' => 'Thời trang Nữ', 'slug' => 'thoi-trang-nu'],
    ['name' => 'Thời trang Trẻ em', 'slug' => 'thoi-trang-tre-em'],
    ['name' => 'Giày thể thao Nam', 'slug' => 'giay-the-thao-nam'],
    ['name' => 'Giày thể thao Nữ', 'slug' => 'giay-the-thao-nu'],
    ['name' => 'Phụ kiện', 'slug' => 'phu-kien'],
    ['name' => 'Trang phục tập luyện', 'slug' => 'trang-phuc-tap-luyen']
];

// Thêm danh mục nếu chưa có
$catIds = [];
foreach ($categories as $cat) {
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$cat['slug']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $catIds[$cat['slug']] = $existing['id'];
    } else {
        $stmtInsert = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmtInsert->execute([$cat['name'], $cat['slug']]);
        $catIds[$cat['slug']] = $conn->lastInsertId();
    }
}

// Xóa các sản phẩm cũ (tùy chọn, ở đây ta cứ để thêm mới để đa dạng)

$new_products = [
    // Thời trang Nam
    ['id' => 'p_m1', 'cat' => 'thoi-trang-nam', 'name' => 'Áo Thun Polo Lịch Lãm', 'desc' => 'Chất liệu thoáng mát, phù hợp công sở và dạo phố.', 'price' => 25.00, 'img' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820', 'tags' => 'áo thun, polo, nam, lịch lãm, công sở'],
    ['id' => 'p_m2', 'cat' => 'thoi-trang-nam', 'name' => 'Quần Jean Ống Suông', 'desc' => 'Quần jean phong cách cổ điển, dễ phối đồ.', 'price' => 40.00, 'img' => 'https://images.unsplash.com/photo-1542272604-780c8dff2e82', 'tags' => 'quần jean, nam, suông, cổ điển'],
    ['id' => 'p_m3', 'cat' => 'thoi-trang-nam', 'name' => 'Áo Khoác Da Biker', 'desc' => 'Áo khoác da cá tính, giữ ấm tốt.', 'price' => 120.00, 'img' => 'https://images.unsplash.com/photo-1520975954732-57dd22299614', 'tags' => 'áo khoác, da, nam, biker, cá tính'],
    
    // Thời trang Nữ
    ['id' => 'p_w1', 'cat' => 'thoi-trang-nu', 'name' => 'Váy Hoa Trễ Vai', 'desc' => 'Váy hoa dịu dàng, phù hợp dạo biển.', 'price' => 35.00, 'img' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1', 'tags' => 'váy, hoa, nữ, dịu dàng, dạo biển'],
    ['id' => 'p_w2', 'cat' => 'thoi-trang-nu', 'name' => 'Áo Crop Top Thể Thao', 'desc' => 'Áo crop top năng động, thấm hút mồ hôi.', 'price' => 20.00, 'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c', 'tags' => 'áo, croptop, thể thao, nữ, năng động'],
    ['id' => 'p_w3', 'cat' => 'thoi-trang-nu', 'name' => 'Quần Tây Ống Rộng', 'desc' => 'Quần tây thanh lịch, tôn dáng.', 'price' => 45.00, 'img' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae', 'tags' => 'quần tây, ống rộng, nữ, thanh lịch'],
    
    // Giày thể thao Nam
    ['id' => 'p_s1', 'cat' => 'giay-the-thao-nam', 'name' => 'Giày Chạy Bộ SpeedX', 'desc' => 'Đế siêu nhẹ, bám đường tốt.', 'price' => 85.00, 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff', 'tags' => 'giày, thể thao, chạy bộ, nam, nhẹ'],
    ['id' => 'p_s2', 'cat' => 'giay-the-thao-nam', 'name' => 'Sneaker Urban Style', 'desc' => 'Phong cách đường phố, dễ phối đồ.', 'price' => 95.00, 'img' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782', 'tags' => 'sneaker, street, nam, thời trang'],
    
    // Giày thể thao Nữ
    ['id' => 'p_s3', 'cat' => 'giay-the-thao-nu', 'name' => 'Giày Thể Thao Pastel', 'desc' => 'Màu sắc ngọt ngào, đệm êm ái.', 'price' => 75.00, 'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77', 'tags' => 'giày, thể thao, nữ, pastel, ngọt ngào'],
    ['id' => 'p_s4', 'cat' => 'giay-the-thao-nu', 'name' => 'Giày Training Pro Nữ', 'desc' => 'Hỗ trợ tập gym và yoga hiệu quả.', 'price' => 80.00, 'img' => 'https://images.unsplash.com/photo-1579338559194-a162d19bf842', 'tags' => 'giày, training, gym, nữ'],
    
    // Phụ kiện
    ['id' => 'p_a1', 'cat' => 'phu-kien', 'name' => 'Đồng Hồ Thông Minh', 'desc' => 'Theo dõi sức khỏe và nhịp tim.', 'price' => 150.00, 'img' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30', 'tags' => 'đồng hồ, thông minh, smartwatch, phụ kiện'],
    ['id' => 'p_a2', 'cat' => 'phu-kien', 'name' => 'Kính Râm Thời Trang', 'desc' => 'Chống tia UV, phong cách.', 'price' => 30.00, 'img' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083', 'tags' => 'kính râm, phụ kiện, thời trang, uv'],
    ['id' => 'p_a3', 'cat' => 'phu-kien', 'name' => 'Balo Chống Nước', 'desc' => 'Balo du lịch đa năng, chống thấm.', 'price' => 60.00, 'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62', 'tags' => 'balo, túi, phụ kiện, chống nước'],

    // Thời trang Trẻ em
    ['id' => 'p_k1', 'cat' => 'thoi-trang-tre-em', 'name' => 'Set Đồ Khủng Long', 'desc' => 'Chất liệu 100% cotton an toàn cho bé.', 'price' => 22.00, 'img' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba', 'tags' => 'trẻ em, bé trai, khủng long, đồ bộ'],
    ['id' => 'p_k2', 'cat' => 'thoi-trang-tre-em', 'name' => 'Váy Công Chúa Nhỏ', 'desc' => 'Đáng yêu và lộng lẫy cho bé gái.', 'price' => 28.00, 'img' => 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8', 'tags' => 'trẻ em, bé gái, váy, công chúa']
];

$stmtInsertProduct = $conn->prepare("INSERT INTO products (id, category_id, name, description, price, image_url, ai_tags, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, 100) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), price=VALUES(price), image_url=VALUES(image_url), ai_tags=VALUES(ai_tags)");

foreach ($new_products as $p) {
    if (isset($catIds[$p['cat']])) {
        $stmtInsertProduct->execute([
            $p['id'], 
            $catIds[$p['cat']], 
            $p['name'], 
            $p['desc'], 
            $p['price'], 
            $p['img'], 
            $p['tags']
        ]);
    }
}

echo "Đã thêm hàng loạt sản phẩm đa dạng thành công!";
?>
