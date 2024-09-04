<?php

define('OPENWEATHERMAP_API_KEY', 'API_KEY_HERE');

function storefront_child_enqueue_styles() {
    wp_enqueue_style('storefront-child-style', get_stylesheet_uri(), array('storefront-style'));
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

// Include Custom Post Type
require get_stylesheet_directory() . '/includes/class-cities.php';

// Include City Temperature Widget
require get_stylesheet_directory() . '/widgets/city-temparature-widget.php';

add_action('before_city_table', 'render_city_temperature_widget');
add_action('after_city_table', 'render_city_temperature_widget');
function render_city_temperature_widget() {
    dynamic_sidebar( 'sidebar-1' );
}