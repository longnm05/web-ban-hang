<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thủ Tục Thanh Toán - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--bg-light);
            padding-top: 100px;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 5%;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
        }
        .checkout-form {
            flex: 2;
            min-width: 300px;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid var(--glass-border);
        }
        .checkout-summary {
            flex: 1;
            min-width: 300px;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid var(--glass-border);
            position: sticky;
            top: 120px;
            height: fit-content;
        }
        .section-title {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: var(--text-main);
            border-bottom: 2px solid var(--glass-border);
            padding-bottom: 10px;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
            font-size: 0.95rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            font-family: var(--font-body);
            font-size: 1rem;
            background: rgba(0,0,0,0.02);
            transition: var(--transition-smooth);
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--accent-purple);
            outline: none;
            box-shadow: 0 0 0 3px rgba(138,43,226,0.1);
        }
        /* Radio Box cho Phí ship và Thanh toán */
        .radio-box {
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition-smooth);
        }
        .radio-box:hover {
            background: rgba(0,0,0,0.02);
        }
        .radio-box input[type="radio"] {
            accent-color: var(--accent-purple);
            transform: scale(1.2);
        }
        .radio-box-content {
            flex: 1;
        }
        .radio-box-title {
            font-weight: 600;
            display: block;
            margin-bottom: 3px;
            color: var(--text-main);
        }
        .radio-box-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .radio-box-price {
            font-weight: 700;
            color: var(--accent-blue);
        }
        
        /* Summary Items */
        .summary-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--glass-border);
        }
        .summary-item img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
        }
        .summary-item-info h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: var(--text-muted);
        }
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-main);
            border-top: 2px solid var(--glass-border);
            padding-top: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <nav class="glass-header">
        <div class="logo">
            <a href="index.php" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-microchip"></i> NovaStyle</a>
        </div>
        <div style="font-weight: 600; color: var(--accent-purple); font-size: 1.2rem;">
            <i class="fa-solid fa-lock"></i> Thanh Toán An Toàn
        </div>
        <div class="nav-links">
            <a href="profile.php" class="nav-icon"><i class="fa-solid fa-user"></i></a>
        </div>
    </nav>

    <div class="checkout-container">
        <!-- Form Thông Tin -->
        <div class="checkout-form">
            <h2 class="section-title"><i class="fa-solid fa-location-dot"></i> 1. Vị trí giao hàng</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Họ và Tên người nhận *</label>
                    <input type="text" id="chkName" placeholder="Ví dụ: Nguyễn Văn A" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="text" id="chkPhone" placeholder="Ví dụ: 0912345678" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Địa chỉ giao hàng chi tiết (Số nhà, đường, phường/xã, quận/huyện, tỉnh/TP) *</label>
                <input type="text" id="chkAddress" placeholder="Ví dụ: 123 Đường Trần Hưng Đạo, Quận 1, TP. Hồ Chí Minh" required>
            </div>
            <div class="form-group" style="margin-bottom: 40px;">
                <label>Ghi chú đơn hàng (Tùy chọn)</label>
                <input type="text" id="chkNote" placeholder="Ví dụ: Giao hàng vào giờ hành chính...">
            </div>

            <h2 class="section-title"><i class="fa-solid fa-truck-fast"></i> 2. Phương thức vận chuyển</h2>
            <label class="radio-box">
                <input type="radio" name="shippingMethod" value="0" checked onchange="calculateTotal()">
                <div class="radio-box-content">
                    <span class="radio-box-title">Giao hàng Tiêu chuẩn (Standard)</span>
                    <span class="radio-box-desc">Dự kiến giao hàng trong 3-5 ngày làm việc</span>
                </div>
                <span class="radio-box-price">Miễn phí</span>
            </label>
            <label class="radio-box">
                <input type="radio" name="shippingMethod" value="15" onchange="calculateTotal()">
                <div class="radio-box-content">
                    <span class="radio-box-title">Giao hàng Hỏa tốc (Express)</span>
                    <span class="radio-box-desc">Dự kiến giao hàng trong 24h</span>
                </div>
                <span class="radio-box-price">$15.00</span>
            </label>

            <h2 class="section-title" style="margin-top: 40px;"><i class="fa-solid fa-credit-card"></i> 3. Phương thức thanh toán</h2>
            <label class="radio-box">
                <input type="radio" name="paymentMethod" value="COD" checked>
                <div class="radio-box-content">
                    <span class="radio-box-title">Thanh toán khi nhận hàng (COD)</span>
                    <span class="radio-box-desc">Khách hàng thanh toán bằng tiền mặt khi bưu tá giao hàng tới</span>
                </div>
                <i class="fa-solid fa-money-bill-wave" style="font-size: 1.5rem; color: #00ff88;"></i>
            </label>
            <label class="radio-box">
                <input type="radio" name="paymentMethod" value="Chuyển khoản Ngân hàng">
                <div class="radio-box-content">
                    <span class="radio-box-title">Chuyển khoản Ngân hàng</span>
                    <span class="radio-box-desc">Chuyển khoản trực tiếp qua Internet Banking hoặc mã QR</span>
                </div>
                <i class="fa-solid fa-building-columns" style="font-size: 1.5rem; color: var(--accent-blue);"></i>
            </label>
        </div>

        <!-- Tóm tắt Đơn Hàng -->
        <div class="checkout-summary">
            <h2 class="section-title">Tóm tắt đơn hàng</h2>
            <div id="checkoutItems">
                <!-- Javascript will inject cart items here -->
            </div>
            
            <div class="summary-row" style="margin-top: 30px;">
                <span>Tạm tính:</span>
                <span id="sumSubtotal" style="color: var(--text-main); font-weight: 600;">$0.00</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span id="sumShipping" style="color: var(--text-main); font-weight: 600;">$0.00</span>
            </div>
            <div class="summary-row total">
                <span>Tổng Cộng:</span>
                <span id="sumTotal" class="gradient-text">$0.00</span>
            </div>

            <button class="btn btn-primary w-100 btn-glow" id="confirmOrderBtn" style="margin-top: 20px; font-size: 1.1rem; padding: 18px;">
                <i class="fa-solid fa-check"></i> XÁC NHẬN ĐẶT HÀNG
            </button>
        </div>
    </div>

    <script>
        let cart = [];
        let subtotal = 0;

        document.addEventListener('DOMContentLoaded', () => {
            // Load cart
            try {
                const storedCart = localStorage.getItem('novaStyleCart');
                if (storedCart) cart = JSON.parse(storedCart);
            } catch (e) {}

            if (cart.length === 0) {
                alert("Giỏ hàng của bạn đang trống. Vui lòng quay lại mua sắm.");
                window.location.href = 'products.php';
                return;
            }

            renderCheckoutItems();
            calculateTotal();
        });

        function renderCheckoutItems() {
            const container = document.getElementById('checkoutItems');
            container.innerHTML = '';
            subtotal = 0;

            cart.forEach(item => {
                subtotal += item.price * item.quantity;
                container.innerHTML += `
                    <div class="summary-item">
                        <img src="${item.image}" alt="${item.name}">
                        <div class="summary-item-info" style="flex:1;">
                            <h4>${item.name}</h4>
                            <div style="display:flex; justify-content:space-between; margin-top:10px; font-size:0.9rem;">
                                <span>SL: <strong>${item.quantity}</strong></span>
                                <span style="font-weight:600; color:var(--accent-blue);">$${(item.price * item.quantity).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('sumSubtotal').innerText = '$' + subtotal.toFixed(2);
        }

        function calculateTotal() {
            let shippingFee = 0;
            const shippingRadios = document.getElementsByName('shippingMethod');
            for (let i = 0; i < shippingRadios.length; i++) {
                if (shippingRadios[i].checked) {
                    shippingFee = parseFloat(shippingRadios[i].value);
                    break;
                }
            }

            document.getElementById('sumShipping').innerText = shippingFee === 0 ? 'Miễn phí' : '$' + shippingFee.toFixed(2);
            document.getElementById('sumTotal').innerText = '$' + (subtotal + shippingFee).toFixed(2);
        }

        document.getElementById('confirmOrderBtn').addEventListener('click', () => {
            const name = document.getElementById('chkName').value.trim();
            const phone = document.getElementById('chkPhone').value.trim();
            const address = document.getElementById('chkAddress').value.trim();
            const note = document.getElementById('chkNote').value.trim();

            if(!name || !phone || !address) {
                alert("Vui lòng điền đầy đủ các thông tin bắt buộc (có dấu *)");
                return;
            }

            let shippingFee = 0;
            document.getElementsByName('shippingMethod').forEach(r => { if(r.checked) shippingFee = r.value; });

            let paymentMethod = 'COD';
            document.getElementsByName('paymentMethod').forEach(r => { if(r.checked) paymentMethod = r.value; });

            const btn = document.getElementById('confirmOrderBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ĐANG XỬ LÝ...';
            btn.disabled = true;

            const fullAddress = address + (note ? ` (Ghi chú: ${note})` : '');

            const payload = {
                cart: cart,
                customer_name: name,
                customer_phone: phone,
                shipping_address: fullAddress,
                shipping_fee: shippingFee,
                payment_method: paymentMethod
            };

            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Đặt hàng thành công! Mã đơn: #' + data.order_id);
                    localStorage.removeItem('novaStyleCart'); // Xóa giỏ hàng
                    window.location.href = 'invoice.php?id=' + data.order_id;
                } else {
                    alert('Lỗi: ' + data.message);
                    if(data.redirect) window.location.href = data.redirect;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> XÁC NHẬN ĐẶT HÀNG';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi mạng xảy ra, vui lòng thử lại.');
                btn.innerHTML = '<i class="fa-solid fa-check"></i> XÁC NHẬN ĐẶT HÀNG';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
