<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thanh toán!', 'redirect' => 'login.php']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống!']);
    exit;
}

$userId = $_SESSION['user_id'];
$cart = $data['cart'];

// Lấy thông tin người dùng để có địa chỉ mặc định
$stmtUser = $conn->prepare("SELECT address FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Đọc thông tin giao hàng từ request (nếu có)
$rawAddress = $data['shipping_address'] ?? $user['address'] ?? 'Chưa cập nhật địa chỉ';
$customerName = $data['customer_name'] ?? '';
$customerPhone = $data['customer_phone'] ?? '';
$paymentMethod = $data['payment_method'] ?? 'Thanh toán khi nhận hàng (COD)';
$shippingFee = isset($data['shipping_fee']) ? floatval($data['shipping_fee']) : 0;

$shippingAddress = $rawAddress;
if (!empty($customerName) && !empty($customerPhone)) {
    $shippingAddress = "Người nhận: $customerName | SĐT: $customerPhone | Địa chỉ: $rawAddress | PTTT: $paymentMethod";
}

$totalAmount = 0;
foreach ($cart as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}
$totalAmount += $shippingFee; // Cộng thêm phí giao hàng

try {
    $conn->beginTransaction();

    // 1. Chèn vào bảng orders
    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'pending', ?)");
    $stmtOrder->execute([$userId, $totalAmount, $shippingAddress]);
    $orderId = $conn->lastInsertId();

    // 2. Chèn vào bảng order_items
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    $stmtCheck = $conn->prepare("SELECT id FROM products WHERE id = ?");
    
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $base_id = $product_id;
        
        // Tách ID (bỏ phần size như _L, _M đi)
        $parts = explode('_', $product_id);
        while(count($parts) > 0) {
            $test_id = implode('_', $parts);
            $stmtCheck->execute([$test_id]);
            if ($stmtCheck->fetch()) {
                $base_id = $test_id;
                break;
            }
            array_pop($parts);
        }
        
        $stmtItem->execute([$orderId, $base_id, $item['quantity'], $item['price']]);
    }

    $conn->commit();

    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>
