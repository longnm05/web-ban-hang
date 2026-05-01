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
        $_SESSION['gender'] = $user['gender'];

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
            background: #0f0f12;
            position: relative;
            overflow: hidden;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 50px;
            border-radius: 35px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.5);
            position: relative;
            z-index: 10;
        }

        .login-header h2 {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .form-group i {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #4facfe;
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.3);
        }

        .submit-btn {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%) !important;
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.4) !important;
        }

        .submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.6) !important;
        }

        .orb {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 1;
            animation: orbMove 20s infinite alternate;
        }
        @keyframes orbMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(100px, 100px); }
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i></a>

    <div class="unified-login-container">
        <!-- Animated Background -->
        <div class="orb" style="background: rgba(79, 172, 254, 0.2); top: -200px; left: -100px;"></div>
        <div class="orb" style="background: rgba(138, 43, 226, 0.2); bottom: -100px; right: -100px; animation-delay: -5s;"></div>
        <div class="orb" style="background: rgba(255, 65, 108, 0.15); top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; filter: blur(120px); animation: none;"></div>

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
                
                <p style="text-align: right; margin-top: -10px; margin-bottom: 25px;">
                    <a href="#" style="color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem;">Quên mật khẩu?</a>
                </p>

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
