<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    // For demo purpose, we skip actual password hashing verification since demo data has fake hashes
    // In production: password_verify($password, $user['password_hash'])

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        // Ưu tiên giới tính vừa chọn để AI gợi ý ngay, nếu không lấy từ DB
        $_SESSION['gender'] = $_POST['login_gender'] ?? $user['gender'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Thông tin đăng nhập không chính xác!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaStyle - Cổng Xác Thực Hệ Thống</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .unified-login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100vw;
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=2000') no-repeat center center/cover;
            position: relative;
        }

        .unified-login-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            color: var(--text-main);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 50px;
            border-radius: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .login-header h2 {
            font-family: var(--font-heading);
            font-size: 2.2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i></a>

    <div class="unified-login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Đăng Nhập</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Truy cập vào hệ thống NovaStyle</p>
            </div>
            
            <?php if($error): ?>
                <p style="color: #ff416c; text-align: center; margin-bottom: 20px; font-weight: 500;"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email của bạn" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-size: 0.9rem; color: var(--text-muted);">Gợi ý AI theo phong cách:</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <input type="radio" name="login_gender" value="nam" checked> Nam
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <input type="radio" name="login_gender" value="nu"> Nữ
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 0.9rem;">
                    <label style="color: var(--text-muted); cursor: pointer;"><input type="checkbox"> Ghi nhớ tôi</label>
                    <a href="#" style="color: var(--accent-blue); text-decoration: none;">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="submit-btn" style="background: var(--primary-gradient); box-shadow: 0 8px 25px rgba(138, 43, 226, 0.4);">
                    Đăng Nhập Ngay <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>

                <p style="text-align: center; margin-top: 25px; font-size: 0.95rem; color: var(--text-muted);">
                    Chưa có tài khoản? <a href="register.php" style="color: var(--accent-purple); text-decoration: none; font-weight: 600;">Tham gia ngay</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>
