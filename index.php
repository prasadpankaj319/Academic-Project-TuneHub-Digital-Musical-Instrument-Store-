<?php require_once 'includes/header.php'; ?>

<!-- Ambient Stage Lighting -->
<div class="ambient-glow ambient-glow-green"></div>
<div class="ambient-glow ambient-glow-blue"></div>

<!-- Add top padding due to fixed navbar -->
<div class="row align-items-center mb-5 mt-5 pt-5 reveal-up">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <h1 class="display-4 text-primary-custom fw-bold">Find Your Perfect Sound.</h1>
        <p class="lead my-4" style="color: var(--text-dark);">Welcome to TuneHub, the ultimate digital musical instrument store. Explore top brands, discover your next instrument, and learn from our curated tutorials.</p>

        <div class="d-inline-flex align-items-center bg-dark rounded-pill px-4 py-2 mb-4 border border-secondary shadow-sm hover-grow" style="cursor: pointer;" onclick="navigator.clipboard.writeText('FIRST10'); const msg = this.querySelector('.promo-text'); const orig = msg.innerHTML; msg.innerHTML = '<i class=\'bi bi-check2-circle text-success me-2\'></i>Copied to Clipboard!'; setTimeout(() => { msg.innerHTML = orig; }, 2000);">
            <span class="badge bg-primary-custom rounded-pill me-3 fs-6 px-3 py-2">NEW</span>
            <span class="text-white fw-bold me-2 promo-text">Use code <span class="text-accent-custom tracking-widest" style="letter-spacing: 2px;">FIRST10</span> for 10% Off!</span>
            <i class="bi bi-copy text-secondary ms-2 small" title="Click to copy"></i>
        </div>
        <br>
        <a href="<?php echo base_url('modules/products/index.php'); ?>" class="btn btn-secondary btn-lg me-3 px-4">Shop Collection</a>
        <a href="<?php echo base_url('modules/tutorials/index.php'); ?>" class="btn btn-outline-primary btn-lg px-4">Watch Tutorials</a>
    </div>
    <div class="col-lg-6">
        <div id="tunehubHeroCarousel" class="carousel slide carousel-fade shadow-lg rounded-4 overflow-hidden border border-secondary" data-bs-ride="carousel">
            <!-- Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#tunehubHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#tunehubHeroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#tunehubHeroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <!-- Carousel Inner -->
            <div class="carousel-inner">
                <div class="carousel-item active" data-bs-interval="4000">
                    <img src="https://images.unsplash.com/photo-1511192336575-5a79af67a629?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Premium Guitars" style="height: 400px; object-fit: cover; filter: brightness(0.8);">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded px-3 py-2 border border-secondary mb-3">
                        <h5 class="fw-bold text-primary-custom mb-1">Premium Guitars</h5>
                        <p class="small mb-0 text-white opacity-75">Iconic brands and unmatched resonance.</p>
                    </div>
                </div>
                <div class="carousel-item" data-bs-interval="4000">
                    <img src="https://images.unsplash.com/photo-1552422535-c45813c61732?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Studio Keyboards" style="height: 400px; object-fit: cover; filter: brightness(0.8);">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded px-3 py-2 border border-secondary mb-3">
                        <h5 class="fw-bold text-secondary-custom mb-1">Studio Synthesizers</h5>
                        <p class="small mb-0 text-white opacity-75">Unlock limitless sonic possibilities.</p>
                    </div>
                </div>
                <div class="carousel-item" data-bs-interval="4000">
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Audio Interfaces" style="height: 400px; object-fit: cover; filter: brightness(0.8);">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded px-3 py-2 border border-secondary mb-3">
                        <h5 class="fw-bold text-accent-custom mb-1">Pro Audio Hardware</h5>
                        <p class="small mb-0 text-white opacity-75">Capture every detail with pristine clarity.</p>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#tunehubHeroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#tunehubHeroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>

<!-- Highlight Section -->
<div class="row mt-5 mb-5 p-5 glass-panel">
    <div class="col-md-4 text-center mb-4 mb-md-0 stagger-item">
        <i class="bi bi-shield-check text-accent-custom" style="font-size: 3rem;"></i>
        <h3 class="h5 mt-3">Secure Checkout</h3>
        <p class="small">Your transactions are protected with state-of-the-art encryption.</p>
    </div>
    <div class="col-md-4 text-center mb-4 mb-md-0 stagger-item">
        <i class="bi bi-camera-video text-secondary-custom" style="font-size: 3rem;"></i>
        <h3 class="h5 mt-3">Expert Tutorials</h3>
        <p class="small">Learn to play with curated videos matching the products you buy.</p>
    </div>
    <div class="col-md-4 text-center stagger-item">
        <i class="bi bi-box-seam text-primary-custom" style="font-size: 3rem;"></i>
        <h3 class="h5 mt-3">Live Inventory</h3>
        <p class="small">Real-time stock tracking means you get what you ordered.</p>
    </div>
</div>

<div class="row mt-5 reveal-up">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2" style="border-color: rgba(0,0,0,0.1) !important;">
            <h2 class="text-primary-custom m-0">Recommended for You</h2>
            <a href="<?php echo base_url('modules/products/index.php'); ?>" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>

        <div id="recommendations-container" class="row">
            <?php if(!isset($_SESSION['user_id'])): ?>
            <div class="col-12 text-center py-5 glass-panel card">
                <i class="bi bi-magic fs-1 text-secondary-custom mb-3"></i>
                <h4>Personalized Recommendations Await</h4>
                <p>Login or create an account to see instruments curated just for your skill level, preferred brands, and budget!</p>
                <a href="<?php echo base_url('modules/user/login.php'); ?>" class="btn btn-primary mt-2">Login Now</a>
            </div>
            <?php else: ?>
            <div class="col-12 text-center py-5 glass-panel card">
                <i class="bi bi-magic fs-1 text-secondary-custom mb-3"></i>
                <h4>Welcome back, <?php echo isset($_SESSION['username']) ? sanitize_html($_SESSION['username']) : 'Musician'; ?>!</h4>
                <p>We've analyzed your profile to calculate your personalized gear matches.</p>
                <a href="<?php echo base_url('modules/user/recommendations.php'); ?>" class="btn btn-primary mt-2">View Your Recommendations</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
