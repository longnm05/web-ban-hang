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

    // 4. Tìm Kiếm & Lọc Tiêu Chuẩn (Standard Search & Filters)
    const magicBtn = document.querySelector('.ai-btn');
    const searchInput = document.querySelector('.smart-search input');
    const productCards = document.querySelectorAll('.product-card');
    const noProductsFound = document.getElementById('noProductsFound');
    let currentCategoryFilter = 'all';

    const performSearch = () => {
        const query = searchInput.value.toLowerCase().trim();
        let foundCount = 0;

        productCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const category = card.querySelector('.category').textContent.toLowerCase();

            const matchesSearch = title.includes(query) || category.includes(query);
            const matchesCategory = currentCategoryFilter === 'all' || category.includes(currentCategoryFilter);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
                foundCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noProductsFound) {
            if (foundCount === 0) {
                noProductsFound.style.display = 'block';
            } else {
                noProductsFound.style.display = 'none';
            }
        }
    };

    // Chuột click vào nút Search
    magicBtn.addEventListener('click', (e) => {
        e.preventDefault();
        performSearch();
        document.querySelector('.ai-curated').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Nhấn Enter trên thanh input
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
            document.querySelector('.ai-curated').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // Tự động tìm ngay khi khách hàng đang gõ chữ
    searchInput.addEventListener('input', performSearch);

    // Lọc theo danh mục (Category Filtering)
    const categoryFilters = document.querySelectorAll('.category-filter');
    categoryFilters.forEach(btn => {
        btn.addEventListener('click', () => {
            currentCategoryFilter = btn.getAttribute('data-category');
            searchInput.value = ''; // Reset search text when clicking category
            performSearch();
            document.querySelector('.ai-curated').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Quick View Logic
    const quickViewOverlay = document.getElementById('quickViewOverlay');
    if(quickViewOverlay) {
        const closeQuickViewBtn = document.getElementById('closeQuickView');
        const qvImage = document.getElementById('qvImage');
        const qvTitle = document.getElementById('qvTitle');
        const qvCategory = document.getElementById('qvCategory');
        const qvPrice = document.getElementById('qvPrice');
        const qvAddToCart = document.getElementById('qvAddToCart');
        const qvQty = document.getElementById('qvQty');

        document.querySelectorAll('.quick-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.target.closest('.product-card');
                const imgUrl = card.querySelector('.card-image img').src;
                const title = card.querySelector('h3').textContent;
                const category = card.querySelector('.category').textContent;
                const price = card.querySelector('.price').textContent;
                const addToCartBtn = card.querySelector('.add-to-cart');
                
                qvImage.src = imgUrl;
                qvTitle.textContent = title;
                qvCategory.textContent = category;
                qvPrice.textContent = price;
                qvQty.value = 1; // Reset quantity
                
                qvAddToCart.setAttribute('data-id', addToCartBtn.getAttribute('data-id'));
                qvAddToCart.setAttribute('data-name', addToCartBtn.getAttribute('data-name'));
                qvAddToCart.setAttribute('data-price', addToCartBtn.getAttribute('data-price'));
                qvAddToCart.setAttribute('data-image', addToCartBtn.getAttribute('data-image'));

                quickViewOverlay.classList.add('active');
            });
        });

        closeQuickViewBtn.addEventListener('click', () => {
            quickViewOverlay.classList.remove('active');
        });
        quickViewOverlay.addEventListener('click', (e) => {
            if(e.target === quickViewOverlay) quickViewOverlay.classList.remove('active');
        });

        // Add to cart inside Quick View
        qvAddToCart.addEventListener('click', () => {
            const id = qvAddToCart.getAttribute('data-id');
            const name = qvAddToCart.getAttribute('data-name');
            const price = parseFloat(qvAddToCart.getAttribute('data-price'));
            const image = qvAddToCart.getAttribute('data-image');
            const qty = parseInt(qvQty.value) || 1;

            qvAddToCart.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm';
            qvAddToCart.style.background = 'var(--accent-purple)';
            setTimeout(() => {
                qvAddToCart.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Thêm Vào Giỏ Hàng';
                qvAddToCart.style.background = 'var(--primary-gradient)';
                quickViewOverlay.classList.remove('active');
                cartOverlay.classList.add('active');
                cartSidebar.classList.add('active');
            }, 800);

            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                cart.push({ id, name, price, image, quantity: qty });
            }
            saveCart();
        });
    }

    // 5. Removed Auth Modal Logic (Moved to login.php)

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

    // 8. Checkout Logic
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Giỏ hàng của bạn đang trống!');
                return;
            }
            
            const originalText = checkoutBtn.innerHTML;
            checkoutBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            checkoutBtn.disabled = true;

            fetch('checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thanh toán thành công! Mã đơn hàng của bạn là: #' + data.order_id);
                    // Xóa giỏ hàng sau khi thanh toán thành công
                    cart = [];
                    saveCart();
                    // Chuyển hướng tới trang hóa đơn hoặc lịch sử
                    window.location.href = 'invoice.php?id=' + data.order_id;
                } else {
                    alert('Lỗi: ' + data.message);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại!');
            })
            .finally(() => {
                checkoutBtn.innerHTML = originalText;
                checkoutBtn.disabled = false;
            });
        });
    }

});
