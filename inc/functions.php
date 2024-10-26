<?php

function odp_p($object)
{
    print("<pre>" . print_r($object, true) . "</pre>");
}

function odp_w($object, $type)
{
    fwrite(fopen(__DIR__ . '/test.txt', $type), wp_json_encode($object));
}

function odp_sc($key, $value)
{
    if (isset($_COOKIE[$key])) {
        unset($_COOKIE[$key]);
        setcookie("odp_{$key}", null, time() - 3600, '/');
    }

    setcookie("odp_{$key}", sanitize_text_field($value), time() + (60 * 60 * 24 * 30), '/');
}

function odp_gc($key)
{
    if (!empty($_GET[$key]))
        return sanitize_text_field($_GET[$key]);
    if (!empty($_COOKIE["odp_{$key}"]))
        return sanitize_text_field($_COOKIE["odp_{$key}"]);
    return '';
}

function odp_is_selected($field, $value)
{
    if ((!empty($_GET[$field]) && $_GET[$field] == $value) || (!empty(odp_gc($field)) && odp_gc($field) == $value))
        return 'selected';
    return '';
}

function odp_is_sync_url()
{
    return isset($_GET['sync']) && !empty($_GET['page']) && $_GET['page'] == 'one_day_options';
}

function odp_is_options_url()
{
    return is_admin() && !empty($_GET['page']) && $_GET['page'] == 'one_day_options';
}

function odp_current_url()
{
    global $wp;

    $params = $_SERVER['QUERY_STRING'] == '' ? '' : '?' . $_SERVER['QUERY_STRING'];

    return home_url($wp->request) . $params;
}

function odp_get_filter_form_settings($filter_id)
{
    if (get_post_type($filter_id) != 'oneday_filter') return [];

    return [
        'url' => get_post_meta($filter_id, 'oneday_filter_form_url', true),
        'one_line' => (bool)get_post_meta($filter_id, 'oneday_filter_form_one_line', true),
        'alignment' => get_post_meta($filter_id, 'oneday_filter_form_alignment', true),
        'bg_color' => get_post_meta($filter_id, 'oneday_filter_form_bg_color', true),
        'border_radius' => get_post_meta($filter_id, 'oneday_filter_form_border_radius', true),
        'btn_color' => get_post_meta($filter_id, 'oneday_filter_form_btn_color', true),
        'btn_font_color' => get_post_meta($filter_id, 'oneday_filter_form_btn_font_color', true),
        'btn_border_radius' => get_post_meta($filter_id, 'oneday_filter_form_btn_border_radius', true),
        'btn_text' => get_post_meta($filter_id, 'oneday_filter_form_btn_text', true),
        'font_color' => get_post_meta($filter_id, 'oneday_filter_form_font_color', true),
        'widget_width' => get_post_meta($filter_id, 'oneday_filter_form_widget_width', true),
        'property_code' => get_post_meta($filter_id, 'oneday_filter_form_property_code', true),
        'default_sorting' => get_post_meta($filter_id, 'oneday_filter_form_default_sorting', true),
        'hide_sorting' => get_post_meta($filter_id, 'oneday_filter_form_hide_sorting', true),
    ];
}

function odp_get_filter_results_settings($filter_id, $field = false)
{
    if (get_post_type($filter_id) != 'oneday_filter') return [];

    $hide_hotel_name = get_post_meta($filter_id, 'oneday_filter_results_hide_hotel_name', true);
    if (empty($hide_hotel_name))
        $hide_hotel_name = 'yes';

    $hide_hotel_image = get_post_meta($filter_id, 'oneday_filter_results_hide_hotel_image', true);
    if (empty($hide_hotel_image))
        $hide_hotel_image = 'yes';

    $hide_hotel_description = get_post_meta($filter_id, 'oneday_filter_results_hide_hotel_description', true);
    if (empty($hide_hotel_description))
        $hide_hotel_description = 'yes';

    $settings = [
        'hide_hotel_name' => $hide_hotel_name,
        'hide_hotel_image' => $hide_hotel_image,
        'hide_hotel_description' => $hide_hotel_description,
        'buttons_class' => get_post_meta($filter_id, 'oneday_filter_results_buttons_class', true),
        'buttons_padding' => get_post_meta($filter_id, 'oneday_filter_results_buttons_padding', true),
        'buttons_background' => get_post_meta($filter_id, 'oneday_filter_results_buttons_background', true),
        'buttons_font_color' => get_post_meta($filter_id, 'oneday_filter_results_buttons_font_color', true),
        'property_code' => get_post_meta($filter_id, 'oneday_filter_form_property_code', true),
        'default_sorting' => get_post_meta($filter_id, 'oneday_filter_form_default_sorting', true),
    ];

    if ($field) {
        return !empty($settings[$field]) ? $settings[$field] : '';
    }

    return $settings;
}

function odp_get_api_key()
{
    $one_day_settings = get_option('1day_options');
    return !empty($one_day_settings['api_key']) ? $one_day_settings['api_key'] : '';
}

function odp_get_hide_hotel_name()
{
    $one_day_settings = get_option('1day_options');
    return !empty($one_day_settings['hide_hotel_name']) ? $one_day_settings['hide_hotel_name'] : '';
}


function odp_get_header_for_api_request()
{
    return ['x-api-key' => odp_get_api_key(), 'Content-Type' => 'application/json'];
}

function odp_get_google_map_api_key()
{
    $one_day_settings = get_option('1day_options');
    return !empty($one_day_settings['google_map_api_key']) ? $one_day_settings['google_map_api_key'] : '';
}

function odp_get_date_format()
{
    $one_day_settings = get_option('1day_options');
    return !empty($one_day_settings['date_format']) ? $one_day_settings['date_format'] : 'M d, Y';
}

function odp_sorting_options()
{
    return [
        'asc' => __('Price (from low to high)', '1day'),
        'desc' => __('Price (from high to low)', '1day')
    ];
}

/**
 * Converts php DateTime format to Javascript Moment format.
 * @param string $phpFormat
 * @return string
 */
function convertPhpToJsMomentFormat($phpFormat)
{
    $replacements = [
        'A' => 'A',      // for the sake of escaping below
        'a' => 'a',      // for the sake of escaping below
        'B' => '',       // Swatch internet time (.beats), no equivalent
        'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
        'D' => 'ddd',
        'd' => 'DD',
        'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
        'F' => 'MMMM',
        'G' => 'H',
        'g' => 'h',
        'H' => 'HH',
        'h' => 'hh',
        'I' => '',       // Daylight Saving Time? => moment().isDST();
        'i' => 'mm',
        'j' => 'D',
        'L' => '',       // Leap year? => moment().isLeapYear();
        'l' => 'dddd',
        'M' => 'MMM',
        'm' => 'MM',
        'N' => 'E',
        'n' => 'M',
        'O' => 'ZZ',
        'o' => 'YYYY',
        'P' => 'Z',
        'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
        'S' => 'o',
        's' => 'ss',
        'T' => 'z',      // deprecated since version 1.6.0 of moment.js
        't' => '',       // days in the month => moment().daysInMonth();
        'U' => 'X',
        'u' => 'SSSSSS', // microseconds
        'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
        'W' => 'W',      // for the sake of escaping below
        'w' => 'e',
        'Y' => 'YYYY',
        'y' => 'YY',
        'Z' => '',       // time zone offset in minutes => moment().zone();
        'z' => 'DDD',
    ];

    // Converts escaped characters.
    foreach ($replacements as $from => $to) {
        $replacements['\\' . $from] = '[' . $from . ']';
    }

    return strtr($phpFormat, $replacements);
}

function prepare_date($date, $oldFormat, $newFormat)
{
    $date = DateTime::createFromFormat($oldFormat, $date);
    if (!$date)
        return '';
    return $date->format($newFormat);
}

require_once __DIR__ . '/functions/hotels.php';
require_once __DIR__ . '/functions/rooms.php';



