<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'customer';

// Lấy ID của Admin từ DB (Người quản trị đầu tiên tìm thấy)
$stmtAdmin = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$adminRow = $stmtAdmin->fetch();
$adminId = $adminRow ? intval($adminRow['id']) : 2;

$action = $_GET['action'] ?? '';

if ($action == 'send') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = trim($data['message'] ?? '');
    $receiverId = isset($data['receiver_id']) ? intval($data['receiver_id']) : $adminId; // Nếu là khách, mặc định gửi cho admin
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Tin nhắn rỗng']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if ($stmt->execute([$userId, $receiverId, $message])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB']);
    }
    exit;
}

if ($action == 'fetch') {
    // Nếu là customer, lấy tin nhắn giữa customer và admin
    // Nếu là admin, lấy tin nhắn giữa admin và $otherUserId
    $otherUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $adminId;
    
    if ($role == 'customer') {
        $otherUserId = $adminId;
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Đánh dấu đã đọc
    $stmtRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmtRead->execute([$otherUserId, $userId]);
    
    echo json_encode(['success' => true, 'messages' => $messages, 'current_user' => $userId]);
    exit;
}

if ($action == 'fetch_conversations' && $role == 'admin') {
    // Lấy danh sách các khách hàng đã nhắn tin với admin (Sắp xếp theo tin nhắn mới nhất)
    $stmt = $conn->prepare("
        SELECT u.id, u.full_name, u.email, MAX(m.created_at) as last_msg_time,
               SUM(CASE WHEN m.is_read = 0 AND m.receiver_id = ? THEN 1 ELSE 0 END) as unread_count
        FROM users u
        JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
        WHERE u.id != ?
        GROUP BY u.id
        ORDER BY last_msg_time DESC
    ");
    $stmt->execute([$userId, $userId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'conversations' => $conversations]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
