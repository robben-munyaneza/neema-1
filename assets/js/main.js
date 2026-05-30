/* assets/js/main.js */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Dynamic Navbar Scroll Effect
    const navbar = document.querySelector(".navbar-premium");
    if (navbar) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    }

    // 2. Rating Star Interactive Picker (if review form exists)
    const starPicker = document.querySelectorAll(".star-picker i");
    if (starPicker.length > 0) {
        const ratingInput = document.getElementById("rating-input");
        starPicker.forEach(star => {
            star.addEventListener("click", () => {
                const rating = parseInt(star.getAttribute("data-rating"));
                ratingInput.value = rating;
                
                // Update color styling
                starPicker.forEach(s => {
                    const sRating = parseInt(s.getAttribute("data-rating"));
                    if (sRating <= rating) {
                        s.classList.remove("bi-star", "star-rating-empty");
                        s.classList.add("bi-star-fill", "star-rating");
                    } else {
                        s.classList.remove("bi-star-fill", "star-rating");
                        s.classList.add("bi-star", "star-rating-empty");
                    }
                });
            });
        });
    }

    // 3. Setup nice custom toast notifications if required
    window.showToast = (message, type = 'success') => {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.bottom = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `animate-fade-in`;
        toast.style.background = 'rgba(18, 20, 32, 0.9)';
        toast.style.backdropFilter = 'blur(10px)';
        toast.style.border = `1px solid ${type === 'success' ? '#10b981' : '#ef4444'}`;
        toast.style.color = '#fff';
        toast.style.padding = '12px 24px';
        toast.style.borderRadius = '10px';
        toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.5)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.minWidth = '250px';

        const icon = document.createElement('i');
        icon.className = `bi ${type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'}`;
        toast.appendChild(icon);

        const text = document.createElement('span');
        text.innerText = message;
        toast.appendChild(text);

        document.getElementById('toast-container').appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.4s ease';
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, 3000);
    };

    // Check if there is a session flash message to show as a toast
    const flashElement = document.getElementById('session-flash-data');
    if (flashElement) {
        const message = flashElement.getAttribute('data-message');
        const type = flashElement.getAttribute('data-type');
        if (message) {
            window.showToast(message, type);
        }
    }
});
