<?php
/**
 * Plugin Name:       1Day Booking Engine
 * Description:       The plugin display hotels and rooms from 1day.io
 * Version:           1.0.4
 * Author:            1Day.io
 * Author URI:        https://1day.io/
 */

if(!defined('ODP_DEV') || !ODP_DEV) {
    define('ODP_API_URL', 'https://apic.1day.io/api/availability/');
    define('ODP_API_URL_BOOKING', 'https://apic.1day.io/api/reservation/');
    define('ODP_API_URL_SYNC', 'https://apic.1day.io/api/property/');
} else {
    define('ODP_API_URL', 'https://stg-connect.1day.io/api/availability/');
    define('ODP_API_URL_BOOKING', 'https://stg-connect.1day.io/api/reservation/');
    define('ODP_API_URL_SYNC', 'https://stg-connect.1day.io/api/property/');
}

if(!defined('ODP_MULTIPLE_HOTELS'))
    define('ODP_MULTIPLE_HOTELS', false);

define('ODP_PLUGIN_DIR', __DIR__);

class ODP_New_Day {
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activation']);
        register_deactivation_hook(__FILE__, [$this, 'deactivation']);

        $this->load();

        bikecoders_is_this_plugin_active('1day io', 'WooCommerce', 'woocommerce/woocommerce.php');
    }

    public function activation()
    {
        wp_clear_scheduled_hook('eventSyncHotels');
        flush_rewrite_rules();
    }

    public function deactivation()
    {
        wp_clear_scheduled_hook('eventSyncHotels');
        flush_rewrite_rules();
    }

    /**
     * Loading all dependencies
     * @return void
     */
    public function load()
    {
        global $exists_woocommerce;
        $exists_woocommerce = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );

        require_once __DIR__ . '/inc/is_the_plugin_active.php';
        require_once __DIR__ . '/inc/api_curl_connect.php';
        require_once __DIR__ . '/inc/enqueue_scripts.php';
        require_once __DIR__ . '/inc/class/settings_page.php';

        if($exists_woocommerce)
        {
            require_once __DIR__ . '/inc/class/init/init_filter_post_type.php';
            require_once __DIR__ . '/inc/class/init/init_hotel_taxonomy.php';
            require_once __DIR__ . '/inc/class/init/init_room_product_type.php';
        }

        require_once __DIR__ . '/inc/functions.php';
        require_once __DIR__ . '/inc/hooks.php';
        require_once __DIR__ . '/inc/ajax.php';
        require_once __DIR__ . '/inc/shortcodes.php';

        if($exists_woocommerce)
        {
            if(odp_is_sync_url())
            {
                require_once __DIR__ . '/inc/class/sync/sync_hotels.php';
            }

            if(odp_is_options_url())
            {
                require_once __DIR__ . '/inc/class/sync/check_hotels.php';
            }
        }
    }
};

new ODP_New_Day();


