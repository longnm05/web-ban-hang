<?php
// 1. Nhúng file kết nối database
require_once 'db_connect.php';

// 2. Kiểm tra xem form đã được gửi lên bằng phương thức POST chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Lấy dữ liệu từ form và dọn dẹp khoảng trắng thừa bằng hàm trim()
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $gender    = $_POST['gender'];
    $address   = trim($_POST['address']);

    // 3. Băm (Mã hóa) mật khẩu - Đây là tiêu chuẩn bảo mật bắt buộc!
    // Tuyệt đối không lưu mật khẩu trần (plain text) vào database
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 4. Chuẩn bị câu lệnh SQL (Dùng Prepared Statement để chống hacker tiêm mã độc SQL Injection)
        $sql = "INSERT INTO Customer (full_name, username, email, password_hash, gender, address) 
                VALUES (:full_name, :username, :email, :password_hash, :gender, :address)";
        
        $stmt = $conn->prepare($sql);

        // 5. Gắn dữ liệu thực tế vào câu lệnh SQL
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);

        // 6. Thực thi câu lệnh
        if ($stmt->execute()) {
            // Nếu thành công, hiện thông báo và đẩy về trang đăng nhập
            echo "<script>
                    alert('Khởi tạo hồ sơ thành công! Vui lòng đăng nhập.');
                    window.location.href = 'login.html';
                  </script>";
        }
        
    } catch (PDOException $e) {
        // 7. Xử lý lỗi (Đặc biệt là lỗi trùng lặp Username hoặc Email)
        // Mã lỗi 23000 trong MySQL nghĩa là "Duplicate entry" (Bị trùng dữ liệu UNIQUE)
        if ($e->getCode() == 23000) {
            echo "<script>
                    alert('Lỗi: Tên đăng nhập hoặc Email này đã có người sử dụng!');
                    window.history.back(); // Đẩy người dùng quay lại trang trước
                  </script>";
        } else {
            // Lỗi hệ thống khác
            echo "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>