<!-- ==========================================================
     FLOATING CHATBOT BUBBLE WIDGET - STELLA ETALASE DIGITAL
     Modern WhatsApp / Intercom Style Assistant Widget
     ========================================================== -->

<style id="stellaChatbotStyles">
/* ----------------------------------------------------------
   CHATBOT MASTER WRAPPER
   ---------------------------------------------------------- */
.chatbot-wrapper {
    position: fixed !important;
    bottom: 24px !important;
    right: 24px !important;
    z-index: 9999999 !important;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    pointer-events: auto !important;
}

/* Row Wadah Tombol & Teaser */
.chatbot-launcher-row {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

/* Floating Teaser Pill Tooltip */
.chatbot-teaser-pill {
    background: #0f172a !important;
    color: #ffffff !important;
    padding: 8px 16px !important;
    border-radius: 999px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    transition: all 0.25s ease !important;
    white-space: nowrap !important;
    animation: teaserFloat 3s infinite ease-in-out !important;
    user-select: none !important;
}

.chatbot-teaser-pill:hover {
    background: #1e293b !important;
    transform: translateY(-2px) scale(1.02) !important;
    border-color: #ef4444 !important;
    box-shadow: 0 12px 28px rgba(239, 68, 68, 0.3) !important;
}

.chatbot-wrapper.open .chatbot-teaser-pill {
    display: none !important;
}

.teaser-dot {
    width: 8px !important;
    height: 8px !important;
    background: #10b981 !important;
    border-radius: 50% !important;
    box-shadow: 0 0 8px #10b981 !important;
}

@keyframes teaserFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

/* ----------------------------------------------------------
   FLOATING LAUNCHER CIRCLE BUTTON
   ---------------------------------------------------------- */
.chatbot-bubble-btn,
.chatbot-toggle-btn {
    width: 60px !important;
    height: 60px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
    border: 3px solid #ffffff !important;
    color: #ffffff !important;
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.45), 0 4px 10px rgba(0, 0, 0, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    position: relative !important;
    outline: none !important;
    flex-shrink: 0 !important;
    user-select: none !important;
    padding: 0 !important;
    margin: 0 !important;
    text-decoration: none !important;
}

.chatbot-bubble-btn:hover,
.chatbot-toggle-btn:hover {
    transform: scale(1.1) translateY(-3px) !important;
    box-shadow: 0 14px 32px rgba(220, 38, 38, 0.55), 0 6px 14px rgba(0, 0, 0, 0.25) !important;
}

.chatbot-bubble-btn:active,
.chatbot-toggle-btn:active {
    transform: scale(0.95) !important;
}

.chatbot-bubble-btn svg,
.chatbot-toggle-btn svg {
    display: block !important;
    width: 26px !important;
    height: 26px !important;
    stroke: #ffffff !important;
    transition: transform 0.25s ease !important;
}

.chatbot-bubble-btn .icon-open,
.chatbot-toggle-btn .icon-open {
    display: block !important;
}

.chatbot-bubble-btn .icon-close,
.chatbot-toggle-btn .icon-close {
    display: none !important;
}

/* Ketika Chat Terbuka: Ganti Ikon jadi X dan Tombol Menjadi Dark */
.chatbot-wrapper.open .chatbot-bubble-btn,
.chatbot-wrapper.open .chatbot-toggle-btn {
    background: #0f172a !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.35) !important;
}

.chatbot-wrapper.open .chatbot-bubble-btn .icon-open,
.chatbot-wrapper.open .chatbot-toggle-btn .icon-open {
    display: none !important;
}

.chatbot-wrapper.open .chatbot-bubble-btn .icon-close,
.chatbot-wrapper.open .chatbot-toggle-btn .icon-close {
    display: block !important;
}

/* Pulsing Dot Status Hijau Aktif di tombol bulat */
.bubble-online-pulse,
.chatbot-status-pulse {
    position: absolute !important;
    top: 2px !important;
    right: 2px !important;
    width: 14px !important;
    height: 14px !important;
    background: #10b981 !important;
    border: 2.5px solid #ffffff !important;
    border-radius: 50% !important;
    box-shadow: 0 0 8px #10b981 !important;
    pointer-events: none !important;
}

.bubble-online-pulse::after,
.chatbot-status-pulse::after {
    content: '' !important;
    position: absolute !important;
    inset: -3px !important;
    border-radius: 50% !important;
    border: 1.5px solid #10b981 !important;
    animation: bubbleDotPulse 2s infinite ease-out !important;
}

@keyframes bubbleDotPulse {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(1.9); opacity: 0; }
}

/* ----------------------------------------------------------
   FLOATING CHAT WINDOW POPUP
   ---------------------------------------------------------- */
.chatbot-window {
    position: absolute !important;
    bottom: 74px !important;
    right: 0 !important;
    width: 380px !important;
    max-width: calc(100vw - 36px) !important;
    height: 530px !important;
    max-height: calc(100vh - 110px) !important;
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 22px !important;
    box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.35), 0 0 35px rgba(220, 38, 38, 0.12) !important;
    display: none !important;
    flex-direction: column !important;
    overflow: hidden !important;
    transform: translateY(20px) scale(0.95) !important;
    opacity: 0 !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    transform-origin: bottom right !important;
    z-index: 9999999 !important;
}

.chatbot-wrapper.open .chatbot-window {
    display: flex !important;
    transform: translateY(0) scale(1) !important;
    opacity: 1 !important;
}

/* ----------------------------------------------------------
   CHAT HEADER
   ---------------------------------------------------------- */
.chatbot-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    color: #ffffff !important;
    padding: 14px 18px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    border-bottom: 2.5px solid #dc2626 !important;
    flex-shrink: 0 !important;
}

.chatbot-header-info {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.chatbot-avatar {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 19px !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
    border: 2px solid rgba(255, 255, 255, 0.35) !important;
    flex-shrink: 0 !important;
}

.chatbot-header-text {
    display: flex !important;
    flex-direction: column !important;
}

.chatbot-name {
    margin: 0 !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    letter-spacing: -0.2px !important;
}

.chatbot-status {
    font-size: 11px !important;
    color: #94a3b8 !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin: 2px 0 0 !important;
}

.chatbot-status .status-dot {
    width: 7px !important;
    height: 7px !important;
    background: #10b981 !important;
    border-radius: 50% !important;
    box-shadow: 0 0 6px #10b981 !important;
}

.chatbot-close-btn {
    background: rgba(255, 255, 255, 0.1) !important;
    border: none !important;
    color: #cbd5e1 !important;
    width: 30px !important;
    height: 30px !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
    padding: 0 !important;
    outline: none !important;
}

.chatbot-close-btn:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
    transform: scale(1.05) !important;
}

/* ----------------------------------------------------------
   CHAT BODY & MESSAGE BUBBLES
   ---------------------------------------------------------- */
.chatbot-body {
    flex: 1 !important;
    padding: 16px !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
    background: #f8fafc !important;
    scroll-behavior: smooth !important;
}

.chatbot-body::-webkit-scrollbar {
    width: 5px;
}

.chatbot-body::-webkit-scrollbar-track {
    background: transparent;
}

.chatbot-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

/* Chat Row */
.chat-row {
    display: flex !important;
    gap: 9px !important;
    max-width: 88% !important;
    animation: chatBubblePop 0.25s ease-out forwards !important;
}

@keyframes chatBubblePop {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Bot Message Row */
.chat-row.bot {
    align-self: flex-start !important;
}

.chat-row.bot .msg-avatar {
    width: 28px !important;
    height: 28px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%) !important;
    color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
    margin-top: 2px !important;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3) !important;
}

.chat-row.bot .msg-content {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
}

.chat-row.bot .msg-bubble {
    background: #ffffff !important;
    color: #1e293b !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 18px 18px 18px 4px !important;
    padding: 11px 14px !important;
    font-size: 13px !important;
    line-height: 1.55 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
    word-break: break-word !important;
}

/* User Message Row */
.chat-row.user {
    align-self: flex-end !important;
    justify-content: flex-end !important;
    margin-left: auto !important;
}

.chat-row.user .msg-content {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
}

.chat-row.user .msg-bubble {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    color: #ffffff !important;
    border-radius: 18px 18px 4px 18px !important;
    padding: 11px 15px !important;
    font-size: 13px !important;
    line-height: 1.55 !important;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.28) !important;
    word-break: break-word !important;
}

/* Timestamp */
.msg-time {
    font-size: 10.5px !important;
    color: #94a3b8 !important;
    margin-top: 4px !important;
    padding: 0 4px !important;
    font-weight: 500 !important;
}

/* ----------------------------------------------------------
   QUICK OPTIONS / FAQ CHIPS
   ---------------------------------------------------------- */
.chatbot-quick-options {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 12px 14px !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03) !important;
    margin-top: 2px !important;
}

.quick-options-label {
    font-size: 11px !important;
    font-weight: 800 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.4px !important;
    margin-bottom: 8px !important;
}

.quick-options-grid {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
}

.faq-chip {
    background: #f8fafc !important;
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 999px !important;
    padding: 6px 12px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    color: #334155 !important;
    cursor: pointer !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    outline: none !important;
    font-family: inherit !important;
}

.faq-chip:hover {
    background: #fef2f2 !important;
    border-color: #fca5a5 !important;
    color: #dc2626 !important;
    transform: translateY(-1.5px) !important;
    box-shadow: 0 4px 10px rgba(220, 38, 38, 0.12) !important;
}

.faq-chip:active {
    transform: scale(0.96) !important;
}

/* ----------------------------------------------------------
   TYPING INDICATOR
   ---------------------------------------------------------- */
.typing-wrapper {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.typing-bubble {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 18px 18px 18px 4px !important;
    padding: 10px 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
}

.typing-dot {
    width: 6px !important;
    height: 6px !important;
    background: #94a3b8 !important;
    border-radius: 50% !important;
    animation: typingBounce 1.2s infinite ease-in-out !important;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s !important; }
.typing-dot:nth-child(3) { animation-delay: 0.4s !important; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-5px); }
}

/* ----------------------------------------------------------
   CHAT INPUT FOOTER
   ---------------------------------------------------------- */
.chatbot-footer {
    padding: 12px 14px !important;
    background: #ffffff !important;
    border-top: 1px solid #f1f5f9 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-shrink: 0 !important;
    margin: 0 !important;
}

.chatbot-input {
    flex: 1 !important;
    height: 42px !important;
    padding: 0 16px !important;
    font-family: inherit !important;
    font-size: 13px !important;
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 999px !important;
    outline: none !important;
    background: #f8fafc !important;
    color: #0f172a !important;
    transition: all 0.2s ease !important;
}

.chatbot-input:focus {
    background: #ffffff !important;
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important;
}

.chatbot-send-btn {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    border: none !important;
    color: #ffffff !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
    outline: none !important;
    padding: 0 !important;
}

.chatbot-send-btn:hover {
    transform: scale(1.06) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35) !important;
}

.chatbot-send-btn:active {
    transform: scale(0.94) !important;
}

.chatbot-send-btn svg {
    margin-left: 2px !important;
    stroke: #ffffff !important;
}

/* ----------------------------------------------------------
   RESPONSIVE MOBILE
   ---------------------------------------------------------- */
@media (max-width: 480px) {
    .chatbot-wrapper {
        bottom: 16px !important;
        right: 16px !important;
    }

    .chatbot-teaser-pill {
        display: none !important;
    }

    .chatbot-bubble-btn,
    .chatbot-toggle-btn {
        width: 54px !important;
        height: 54px !important;
    }

    .chatbot-window {
        width: calc(100vw - 32px) !important;
        height: calc(100vh - 100px) !important;
        bottom: 68px !important;
        right: 0 !important;
    }
}
</style>

<div class="chatbot-wrapper" id="chatbotWrapper">
    <!-- Chat Window Box -->
    <div class="chatbot-window" id="chatbotWindow">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-avatar">🤖</div>
                <div class="chatbot-header-text">
                    <span class="chatbot-name">Stella Assistant</span>
                    <span class="chatbot-status"><span class="status-dot"></span> Online</span>
                </div>
            </div>
            <button type="button" class="chatbot-close-btn" id="chatbotCloseBtn" title="Tutup Chat" aria-label="Tutup Obrolan">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Chat Conversation Body -->
        <div class="chatbot-body" id="chatbotBody">
            <!-- Bot Initial Message Bubble -->
            <div class="chat-row bot">
                <div class="msg-avatar">🤖</div>
                <div class="msg-content">
                    <div class="msg-bubble">
                        Halo! 👋 Selamat datang di <b>Stella Etalase Digital</b>.<br>
                        Ada yang bisa saya bantu seputar karya siswa atau informasi etalase?
                    </div>
                    <span class="msg-time" id="initialBotTime">Baru saja</span>
                </div>
            </div>

            <!-- Quick FAQ Options -->
            <div class="chatbot-quick-options" id="chatbotQuickOptions">
                <div class="quick-options-label">Pilih bantuan cepat:</div>
                <div class="quick-options-grid">
                    <button type="button" class="faq-chip" data-question="Cara unggah karya">💡 Cara Unggah Karya</button>
                    <button type="button" class="faq-chip" data-question="Kategori karya apa saja?">🎨 Kategori Karya</button>
                    <button type="button" class="faq-chip" data-question="Bagaimana cara memberi like?">❤️ Cara Memberi Like</button>
                    <button type="button" class="faq-chip" data-question="Tentang SMK Stella Maris">🏫 Info Sekolah</button>
                    <button type="button" class="faq-chip" data-question="Hubungi Admin">🛡️ Hubungi Admin</button>
                </div>
            </div>
        </div>

        <!-- Chat Input Footer -->
        <form class="chatbot-footer" id="chatbotForm" autocomplete="off">
            <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Ketik pesan di sini..." autocomplete="off">
            <button type="submit" class="chatbot-send-btn" title="Kirim Pesan" aria-label="Kirim Pesan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>

    <!-- Floating Launcher Controls (Pill Teaser + Circle Button) -->
    <div class="chatbot-launcher-row">
        <div class="chatbot-teaser-pill" id="chatbotTeaserPill" title="Buka Asisten Stella">
            <span class="teaser-dot"></span>
            <span>💬 Butuh Bantuan? Tanya Stella!</span>
        </div>

        <!-- Floating Chat Bubble Launcher Button -->
        <button type="button" class="chatbot-bubble-btn chatbot-toggle-btn" id="chatbotToggleBtn" title="Buka Asisten Stella" aria-label="Buka Chatbot">
            <span class="bubble-online-pulse"></span>
            <svg class="icon-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>

<!-- Built-in Self-Contained Controller Script -->
<script id="stellaChatbotInlineScript">
(function() {
    if (window.stellaChatbotWidgetLoaded) return;
    window.stellaChatbotWidgetLoaded = true;

    function initChatbot() {
        const wrapper = document.getElementById("chatbotWrapper");
        const toggleBtn = document.getElementById("chatbotToggleBtn");
        const teaserPill = document.getElementById("chatbotTeaserPill");
        const closeBtn = document.getElementById("chatbotCloseBtn");
        const body = document.getElementById("chatbotBody");
        const form = document.getElementById("chatbotForm");
        const input = document.getElementById("chatbotInput");
        const initialTimeEl = document.getElementById("initialBotTime");

        if (!wrapper || !toggleBtn) return;

        function getCurrentTime() {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        }

        if (initialTimeEl) {
            initialTimeEl.textContent = getCurrentTime();
        }

        function scrollToBottom() {
            if (body) body.scrollTop = body.scrollHeight;
        }

        function toggleChat(forceOpen = null) {
            if (forceOpen === true) {
                wrapper.classList.add("open");
            } else if (forceOpen === false) {
                wrapper.classList.remove("open");
            } else {
                wrapper.classList.toggle("open");
            }

            if (wrapper.classList.contains("open")) {
                setTimeout(() => {
                    if (input) input.focus();
                    scrollToBottom();
                }, 200);
            }
        }

        toggleBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            toggleChat();
        });

        if (teaserPill) {
            teaserPill.addEventListener("click", function(e) {
                e.stopPropagation();
                toggleChat(true);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                toggleChat(false);
            });
        }

        function appendUserMessage(text) {
            const time = getCurrentTime();
            const row = document.createElement("div");
            row.className = "chat-row user";
            row.innerHTML = `
                <div class="msg-content">
                    <div class="msg-bubble">${escapeHtml(text)}</div>
                    <span class="msg-time">${time}</span>
                </div>
            `;
            body.appendChild(row);
            scrollToBottom();
        }

        function showTypingIndicator() {
            removeTypingIndicator();
            const typingRow = document.createElement("div");
            typingRow.className = "chat-row bot typing-wrapper";
            typingRow.id = "chatbotTyping";
            typingRow.innerHTML = `
                <div class="msg-avatar">🤖</div>
                <div class="msg-content">
                    <div class="typing-bubble">
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                    </div>
                </div>
            `;
            body.appendChild(typingRow);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            const typingEl = document.getElementById("chatbotTyping");
            if (typingEl) typingEl.remove();
        }

        function appendBotMessage(htmlContent) {
            removeTypingIndicator();
            const time = getCurrentTime();
            const row = document.createElement("div");
            row.className = "chat-row bot";
            row.innerHTML = `
                <div class="msg-avatar">🤖</div>
                <div class="msg-content">
                    <div class="msg-bubble">${htmlContent}</div>
                    <span class="msg-time">${time}</span>
                </div>
            `;
            body.appendChild(row);
            scrollToBottom();
        }

        function getBotResponse(userText) {
            const q = userText.toLowerCase().trim();

            if (q.includes("unggah") || q.includes("upload") || q.includes("tambah karya") || q.includes("cara buat karya")) {
                return `<b>Cara Mengunggah Karya Baru:</b><br><br>
                1. Masuk / Login ke akun siswa Anda.<br>
                2. Klik tombol merah <b>+ Tambah Karya</b> di bagian atas.<br>
                3. Isi judul proyek, link demo/website, serta deskripsi lengkap.<br>
                4. Pilih salah satu <b>Kategori</b> (Web Design, UI/UX, Game Dev, IoT, dll).<br>
                5. Unggah foto cover karya (maks 5MB).<br>
                6. Klik <b>Publikasikan Karya</b>. Karya langsung live di katalog! 🚀`;
            }

            if (q.includes("kategori") || q.includes("bidang") || q.includes("jenis") || q.includes("jurusan")) {
                return `<b>Kategori Karya yang Tersedia di Stella Etalase:</b><br><br>
                • 🎨 <b>Web Design</b>: Desain website & antarmuka web modern.<br>
                • 💻 <b>Web Development</b>: Aplikasi web fullstack, database & sistem.<br>
                • ✨ <b>UI/UX Design</b>: Desain prototipe Figma & alur pengguna.<br>
                • 🖌️ <b>Desain Grafis</b>: Ilustrasi, poster, identitas visual & logo.<br>
                • 📱 <b>Mobile App</b>: Aplikasi Android, iOS & cross-platform.<br>
                • 🎮 <b>Game Development</b>: Game edukasi & interaktif 2D/3D.<br>
                • 🤖 <b>IoT & Robotika</b>: Proyek sensor, Arduino, ESP32 & otomatisasi.<br>
                • 🎬 <b>Animasi & 3D</b>: Modelling Blender, render & animasi visual.<br>
                • 💡 <b>Lainnya</b>: Karya teknologi & riset kreatif lainnya.`;
            }

            if (q.includes("like") || q.includes("suka") || q.includes("hati") || q.includes("apresiasi")) {
                return `<b>Cara Memberi Like pada Karya:</b><br><br>
                • Cukup klik ikon hati (❤️) pada kartu karya di halaman katalog/beranda.<br>
                • Anda dapat memberikan like ke berbagai karya yang Anda sukai.<br>
                • Pastikan Anda sudah login untuk menyimpan apresiasi Anda!`;
            }

            if (q.includes("edit") || q.includes("ubah") || q.includes("hapus") || q.includes("delete")) {
                return `<b>Mengedit & Menghapus Karya:</b><br><br>
                • Siswa dapat mengedit atau menghapus karyanya sendiri melalui kartu karya atau tab <b>Profil Saya</b>.<br>
                • Admin dan Super Admin dapat meninjau dan mengelola seluruh karya etalase di menu <b>Dashboard Admin</b>.`;
            }

            if (q.includes("tentang") || q.includes("stella") || q.includes("sekolah") || q.includes("smk")) {
                return `<b>Tentang Stella Etalase Digital:</b><br><br>
                Platform pameran karya digital dan portofolio teknologi siswa SMK Stella Maris. Wadah resmi untuk menunjukkan bakat, kreativitas, dan inovasi siswa kepada sekolah, mitra industri, dan dunia.`;
            }

            if (q.includes("admin") || q.includes("kontak") || q.includes("hubungi") || q.includes("bantuan") || q.includes("superadmin")) {
                return `<b>Bantuan & Kontak Administrator:</b><br><br>
                Jika memerlukan bantuan terkait akun, keluhan teknis, atau verifikasi:<br>
                • Email: <b>admin@stella.sch.id</b><br>
                • Ruang Pengelola: Lab Komputer SMK Stella Maris.<br>
                • Admin & Super Admin juga dapat memantau aktivitas melalui Panel Admin.`;
            }

            if (q.includes("halo") || q.includes("hai") || q.includes("pagi") || q.includes("siang") || q.includes("sore") || q.includes("malam") || q.includes("assalamualaikum")) {
                return `Halo! 👋 Senang bisa menyapa Anda. Silakan tanyakan apa saja tentang cara unggah karya, kategori, fitur like, atau pilih tombol bantuan cepat di atas! 😊`;
            }

            return `Terima kasih telah bertanya! 🤖<br><br>
            <i>(Saat ini asisten beroperasi dalam mode panduan cepat non-AI).</i><br><br>
            Anda dapat memilih salah satu topik bantuan cepat di atas seperti <b>Cara Unggah Karya</b>, <b>Kategori Karya</b>, atau <b>Cara Beri Like</b>.`;
        }

        function processUserMessage(rawText) {
            const text = rawText ? rawText.trim() : "";
            if (!text) return;

            if (!wrapper.classList.contains("open")) {
                toggleChat(true);
            }

            appendUserMessage(text);
            if (input) input.value = "";

            showTypingIndicator();

            setTimeout(() => {
                const reply = getBotResponse(text);
                appendBotMessage(reply);
            }, 450);
        }

        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                if (input && input.value) {
                    processUserMessage(input.value);
                }
            });
        }

        document.querySelectorAll(".faq-chip").forEach(chip => {
            chip.addEventListener("click", function() {
                const question = this.getAttribute("data-question");
                if (question) {
                    processUserMessage(question);
                }
            });
        });

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initChatbot);
    } else {
        initChatbot();
    }
})();
</script>
