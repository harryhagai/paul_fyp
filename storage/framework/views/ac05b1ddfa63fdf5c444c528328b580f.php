



<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/about.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
    $brandName = $headerSettings['header_school_name'] ?? config('app.name', 'KidzStore365');
    $headerLogo = $headerSettings['header_logo'] ?? null;
    $headerLogoUrl = $headerLogo ? asset($headerLogo) : asset('img/logo.png');
    $pageSettings = $pageSettings ?? [];
?>

<div class="about-page">
    <section class="about-hero">
        <div class="about-shell about-hero-grid">
            <div>
                <h1 class="about-title display-6 fw-semibold lh-sm"><?php echo e($pageContent->title ?? 'Thoughtful products for little everyday joys.'); ?></h1>
                <p class="about-lead lead mb-0 text-body-secondary">
                    <?php echo e($pageContent->subtitle ?? ($brandName . ' helps parents find safe, cute, and practical essentials for children, from everyday wear to toys and gifts chosen with care.')); ?>

                </p>
                <div class="about-actions">
                    <a href="/shop" class="about-btn about-btn-primary">
                        <i class="bi bi-bag-heart"></i>
                        Shop Products
                    </a>
                    <a href="<?php echo e(route('contact')); ?>" class="about-btn about-btn-ghost">
                        <i class="bi bi-chat-dots"></i>
                        Contact Us
                    </a>
                </div>
                </div>

            <div class="about-visual">
                <div class="about-logo-frame">
                    <div class="about-logo-backdrop"></div>
                    <div class="about-logo-card">
                        <img src="<?php echo e($headerLogoUrl); ?>" alt="<?php echo e($brandName); ?> logo">
                    </div>
                </div>
                <div class="about-floating-note">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong class="fw-semibold">Parent-minded curation</strong>
                        <span class="small">Quality, comfort, and safety guide what we bring into the store.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-shell about-story-grid">
            <div class="about-story-text">
                <div class="about-section-head text-start mx-0">
                    <h2 class="about-section-title h3 fw-semibold"><?php echo e($pageSettings['story_title'] ?? 'Built for families who want shopping to feel easier.'); ?></h2>
                    <p class="about-section-copy mb-0 text-body-secondary">
                        <?php echo e($pageSettings['story_intro'] ?? 'We focus on products that make childhood feel cared for: soft clothes, playful toys, thoughtful gifts, and essentials parents can trust.'); ?>

                    </p>
                </div>
                <p>
                    <?php echo e($pageSettings['story_body_1'] ?? ($pageContent->body ?? 'Our store began with a simple idea: parents should not have to choose between adorable, practical, and safe. Every collection is selected with real family routines in mind, so shopping feels calm and useful.')); ?>

                </p>
                <p>
                    <?php echo e($pageSettings['story_body_2'] ?? 'Whether you are preparing for a newborn, refreshing school items, or choosing a gift, we keep the experience simple, friendly, and reliable.'); ?>

                </p>

            </div>

            <div class="about-promise">
                <div class="about-promise-item">
                    <i class="bi bi-patch-check"></i>
                    <div>
                        <h3><?php echo e($pageSettings['promise_1_title'] ?? 'Safety First'); ?></h3>
                        <p><?php echo e($pageSettings['promise_1_text'] ?? 'We prioritize products that feel dependable for children and reassuring for parents.'); ?></p>
                    </div>
                </div>
                <div class="about-promise-item">
                    <i class="bi bi-heart"></i>
                    <div>
                        <h3><?php echo e($pageSettings['promise_2_title'] ?? 'Chosen With Care'); ?></h3>
                        <p><?php echo e($pageSettings['promise_2_text'] ?? 'Items are selected for comfort, usefulness, charm, and everyday value.'); ?></p>
                    </div>
                </div>
                <div class="about-promise-item">
                    <i class="bi bi-truck"></i>
                    <div>
                        <h3><?php echo e($pageSettings['promise_3_title'] ?? 'Smooth Delivery'); ?></h3>
                        <p><?php echo e($pageSettings['promise_3_text'] ?? 'We work to keep ordering clear and delivery quick, secure, and predictable.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section about-section-soft">
        <div class="about-shell">
            <div class="about-section-head">
                    <h2 class="about-section-title h3 fw-semibold"><?php echo e($pageSettings['values_title'] ?? 'What guides us'); ?></h2>
                    <p class="about-section-copy mb-0 text-body-secondary">
                    <?php echo e($pageSettings['values_intro'] ?? 'Simple values shape every product, every order, and every conversation with our customers.'); ?>

                </p>
            </div>

            <div class="about-values-grid">
                <div class="about-value-card">
                    <div class="about-card-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>Safe Choices</h3>
                    <p>Products are selected with child comfort and parent peace of mind at the center.</p>
                </div>
                <div class="about-value-card">
                    <div class="about-card-icon"><i class="bi bi-palette"></i></div>
                    <h3>Playful Style</h3>
                    <p>We love items that feel bright, expressive, and joyful without losing practicality.</p>
                </div>
                <div class="about-value-card">
                    <div class="about-card-icon"><i class="bi bi-headset"></i></div>
                    <h3>Helpful Service</h3>
                    <p>Questions, orders, and support are handled with clarity and real attention.</p>
                </div>
                <div class="about-value-card">
                    <div class="about-card-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <h3>Easy Experience</h3>
                    <p>We keep shopping, delivery, and support straightforward from start to finish.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-band">
        <div class="about-shell about-band-grid">
            <div>
                <h2><?php echo e($pageSettings['band_title'] ?? 'Ready to find something lovely?'); ?></h2>
                <p>
                    <?php echo e($pageSettings['band_text'] ?? 'Browse thoughtful kids products chosen for comfort, joy, and everyday family life.'); ?>

                </p>
            </div>
            <div class="about-actions">
                <a href="/shop" class="about-btn">
                    <i class="bi bi-arrow-right"></i>
                    Start Shopping
                </a>
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\about.blade.php ENDPATH**/ ?>