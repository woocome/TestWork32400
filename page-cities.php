<?php
/**
 * Template Name: Cities
 */

get_header();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        // Custom action hook before the table
        do_action('before_city_table');

        // Include the table template part
        get_template_part('template-parts/table-cities');

        // Custom action hook after the table
        do_action('after_city_table');
        ?>
    </main><!-- #main -->
</div><!-- #primary -->
<?php
get_footer();
