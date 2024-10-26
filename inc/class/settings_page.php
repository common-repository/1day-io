<?php

class ODP_Settings_Page
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_menu_page(
            '1Day options',
            '1Day options',
            'edit_others_posts',
            'one_day_options',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('1day_options');
        ?>
        <div class="wrap">
            <h1>1Day Options</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('1day_option_group');
                do_settings_sections('1day-options');
                ?>

                <?php submit_button(); ?>

                <h2>
                    Sync With 1Day.io
                    <span title="Click synchronize to automatically update WooCommerce products to match the hotels and room types in your 1Day account." class="odp_star"></span>
                </h2>

                <?php
                if (class_exists('ODP_Check_Hotels')) {
                    $review = (new ODP_Check_Hotels())->get_review();
                    require_once __DIR__ . '/../templates/admin/review.php';
                    ?>

                    <p class="submit">
                        <a href="<?php echo admin_url('/admin.php?page=one_day_options&sync'); ?>"
                           class="button button-primary"><?php echo esc_attr__('Synchronize', '1day'); ?></a>
                    </p>

                <?php } ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            '1day_option_group', // Option group
            '1day_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            '1day_settings', // ID
            'API Settings', // Title
            array($this, 'print_section_info'), // Callback
            '1day-options' // Page
        );

        add_settings_field(
            'api_key', // ID
            '1Day API Key <span title="You can get this by logging into your 1Day account, then go to Settings > Users > ... > Generate API key" class="odp_star"></span>', // Title
            array($this, 'api_key_callback'), // Callback
            '1day-options', // Page
            '1day_settings' // Section
        );

        if (ODP_MULTIPLE_HOTELS)
            add_settings_field(
                'google_map_api_key', // ID
                'Google Map API key', // Title
                array($this, 'google_map_api_key_callback'), // Callback
                '1day-options', // Page
                '1day_settings' // Section
            );

        add_settings_field(
            'date_format', // ID
            'Date format', // Title
            array($this, 'date_format_callback'), // Callback
            '1day-options', // Page
            '1day_settings' // Section
        );

        if (ODP_MULTIPLE_HOTELS)
            add_settings_field(
                'hotel_page_id', // ID
                'Hotel template page id', // Title
                array($this, 'hotel_page_id_callback'), // Callback
                '1day-options', // Page
                '1day_settings' // Section
            );

        if (ODP_MULTIPLE_HOTELS)
            add_settings_field(
                'page_count',
                'Hotels to display per page',
                array($this, 'page_items_count_callback'), // Callback
                '1day-options', // Page
                '1day_settings' // Section
            );

        if (ODP_MULTIPLE_HOTELS)
            add_settings_field(
                'hide_hotel_name',
                'Hide hotel name on a room page',
                array($this, 'hide_hotel_name_callback'), // Callback
                '1day-options', // Page
                '1day_settings' // Section
            );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['api_key']))
            $new_input['api_key'] = sanitize_text_field($input['api_key']);

        if (isset($input['google_map_api_key']))
            $new_input['google_map_api_key'] = sanitize_text_field($input['google_map_api_key']);

        if (isset($input['date_format']))
            $new_input['date_format'] = sanitize_text_field($input['date_format']);

        if (isset($input['hotel_page_id']))
            $new_input['hotel_page_id'] = sanitize_text_field($input['hotel_page_id']);

        if (isset($input['page_items_count']))
            $new_input['page_items_count'] = sanitize_text_field($input['page_items_count']);

        $new_input['hide_hotel_name'] = isset($input['hide_hotel_name']) ? 'yes' : 'no';
        $new_input['form_position'] = isset($input['form_position']);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="1day_options[api_key]" value="%s" />',
            isset($this->options['api_key']) ? esc_attr($this->options['api_key']) : ''
        );
    }

    public function google_map_api_key_callback()
    {
        printf(
            '<input type="text" id="google_map_api_key" name="1day_options[google_map_api_key]" value="%s" />',
            isset($this->options['google_map_api_key']) ? esc_attr($this->options['google_map_api_key']) : ''
        );
    }

    public function date_format_callback() {
        $options = [
            'M d, Y',
            'm/d/Y',
            'd/m/Y',
            'd M Y',
            'Y/m/d'
        ];

        $new_options = [];

        foreach ($options as &$option) {
            $date = new DateTime('now');
            $new_options[$option] = $date->format($option) . ' (' . $option . ')';
        }

        $options = $new_options;

        $date_format = !empty($this->options['date_format']) ? $this->options['date_format'] : '';
        if (empty($date_format))
            $date_format = array_keys($options)[0];
        ?>

        <select name="1day_options[date_format]">
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($date_format, $value, true); ?>><?php echo esc_attr($label); ?></option>
            <?php endforeach; ?>
        </select>

        <?php
    }


    public function hotel_page_id_callback()
    {
        printf(
            '<input type="text" id="hotel_page_id" name="1day_options[hotel_page_id]" value="%s" />',
            isset($this->options['hotel_page_id']) ? esc_attr($this->options['hotel_page_id']) : ''
        );
    }

    public function page_items_count_callback()
    {
        printf(
            '<input type="text" id="page_items_count" name="1day_options[page_items_count]" value="%s" />',
            isset($this->options['page_items_count']) ? esc_attr($this->options['page_items_count']) : ''
        );
    }

    public function hide_hotel_name_callback()
    {
        $hide_hotel_name = isset($this->options['hide_hotel_name']) ? esc_attr($this->options['hide_hotel_name']) : 'yes';
        ?>
        <input type="checkbox" id="hide_hotel_name" name="1day_options[hide_hotel_name]"
               value="yes" <?php checked('yes', $hide_hotel_name); ?> />
        <?php
    }
}


if (is_admin())
    $my_settings_page = new ODP_Settings_Page();