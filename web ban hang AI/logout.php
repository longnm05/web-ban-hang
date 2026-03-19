<?php
session_start();
session_unset();    // Xóa toàn bộ biến session
session_destroy();  // Phá hủy session hiện tại

// Chuyển hướng về lại trang chủ
header("Location: index.php");
exit();
?>