<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// Lấy dữ liệu thô
$users = $conn->query("SELECT * FROM users")->fetchAll();
$orders = $conn->query("SELECT * FROM orders")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>DEBUG DATABASE</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 50px; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        h1 { color: red; }
    </style>
</head>
<body>
    <h1>CHẾ ĐỘ KIỂM TRA DỮ LIỆU (DEBUG MODE)</h1>
    
    <h2>1. DANH SÁCH TÀI KHOẢN (Đang có: <?= count($users) ?>)</h2>
    <table>
        <tr><th>ID</th><th>Email</th><th>Role</th></tr>
        <?php foreach($users as $u): ?>
        <tr><td><?= $u['id'] ?></td><td><?= $u['email'] ?></td><td><?= $u['role'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>2. DANH SÁCH ĐƠN HÀNG (Đang có: <?= count($orders) ?>)</h2>
    <table>
        <tr><th>ID</th><th>User ID</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
        <?php foreach($orders as $o): ?>
        <tr><td><?= $o['id'] ?></td><td><?= $o['user_id'] ?></td><td><?= $o['total_amount'] ?></td><td><?= $o['status'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <p><a href="admin.php?restore=1">Quay lại giao diện chính</a></p>
</body>
</html>
