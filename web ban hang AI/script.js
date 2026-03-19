function solveSimpleChallenge() {
    console.log("Challenge Solved!");
    return true;
}
window.solveSimpleChallenge = solveSimpleChallenge;

document.addEventListener('DOMContentLoaded', () => {

    // 1. Hover effect for Product Cards (Glow follows cursor)
    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const glow = card.querySelector('.card-glow');
            glow.style.left = `${x - 75}px`; // 75 = half of glow width (150)
            glow.style.top = `${y - 75}px`;  // 75 = half of glow height
        });
    });

    // 2. Chatbot Toggle Logic
    const aiBotBtn = document.getElementById('aiBotBtn');
    const chatWindow = document.getElementById('chatWindow');
    const closeChat = document.getElementById('closeChat');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatBody = document.getElementById('chatBody');

    // Mở / Đóng Chatbot
    aiBotBtn.addEventListener('click', () => {
        chatWindow.classList.toggle('active');
        if (chatWindow.classList.contains('active')) {
            chatInput.focus();
        }
    });

    closeChat.addEventListener('click', () => {
        chatWindow.classList.remove('active');
    });

    // Xử lý logic gửi tin nhắn
    const sendMessage = () => {
        const text = chatInput.value.trim();
        if (!text) return;

        // Xóa nội dung input
        chatInput.value = '';

        // Tạo tin nhắn của user
        const userMsg = document.createElement('div');
        userMsg.className = 'message user-message';
        userMsg.textContent = text;
        chatBody.appendChild(userMsg);

        // Cuộn xuống cuối
        chatBody.scrollTop = chatBody.scrollHeight;

        // Giả lập AI trả lời sau 1 giây với hệ thống phân tích ý định (Intent Recognition)
        setTimeout(() => {
            const aiMsg = document.createElement('div');
            aiMsg.className = 'message ai-message';

            const lowerText = text.toLowerCase();

            // Xây dựng kho từ khóa (Intent Dictionary)
            const intents = {
                greeting: ['chào', 'hello', 'hi', 'xin chào', 'hé lô'],
                clothing: ['quần', 'áo', 'giày', 'mũ', 'váy', 'túi', 'sneaker', 'jacket', 'đồ'],
                pricing: ['giá', 'nhiêu', 'bao nhiêu', 'tiền', 'mắc', 'rẻ', 'sale', 'giảm giá', 'khuyến mãi'],
                shipping: ['giao', 'ship', 'nhận hàng', 'bao lâu', 'vận chuyển', 'phí'],
                sizing: ['size', 'kích cỡ', 'mặc vừa', 'bao nhiêu kg', 'chiều cao', 'rộng', 'chật', 'mập', 'ốm'],
                thanks: ['cảm ơn', 'thank', 'tuyệt vời', 'ok', 'được rồi', 'dạ']
            };

            // Hàm kiểm tra xem câu có chứa từ khóa nào thuộc intent không
            const hasIntent = (intentKeys) => intentKeys.some(kw => lowerText.includes(kw));

            // Xử lý Logic AI hiểu ngôn ngữ
            if (hasIntent(intents.greeting)) {
                aiMsg.innerHTML = `Xin chào! 👋 Tôi là trợ lý ảo của NovaStyle. Bạn cần tìm trang phục tự mặc hay để làm quà tặng?`;
            }
            else if (hasIntent(intents.pricing)) {
                aiMsg.innerHTML = `💰 Hiện tại đang có sự kiện Flash Sale! Các sản phẩm đang được giảm giá từ 20% đến 50%. Bạn có muốn tham khảo danh mục Khuyến mãi thay vì giá niêm yết không?`;
            }
            else if (hasIntent(intents.sizing)) {
                aiMsg.innerHTML = `📏 Đừng lo về kích cỡ! NovaStyle có hệ thống Virtual Try-on (Thử đồ ảo). Bạn có thể cho tôi biết chiều cao và cân nặng để AI của chúng tôi gợi ý chuẩn size (S, M, L, XL) cho bạn nhé.`;
            }
            else if (hasIntent(intents.shipping)) {
                aiMsg.innerHTML = `🚚 Về vấn đề giao hàng: Chúng tôi miễn phí vận chuyển (freeship) toàn quốc cho mọi đơn hàng trên 500.000đ. Nhận hàng sau 1-2 ngày đối với trung tâm và 3-4 ngày với tuyến huyện.`;
            }
            else if (hasIntent(intents.thanks)) {
                aiMsg.innerHTML = `Không có chi! 😊 Rất hân hạnh được hỗ trợ bạn. Chúc bạn một ngày mua sắm tuyệt vời tại NovaStyle!`;
            }
            else if (hasIntent(intents.clothing)) {
                aiMsg.innerHTML = `✨ Cảm ơn bạn, tôi đã phân tích yêu cầu <strong>"${text}"</strong>. Tôi đang tạo một danh sách các lựa chọn có phong cách tương thích với bạn, tỷ lệ match lên tới 98%. Xin vui lòng đợi một chút...`;

                // Demo hành động tạo UI danh sách
                setTimeout(() => {
                    const aiMsg2 = document.createElement('div');
                    aiMsg2.className = 'message ai-message';
                    aiMsg2.innerHTML = `👕 <a href="#" style="color:var(--accent-blue); text-decoration:none; font-weight:600;">Xem bộ sưu tập AI đã lọc riêng cho bạn.</a>`;
                    chatBody.appendChild(aiMsg2);
                    chatBody.scrollTop = chatBody.scrollHeight;
                }, 1500);
            }
            else {
                aiMsg.innerHTML = `Xin lỗi, tôi chưa hiểu chính xác ý của bạn. Bạn có thể cung cấp thêm một vài chi tiết, chẳng hạn bạn muốn tìm phong cách thế nào (ví dụ: streetwear, minimalism), hoặc hỏi tôi cách phối đồ không?`;
            }

            chatBody.appendChild(aiMsg);
            chatBody.scrollTop = chatBody.scrollHeight;
        }, 1000);
    };

    // Bắt sự kiện click nút gửi hoặc phím Enter
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // 3. Hiệu ứng Parallax nhỏ cho Navigation khi cuộn
    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.glass-header');
        if (window.scrollY > 50) {
            nav.style.boxShadow = 'var(--glass-shadow)';
            nav.style.background = 'rgba(10, 10, 12, 0.7)';
        } else {
            nav.style.boxShadow = 'none';
            nav.style.background = 'var(--glass-bg)';
        }
    });

    // 4. Tìm Kiếm Tiêu Chuẩn (Standard Search)
    const magicBtn = document.querySelector('.ai-btn');
    const searchInput = document.querySelector('.smart-search input');
    const productCards = document.querySelectorAll('.product-card');

    const performSearch = () => {
        const query = searchInput.value.toLowerCase().trim();

        // Cuộn xuống để dễ nhìn kết quả
        document.querySelector('.ai-curated').scrollIntoView({ behavior: 'smooth', block: 'start' });

        let foundCount = 0;
        productCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const category = card.querySelector('.category').textContent.toLowerCase();

            if (title.includes(query) || category.includes(query)) {
                card.style.display = 'block';
                foundCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (foundCount === 0 && query !== '') {
            alert("Không tìm thấy sản phẩm nào khớp với từ khóa của bạn. Đang hiển thị lại tất cả.");
            productCards.forEach(card => card.style.display = 'block');
            searchInput.value = '';
        }
    };

    // Chuột click vào nút Search
    magicBtn.addEventListener('click', (e) => {
        e.preventDefault();
        performSearch();
    });

    // Nhấn Enter trên thanh input
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    // Tự động tìm ngay khi khách hàng đang gõ chữ (Live Filtering phổ biến)
    searchInput.addEventListener('input', performSearch);

    // 5. Auth Modal Logic (Login/Register)
    const loginModal = document.getElementById('loginModal');
    const openLoginBtn = document.getElementById('openLoginBtn');
    const closeLoginBtn = document.getElementById('closeLoginBtn');

    // Toggle Sections
    const loginSection = document.getElementById('loginSection');
    const registerSection = document.getElementById('registerSection');
    const showRegisterLink = document.getElementById('showRegister');
    const showLoginLink = document.getElementById('showLogin');

    openLoginBtn.addEventListener('click', (e) => {
        e.preventDefault();
        loginModal.classList.add('active');
        // Luôn hiển thị Đăng nhập làm mặc định khi mở lại Modal
        loginSection.style.display = 'block';
        registerSection.style.display = 'none';
    });

    closeLoginBtn.addEventListener('click', () => {
        loginModal.classList.remove('active');
    });

    // Chuyển sang form Đăng Ký
    showRegisterLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginSection.style.display = 'none';
        registerSection.style.display = 'block';
    });

    // Chuyển về form Đăng Nhập
    showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        registerSection.style.display = 'none';
        loginSection.style.display = 'block';
    });

    // 6. Cart Sidebar Logic
    const cartOverlay = document.getElementById('cartOverlay');
    const cartSidebar = document.getElementById('cartSidebar');
    const openCartBtn = document.getElementById('openCartBtn');
    const closeCartBtn = document.getElementById('closeCartBtn');

    openCartBtn.addEventListener('click', (e) => {
        e.preventDefault();
        cartOverlay.classList.add('active');
        cartSidebar.classList.add('active');
    });

    const closeCart = () => {
        cartOverlay.classList.remove('active');
        cartSidebar.classList.remove('active');
    };

    closeCartBtn.addEventListener('click', closeCart);
    cartOverlay.addEventListener('click', closeCart);

    // 7. Cart Core Logic (Save/Load with JSON)
    let cart = [];

    // Khôi phục giỏ hàng từ LocalStorage
    try {
        const storedCart = localStorage.getItem('novaStyleCart');
        if (storedCart) cart = JSON.parse(storedCart);
    } catch (e) { console.error("Could not parse cart JSON", e); }

    const cartBadge = document.getElementById('cartBadge');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartTotal = document.getElementById('cartTotal');

    const saveCart = () => {
        localStorage.setItem('novaStyleCart', JSON.stringify(cart));
        renderCartUI();
    };

    const renderCartUI = () => {
        // Update badge
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartBadge.textContent = totalItems;

        // Render HTML
        cartItemsContainer.innerHTML = '';
        if (cart.length === 0) {
            cartEmpty.style.display = 'block';
            cartTotal.textContent = '$0.00';
            return;
        }

        cartEmpty.style.display = 'none';
        let totalPrice = 0;

        cart.forEach((item, index) => {
            totalPrice += item.price * item.quantity;
            const cartItemDiv = document.createElement('div');
            cartItemDiv.className = 'cart-item';
            cartItemDiv.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="item-info">
                    <h4 style="font-size:0.95rem; margin-bottom:5px;">${item.name}</h4>
                    <span class="price" style="font-size:1rem; color:var(--accent-blue);">$${item.price.toFixed(2)}</span>
                    <div class="qty-control">
                        <button class="qty-btn dec-btn" data-index="${index}">-</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn inc-btn" data-index="${index}">+</button>
                    </div>
                </div>
                <button class="remove-item" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
            `;
            cartItemsContainer.appendChild(cartItemDiv);
        });

        cartTotal.textContent = '$' + totalPrice.toFixed(2);

        // Bind events to generated buttons
        document.querySelectorAll('.inc-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = e.target.getAttribute('data-index');
                cart[idx].quantity += 1;
                saveCart();
            });
        });

        document.querySelectorAll('.dec-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = e.target.getAttribute('data-index');
                if (cart[idx].quantity > 1) {
                    cart[idx].quantity -= 1;
                } else {
                    cart.splice(idx, 1);
                }
                saveCart();
            });
        });

        document.querySelectorAll('.remove-item').forEach(btn => {
            let properBtn = btn;
            // Handle if they click the icon inside 
            if (btn.tagName.toLowerCase() === 'i') properBtn = btn.parentElement;
            properBtn.addEventListener('click', (e) => {
                const idx = properBtn.getAttribute('data-index');
                cart.splice(idx, 1);
                saveCart();
            });
        });
    };

    // Khởi tạo UI lần đầu
    renderCartUI();

    // Lắng nghe sự kiện thêm vào giỏ
    const addToCartBtns = document.querySelectorAll('.add-to-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Hiệu ứng UX nhẹ
            let targetBtn = e.target;
            if (targetBtn.tagName.toLowerCase() === 'i') targetBtn = targetBtn.parentElement;

            targetBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
            targetBtn.style.background = 'var(--accent-purple)';
            targetBtn.style.borderColor = 'transparent';
            setTimeout(() => {
                targetBtn.innerHTML = '<i class="fa-solid fa-cart-plus"></i>';
                targetBtn.style.background = 'rgba(255,255,255,0.05)';
                targetBtn.style.borderColor = 'var(--glass-border)';
            }, 800);

            // Tự động bật Sidebar khi mua (UX tuyệt hảo)
            cartOverlay.classList.add('active');
            cartSidebar.classList.add('active');

            // Fetch data-attributes
            const id = targetBtn.getAttribute('data-id');
            const name = targetBtn.getAttribute('data-name');
            const price = parseFloat(targetBtn.getAttribute('data-price'));
            const image = targetBtn.getAttribute('data-image');

            // Cập nhật cấu trúc mảng JSON
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id, name, price, image, quantity: 1 });
            }
            saveCart();
        });
    });

});
