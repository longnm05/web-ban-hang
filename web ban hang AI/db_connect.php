<?php
// Thông tin cấu hình database
$host = 'localhost';      // Thường là localhost nếu bạn dùng XAMPP/WAMP
$dbname = 'nova';         // Tên database bạn vừa tạo
$username = 'root';       // Tên đăng nhập MySQL (mặc định của XAMPP là root)
$password = '';           // Mật khẩu MySQL (mặc định của XAMPP là để trống)

try {
    // Khởi tạo kết nối PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Thiết lập chế độ báo lỗi để dễ dàng debug (tìm lỗi)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bạn có thể bỏ comment (xóa dấu //) ở dòng dưới để kiểm tra thử xem đã kết nối được chưa
    // echo "Kết nối Database Nova thành công!";
    
} catch(PDOException $e) {
    // Nếu lỗi, dừng chương trình và in ra thông báo
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>