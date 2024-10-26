<?php

/**
 * Search Map Shortcode
 */
add_shortcode('oneday_search_map', 'odp_search_map_output');
function odp_search_map_output($atts)
{
    ob_start(); ?>

    <?php ?>
    <?php include_once __DIR__ . "/../templates/search/map.php"; ?>

    <?php

    $out = ob_get_contents();
    ob_end_clean();

    return $out;
}