<?php
/**
 * Handles the frontend display of the sticky posts slider.
 *
 * @package HomepageStickySlider
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Dynamically hooks the slider display function into the theme.
 */
function hss_init_slider_hook() {
    $hook_name = get_option( 'hss_slider_hook_name', 'generate_after_header' );
    if ( ! empty( $hook_name ) ) {
        add_action( trim( $hook_name ), 'hss_display_slider' );
    }
}
add_action( 'wp', 'hss_init_slider_hook' );


/**
 * Enqueues scripts and styles for the slider.
 */
function hss_enqueue_slider_assets() {
    if ( is_front_page() ) {
        // Enqueue styles and scripts.
        wp_enqueue_style( 'swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), '8.0.0' );
        wp_enqueue_style( 'hss-slider-style', plugin_dir_url( __FILE__ ) . '../css/slider-style.css', array(), HSS_VERSION );
        wp_enqueue_script( 'swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), '8.0.0', true );
        wp_enqueue_script( 'hss-slider-init', plugin_dir_url( __FILE__ ) . '../js/slider-init.js', array( 'swiper-js' ), HSS_VERSION, true );

        // Get settings to pass to our script.
        $autoplay_enabled = get_option( 'hss_enable_autoplay', 'on' );
        $autoplay_delay = get_option( 'hss_autoplay_delay', 5000 );

        $slider_settings = array(
            'autoplay_enabled' => ( 'on' === $autoplay_enabled ),
            'autoplay_delay'   => absint( $autoplay_delay ),
        );

        // Pass the settings to the script.
        wp_localize_script( 'hss-slider-init', 'hss_slider_settings', $slider_settings );
    }
}
add_action( 'wp_enqueue_scripts', 'hss_enqueue_slider_assets' );

/**
 * Renders the slider HTML on the frontend.
 */
function hss_display_slider() {
    if ( ! is_front_page() ) {
        return;
    }

    // Get settings from the database.
    $posts_to_show  = get_option( 'hss_slider_post_count', 5 );
    $margin_top     = get_option( 'hss_slider_margin_top', 20 );
    $margin_bottom  = get_option( 'hss_slider_margin_bottom', 20 );
    $click_behavior = get_option( 'hss_click_behavior', 'entire_slide' );

    // Prepare inline styles for the container.
    $inline_styles = sprintf(
        'margin-top: %dpx; margin-bottom: %dpx;',
        absint( $margin_top ),
        absint( $margin_bottom )
    );

    // WP_Query arguments.
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => $posts_to_show,
        'meta_query'     => array(
            array(
                'key'     => '_is_sticky_post',
                'value'   => 'yes',
                'compare' => '=',
            ),
        ),
    );

    $sticky_posts_query = new WP_Query( $args );

    if ( $sticky_posts_query->have_posts() ) {
        ?>
        <div class="hss-slider-container" style="<?php echo esc_attr( $inline_styles ); ?>">
            <div class="swiper hss-swiper">
                <div class="swiper-wrapper">
                    <?php while ( $sticky_posts_query->have_posts() ) : $sticky_posts_query->the_post(); ?>
                        <div class="swiper-slide">
                            <?php if ( 'entire_slide' === $click_behavior ) : ?><a href="<?php the_permalink(); ?>" class="hss-slide-link"><?php endif; ?>

                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="hss-slide-image"><?php the_post_thumbnail( 'large' ); ?></div>
                                <?php endif; ?>

                                <div class="hss-slide-content">
                                    <h3 class="hss-slide-title">
                                        <?php if ( 'title_only' === $click_behavior ) : ?>
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        <?php else : ?>
                                            <?php the_title(); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="hss-slide-excerpt"><?php the_excerpt(); ?></div>
                                    <?php if ( 'read_more' === $click_behavior ) : ?>
                                        <a href="<?php the_permalink(); ?>" class="hss-read-more">Read More</a>
                                    <?php endif; ?>
                                </div>

                            <?php if ( 'entire_slide' === $click_behavior ) : ?></a><?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
        <?php
    }

    wp_reset_postdata();
}
