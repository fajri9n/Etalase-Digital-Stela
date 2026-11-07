<!-- ==========================================================
     FLOATING CHATBOT WIDGET (NON-AI ASSISTANT)
     ========================================================== -->
<div class="chatbot-wrapper" id="chatbotWrapper">
    <!-- Chat Window Box -->
    <div class="chatbot-window" id="chatbotWindow">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-header-left">
                <div class="chatbot-avatar">🤖</div>
                <div class="chatbot-title-box">
                    <h4>Stella Assistant <span class="chatbot-badge-beta">PANDUAN</span></h4>
                    <p class="chatbot-subtext"><span class="dot-online"></span> Siap Membantu • Online</p>
                </div>
            </div>
            <button type="button" class="chatbot-close-btn" id="chatbotCloseBtn" aria-label="Tutup Obrolan">&times;</button>
        </div>

        <!-- Messages Area -->
        <div class="chatbot-body" id="chatbotBody">
            <div class="chat-msg bot">
                <div class="chat-bubble">
                    Halo! 👋 Selamat datang di <b>Stella Etalase Digital</b>.<br>
                    Ada yang bisa saya bantu terkait karya inovasi siswa atau informasi sekolah?
                    <div class="chatbot-quick-chips">
                        <button type="button" class="quick-chip" data-question="Cara unggah karya">💡 Cara Unggah</button>
                        <button type="button" class="quick-chip" data-question="Kategori karya apa saja?">🎨 Kategori</button>
                        <button type="button" class="quick-chip" data-question="Bagaimana cara memberi like?">❤️ Cara Like</button>
                        <button type="button" class="quick-chip" data-question="Tentang Stella Etalase">🏫 Info Sekolah</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Footer -->
        <form class="chatbot-footer" id="chatbotForm" autocomplete="off">
            <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Tanyakan sesuatu di sini..." autocomplete="off">
            <button type="submit" class="chatbot-send-btn" aria-label="Kirim Pesan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>

    <!-- Floating Toggle Button -->
    <button type="button" class="chatbot-toggle-btn" id="chatbotToggleBtn" aria-label="Buka Chat Bantuan">
        <span class="chatbot-status-pulse"></span>
        <svg class="icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</div>
