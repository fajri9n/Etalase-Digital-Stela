/**
 * Etalase Digital Stella - Navbar Interactive Controller
 * Handles sticky glassmorphism scroll elevation and responsive mobile drawer
 */
document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.querySelector(".navbar");
    const toggleBtn = document.getElementById("navbarToggle");
    const mobileDrawer = document.getElementById("navbarMobileDrawer");

    // Dynamic scroll elevation & compaction
    if (navbar) {
        const handleScroll = () => {
            if (window.scrollY > 15) {
                navbar.classList.add("navbar-scrolled");
            } else {
                navbar.classList.remove("navbar-scrolled");
            }
        };

        window.addEventListener("scroll", handleScroll, { passive: true });
        handleScroll();
    }

    // Responsive Mobile Drawer Toggle
    if (toggleBtn && mobileDrawer) {
        toggleBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            const isOpen = mobileDrawer.classList.toggle("open");
            toggleBtn.classList.toggle("active", isOpen);
        });

        // Close when clicking outside
        document.addEventListener("click", function (e) {
            if (mobileDrawer.classList.contains("open") && !navbar.contains(e.target)) {
                mobileDrawer.classList.remove("open");
                toggleBtn.classList.remove("active");
            }
        });

        // Close on escape key
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && mobileDrawer.classList.contains("open")) {
                mobileDrawer.classList.remove("open");
                toggleBtn.classList.remove("active");
            }
        });
    }
});
