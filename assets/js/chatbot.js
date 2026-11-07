/**
 * ==========================================================
 * ETALASE DIGITAL STELLA - INTERACTIVE CHATBOT CONTROLLER
 * Assistant Virtual Interaktif (Non-AI Template Engine)
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById("chatbotWrapper");
    const toggleBtn = document.getElementById("chatbotToggleBtn");
    const closeBtn = document.getElementById("chatbotCloseBtn");
    const body = document.getElementById("chatbotBody");
    const form = document.getElementById("chatbotForm");
    const input = document.getElementById("chatbotInput");

    if (!wrapper || !toggleBtn) return;

    // Toggle Chatbot
    toggleBtn.addEventListener("click", function () {
        wrapper.classList.toggle("open");
        if (wrapper.classList.contains("open")) {
            setTimeout(() => {
                if (input) input.focus();
                scrollToBottom();
            }, 250);
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            wrapper.classList.remove("open");
        });
    }

    // Scroll to bottom
    function scrollToBottom() {
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    }

    // Helper format jam
    function getCurrentTime() {
        const now = new Date();
        return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    }

    // Append User Message
    function appendUserMessage(text) {
        const time = getCurrentTime();
        const msgDiv = document.createElement("div");
        msgDiv.className = "chat-msg user";
        msgDiv.innerHTML = `
            <div class="chat-bubble">${escapeHtml(text)}</div>
        `;
        body.appendChild(msgDiv);
        scrollToBottom();
    }

    // Show Typing Indicator
    function showTypingIndicator() {
        const typingDiv = document.createElement("div");
        typingDiv.className = "chat-msg bot typing-wrapper";
        typingDiv.id = "chatbotTyping";
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        `;
        body.appendChild(typingDiv);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const typingDiv = document.getElementById("chatbotTyping");
        if (typingDiv) {
            typingDiv.remove();
        }
    }

    // Append Bot Message
    function appendBotMessage(htmlContent, showChips = true) {
        removeTypingIndicator();

        const time = getCurrentTime();
        const msgDiv = document.createElement("div");
        msgDiv.className = "chat-msg bot";
        
        let chipsHtml = "";
        if (showChips) {
            chipsHtml = `
                <div class="chatbot-quick-chips">
                    <button type="button" class="quick-chip" data-question="Cara unggah karya">💡 Cara Unggah</button>
                    <button type="button" class="quick-chip" data-question="Kategori karya apa saja?">🎨 Kategori</button>
                    <button type="button" class="quick-chip" data-question="Bagaimana cara memberi like?">❤️ Cara Like</button>
                    <button type="button" class="quick-chip" data-question="Tentang Stella Etalase">🏫 Info Sekolah</button>
                </div>
            `;
        }

        msgDiv.innerHTML = `
            <div class="chat-bubble">
                ${htmlContent}
                ${chipsHtml}
            </div>
        `;
        body.appendChild(msgDiv);
        scrollToBottom();
        attachChipEvents();
    }

    // Canned Response Rule Engine
    function getBotResponse(userText) {
        const q = userText.toLowerCase().trim();

        if (q.includes("unggah") || q.includes("upload") || q.includes("tambah karya") || q.includes("cara buat karya")) {
            return `<b>Cara Mengunggah Karya:</b><br>
            1. Masuk ke akun siswa Anda.<br>
            2. Klik tombol <b>+ Tambah Karya</b> di navbar.<br>
            3. Pilih <b>Kategori</b> (misal Web Design, IoT, dll).<br>
            4. Tulis judul, link demo, dan deskripsi.<br>
            5. Unggah gambar cover karya (JPG, PNG, WEBP maks 5MB).<br>
            6. Klik <b>Publikasikan Karya</b>. Karya langsung tayang di katalog! 🚀`;
        }

        if (q.includes("kategori") || q.includes("bidang") || q.includes("jenis karya")) {
            return `<b>Daftar Kategori Karya di Stella Etalase:</b><br>
            • 🎨 <b>Web Design</b>: Desain website & landing page<br>
            • 💻 <b>Web Development</b>: Aplikasi web fullstack & backend<br>
            • ✨ <b>UI/UX Design</b>: Prototipe Figma & desain antarmuka<br>
            • 🖌️ <b>Desain Grafis</b>: Poster, ilustrasi & branding digital<br>
            • 📱 <b>Mobile App</b>: Aplikasi Android/iOS/Flutter<br>
            • 🎮 <b>Game Development</b>: Game 2D & 3D interaktif<br>
            • 🤖 <b>IoT & Robotika</b>: Arduino, ESP32 & otomasi<br>
            • 🎬 <b>Animasi & 3D</b>: Blender, motion graphic & video<br>
            • 💡 <b>Lainnya</b>: Inovasi teknologi kreatif lainnya.`;
        }

        if (q.includes("like") || q.includes("suka") || q.includes("apresiasi") || q.includes("love")) {
            return `<b>Cara Memberikan Like pada Karya:</b><br>
            Cukup klik tombol hati (❤️) pada kartu karya di halaman beranda atau di dalam modal detail. Pastikan Anda sudah login untuk memberikan apresiasi ya!`;
        }

        if (q.includes("edit") || q.includes("hapus") || q.includes("ubah karya")) {
            return `<b>Panduan Edit & Hapus Karya:</b><br>
            • Siswa dapat mengedit atau menghapus karya yang dibuatnya sendiri melalui tombol Edit/Hapus pada kartu karya atau di halaman <b>Profil Saya</b>.<br>
            • Admin dan Super Admin memiliki otoritas mengelola semua karya di etalase.`;
        }

        if (q.includes("tentang") || q.includes("stella") || q.includes("sekolah") || q.includes("smk")) {
            return `<b>Tentang Stella Etalase Digital:</b><br>
            Stella Etalase Digital adalah platform panggung apresiasi karya dan portofolio inovasi teknologi siswa SMK Stella Maris. Di sini karya siswa dipamerkan kepada guru, industri, dan khalayak luas.`;
        }

        if (q.includes("halo") || q.includes("hai") || q.includes("pagi") || q.includes("siang") || q.includes("malam") || q.includes("assalamualaikum")) {
            return `Halo! 👋 Senang bisa membantu Anda di Stella Etalase Digital. Anda bisa bertanya tentang cara unggah, kategori karya, atau fitur lainnya melalui tombol cepat di bawah!`;
        }

        if (q.includes("admin") || q.includes("kontak") || q.includes("hubungi") || q.includes("bantuan")) {
            return `<b>Bantuan & Kontak Admin:</b><br>
            Untuk pertanyaan lanjutan atau bantuan teknis akun, silakan hubungi tim Admin Sekolah melalui email <b>admin@stella.sch.id</b> atau kunjungi ruang pengelola sekolah.`;
        }

        // Default template response
        return `Terima kasih atas pertanyaan Anda! 🤖<br><br>
        <i>(Catatan: Asisten ini saat ini beroperasi dalam mode cepat template non-AI. Fitur AI pintar sedang disiapkan).</i><br><br>
        Silakan pilih topik bantuan populer di bawah ini:`;
    }

    // Process Message
    function processUserMessage(text) {
        if (!text.trim()) return;

        appendUserMessage(text);
        if (input) input.value = "";

        showTypingIndicator();

        setTimeout(() => {
            const botResponse = getBotResponse(text);
            appendBotMessage(botResponse);
        }, 550);
    }

    // Handle Form Submit
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (input) {
                processUserMessage(input.value);
            }
        });
    }

    // Attach Event ke Quick Chips
    function attachChipEvents() {
        document.querySelectorAll(".quick-chip").forEach(chip => {
            chip.onclick = function () {
                const q = this.getAttribute("data-question");
                if (q) {
                    processUserMessage(q);
                }
            };
        });
    }

    attachChipEvents();

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
