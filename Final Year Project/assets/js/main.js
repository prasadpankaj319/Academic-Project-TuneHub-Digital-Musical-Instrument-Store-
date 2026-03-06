/**
 * TuneHub Main Javascript
 */
document.addEventListener("DOMContentLoaded", function () {
    // Initialization code or common event listeners


    // Setup CSRF token for all fetch calls if needed globally
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // --- Show/Hide Password Toggle ---
    const togglePasswordBtns = document.querySelectorAll('.password-toggle');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            // Traverse DOM to find the sibling input field within the input-group
            const inputGroup = this.closest('.input-group');
            if (!inputGroup) return;

            const inputField = inputGroup.querySelector('input');
            const icon = this.querySelector('i');

            if (inputField) {
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                } else {
                    inputField.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            }
        });
    });

    /* --- Scroll Reveal Animations (Intersection Observer) --- */
    const revealElements = document.querySelectorAll('.reveal-up');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target); // Stop observing once revealed
                }
            });
        }, {
            root: null,
            threshold: 0.15, // Trigger when 15% of element is visible
            rootMargin: "0px 0px -50px 0px"
        });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        // Fallback for older browsers
        revealElements.forEach(el => el.classList.add('active'));
    }

    /* --- Dynamic Navbar (Frosted Glass on Scroll) --- */
    const navbar = document.querySelector('.navbar');

    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-glass');
            } else {
                navbar.classList.remove('navbar-glass');
            }
        });

        // Trigger once on load in case page is already scrolled
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-glass');
        }
    }

    /* --- Admin Dashboard: File Upload Zone Preview --- */
    const uploadZones = document.querySelectorAll('.file-upload-zone');
    uploadZones.forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        const preview = zone.querySelector('.upload-preview');
        const placeholder = zone.querySelector('.upload-placeholder');

        if (fileInput && preview && placeholder) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        placeholder.style.display = 'none';
                        zone.style.padding = '10px'; // Shrink padding when image exists
                    }
                    reader.readAsDataURL(file);
                } else {
                    // Reset if cancelled
                    preview.src = '';
                    preview.style.display = 'none';
                    placeholder.style.display = 'block';
                    zone.style.padding = '40px 20px';
                }
            });
        }
    });

    /* --- AJAX Wishlist Toggling --- */
    const wishlistBtns = document.querySelectorAll('.toggle-wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            const icon = this.querySelector('i');

            try {
                // Fetch CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch(`${BASE_URL}modules/user/toggle_wishlist.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    if (data.action === 'added') {
                        icon.classList.remove('bi-heart', 'text-white');
                        icon.classList.add('bi-heart-fill', 'text-danger');
                        icon.style.transform = 'scale(1.2)';
                        setTimeout(() => icon.style.transform = 'scale(1)', 200);
                    } else if (data.action === 'removed') {
                        icon.classList.remove('bi-heart-fill', 'text-danger');
                        if (this.classList.contains('btn-dark')) {
                            // Recover white icon if on Catalog grid
                            icon.classList.add('bi-heart', 'text-white');
                        } else {
                            // Recover standard outline if on Details page
                            icon.classList.add('bi-heart');
                        }
                    }
                } else {
                    alert('Error updating wishlist: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while updating wishlist.');
            }
        });
    });

    /* --- AJAX Review Deletion --- */
    const deleteReviewBtns = document.querySelectorAll('.confirm-delete-review-btn');
    deleteReviewBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();

            const reviewId = this.getAttribute('data-id');
            const card = document.getElementById(`review-card-${reviewId}`);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`${BASE_URL}modules/products/delete_review.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ review_id: reviewId })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Visually animate removal of the review card
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => card.remove(), 400);
                } else {
                    alert('Error deleting review: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error. Could not delete review.');
            }
        });
    });

});
