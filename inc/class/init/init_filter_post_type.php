<?php

class ODP_Init_Filter_Post_Type
{

    public function __construct()
    {
        /**
         * 1. Register filter post type
         */
        add_action('init', [$this, 'register_filter_post_type']);

        /**
         * 2. Add meta box to filter post type
         */
        add_action('add_meta_boxes', [$this, 'add_filter_meta_boxes']);

        /**
         * 3. Save meta box to filter post type
         */
        add_action('save_post', [$this, 'save_filter_post_type_meta']);

        add_filter('manage_oneday_filter_posts_columns', function ($columns) {
            return array_merge(
                $columns, ['shortcodes' => __('Shortcodes', '1day')]
            );
        });

        add_action('manage_oneday_filter_posts_custom_column', function ($column, $post_id) {

            if ($column == 'shortcodes') {
                $post_id = esc_attr($post_id);

                echo "[oneday_search_form filter_id=\"{$post_id}\"]<br/>";
                echo "[oneday_search_results filter_id=\"{$post_id}\"]<br/>";
                if(ODP_MULTIPLE_HOTELS)
                    echo "[oneday_search_map filter_id=\"{$post_id}\"]<br/>";
            }
        }, 10, 2);

        add_action('admin_menu', function () {
            add_submenu_page('one_day_options', '1Day filters', '1Day filters', 'edit_pages', 'edit.php?post_type=oneday_filter');
        }, 11);


    }

    public function register_filter_post_type()
    {
        register_post_type('oneday_filter', [
            'labels' => [
                'name' => __('1Day filters', '1day'),
                'singular_name' => __('1Day filter', '1day'),
                'add_new' => __('Add filter', '1day'),
                'add_new_item' => __('Add 1Day filter', '1day'),
                'edit_item' => __('Edit 1Day filter', '1day'),
                'new_item' => __('New filter', '1day'),
                'view_item' => __('View filter', '1day'),
                'search_items' => __('Search filter', '1day'),
                'not_found' => __('Filters not found', '1day'),
                'not_found_in_trash' => __('Not found in trash', '1day'),
                'parent_item_colon' => __('', '1day'),
                'menu_name' => __('1Day filters', '1day'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title'],
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
        ]);
    }

    public function add_filter_meta_boxes()
    {
        global $post;

        if ($post && get_post_type($post->ID) != 'oneday_filter') return;

        $boxes = [
            'oneday_filter_form_fields' => __('Search form settings', '1day'),
            'oneday_filter_results_fields' => __('Search results settings', '1day'),
            'oneday_filter_map_fields' => __('Filter map fields', '1day'),
        ];

        foreach ($boxes as $name => $label) {
            if (!ODP_MULTIPLE_HOTELS && $name == 'oneday_filter_map_fields')
                continue;
            $render_callback = str_replace('oneday', 'render', $name);
            $this->add_filter_meta_box($name, $label, $render_callback);
        }

        $render_callback = str_replace('oneday', 'render', 'oneday_filter_shortcodes');
        $this->add_filter_meta_box('oneday_filter_shortcodes', __('Filter shortcodes', '1day'), $render_callback, 'side');
    }

    private function add_filter_meta_box($name, $label, $render_callback, $context = 'advanced')
    {
        add_meta_box(
            $name,
            $label,
            [$this, $render_callback],
            null,
            $context
        );
    }

    public function render_filter_form_fields($filter)
    {
        ?>
        <?php wp_nonce_field('oneday_filter_fields_nonce', 'oneday_filter_fields_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="oneday_filter_form_url">
                        <?php echo esc_attr__('URL', '1day'); ?>
                        <span title="<?php echo esc_attr__('URL of the Wordpress page where you are going to display the search results', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_url" id="oneday_filter_form_url"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_url', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_property_code">
                        <?php echo esc_attr__('Select hotel', '1day'); ?>
                        <span title="<?php echo esc_attr__('Select the hotel that will be searched for available rooms.', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <?php
                    $terms = get_terms([
                        'taxonomy' => 'hotel',
                        'hide_empty' => false,
                    ]);
                    if (empty($terms))
                        $terms = [];

                    $options = [];

                    if (ODP_MULTIPLE_HOTELS)
                        $options[] = esc_attr__('All hotels', '1day');
                    foreach ($terms as $term) {
                        $property_code = get_term_meta($term->term_id, 'property_code', true);
                        $options[$property_code] = $term->name;
                    }
                    $property_code = get_post_meta($filter->ID, 'oneday_filter_form_property_code', true);
                    if (empty($property_code))
                        $property_code = array_keys($options)[0];
                    ?>

                    <select name="oneday_filter_form_property_code" id="oneday_filter_form_property_code">
                        <?php foreach ($options as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($property_code, $value, true); ?>><?php echo esc_attr($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_hide_sorting">
                        <?php echo esc_attr__('Hide sorting', '1day'); ?>
                        <span title="<?php echo esc_attr__('Do not display the sort field in the search form', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="checkbox" name="oneday_filter_form_hide_sorting" id="oneday_filter_form_hide_sorting"
                           value="yes" <?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_hide_sorting', true) == 'yes' ? 'checked="checked"' : ''); ?>>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_default_sorting">
                        <?php echo esc_attr__('Default sorting', '1day'); ?>
                        <span title="<?php echo esc_attr__('The default sorting displayed in the search form', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <?php
                    $options = odp_sorting_options();
                    $default_sorting = get_post_meta($filter->ID, 'oneday_filter_form_default_sorting', true);
                    if (empty($default_sorting))
                        $default_sorting = array_keys($options)[0];
                    ?>
                    <select name="oneday_filter_form_default_sorting" id="oneday_filter_form_default_sorting">
                        <?php foreach ($options as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($default_sorting, $value, true); ?>><?php echo esc_attr($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_one_line">
                        <?php echo esc_attr__('One line?', '1day'); ?>
                        <span title="<?php echo esc_attr__('Display the search form in one line', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="checkbox" name="oneday_filter_form_one_line" id="oneday_filter_form_one_line"
                           value="yes" <?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_one_line', true) == 'yes' ? 'checked="checked"' : ''); ?>>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_alignment"><?php echo esc_attr__('Form alignment', '1day'); ?></label>
                </th>
                <td>
                    <?php
                    $options = [
                        'center' => __('Center', '1day'),
                        'left' => __('Left', '1day'),
                        'right' => __('Right', '1day'),
                    ];
                    $alignment = get_post_meta($filter->ID, 'oneday_filter_form_alignment', true);
                    if (empty($alignment))
                        $alignment = array_keys($options)[0];
                    ?>
                    <?php foreach ($options as $value => $label): ?>
                        <input type="radio" name="oneday_filter_form_alignment"
                               value="<?php echo esc_attr($value); ?>" <?php checked($alignment, $value, true); ?>><?php echo esc_attr($label); ?></input>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_widget_width">
                        <?php echo esc_attr__('Form width', '1day'); ?>
                        <span title="<?php echo esc_attr__('The width of the search form widget. Example: 900px', '1day'); ?>" class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_widget_width" id="oneday_filter_form_widget_width"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_widget_width', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_bg_color">
                        <?php echo esc_attr__('Background color', '1day'); ?>
                        <span title="<?php echo esc_attr__('Example, to set to white enter: #ffffff. For semi-transparent black enter: #22222288', '1day'); ?>" class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_bg_color" id="oneday_filter_form_bg_color"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_bg_color', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_font_color">
                        <?php echo esc_attr__('Font color', '1day'); ?>
                        <span title="<?php echo esc_attr__('Example, to set to white enter: #ffffff', '1day'); ?>" class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_font_color" id="oneday_filter_form_font_color"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_font_color', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_border_radius">
                        <?php echo esc_attr__('Border radius', '1day'); ?>
                        <span title="<?php echo esc_attr__('Example, for rounded corners enter: 5px', '1day'); ?>" class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_border_radius" id="oneday_filter_form_border_radius"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_border_radius', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_btn_color"><?php echo esc_attr__('Button color', '1day'); ?></label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_btn_color" id="oneday_filter_form_btn_color"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_btn_color', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_btn_font_color"><?php echo esc_attr__('Button font color', '1day'); ?></label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_btn_font_color"
                           id="oneday_filter_form_btn_font_color"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_btn_font_color', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_btn_border_radius">
                        <?php echo esc_attr__('Button border radius', '1day'); ?>
                        <span title="<?php echo esc_attr__('Example, for rounded corners enter: 5px', '1day'); ?>" class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_form_btn_border_radius" id="oneday_filter_form_btn_border_radius"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_form_btn_border_radius', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_form_btn_text"><?php echo esc_attr__('Button text', '1day'); ?></label>
                </th>
                <td>
                    <?php
                    $btn_text = get_post_meta($filter->ID, 'oneday_filter_form_btn_text', true);
                    if(empty($btn_text)) $btn_text = __('Book', '1day');
                    ?>
                    <input type="text" name="oneday_filter_form_btn_text"
                           id="oneday_filter_form_btn_text"
                           value="<?php echo esc_attr($btn_text); ?>">
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_filter_results_fields($filter)
    {
        ?>
        <table class="form-table">
            <?php if(ODP_MULTIPLE_HOTELS) : ?>
            <tr>
                <th>
                    <label for="oneday_filter_results_hide_hotel_name">
                        <?php echo esc_attr__('Hide hotel name', '1day'); ?>
                        <span title="<?php echo esc_attr__('This will work for one hotel search results', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <?php $temp = get_post_meta($filter->ID, 'oneday_filter_results_hide_hotel_name', true); ?>
                    <input type="checkbox" name="oneday_filter_results_hide_hotel_name"
                           id="oneday_filter_results_hide_hotel_name"
                           value="yes" <?php echo esc_attr(empty($temp) || $temp == 'yes' ? 'checked="checked"' : ''); ?>>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_results_hide_hotel_image">
                        <?php echo esc_attr__('Hide hotel image', '1day'); ?>
                        <span title="<?php echo esc_attr__('this will work for one hotel search results', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <?php $temp = get_post_meta($filter->ID, 'oneday_filter_results_hide_hotel_image', true); ?>
                    <input type="checkbox" name="oneday_filter_results_hide_hotel_image"
                           id="oneday_filter_results_hide_hotel_image"
                           value="yes" <?php echo esc_attr(empty($temp) || $temp == 'yes' ? 'checked="checked"' : ''); ?>>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_results_hide_hotel_description">
                        <?php echo esc_attr__('Hide hotel description', '1day'); ?>
                        <span title="<?php echo esc_attr__('this will work for one hotel search results', '1day'); ?>"
                              class="odp_star"></span>
                    </label>
                </th>
                <td>
                    <?php $temp = get_post_meta($filter->ID, 'oneday_filter_results_hide_hotel_description', true); ?>
                    <input type="checkbox" name="oneday_filter_results_hide_hotel_description"
                           id="oneday_filter_results_hide_hotel_description"
                           value="yes" <?php echo esc_attr(empty($temp) || $temp == 'yes' ? 'checked="checked"' : ''); ?>>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>
                    <label for="oneday_filter_results_buttons_class">
                        <?php echo esc_attr__('HTML class for buttons', '1day'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_results_buttons_class"
                           id="oneday_filter_results_buttons_class"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_results_buttons_class', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_results_buttons_padding"><?php echo esc_attr__('CSS padding for buttons', '1day'); ?></label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_results_buttons_padding"
                           id="oneday_filter_results_buttons_padding"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_results_buttons_padding', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_results_buttons_background">
                        <?php echo esc_attr__('Buttons background', '1day'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_results_buttons_background"
                           id="oneday_filter_results_buttons_background"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_results_buttons_background', true)); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="oneday_filter_results_buttons_font_color">
                        <?php echo esc_attr__('Buttons font color', '1day'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="oneday_filter_results_buttons_font_color"
                           id="oneday_filter_results_buttons_font_color"
                           value="<?php echo esc_attr(get_post_meta($filter->ID, 'oneday_filter_results_buttons_font_color', true)); ?>">
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_filter_map_fields($filter)
    {
        ?>

        <?php
    }

    public function render_filter_shortcodes($filter)
    {
        ?>
        <p>
            <label><?php echo esc_attr__('Search form shortcode', '1day'); ?></label><br/>
            <b>[oneday_search_form filter_id="<?php echo esc_attr($filter->ID); ?>"]</b>
        </p>
        <p>
            <label><?php echo esc_attr__('Search results shortcode', '1day'); ?></label><br/>
            <b>[oneday_search_results filter_id="<?php echo esc_attr($filter->ID); ?>"]</b>
        </p>
        <?php if(ODP_MULTIPLE_HOTELS) : ?>
        <p>
            <label><?php echo esc_attr__('Search map shortcode', '1day'); ?></label><br/>
            <b>[oneday_search_map filter_id="<?php echo esc_attr($filter->ID); ?>"]</b>
        </p>
        <?php endif; ?>

        <?php
    }

    public function save_filter_post_type_meta($filter_id)
    {
        if (!isset($_POST['oneday_filter_fields_nonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field($_POST['oneday_filter_fields_nonce']), 'oneday_filter_fields_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $filter_id)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $filter_id)) {
                return;
            }
        }

        if (isset($_POST['oneday_filter_form_url'])) {
            update_post_meta($filter_id, 'oneday_filter_form_url', sanitize_text_field($_POST['oneday_filter_form_url']));
        }

        if (isset($_POST['oneday_filter_form_one_line'])) {
            update_post_meta($filter_id, 'oneday_filter_form_one_line', sanitize_text_field($_POST['oneday_filter_form_one_line']));
        } else {
            delete_post_meta($filter_id, 'oneday_filter_form_one_line');
        }

        if (isset($_POST['oneday_filter_form_hide_sorting'])) {
            update_post_meta($filter_id, 'oneday_filter_form_hide_sorting', sanitize_text_field($_POST['oneday_filter_form_hide_sorting']));
        } else {
            delete_post_meta($filter_id, 'oneday_filter_form_hide_sorting');
        }

        if (isset($_POST['oneday_filter_form_alignment'])) {
            update_post_meta($filter_id, 'oneday_filter_form_alignment', sanitize_text_field($_POST['oneday_filter_form_alignment']));
        }
        if (isset($_POST['oneday_filter_form_bg_color'])) {
            update_post_meta($filter_id, 'oneday_filter_form_bg_color', sanitize_text_field($_POST['oneday_filter_form_bg_color']));
        }
        if (isset($_POST['oneday_filter_form_border_radius'])) {
            update_post_meta($filter_id, 'oneday_filter_form_border_radius', sanitize_text_field($_POST['oneday_filter_form_border_radius']));
        }
        if (isset($_POST['oneday_filter_form_btn_color'])) {
            update_post_meta($filter_id, 'oneday_filter_form_btn_color', sanitize_text_field($_POST['oneday_filter_form_btn_color']));
        }
        if (isset($_POST['oneday_filter_form_btn_border_radius'])) {
            update_post_meta($filter_id, 'oneday_filter_form_btn_border_radius', sanitize_text_field($_POST['oneday_filter_form_btn_border_radius']));
        }
        if (isset($_POST['oneday_filter_form_widget_width'])) {
            update_post_meta($filter_id, 'oneday_filter_form_widget_width', sanitize_text_field($_POST['oneday_filter_form_widget_width']));
        }
        if (isset($_POST['oneday_filter_form_font_color'])) {
            update_post_meta($filter_id, 'oneday_filter_form_font_color', sanitize_text_field($_POST['oneday_filter_form_font_color']));
        }
        if (isset($_POST['oneday_filter_form_btn_font_color'])) {
            update_post_meta($filter_id, 'oneday_filter_form_btn_font_color', sanitize_text_field($_POST['oneday_filter_form_btn_font_color']));
        }
        if (isset($_POST['oneday_filter_form_btn_text'])) {
            update_post_meta($filter_id, 'oneday_filter_form_btn_text', sanitize_text_field($_POST['oneday_filter_form_btn_text']));
        }
        if (isset($_POST['oneday_filter_form_property_code'])) {
            update_post_meta($filter_id, 'oneday_filter_form_property_code', sanitize_text_field($_POST['oneday_filter_form_property_code']));
        }
        if (isset($_POST['oneday_filter_form_default_sorting'])) {
            update_post_meta($filter_id, 'oneday_filter_form_default_sorting', sanitize_text_field($_POST['oneday_filter_form_default_sorting']));
        }

        update_post_meta($filter_id, 'oneday_filter_results_hide_hotel_name', isset($_POST['oneday_filter_results_hide_hotel_name']) ? 'yes' : 'no');
        update_post_meta($filter_id, 'oneday_filter_results_hide_hotel_image', isset($_POST['oneday_filter_results_hide_hotel_image']) ? 'yes' : 'no');
        update_post_meta($filter_id, 'oneday_filter_results_hide_hotel_description', isset($_POST['oneday_filter_results_hide_hotel_description']) ? 'yes' : 'no');

        if (!empty($_POST['oneday_filter_results_buttons_class'])) {
            update_post_meta($filter_id, 'oneday_filter_results_buttons_class', sanitize_text_field($_POST['oneday_filter_results_buttons_class']));
        }
        if (!empty($_POST['oneday_filter_results_buttons_padding'])) {
            update_post_meta($filter_id, 'oneday_filter_results_buttons_padding', sanitize_text_field($_POST['oneday_filter_results_buttons_padding']));
        }
        if (!empty($_POST['oneday_filter_results_buttons_background'])) {
            update_post_meta($filter_id, 'oneday_filter_results_buttons_background', sanitize_text_field($_POST['oneday_filter_results_buttons_background']));
        }
        if (!empty($_POST['oneday_filter_results_buttons_font_color'])) {
            update_post_meta($filter_id, 'oneday_filter_results_buttons_font_color', sanitize_text_field($_POST['oneday_filter_results_buttons_font_color']));
        }

    }

    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}

ODP_Init_Filter_Post_Type::getInstance();