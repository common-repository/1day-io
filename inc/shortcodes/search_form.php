<?php

/**
 * Search Form Shortcode
 */
add_shortcode('oneday_search_form', 'odp_search_form_output');
function odp_search_form_output($atts) {

    if(empty($atts['filter_id']))
        return;

    $filter_id = sanitize_text_field($atts['filter_id']);
    $filter_form_settings = odp_get_filter_form_settings($filter_id);

    ob_start(); ?>

    <?php include_once __DIR__ . "/../templates/search/form.php"; ?>

    <?php

    $out = ob_get_contents();
    ob_end_clean();

    return $out;
}