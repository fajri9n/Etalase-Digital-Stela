/**
 * ==========================================================
 * ETALASE DIGITAL STELLA - FLOATING BUBBLE CHATBOT CONTROLLER
 * Assistant Virtual Interaktif Siswa & Pengunjung (Rule Engine)
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById("chatbotWrapper");
    const toggleBtn = document.getElementById("chatbotToggleBtn");
    const closeBtn = document.getElementById("chatbotCloseBtn");
    const body = document.getElementById("chatbotBody");
    const form = document.getElementById("chatbotForm");
    const input = document.getElementById("chatbotInput");
    const initialTimeEl = document.getElementById("initialBotTime");

    if (!wrapper || !toggleBtn) return;

    // Helper format jam saat ini
    function getCurrentTime() {
        const now = new Date();
        return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    }

    // Set waktu awal pesan bot
    if (initialTimeEl) {
        initialTimeEl.textContent = getCurrentTime();
    }

    // Scroll otomatis ke percakapan paling bawah
    function scrollToBottom() {
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    }

    // Buka / Tutup Chat Window
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

    toggleBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleChat();
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            toggleChat(false);
        });
    }

    // Klik di luar window untuk menutup (opsional pada layar desktop)
    document.addEventListener("click", function (e) {
        if (wrapper.classList.contains("open")) {
            const windowEl = document.getElementById("chatbotWindow");
            if (windowEl && !windowEl.contains(e.target) && !toggleBtn.contains(e.target)) {
                // Biarkan tetap terbuka atau bisa ditutup jika user klik background
            }
        }
    });

    // Tambah Pesan User ke Bubble
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

    // Tampilkan Indikator Bot Mengetik
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
        if (typingEl) {
            typingEl.remove();
        }
    }

    // Tambah Pesan Bot ke Bubble
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

    // Otak Respons Cerdas (Rule Engine Non-AI)
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

        // Default jika pertanyaan di luar rule
        return `Terima kasih telah bertanya! 🤖<br><br>
        <i>(Saat ini asisten beroperasi dalam mode cepat non-AI).</i><br><br>
        Anda dapat memilih salah satu topik bantuan cepat di atas seperti <b>Cara Unggah Karya</b>, <b>Kategori Karya</b>, atau <b>Cara Beri Like</b>.`;
    }

    // Proses Pengiriman Pesan
    function processUserMessage(rawText) {
        const text = rawText ? rawText.trim() : "";
        if (!text) return;

        // Buka chat window jika belum terbuka
        if (!wrapper.classList.contains("open")) {
            toggleChat(true);
        }

        appendUserMessage(text);
        if (input) input.value = "";

        showTypingIndicator();

        // Simulasi jeda pengetikan alami
        setTimeout(() => {
            const reply = getBotResponse(text);
            appendBotMessage(reply);
        }, 450);
    }

    // Event Submit Form Input
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (input && input.value) {
                processUserMessage(input.value);
            }
        });
    }

    // Event Klik FAQ Chips
    document.querySelectorAll(".faq-chip").forEach(chip => {
        chip.addEventListener("click", function () {
            const question = this.getAttribute("data-question");
            if (question) {
                processUserMessage(question);
            }
        });
    });

    // Helper sanitasi teks input
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
