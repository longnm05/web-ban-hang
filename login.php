<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $email = $_POST['email'];
    // For demo purpose, we skip actual password hashing verification since demo data has fake hashes
    // In production: password_verify($password, $user['password_hash'])

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        // Ưu tiên giới tính vừa chọn để AI gợi ý ngay, nếu không lấy từ DB
        $_SESSION['gender'] = $_POST['login_gender'] ?? $user['gender'];

        if ($role == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php"); // or profile.php
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
        .split-login-container {
            display: flex;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        .login-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            transition: var(--transition-smooth);
        }

        .login-side::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover;
            background-position: center;
            filter: brightness(0.4);
            z-index: -1;
            transition: var(--transition-smooth);
        }

        .customer-side::before {
            background-image: url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=2000');
        }

        .admin-side::before {
            background-image: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=2000');
        }

        .login-side:hover::before {
            filter: brightness(0.6);
            transform: scale(1.05);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-main);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            transform: translateY(0);
            transition: var(--transition-smooth);
        }

        .login-side:hover .login-card {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        }

        .customer-side .login-card {
            border-color: rgba(138, 43, 226, 0.3);
        }

        .admin-side .login-card {
            border-color: rgba(255, 65, 108, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-family: var(--font-heading);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .customer-side .login-header h2 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-side .login-header h2 {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--text-main);
            font-family: var(--font-body);
            transition: var(--transition-smooth);
        }

        .customer-side .form-group input:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.2);
            outline: none;
        }

        .admin-side .form-group input:focus {
            border-color: #ff416c;
            box-shadow: 0 0 10px rgba(255, 65, 108, 0.2);
            outline: none;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            cursor: pointer;
            transition: var(--transition-smooth);
            margin-top: 10px;
        }

        .customer-side .submit-btn {
            background: var(--primary-gradient);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        }

        .customer-side .submit-btn:hover {
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.5);
            transform: translateY(-2px);
        }

        .admin-side .submit-btn {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
        }

        .admin-side .submit-btn:hover {
            box-shadow: 0 8px 25px rgba(255, 65, 108, 0.5);
            transform: translateY(-2px);
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            z-index: 10;
            background: rgba(0,0,0,0.5);
            padding: 10px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
            transition: var(--transition-smooth);
        }

        .back-home:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .split-login-container {
                flex-direction: column;
            }
            .login-side {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i></a>

    <div class="split-login-container">
        <!-- Customer Login -->
        <div class="login-side customer-side">
            <div class="login-card">
                <div class="login-header">
                    <h2>Khách Hàng</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Truy cập không gian mua sắm cá nhân hóa</p>
                </div>
                <?php if($error && isset($_POST['role']) && $_POST['role'] == 'customer'): ?>
                    <p style="color: red; text-align: center; margin-bottom: 10px;"><?= $error ?></p>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <input type="hidden" name="role" value="customer">
                    <div class="form-group">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="email" placeholder="Email (VD: guest@novastyle.com)" required>
                    </div>
                    <div class="form-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Mật khẩu (bất kỳ)" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 10px; font-size: 0.9rem; color: var(--text-muted);">Bạn muốn AI gợi ý sản phẩm cho:</label>
                        <div style="display: flex; gap: 20px;">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="login_gender" value="nam" checked> Nam
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="login_gender" value="nu"> Nữ
                            </label>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 0.85rem;">
                        <label style="color: var(--text-muted);"><input type="checkbox"> Ghi nhớ</label>
                        <a href="#" style="color: var(--accent-blue); text-decoration: none;">Quên mật khẩu?</a>
                    </div>
                    <button type="submit" class="submit-btn">Đăng Nhập <i class="fa-solid fa-right-to-bracket"></i></button>
                    <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
                        Chưa có tài khoản? <a href="register.php" style="color: var(--accent-purple); text-decoration: none; font-weight: 600;">Đăng ký ngay</a>
                    </p>
                </form>
            </div>
        </div>

        <!-- Admin Login -->
        <div class="login-side admin-side">
            <div class="login-card">
                <div class="login-header">
                    <h2>Ban Quản Trị</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Cổng kiểm soát hệ thống bán hàng</p>
                </div>
                <?php if($error && isset($_POST['role']) && $_POST['role'] == 'admin'): ?>
                    <p style="color: red; text-align: center; margin-bottom: 10px;"><?= $error ?></p>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <input type="hidden" name="role" value="admin">
                    <div class="form-group">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input type="email" name="email" placeholder="Email Admin (VD: admin@novastyle.com)" required>
                    </div>
                    <div class="form-group">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" name="password" placeholder="Mật khẩu (bất kỳ)" required>
                    </div>
                    <div class="form-group">
                        <i class="fa-solid fa-fingerprint"></i>
                        <input type="text" placeholder="Mã xác thực 2FA (bỏ trống)" >
                    </div>
                    <button type="submit" class="submit-btn">Truy Cập Hệ Thống <i class="fa-solid fa-unlock-keyhole"></i></button>
                    <p style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #ff4b2b;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Mọi truy cập trái phép sẽ bị ghi log.
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
