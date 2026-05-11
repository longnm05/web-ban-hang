<?php
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
?>
<style>
    .chat-widget-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 1.8rem;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(255, 65, 108, 0.4);
        z-index: 1000;
        transition: 0.3s;
    }
    .chat-widget-btn:hover {
        transform: scale(1.1);
    }
    .chat-window {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px);
        transition: 0.3s;
        border: 1px solid rgba(0,0,0,0.1);
        font-family: 'Inter', sans-serif;
    }
    .chat-window.active {
        opacity: 1;
        pointer-events: all;
        transform: translateY(0);
    }
    .chat-header {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chat-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f9f9f9;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .chat-input-area {
        padding: 15px;
        border-top: 1px solid rgba(0,0,0,0.1);
        display: flex;
        gap: 10px;
        background: white;
    }
    .chat-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 20px;
        outline: none;
        font-family: 'Inter', sans-serif;
    }
    .chat-send-btn {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
    }
    .msg-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 15px;
        line-height: 1.4;
        font-size: 0.95rem;
    }
    .msg-me {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 5px;
    }
    .msg-them {
        background: white;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 5px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .chat-login-prompt {
        text-align: center;
        padding: 30px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #666;
    }
</style>

<div class="chat-widget-btn" onclick="toggleChatWindow()">
    <i class="fa-solid fa-comment-dots"></i>
</div>

<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <div style="font-weight: 600;"><i class="fa-solid fa-headset"></i> Hỗ Trợ Trực Tuyến</div>
        <i class="fa-solid fa-times" style="cursor: pointer;" onclick="toggleChatWindow()"></i>
    </div>
    
    <?php if ($isLoggedIn == 'true'): ?>
    <div class="chat-body" id="customerChatBody">
        <div class="msg-bubble msg-them">Xin chào! Tôi là quản lý của NovaStyle. Bạn cần hỗ trợ thông tin gì ạ?</div>
    </div>
    <div class="chat-input-area">
        <input type="text" class="chat-input" id="customerChatInput" placeholder="Nhập tin nhắn...">
        <button class="chat-send-btn" onclick="sendCustomerMsg()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
    <script>
        function toggleChatWindow() {
            document.getElementById('chatWindow').classList.toggle('active');
            if(document.getElementById('chatWindow').classList.contains('active')) {
                loadCustomerChat();
            }
        }

        function loadCustomerChat() {
            fetch('chat_api.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if(data.success && data.messages.length > 0) {
                    const body = document.getElementById('customerChatBody');
                    body.innerHTML = '';
                    data.messages.forEach(m => {
                        const isMe = m.sender_id == data.current_user;
                        body.innerHTML += `
                            <div class="msg-bubble ${isMe ? 'msg-me' : 'msg-them'}">
                                ${m.message}
                            </div>
                        `;
                    });
                    body.scrollTop = body.scrollHeight;
                }
            });
        }

        function sendCustomerMsg() {
            const input = document.getElementById('customerChatInput');
            const msg = input.value.trim();
            if(!msg) return;
            
            input.value = '';
            const body = document.getElementById('customerChatBody');
            body.innerHTML += `<div class="msg-bubble msg-me">${msg}</div>`;
            body.scrollTop = body.scrollHeight;

            fetch('chat_api.php?action=send', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message: msg}) 
            }).then(() => loadCustomerChat());
        }

        document.getElementById('customerChatInput')?.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendCustomerMsg();
        });

        setInterval(() => {
            if(document.getElementById('chatWindow').classList.contains('active')) {
                loadCustomerChat();
            }
        }, 3000);
    </script>
    <?php else: ?>
    <div class="chat-login-prompt">
        <i class="fa-solid fa-lock" style="font-size: 3rem; color: #ddd;"></i>
        <p>Vui lòng đăng nhập để trò chuyện trực tiếp với chuyên viên tư vấn của chúng tôi.</p>
        <a href="login.php" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">Đăng Nhập Ngay</a>
    </div>
    <script>
        function toggleChatWindow() {
            document.getElementById('chatWindow').classList.toggle('active');
        }
    </script>
    <?php endif; ?>
</div>
