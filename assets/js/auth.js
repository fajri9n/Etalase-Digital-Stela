/**
 * Etalase Digital Stella - Auth Pages Controller
 * Handles password visibility toggling and video audio controls
 */
document.addEventListener("DOMContentLoaded", function () {
    // 1. Password Visibility Toggle
    const toggleButtons = document.querySelectorAll(".toggle-password-btn");
    toggleButtons.forEach(button => {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-target");
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;

            const isPassword = targetInput.type === "password";
            targetInput.type = isPassword ? "text" : "password";

            // Toggle active state for CSS / SVG visibility
            this.classList.toggle("is-revealed", isPassword);
            this.setAttribute("aria-label", isPassword ? "Sembunyikan kata sandi" : "Tampilkan kata sandi");
        });
    });

    // 2. Video Audio Toggle Controller
    const video = document.getElementById("authVideo");
    const soundToggle = document.getElementById("videoSoundToggle");

    if (video && soundToggle) {
        soundToggle.addEventListener("click", function () {
            video.muted = !video.muted;
            if (!video.muted) {
                // If unmuted, make sure volume is audible
                video.volume = 0.7;
            }
            soundToggle.classList.toggle("is-unmuted", !video.muted);
            const iconMuted = soundToggle.querySelector(".sound-icon-muted");
            const iconUnmuted = soundToggle.querySelector(".sound-icon-unmuted");
            if (iconMuted && iconUnmuted) {
                iconMuted.style.display = video.muted ? "block" : "none";
                iconUnmuted.style.display = video.muted ? "none" : "block";
            }
        });
    }

    // 3. Auto-dismiss alerts after 6 seconds (optional subtle fade)
    const alertBox = document.querySelector(".auth-alert-dismissible");
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.6s ease, transform 0.6s ease";
            alertBox.style.opacity = "0";
            alertBox.style.transform = "translateY(-6px)";
            setTimeout(() => {
                if (alertBox.parentNode) alertBox.remove();
            }, 600);
        }, 6500);
    }
});
