<?php
    $post_id = $args['post_id'];
    if (! $post_id) return;

    $latitude = get_post_meta($post_id, '_city_latitude', true);
    $longitude = get_post_meta($post_id, '_city_longitude', true);
?>
<style>
    .form-input {
        margin-bottom: 5px;
    }

    .form-input-field {
        width: 100%;
    }
</style>
<div class="form-input">
    <label class="form-input-label" for="city_latitude">Latitude</label>
    <input class="form-input-field" type="text" id="city_latitude" name="city_latitude" value="<?= esc_attr($latitude); ?>" class="form-required" size="100" required />
</div>

<div class="form-input">
    <label class="form-input-label" for="city_longitude">Longitude</label>
    <input class="form-input-field" type="text" id="city_longitude" name="city_longitude" value="<?= esc_attr($longitude); ?>" class="form-required" required />
</div>
