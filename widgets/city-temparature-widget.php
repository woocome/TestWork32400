<?php
/**
 * Class City_Temperature_Widget
 *
 * A widget that displays the current temperature of a selected city.
 */
class City_Temperature_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'city_temperature_widget',
            'City Temperature',
            array('description' => 'Displays a city\'s current temperature.')
        );
    }

    /**
     * Outputs the content of the widget.
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['city'])) {
            $city_id = $instance['city'];
            $city = get_post($city_id);
            $temperature = test_work_cities()::get_city_temperature($city_id);

            echo $args['before_title'] . esc_html($city->post_title) . $args['after_title'];
            echo '<p>Temperature: ' . esc_html($temperature) . '</p>';
        }
        echo $args['after_widget'];
    }

    /**
     * Outputs the settings form for the widget in the admin area.
     *
     * @param array $instance
     */
    public function form($instance) {
        $city_id = !empty($instance['city']) ? $instance['city'] : '';
        $cities = get_posts(array(
            'post_type' => 'cities',
            'posts_per_page' => -1
        ));

        echo '<p>';
        echo '<label for="' . $this->get_field_id('city') . '">' . __('City:', 'storefront') . '</label>';
        echo '<select id="' . $this->get_field_id('city') . '" name="' . $this->get_field_name('city') . '">';
        
        foreach ($cities as $city) {
            echo '<option value="' . esc_attr($city->ID) . '" ' . selected($city->ID, $city_id, false) . '>' . esc_html($city->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '</p>';
    }

    /**
     * Updates the widget settings.
     *
     * @param array $new_instance
     * @param array $old_instance
     * 
     * @return array Updated settings.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['city'] = (!empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '';
        return $instance;
    }
}

/**
 * Registers the City_Temperature_Widget.
 */
function register_city_temperature_widget() {
    register_widget('City_Temperature_Widget');
}
add_action('widgets_init', 'register_city_temperature_widget');
