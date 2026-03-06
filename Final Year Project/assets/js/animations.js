/**
 * TuneHub Advanced Animations & Motion JavaScript
 */
document.addEventListener("DOMContentLoaded", () => {

    // --- Preloader Fallback (in case window.load already fired) ---
    const preloader = document.getElementById("tunehub-preloader");
    if (preloader && document.readyState === 'complete') {
        preloader.classList.add("fade-out");
        setTimeout(() => {
            preloader.style.display = "none";
        }, 800);
    }

    // --- Phase 1: Canvas Audio/Particle Visualizer ---
    const canvas = document.getElementById("bg-visualizer");
    if (canvas) {
        const ctx = canvas.getContext("2d");
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;
        const rootStyles = getComputedStyle(document.documentElement);
        const primaryColor = rootStyles.getPropertyValue('--primary-color').trim() || '#2C5F2D';
        const accentColor = rootStyles.getPropertyValue('--accent-color').trim() || '#004E89';

        // Mouse reaction coords
        let mouseX = width / 2;
        let mouseY = height / 2;

        window.addEventListener("resize", () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        window.addEventListener("mousemove", (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.8;
                this.vy = (Math.random() - 0.5) * 0.8;
                this.radius = Math.random() * 2 + 1;
                // Alternate colors between primary and accent
                this.color = Math.random() > 0.5 ? primaryColor : accentColor;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;

                // Edge wrap
                if (this.x < 0) this.x = width;
                if (this.x > width) this.x = 0;
                if (this.y < 0) this.y = height;
                if (this.y > height) this.y = 0;

                // Subtle Mouse Repulsion
                let dx = mouseX - this.x;
                let dy = mouseY - this.y;
                let distance = Math.sqrt(dx * dx + dy * dy);
                let maxDist = 150;

                if (distance < maxDist) {
                    let force = (maxDist - distance) / maxDist;
                    this.x -= (dx / distance) * force * 1.5;
                    this.y -= (dy / distance) * force * 1.5;
                }
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
            }
        }

        const particles = [];
        for (let i = 0; i < 70; i++) particles.push(new Particle());

        function animateCanvas() {
            ctx.clearRect(0, 0, width, height);
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animateCanvas);
        }
        animateCanvas();
    }

    // --- Phase 2: Staggered Grid Reveal ---
    const staggerItems = document.querySelectorAll('.stagger-item');
    if (staggerItems.length > 0 && 'IntersectionObserver' in window) {

        let batchReveals = [];
        let isBatching = false;

        const processBatch = () => {
            // Sort batch by their horizontal/vertical coordinates roughly to animate top-left to bottom-right
            batchReveals.sort((a, b) => {
                const rectA = a.getBoundingClientRect();
                const rectB = b.getBoundingClientRect();
                return (rectA.top - rectB.top) || (rectA.left - rectB.left);
            });

            batchReveals.forEach((el, index) => {
                // Apply a staggered transition delay
                el.style.transitionDelay = `${index * 120}ms`;
                el.classList.add('revealed');
            });

            batchReveals = [];
            isBatching = false;
        };

        const staggerObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    batchReveals.push(entry.target);
                    observer.unobserve(entry.target);

                    if (!isBatching) {
                        isBatching = true;
                        // process the batch shortly after the first item hits the threshold
                        setTimeout(processBatch, 50);
                    }
                }
            });
        }, { threshold: 0.1 });

        staggerItems.forEach(item => staggerObserver.observe(item));
    } else {
        staggerItems.forEach(item => {
            item.style.transitionDelay = "0ms";
            item.classList.add('revealed');
        });
    }

    // --- Phase 2: "Add to Cart" Parabolic Flight ---
    const addToCartBtns = document.querySelectorAll('.add-to-cart-flight');
    const cartIcon = document.querySelector('.navbar .bi-cart3');

    if (cartIcon) {
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault(); // For demo if it's an anchor tag. (Otherwise manage ajax).

                // In a real app we find the closest product image
                const card = this.closest('.card, .product-item');
                let img = null;
                if (card) img = card.querySelector('img');
                if (!img) return; // Can't animate if no image

                const imgRect = img.getBoundingClientRect();
                const cartRect = cartIcon.getBoundingClientRect();

                // Clone image
                const clone = img.cloneNode();
                clone.classList.add('flight-clone');

                // Initial size/pos
                clone.style.width = `${imgRect.width}px`;
                clone.style.height = `${imgRect.height}px`;
                clone.style.left = `${imgRect.left}px`;
                clone.style.top = `${imgRect.top}px`;

                document.body.appendChild(clone);

                // Calculate math for flight trajectory (Bezier-like feeling via CSS variables or translation)
                const startX = imgRect.left;
                const startY = imgRect.top;
                const endX = cartRect.left + cartRect.width / 2 - 20; // Target center of cart icon
                const endY = cartRect.top + cartRect.height / 2 - 20;

                /**
                 * The Math:
                 * We transition X linearly, but Y smoothly to simulate a parabolic arc.
                 * A trick in modern CSS+JS is separating axis DOM layers, but to keep it lightweight in vanilla JS,
                 * we use CSS transitions. We assign new Left/Top, shrink Width/Height, and lower Opacity.
                 * To make it arc, we rely on the `ease-in-out` vertical against an `linear` horizontal, or purely requestAnimationFrame.
                 * We'll use CSS transitions for performance: Add a temporary wrapper if true generic bezier is needed,
                 * or simply direct to `endX, endY` with a cubic-bezier mimicking a curve.
                 * For strict parabolic curve via JS requestAnimationFrame:
                 */

                const animDuration = 800; // ms
                const startTime = performance.now();

                // Base control points for a quadratic bezier (parabolic arc)
                // cpX is halfway between, cpY is higher than the max(startY, endY) to arc upwards
                const cpX = startX + (endX - startX) / 2;
                const cpY = Math.min(startY, endY) - 150; // Arc 150px upwards

                // We change width/height/opacity via CSS transition defined in motion.css.
                // We use script solely for traversing the quadratic bezier path.
                // Force layout recalculation for the transitions to apply properly.
                clone.getBoundingClientRect();
                clone.style.width = '30px';
                clone.style.height = '30px';
                clone.style.opacity = '0.4';
                clone.style.borderRadius = '50%';

                function fly(time) {
                    let t = (time - startTime) / animDuration;
                    if (t > 1) t = 1; // Clamp

                    // Quadratic Bezier Formula: P = (1-t)^2*P0 + 2*(1-t)*t*P1 + t^2*P2
                    const curX = Math.pow(1 - t, 2) * startX + 2 * (1 - t) * t * cpX + Math.pow(t, 2) * endX;
                    const curY = Math.pow(1 - t, 2) * startY + 2 * (1 - t) * t * cpY + Math.pow(t, 2) * endY;

                    clone.style.left = `${curX}px`;
                    clone.style.top = `${curY}px`;

                    if (t < 1) {
                        requestAnimationFrame(fly);
                    } else {
                        // Animation finished, remove clone, add little pump to cart icon
                        clone.remove();
                        cartIcon.parentElement.style.transform = 'scale(1.3)';
                        setTimeout(() => {
                            cartIcon.parentElement.style.transform = 'scale(1)';
                            cartIcon.parentElement.style.transition = 'transform 0.2s';
                        }, 200);

                        // If you need to update AJAX cart count
                        // ...
                    }
                }

                requestAnimationFrame(fly);
            });
        });
    }

    // --- Phase 3: Tutorial Hover Previews ---
    const tutorialCards = document.querySelectorAll('.tutorial-card');

    tutorialCards.forEach(card => {
        const video = card.querySelector('video.tutorial-video-preview');
        if (video) {
            // Mute video to ensure browser autoplay policy doesn't block it
            video.muted = true;

            card.addEventListener('mouseenter', () => {
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => { console.warn("Autoplay prevented: ", error); });
                }
            });

            card.addEventListener('mouseleave', () => {
                video.pause();
                video.currentTime = 0; // Reset video to start
            });
        }
    });
});
