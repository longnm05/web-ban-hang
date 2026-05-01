<?php
require_once 'db.php';

// Xóa dữ liệu cũ
$conn->exec("DELETE FROM products");
$conn->exec("DELETE FROM categories");

// 1. Thêm danh mục
$categories = [
    ['Áo Thun', 'ao-thun', 'Các loại áo thun thời trang'],
    ['Áo Khoác', 'ao-khoac', 'Áo khoác mùa đông, áo khoác da'],
    ['Quần Jeans', 'quan-jeans', 'Quần jeans nam nữ, form rộng'],
    ['Phụ Kiện', 'phu-kien', 'Túi xách, mũ, nón, kính mắt'],
    ['Giày Dép', 'giay-dep', 'Giày sneaker, giày da, sandal']
];

$stmtCat = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
foreach ($categories as $cat) {
    $stmtCat->execute($cat);
}

// Lấy ID danh mục
$catMap = [];
$res = $conn->query("SELECT id, name FROM categories")->fetchAll();
foreach ($res as $row) {
    $catMap[$row['name']] = $row['id'];
}

// 2. Thêm 50 sản phẩm
$productsData = [];
$images = [
    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500',
    'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500',
    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=500',
    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500',
    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=500'
];

for ($i = 1; $i <= 50; $i++) {
    // Phân bổ ngẫu nhiên
    $catNames = array_keys($catMap);
    $catName = $catNames[array_rand($catNames)];
    $catId = $catMap[$catName];
    
    $name = "Sản phẩm " . $catName . " Cao Cấp #" . str_pad($i, 3, '0', STR_PAD_LEFT);
    $desc = "Đây là mô tả chi tiết cho sản phẩm $name. Chất liệu cao cấp, form dáng chuẩn.";
    $price = rand(15, 120) + 0.99;
    $stock = rand(10, 500);
    $img = $images[array_rand($images)];
    $ai_tags = '["fashion", "new_arrival", "premium"]';
    
    $productsData[] = [
        'id' => 'p' . time() . rand(1000, 9999),
        'category_id' => $catId,
        'name' => $name,
        'description' => $desc,
        'price' => $price,
        'image_url' => $img,
        'ai_tags' => $ai_tags,
        'stock_quantity' => $stock
    ];
}

$stmtProd = $conn->prepare("INSERT INTO products (id, category_id, name, description, price, image_url, ai_tags, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($productsData as $p) {
    $stmtProd->execute([
        $p['id'], $p['category_id'], $p['name'], $p['description'], $p['price'], $p['image_url'], $p['ai_tags'], $p['stock_quantity']
    ]);
}

echo "Đã thêm " . count($productsData) . " sản phẩm thành công!\n";
?>
