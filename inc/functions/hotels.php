<?php

function odp_get_hotel_lowest_rate($hotel)
{
    $lowest_hotel_nightly_rate = false;

    if (!empty($hotel->room_types)) {
        $lowest_hotel_nightly_rates = [];

        foreach ($hotel->room_types as $room) {
            foreach ($room->rates as $rate) {
                $lowest_hotel_nightly_rates[] = $rate->lowest_nightly_rate;
            }
        }

        if (empty($lowest_hotel_nightly_rates))
            return false;

        $lowest_hotel_nightly_rate = min($lowest_hotel_nightly_rates);
    }

    return $lowest_hotel_nightly_rate;
}

function odp_prepare_currency($hotelsOrRooms = null, $isHotels = true)
{
    if (
        $isHotels
        && !empty($hotelsOrRooms)
        && !empty($hotelsOrRooms[0]->room_types)
        && !empty($hotelsOrRooms[0]->room_types[0]->rates)
        && !empty($hotelsOrRooms[0]->room_types[0]->rates[0]->nightly_rates)
    ) {
        return $hotelsOrRooms[0]->room_types[0]->rates[0]->nightly_rates[0]->currency_code;
    } else if (
        !$isHotels
        && !empty($hotelsOrRooms)
        && !empty($hotelsOrRooms[0]->rates)
        && !empty($hotelsOrRooms[0]->rates[0]->nightly_rates)
    ) {
        return $hotelsOrRooms[0]->rates[0]->nightly_rates[0]->currency_code;
    } else {
        return get_woocommerce_currency();
    }
}

function odp_get_currency($hotelsOrRooms = null, $isHotels = true)
{
    $cache_key = json_encode($hotelsOrRooms) . '_currency';
    $data = wp_cache_get($cache_key);
    if (false === $data) {
        $data = odp_prepare_currency($hotelsOrRooms, $isHotels);

        wp_cache_set($cache_key, $data);
    }

    return $data;
}

function odp_prepare_currency_symbol($hotelsOrRooms = null, $isHotels = true)
{
    return get_woocommerce_currency_symbol(odp_get_currency($hotelsOrRooms, $isHotels));
}

function odp_get_currency_symbol($hotelsOrRooms = null, $isHotels = true)
{
    $cache_key = json_encode($hotelsOrRooms) . '_currency_symbol';
    $data = wp_cache_get($cache_key);
    if (false === $data) {
        $data = odp_prepare_currency_symbol($hotelsOrRooms, $isHotels);

        wp_cache_set($cache_key, $data);
    }

    return $data;
}

function odp_get_hotel_term_by_property_code($property_code)
{
    $hotel_terms = get_terms([
        'hide_empty' => false,
        'meta_query' => [
            [
                'key' => 'property_code',
                'value' => $property_code,
                'compare' => '='
            ]
        ],
        'taxonomy' => 'hotel',
    ]);

    if (!empty($hotel_terms) && !is_wp_error($hotel_terms)) {
        return $hotel_terms[0];
    }

    return false;
}


function odp_get_hotel_property_code($term_id)
{
    return esc_attr(get_term_meta($term_id, 'property_code', true));
}

function odp_get_hotel_img($hotel_term_id)
{
    $img = esc_attr(get_term_meta($hotel_term_id, 'hotel_image', true));

    if (empty($img)) return false;

    return $img;
}

function odp_get_hotel_page_id()
{
    $one_day_settings = get_option('1day_options');
    return esc_attr($one_day_settings['hotel_page_id']);
}

function odp_get_hotel_page_url($property_code)
{
    return get_permalink(odp_get_hotel_page_id()) . "?property_code={$property_code}";
}

function odp_get_hotel_page_items_count()
{
    $one_day_settings = get_option('1day_options');
    $page_items_count = $one_day_settings['page_items_count'];
    return empty($page_items_count) ? 10 : $page_items_count;
}

function odp_get_hotel_short_description($hotel_term_id)
{
    return html_entity_decode(get_term_meta($hotel_term_id, 'hotel_short_description', true));
}

function odp_get_hotel_description($hotel_term_id)
{
    return html_entity_decode(get_term_meta($hotel_term_id, 'hotel_description', true));
}

function odp_get_current_hotel_property_code()
{
    global $wp_query;

    return is_tax('hotel') ? odp_get_hotel_property_code($wp_query->get_queried_object()->term_id) : '';
}
