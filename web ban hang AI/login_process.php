<?php
// Bắt buộc phải có dòng này ở đầu file để sử dụng Session (lưu trạng thái đăng nhập)
session_start();

// Nhúng file kết nối database
require_once 'db_connect.php';

// Kiểm tra xem dữ liệu có được gửi bằng phương thức POST không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Lấy dữ liệu từ form
    $login_id = trim($_POST['login_id']); // Có thể là username hoặc email
    $password = $_POST['password'];

    try {
        // 1. Tìm người dùng trong cơ sở dữ liệu dựa trên username HOẶC email
        $sql = "SELECT * FROM Customer WHERE username = :login_id OR email = :login_id";
        $stmt = $conn->prepare($sql);
        
        // Gắn dữ liệu vào câu lệnh SQL
        $stmt->bindParam(':login_id', $login_id);
        $stmt->execute();

        // Lấy thông tin người dùng tìm được (nếu có)
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Kiểm tra xem user có tồn tại và mật khẩu có khớp không
        // Hàm password_verify sẽ tự động so sánh mật khẩu trần với mã hash trong DB
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Đăng nhập thành công: Lưu thông tin vào Session để dùng ở các trang khác
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];

            // Hiện thông báo chào mừng và chuyển hướng về trang chủ
            echo "<script>
                    alert('Đăng nhập thành công! Chào mừng " . $user['full_name'] . "');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            // Đăng nhập thất bại: Sai tài khoản hoặc mật khẩu
            echo "<script>
                    alert('Lỗi: Tên đăng nhập/Email hoặc mật khẩu không chính xác!');
                    window.history.back(); // Đẩy người dùng quay lại trang đăng nhập
                  </script>";
        }
        
    } catch (PDOException $e) {
        // Bắt lỗi hệ thống
        echo "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>