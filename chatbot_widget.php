<!-- ==========================================================
     FLOATING CHATBOT BUBBLE WIDGET
     ========================================================== -->
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
