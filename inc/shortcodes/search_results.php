<?php

/**
 * Search Search Shortcode
 */
add_shortcode('oneday_search_results', 'odp_search_results_output');
function odp_search_results_output($atts)
{

    $filter_id = !empty($atts['filter_id']) ? $atts['filter_id'] : '';

    $filter_results_settings = odp_get_filter_results_settings($filter_id);

    $start_date = !empty($_GET['start_date'])
        ? sanitize_text_field($_GET['start_date'])
        : odp_gc('start_date');

    $end_date = !empty($_GET['end_date'])
        ? sanitize_text_field($_GET['end_date'])
        : odp_gc('end_date');

    if (is_tax('hotel') && isset($atts['dynamic'])) {
        $property_code = odp_get_current_hotel_property_code();
    } else {
        $property_code = !empty($filter_results_settings['property_code']) ? $filter_results_settings['property_code'] : '';
    }

    $current_page = !empty($_GET['pa']) ? sanitize_text_field($_GET['pa']) : 1;
    $limit = empty($property_code) ? odp_get_hotel_page_items_count() : 0;

    $template = !empty($property_code) ? 'rooms' : 'hotels';

    ?>


    <?php if (empty($start_date) || empty($end_date)) : ?>
        <?php ob_start(); ?>

        <div class="odp_search_results-empty">
            <?php echo esc_attr__('Empty search results. Please, enter the dates (from, to)', '1day'); ?>
        </div>

        <?php
        $out = ob_get_contents();
        ob_end_clean();

        return $out; ?>

    <?php endif; ?>

    <?php
    ob_start();

    $guests = !empty($_GET['guests'])
        ? sanitize_text_field($_GET['guests'])
        : odp_gc('guests');

    $sort = !empty($_GET['sort'])
        ? sanitize_text_field($_GET['sort'])
        : odp_gc('sort');

    $args = [
        'start_date' => prepare_date($start_date, odp_get_date_format(), 'Y-m-d'),
        'end_date' => prepare_date($end_date, odp_get_date_format(), 'Y-m-d'),
        'guests' => $guests,
        'sort' => "price.{$sort}",
        'limit' => $limit,
        'offset' => $limit * ($current_page - 1)
    ];

    if (!empty($property_code)) {
        $args['property_code'] = $property_code;
    }

    $result = odp_api_connect(ODP_API_URL, 'get', $args);

    $pages = !empty($result['content']->total) && empty($property_code) ? ceil((int)sanitize_text_field($result['content']->total) / $limit) : 1;

    if (!$result['state']) return '';

    $items = !empty($result['content']->data) ? $result['content']->data : false;

    global $hotel_points;


    if (empty($property_code) && !empty($items)) {
        foreach ($items as $hotel) {
            if (!empty($hotel->latitude) && !empty($hotel->longitude)) {
                $hotel_points[] = (object)[
                    'lat' => sanitize_text_field($hotel->latitude),
                    'lng' => sanitize_text_field($hotel->longitude)
                ];
            }

        }
    }

    ?>

    <?php ?>
    <div class="odp_search_results" <?php echo "id=\"odp_search_results_" . esc_attr($filter_id) . "\""; ?> >
        <?php include_once __DIR__ . "/../templates/search/{$template}.php"; ?>
    </div>

    <style>
        #odp_search_results_<?php echo $filter_id?> .odp_btn {
        <?php echo !empty($filter_results_settings['buttons_background']) ? "background: " . sanitize_text_field($filter_results_settings['buttons_background']) : ''; ?>;
        <?php echo !empty($filter_results_settings['buttons_font_color']) ? "color: " . sanitize_text_field($filter_results_settings['buttons_font_color']) : ''; ?>;
        <?php echo !empty($filter_results_settings['buttons_padding']) ? "padding: " . sanitize_text_field($filter_results_settings['buttons_padding']) : ''; ?>;
        }

    </style>

    <?php
    $out = ob_get_contents();
    ob_end_clean();

    return $out;
}
