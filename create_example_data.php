<?php
require_once 'db.php';

// 1. Tạo thêm khách hàng mẫu
$sample_customers = [
    ['Nguyễn Văn A', 'customer', 'nguyenvana@example.com', '0901234567', '123 Lê Lợi, Quận 1, TP.HCM', 'hashed_123'],
    ['Trần Thị B', 'customer', 'tranthib@example.com', '0912345678', '456 Nguyễn Huệ, Quận 1, TP.HCM', 'hashed_123'],
    ['Lê Văn C', 'customer', 'levanc@example.com', '0923456789', '789 CMT8, Tân Bình, TP.HCM', 'hashed_123'],
    ['Phạm Minh D', 'customer', 'phamminhd@example.com', '0934567890', '101 Võ Văn Tần, Quận 3, TP.HCM', 'hashed_123'],
    ['Hoàng Anh E', 'customer', 'hoanganhe@example.com', '0945678901', '202 Lý Tự Trọng, Quận 1, TP.HCM', 'hashed_123']
];

$stmtUser = $conn->prepare("INSERT INTO users (full_name, role, email, phone, address, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($sample_customers as $u) {
    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$u[2]]);
    if (!$check->fetch()) {
        $stmtUser->execute($u);
    }
}

// Lấy danh sách ID khách hàng và sản phẩm
$customerIds = $conn->query("SELECT id FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_COLUMN);
$productIds = $conn->query("SELECT id, price FROM products")->fetchAll();

if (empty($customerIds) || empty($productIds)) {
    die("Cần có khách hàng và sản phẩm trước khi tạo đơn hàng mẫu!");
}

// 2. Tạo 15 đơn hàng mẫu với các trạng thái khác nhau
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

for ($i = 0; $i < 15; $i++) {
    $userId = $customerIds[array_rand($customerIds)];
    $status = $statuses[array_rand($statuses)];
    $total = rand(50, 500) + 0.99;
    $date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days -' . rand(0, 23) . ' hours'));
    
    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, ?, ?)");
    $stmtOrder->execute([$userId, $total, $status, $date]);
    $orderId = $conn->lastInsertId();
    
    // Thêm 1-3 sản phẩm vào đơn hàng
    $numItems = rand(1, 3);
    for ($j = 0; $j < $numItems; $j++) {
        $p = $productIds[array_rand($productIds)];
        $qty = rand(1, 2);
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmtItem->execute([$orderId, $p['id'], $qty, $p['price']]);
    }
}

echo "Đã tạo thêm khách hàng và 15 đơn hàng mẫu thành công!";
?>
