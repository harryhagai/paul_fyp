
<!-- Footer Component - Kids Shop Edition -->
<?php
    $footerSettings = \App\Models\SiteSetting::where('group', 'footer')->pluck('value', 'key');
?>
<footer class="footer">
    <div class="footer-container">
        
        <!-- Top Section -->
        <div class="footer-top">
            
            <!-- Brand Section -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <div class="footer-logo-text">
                        <h3 class="footer-school-name"><?php echo e($footerSettings['footer_brand_name'] ?? 'myKidsShop365'); ?></h3>
                        <p class="footer-school-subtitle"><?php echo e($footerSettings['footer_brand_subtitle'] ?? '“Everything Cute for Little Ones”'); ?></p>
                    </div>
                </div>

                <p class="footer-description">
                    <?php echo e($footerSettings['footer_description'] ?? 'Your #1 trusted store for baby clothes, toys, accessories, maternity items, and every adorable thing your child deserves.'); ?>

                </p>

                <div class="footer-social-links">
                    <?php if(!empty($footerSettings['footer_social_facebook']) && $footerSettings['footer_social_facebook'] !== '#'): ?>
                    <a href="<?php echo e($footerSettings['footer_social_facebook']); ?>" class="footer-social-link">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <?php endif; ?>
                    <?php if(!empty($footerSettings['footer_social_instagram']) && $footerSettings['footer_social_instagram'] !== '#'): ?>
                    <a href="<?php echo e($footerSettings['footer_social_instagram']); ?>" class="footer-social-link">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <?php endif; ?>
                    <?php if(!empty($footerSettings['footer_social_tiktok']) && $footerSettings['footer_social_tiktok'] !== '#'): ?>
                    <a href="<?php echo e($footerSettings['footer_social_tiktok']); ?>" class="footer-social-link">
                        <i class="bi bi-tiktok"></i>
                    </a>
                    <?php endif; ?>
                    <?php if(!empty($footerSettings['footer_social_youtube']) && $footerSettings['footer_social_youtube'] !== '#'): ?>
                    <a href="<?php echo e($footerSettings['footer_social_youtube']); ?>" class="footer-social-link">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Contact -->
            <div class="footer-links-grid">
                <div class="footer-column footer-contact">
                    <h4 class="footer-column-title">Contact Us</h4>
                    <div class="footer-contact-info">

                        <div class="footer-contact-item">
                            <i class="bi bi-geo-alt"></i>
                            <span><?php echo e($footerSettings['footer_contact_address'] ?? 'Kids Plaza, Dar es Salaam, Tanzania'); ?></span>
                        </div>

                        <div class="footer-contact-item">
                            <i class="bi bi-telephone"></i>
                            <span><?php echo e($footerSettings['footer_contact_phone'] ?? '+255 712 345 678'); ?></span>
                        </div>

                        <div class="footer-contact-item">
                            <i class="bi bi-envelope"></i>
                            <span><?php echo e($footerSettings['footer_contact_email'] ?? 'support@mykidsshop365.com'); ?></span>
                        </div>

                        <div class="footer-contact-item">
                            <i class="bi bi-clock"></i>
                            <span><?php echo e($footerSettings['footer_contact_hours'] ?? 'Mon - Sat: 9:00 AM - 7:00 PM'); ?></span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Section -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="footer-copyright">
                    &copy; <?php echo e(now()->year); ?> <?php echo e($footerSettings['footer_brand_name'] ?? 'myKidsShop365'); ?>. All rights reserved.
                </p>

                <div class="footer-bottom-links">
                    <a href="#" class="footer-bottom-link"><i class="bi bi-shield-lock"></i><span>Privacy Policy</span></a>
                    <a href="#" class="footer-bottom-link"><i class="bi bi-file-earmark-text"></i><span>Terms of Service</span></a>
                    <a href="#" class="footer-bottom-link"><i class="bi bi-arrow-counterclockwise"></i><span>Returns</span></a>
                </div>

            </div>
        </div>

    </div>
</footer>
<?php /**PATH C:\Users\hngob\eccomerce_edu\resources\views\components\footer.blade.php ENDPATH**/ ?>