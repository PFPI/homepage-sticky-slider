/**
 * Initializes the Swiper.js slider for the Homepage Sticky Slider.
 */
document.addEventListener('DOMContentLoaded', function () {

    // Base configuration for the slider.
    const swiperConfig = {
        direction: 'horizontal',
        loop: true,

        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },

        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        slidesPerView: 1,
        spaceBetween: 10,
    };

    // Check if autoplay is enabled in our settings object (passed from PHP).
    // The `hss_slider_settings` object is created by wp_localize_script.
    if (hss_slider_settings.autoplay_enabled) {
        swiperConfig.autoplay = {
            delay: hss_slider_settings.autoplay_delay,
            disableOnInteraction: false,
        };
    }

    // Initialize the Swiper with our final configuration.
    const swiper = new Swiper('.hss-swiper', swiperConfig);
});
