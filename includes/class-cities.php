<?php
/**
 * Core class for Cities
 */
class Cities
{

    const POST_TYPE = 'cities';

    /**
     * Get the class post type slug
     * 
     * @return string
     */
    public function get_post_type()
    {
        return self::POST_TYPE;
    }

    public function init_hooks()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'create_countries_taxonomy']);

        add_action('add_meta_boxes', [$this, 'add_metabox_for_cities']);
        add_action('save_post', [$this, 'save_city_location_meta_box']);

        add_action('wp_ajax_city_search', [$this, 'ajax_search_cities']);
        add_action('wp_ajax_nopriv_city_search', [$this, 'ajax_search_cities']);

        add_action('save_post', [$this, 'clear_query_cities_cache'], 10, 3);
        add_action('wp_insert_post', [$this, 'clear_query_cities_cache'], 10, 3);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('city-search', get_stylesheet_directory_uri() . '/assets/js/city-search.js', array('jquery'), null, true);

        wp_localize_script('city-search', 'city_search', array(
            'ajax_nonce' => wp_create_nonce( 'cities_ajax_nonce' ),
            'ajax_url' => admin_url('admin-ajax.php'),
            'action' => 'city_search',
        ));
    }


    /**
     * Creates a new custom post type for Cities
     * 
     * @return void
     */
    public function register_post_type()
    {
        $labels = array(
            'name'               => 'Cities',
            'singular_name'      => 'City',
            'add_new'            => 'Add New City',
            'add_new_item'       => 'Add New City',
            'edit_item'          => 'Edit City',
            'new_item'           => 'New City',
            'view_item'          => 'View City',
            'search_items'       => 'Search Cities',
            'not_found'          => 'No cities found',
            'not_found_in_trash' => 'No cities found in Trash',
            'all_items'          => 'All Cities',
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => $this->get_post_type()),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'supports'           => array('title', 'editor', 'thumbnail'),
            'taxonomies'         => array('countries'),
            'menu_icon'          => 'dashicons-location-alt',
        );
    
        register_post_type($this->get_post_type(), $args);
    }

    /**
     * Creates a new taxonomy Countries.
     * Associated to Cities post type
     * 
     * @return void
     */
    public function create_countries_taxonomy() {
        $labels = array(
            'name'              => __( 'Countries', 'text_domain' ),
            'singular_name'     => __( 'Country', 'text_domain' ),
            'search_items'      => __( 'Search Countries', 'text_domain' ),
            'all_items'         => __( 'All Countries', 'text_domain' ),
            'edit_item'         => __( 'Edit Country', 'text_domain' ),
            'update_item'       => __( 'Update Country', 'text_domain' ),
            'add_new_item'      => __( 'Add New Country', 'text_domain' ),
            'new_item_name'     => __( 'New Country Name', 'text_domain' ),
            'menu_name'         => __( 'Countries', 'text_domain' ),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => false, // Still hierarchical for the dropdown
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'country'),
            'meta_box_cb'       => 'post_categories_meta_box', // Use the category dropdown UI
        );

        register_taxonomy( 'country', array( $this->get_post_type() ), $args );
    }

    /**
     * Adds a metabox for entering the city location (latitude and longitude) in the Cities post type.
     */
    public function add_metabox_for_cities()
    {
        add_meta_box(
            'city_location',
            __( 'City Location', 'storefront' ),
            [$this, 'render_city_meta_box'],
            $this->get_post_type(),
            'side',
            'default'
        );
    }

    /**
     * Renders the content of the City Location metabox.
     *
     * @param WP_Post $post
     */
    public function render_city_meta_box($post) {
        get_template_part('template-parts/metabox', 'city-location', ['post_id' => $post->ID]);
    }

    /**
     * Saves the latitude and longitude metadata for a City post.
     *
     * @param int $post_id
     */
    public function save_city_location_meta_box($post_id) {
        if (isset($_POST['city_latitude'])) {
            update_post_meta($post_id, '_city_latitude', sanitize_text_field($_POST['city_latitude']));
        }
    
        if (isset($_POST['city_longitude'])) {
            update_post_meta($post_id, '_city_longitude', sanitize_text_field($_POST['city_longitude']));
        }
    }

    /**
     * Queries the 'Cities' custom post type and retrieves a list of cities 
     * along with their associated countries. Supports optional search functionality.
     * 
     * @param string|null $search Optional
     *
     *  @return array|object|null|bool
     */
    public static function query_cities($search = null) {
        global $wpdb;

        $post_type = self::POST_TYPE;

        $cache_key = 'query_cities';

        $cities = get_transient($cache_key);

        // return cached cities only if user is not searching
        if ($cities && !isset($search)) return $cities;

        $query = "
            SELECT p.ID, p.post_title, t.name as country_name FROM $wpdb->posts p
            INNER JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
            INNER JOIN $wpdb->terms t ON tr.term_taxonomy_id = t.term_id
            WHERE p.post_type = '{$post_type}' AND p.post_status = 'publish'
        ";

        if (isset($search)) {
            $query .= " AND p.post_title LIKE '%{$search}%'";
            $query .= " OR t.name LIKE '%{$search}%'";
        }

        $cities = $wpdb->get_results($query);

        set_transient($cache_key, $cities, MONTH_IN_SECONDS);

        return $cities;
    }

    /**
     * Handles the AJAX request to search for cities by a given search term.
     * Returns a table row for each city found, including the country name, city name,
     * and current temperature.
     *
     * @global wpdb $wpdb
     *
     * @return void
     */
    public function ajax_search_cities() {
        global $wpdb;

        if (isset( $_REQUEST['ajax_nonce'] ) && wp_verify_nonce( $_REQUEST['ajax_nonce'], 'cities_ajax_nonce' ) ) {
            $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
            $cities = self::query_cities($search_term);

            if (!empty($cities)) {
                foreach ($cities as $city) {
                    $temperature = self::get_city_temperature($city->ID);

                    ob_start();
                        get_template_part('template-parts/table', 'cities-row-item', [
                            'country_name' => $city->country_name,
                            'city' => $city->post_title,
                            'temperature' => $temperature
                        ]);
                    echo ob_get_clean();
                }
            } else {
                echo '<tr><td colspan="3">No cities found.</td></tr>';
            }
        } else {
            echo '<tr><td colspan="3">Invalid request.</td></tr>';
        }

        wp_die();
    }

    /**
     * Retrieves the current temperature for a city based on its latitude and longitude.
     * 
     * The temperature data is fetched from the OpenWeatherMap API and cached for 1 hour
     * to reduce API calls and improve performance.
     *
     * @param int $post_id
     *
     * @return string
     */
    public static function get_city_temperature($post_id) {
        if (! $post_id) return "N/A";

        $latitude = get_post_meta($post_id, '_city_latitude', true);
        $longitude = get_post_meta($post_id, '_city_longitude', true);
        if (!$latitude || !$longitude) return "N/A";

        $cache_key = "city_temperature_{$post_id}";

        $temperature = get_transient($cache_key);

        // if not empty, or false, return cached value
        if ($temperature) return $temperature;

        // Fetch temperature from OpenWeatherMap API
        $api_key = OPENWEATHERMAP_API_KEY;
        $response = wp_remote_get("https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric");
        $data = json_decode(wp_remote_retrieve_body($response), true);
        $temperature = isset($data['main']['temp']) ? $data['main']['temp'] . ' °C' : 'N/A';

        // set cache value for 1 hour
        set_transient($cache_key, $temperature, HOUR_IN_SECONDS);

        return $temperature;
    }

    /**
     * Clears the cache for the city query when a City post is created, updated, or deleted.
     * 
     * This ensures that the cached data reflects the most recent changes to the Cities custom post type.
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     */
    public function clear_query_cities_cache($post_id, $post, $update) {
        if ($post->post_type !== self::POST_TYPE) return;

        delete_transient("query_cities");
    }
}

/**
 * Instantiates and returns a new Cities object
 * 
 * @return Cities
 */
function test_work_cities() {
    return new Cities();
}

test_work_cities()->init_hooks();