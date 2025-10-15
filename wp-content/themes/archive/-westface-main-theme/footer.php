<?php
/**
 * Footer template for Westface Child Theme
 *
 * @package WordPress
 * @subpackage westface-child
 */
?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="footer-container">
                <!-- Column 1: About -->
                <div class="footer-column footer-about">
                  
                    <?php if (has_custom_logo()) : ?>
                        <div class="footer-logo site-title">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <div class="footer-logo">
                            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/footer-logo.png'); ?>" alt="<?php bloginfo('name'); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <p><?php echo get_theme_mod('footer_about_text', __('Olemme moderni yritys, joka keskittyy korkealaatuisten ratkaisujen toimittamiseen skandinaavisilla suunnitteluperiaatteilla: yksinkertaisuus, minimalismi ja toiminnallisuus.', 'westface-child')); ?></p>
                </div>
                
                <!-- Column 2: Quick Links -->
                <div class="footer-column footer-links">
                    <h3><?php _e('Pikalinkit', 'westface-child'); ?></h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-1',
                        'menu_class'     => 'footer-menu',
                        'container'      => false,
                        'depth'          => 2,
                        'fallback_cb'    => function() {
                            echo '<ul class="footer-menu">';
                            echo '<li><a href="' . esc_url(home_url('/')) . '">' . __('Etusivu', 'westface-child') . '</a></li>';
                            echo '<li><a href="#">' . __('Tietoa meistä', 'westface-child') . '</a></li>';
                            echo '<li><a href="#">' . __('Palvelut', 'westface-child') . '</a></li>';
                            echo '<li><a href="#">' . __('Portfolio', 'westface-child') . '</a></li>';
                            echo '<li><a href="#">' . __('Yhteystiedot', 'westface-child') . '</a></li>';
                            echo '</ul>';
                        },
                    ));
                    ?>
                </div>
                
                <!-- Column 3: Contact Info -->
                <div class="footer-column footer-contact">
                    <h3><?php _e('Ota yhteyttä', 'westface-child'); ?></h3>
                    <ul>
                        <li>
                            <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span><?php echo get_theme_mod('footer_address', __('Pulttitie 20, 00880 Helsinki', 'westface-child')); ?></span>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-phone"></i></span>
                            <a href="tel:<?php echo esc_attr(get_theme_mod('footer_phone', '+358 40 5206942')); ?>">
                                <?php echo get_theme_mod('footer_phone', __('+ 358 40 5206942', 'westface-child')); ?>
                            </a>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-envelope"></i></span>
                            <a href="mailto:<?php echo esc_attr(get_theme_mod('footer_email', 'info@westface.fi')); ?>">
                                <?php echo get_theme_mod('footer_email', __('info@westface.fi', 'westface-child')); ?>
                            </a>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-clock"></i></span>
                            <span><?php echo get_theme_mod('footer_hours', __('Ma - Pe: 9:00 - 17:00', 'westface-child')); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('Kaikki oikeudet pidätetään.', 'westface-child'); ?></p>
            </div>
        </div>
    </footer><!-- #colophon -->


</div><!-- #page -->
<script>

    if(document.querySelector(".scroll-to-top")) {
      const scrollToTopBtn = document.querySelector(".scroll-to-top");

        // Add a scroll event listener to the window
        window.addEventListener("scroll", () => {
            // If the user has scrolled down more than 300px from the top
            if (window.scrollY > 300) {
                // Add the 'show' class to the button, making it visible
                scrollToTopBtn.classList.add("show");
            } else {
                // Otherwise, remove the 'show' class, hiding it
                scrollToTopBtn.classList.remove("show");
            }
        });

        // Add a click event listener to the button
        scrollToTopBtn.addEventListener("click", () => {
            // When the button is clicked, scroll to the top of the page smoothly
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });  
    }
     // Get the button element
        
</script>

<?php wp_footer(); ?>

</body>
</html>
