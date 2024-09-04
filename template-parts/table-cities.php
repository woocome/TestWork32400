<form id="city-search-form">
    <input type="text" name="city-search" id="city-search" class="form-required form-field" placeholder="Search cities..." required>
</form>

<div id="city-table">
    <table>
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Temperature</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $cities = test_work_cities()::query_cities();

                foreach ($cities as $city) :
                    $temperature = test_work_cities()::get_city_temperature($city->ID);
            ?>
                <?php
                    get_template_part('template-parts/table', 'cities-row-item', [
                        'country_name' => $city->country_name,
                        'city' => $city->post_title,
                        'temperature' => $temperature
                    ]);
                ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
