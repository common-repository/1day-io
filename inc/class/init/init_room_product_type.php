<?php

class ODP_Init_Room_Product_Type
{

    public function __construct()
    {
        /**
         * 1. Register a room product type in WooCommerce
         */
        add_action('init', [$this, 'add_room_product_type_class']);

        /**
         * 2. Add room product type to the Product type Drop Down
         */
        add_filter('product_type_selector', [$this, 'add_room_product_type']);

        /**
         * 3. Add a new tab for room product type
         */
        add_filter('woocommerce_product_data_tabs', [$this, 'room_tab']);

        /**
         * 4. Hide product tabs if product type is room
         */
        add_action('woocommerce_product_data_tabs', [$this, 'remove_product_tab_if_room']);

        /**
         * 5. Add fields / settings to the room tab
         */
        add_action('woocommerce_product_data_panels', [$this, 'room_options_product_tab_content']);

        /**
         * 6. Saving the room product type Settings
         */
        add_action('woocommerce_process_product_meta', [$this, 'save_room_fields']);

        /**
         * 7. Room rates and resources output
         */
        add_action('woocommerce_after_single_product_summary', [$this, 'room_rates_options'], 10);

        /**
         * 8. Redirect to cart after additing in cart
         */
//        add_action('option_woocommerce_cart_redirect_after_add', function(){
//            return 'yes';
//        });

        /**
         * 9. Make all the rooms parchasables
         */
        add_action('woocommerce_is_purchasable', function ($purchasable, $product) {
            return !$product->is_type('room') ? $purchasable : true;
        }, 10, 2);

        /**
         * 10. Add to cart validation
         */
        add_filter('woocommerce_add_to_cart_validation', [$this, 'add_to_cart_validation'], 10, 5);

        add_filter('woocommerce_add_cart_item_data', [$this, 'odp_woocommerce_add_cart_item_data'], 10, 2);

        add_action('woocommerce_checkout_create_order_line_item', [$this, 'odp_woocommerce_checkout_create_order_line_item'], 10, 4);

        add_action('woocommerce_before_calculate_totals', [$this, 'before_calculate_totals'], 20, 1);

        //add_filter('woocommerce_is_sold_individually', [ $this, 'custom_is_sold_individually' ], 10, 2);

        add_action('woocommerce_single_product_summary', [$this, 'clean_single_product_summary'], 10);

        add_action('woocommerce_checkout_order_processed', [$this, 'room_checkout_process'], 10, 3);

        /**
         * 11. To change cart and order item name
         */

        add_action('woocommerce_cart_item_name', [$this, 'get_cart_item_name'], 10, 2);
        add_action('woocommerce_order_item_name', [$this, 'get_order_item_name'], 10, 2);

    }

    public function room_rates_options()
    {
        ?>
        <div class="odp_room_rates_options">
            <?php
            $this->room_rates_output();
            $this->room_resources_output();
            $this->room_booking_button_output();
            ?>
        </div>
        <?php
    }

    public function room_booking_button_output()
    {
        global $product;

        if (!$product || !$product->is_type('room'))
            return;

        $room_id = odp_get_room_id($product->get_id());

        require_once __DIR__ . '/../../templates/rooms/booking_button.php';
    }


    public function room_resources_output()
    {
        global $product;

        if (!$product || !$product->is_type('room'))
            return; ?>

        <div class="room_resources_wrapper"><?php

            $room_resources = get_post_meta($product->get_id(), 'room_resources', true);
            $room_resources_title = esc_attr__('Choose room add-ons', '1day');

            include ODP_PLUGIN_DIR . '/inc/templates/rooms/resources.php';

            $room_resources = get_post_meta($product->get_id(), 'room_deposits', true);
            $room_resources_title = esc_attr__('Choose room deposits', '1day');

            include ODP_PLUGIN_DIR . '/inc/templates/rooms/resources.php'; ?>

        </div>

        <?php
    }

    public function room_data_output()
    {
        global $product;

        if (!$product || !$product->is_type('room'))
            return;

        $room_checkin = get_post_meta($product->get_id(), 'room_checkin', true);
        $room_checkout = get_post_meta($product->get_id(), 'room_checkout', true);

        $start_date = !empty(odp_gc('start_date')) ?
            odp_gc('start_date') : '';

        $end_date = !empty(odp_gc('end_date')) ?
            odp_gc('end_date') : '';

        $guests = odp_gc('guests');

        $hotel_term = odp_get_hotel_term_by_room_product_id($product->get_id());
        $hotel_name = !empty($hotel_term) ? $hotel_term->name : '';

        include ODP_PLUGIN_DIR . '/inc/templates/rooms/data.php';
    }

    public function clean_single_product_summary()
    {
        if (is_admin()) return;

        global $product;

        if ($product && $product->is_type('room')) {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 20);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);


            add_action('woocommerce_single_product_summary', [$this, 'room_data_output'], 20);

        }
    }

    public function before_calculate_totals($cart)
    {
        if (is_admin() && !defined('DOING_AJAX'))
            return;

        if (did_action('woocommerce_before_calculate_totals') >= 2)
            return;

        foreach ($cart->get_cart() as $cart_item) {
            if (!$cart_item['data']->is_type('room')) continue;

            if (!empty($cart_item['currency'])) {
                global $currency;
                $currency = $cart_item['currency'];
                add_filter('woocommerce_currency', 'change_woocommerce_currency');
                function change_woocommerce_currency()
                {
                    global $currency;
                    return $currency;
                }
            }

            $room_price = $cart_item['room_subtotal'];

            if (!empty($cart_item['room_resources'])) {
                $room_price += get_resources_total_price($cart_item['data']->get_id(), explode('||', $cart_item['room_resources']));
            }

            if (!empty($cart_item['room_tax'])) {
                $room_price += $cart_item['room_tax'];
            }

            $cart_item['data']->set_price($room_price);
        }
    }

    public function custom_is_sold_individually($result, $product)
    {
        $found = false;

        foreach (WC()->cart->get_cart() as $ci_key => $ci) {
            if (!$ci['data']->is_type('room')) continue;

            if (!empty($ci['rate_plan_id']) && !empty($_POST['rate_plan_id']) && $ci['rate_plan_id'] == $_POST['rate_plan_id'] && !empty($_POST['quantity'])) {
                $found = true;
                break;
            }
        }

        return $product->is_type('room') ? !$found : true;
    }

    public function add_to_cart_validation($passed, $product_id, $quantity)
    {
        $product = wc_get_product($product_id);

        if (!$product) return $passed;

        $currentCurrency = !$product->is_type('room') ? get_woocommerce_currency() : $_POST['currency'];
        $currency = null;

        foreach (WC()->cart->get_cart() as $ci_key => $ci) {
            $currency = !$ci['data']->is_type('room') ? get_woocommerce_currency() : $ci['currency'];
            break;
        }

        if ($currency && $currentCurrency !== $currency) {
            wc_add_notice(esc_attr__("You can't add to the cart products with different currencies", '1day'), 'error');
            return false;
        }

        if (!$product->is_type('room')) return $passed;

        $start_date = odp_gc('start_date');
        $end_date = odp_gc('end_date');
        $guests = odp_gc('guests');
        $room_type_id = !empty($_POST['room_type_id']) ? sanitize_text_field($_POST['room_type_id']) : '';
        $rate_plan_id = !empty($_POST['rate_plan_id']) ? sanitize_text_field($_POST['rate_plan_id']) : '';

        if (empty($start_date) || empty($end_date) || empty($guests) || empty($rate_plan_id)) {
            wc_add_notice(esc_attr__('Session expired', '1day'), 'error');
            return false;
        }

        $args = [
            'start_date' => prepare_date($start_date, odp_get_date_format(), 'Y-m-d'),
            'end_date' => prepare_date($end_date, odp_get_date_format(), 'Y-m-d'),
            'guests' => $guests,
            'room_type_id' => $room_type_id
        ];

        $result = odp_api_connect(ODP_API_URL, 'get', $args);

        $room = !empty($result['content']->data[0]->room_types[0]) ? $result['content']->data[0]->room_types[0] : false;

        if (!$room) {
            wc_add_notice(esc_attr__('Incorrect data from API', '1day'), 'error');
            return false;
        }

        $in_cart = false;

        $cart_items = WC()->cart->cart_contents;

        if (!empty($cart_items)) {
            foreach ($cart_items as $cart_item_key => $cart_item) {
                if (
                    $cart_item['room_type_id'] == $room_type_id &&
                    $cart_item['rate_plan_id'] == $rate_plan_id
                ) {
                    $in_cart = true;
                    break;
                }
            }
        }

        if ($in_cart) {
            wc_add_notice(esc_attr__('The room is already in the cart', '1day'), 'error');
            return false;
        }

        if ($room->quantity < $quantity) {
            wc_add_notice(esc_attr__('All these rooms are booked out', '1day'), 'error');
            return false;
        }

        if (empty($room->rates)) {
            wc_add_notice(esc_attr__('All these rooms are booked out', '1day'), 'error');
            return false;
        }

        $rates = array_filter($room->rates, function ($rate) use ($rate_plan_id) {
            return $rate->rate_plan_id == $rate_plan_id;
        });

        $key = key($rates);
        $rate = $rates[$key];

        if (empty($rate)) {
            wc_add_notice(esc_attr__('There no rate plans for this room', '1day'), 'error');
            return false;
        }

        if (!isset($rate->room_subtotal) || !isset($rate->room_tax) || !isset($rate->room_total)) {
            wc_add_notice(esc_attr__('Incorrect data from API', '1day'), 'error');
            return false;
        }

        return true;
    }

    public function odp_woocommerce_add_cart_item_data($cart_item_data, $product_id)
    {
        $product = wc_get_product($product_id);

        if (!$product || !$product->is_type('room')) return $cart_item_data;

        remove_action('woocommerce_add_cart_item_data', __FUNCTION__);

        $start_date = odp_gc('start_date');
        $end_date = odp_gc('end_date');
        $guests = odp_gc('guests');
        $room_type_id = !empty($_POST['room_type_id']) ? sanitize_text_field($_POST['room_type_id']) : '';
        $rate_plan_id = !empty($_POST['rate_plan_id']) ? sanitize_text_field($_POST['rate_plan_id']) : '';
        $rate_plan_name = !empty($_POST['rate_plan_name']) ? sanitize_text_field($_POST['rate_plan_name']) : '';
        $room_resources = !empty($_POST['room_resources']) ? sanitize_text_field($_POST['room_resources']) : '';
        $currency = !empty($_POST['currency']) ? sanitize_text_field($_POST['currency']) : odp_get_currency();

        $args = [
            'start_date' => prepare_date($start_date, odp_get_date_format(), 'Y-m-d'),
            'end_date' => prepare_date($end_date, odp_get_date_format(), 'Y-m-d'),
            'guests' => $guests,
            'room_type_id' => $room_type_id
        ];

        $result = odp_api_connect(ODP_API_URL, 'get', $args);

        $room = !empty($result['content']->data[0]->room_types[0]) ? $result['content']->data[0]->room_types[0] : false;

        $rates = array_filter($room->rates, function ($rate) use ($rate_plan_id) {
            return $rate->rate_plan_id == $rate_plan_id;
        });

        $key = key($rates);
        $rate = $rates[$key];

        if (!empty($rate_plan_id)) {
            $cart_item_data['rate_plan_id'] = sanitize_text_field($rate_plan_id);
            $cart_item_data['rate_plan_name'] = sanitize_text_field($rate_plan_name);
            $cart_item_data['lowest_nightly_rate'] = $rate->lowest_nightly_rate;
            $cart_item_data['room_tax'] = $rate->room_tax;
            $cart_item_data['room_subtotal'] = $rate->room_subtotal;
            $cart_item_data['room_total'] = $rate->room_total;

            $cart_item_data['room_type_id'] = $room_type_id;

            $cart_item_data['room_guests'] = $guests;

            $cart_item_data['room_start_date'] = prepare_date($start_date, odp_get_date_format(), 'Y-m-d');
            $cart_item_data['room_end_date'] = prepare_date($end_date, odp_get_date_format(), 'Y-m-d');

            $cart_item_data['room_resources'] = $room_resources;
            $cart_item_data['currency'] = $currency;

        }

        return $cart_item_data;
    }

    public function odp_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
    {
        foreach ([
                     'rate_plan_id',
                     'rate_plan_name',
                     'lowest_nightly_rate',
                     'room_tax',
                     'room_subtotal',
                     'room_total',
                     'room_type_id',
                     'room_guests',
                     'room_start_date',
                     'room_end_date',
                     'room_resources',
                 ] as $key) {
            if (isset($values[$key])) {
                $item->update_meta_data('_' . $key, $values[$key]);
            }
        }
    }

    public function add_room_product_type_class()
    {
        include_once __DIR__ . '/../room_product_type_class.php';
    }

    public function add_room_product_type($type)
    {
        $type['room'] = esc_attr__('Room', '1day');
        return $type;
    }

    public function room_tab($tabs)
    {
        $tabs['room'] = array(
            'label' => esc_attr__('Room', '1day'),
            'target' => 'room_options',
            'class' => ('show_if_room'),
        );
        return $tabs;
    }

    public function remove_product_tab_if_room($tabs)
    {
        $tabs['attribute']['class'][] = 'hide_if_room';
        $tabs['shipping']['class'][] = 'hide_if_room';
        $tabs['linked_product']['class'][] = 'hide_if_room';
        $tabs['inventory']['class'][] = 'show_if_room';
        return $tabs;
    }

    public function room_options_product_tab_content()
    {
        global $post;
        $product = wc_get_product($post->ID);
        ?>

        <div id='room_options' class='panel woocommerce_options_panel'>


            <p class="form-field">
                <label for="room_features"><?php echo esc_attr__('Room features', '1day'); ?></label>

                <?php $room_features = get_post_meta($post->ID, 'room_features', true);

                wp_editor(
                    html_entity_decode($room_features),
                    'room_features',
                    $settings = [
                        'wpautop' => 1,
                        'media_buttons' => 1,
                        'textarea_name' => 'room_features',
                        'textarea_rows' => 8,
                        'quicktags' => 1,
                    ]
                ); ?>
            </p>

            <p class="form-field">
                <label for="room_checkin"><?php echo esc_attr__('Room check-in time', '1day'); ?></label>
                <?php $room_checkin = get_post_meta($post->ID, 'room_checkin', true); ?>
                <input type="text" value="<?php echo esc_attr($room_checkin); ?>" name="room_checkin" />
                <span title="<?php echo esc_attr__('Text to display in the room page', '1day'); ?>"
                      class="odp_star"></span>
            </p>

            <p class="form-field">
                <label for="room_checkout"><?php echo esc_attr__('Room check-out time', '1day'); ?></label>
                <?php $room_checkout = get_post_meta($post->ID, 'room_checkout', true); ?>
                <input type="text" value="<?php echo esc_attr($room_checkout); ?>" name="room_checkout" />
                <span title="<?php echo esc_attr__('Text to display in the room page', '1day'); ?>"
                      class="odp_star"></span>
            </p>

            <?php if (ODP_MULTIPLE_HOTELS) : ?>
                <div class="form-field form_field_resources">

                    <h4><?php echo esc_attr__('Room resources', '1day'); ?></h4>

                    <div class="room_resources">
                        <?php if ($product && $product->is_type('room')) : ?>

                            <?php $room_resources = get_post_meta($post->ID, 'room_resources', true); ?>

                            <?php if (!empty($room_resources)) : $room_resources = json_decode($room_resources, true); ?>
                                <?php if (!empty($room_resources) && is_array($room_resources)) : ?>
                                    <?php foreach ($room_resources as $room_resource) : ?>
                                        <?php echo $product->get_admin_room_resource($room_resource); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>

                    <div class="options_group">
                        <div class="footer">
                            <a class="button add_room_resource">
                                Add resource
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-field form_field_deposits">

                    <h4><?php echo esc_attr__('Room deposits', '1day'); ?></h4>

                    <div class="room_deposits">
                        <?php if ($product && $product->is_type('room')) : ?>

                            <?php $room_deposits = get_post_meta($post->ID, 'room_deposits', true); ?>

                            <?php if (!empty($room_deposits)) : $room_deposits = json_decode($room_deposits, true); ?>
                                <?php if (!empty($room_deposits) && is_array($room_deposits)) : ?>
                                    <?php foreach ($room_deposits as $room_deposit) : ?>
                                        <?php echo $product->get_admin_room_deposit($room_deposit); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>

                    <div class="options_group">
                        <div class="footer">
                            <a class="button add_room_deposit">
                                Add deposit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (ODP_MULTIPLE_HOTELS) : ?>
        <?php $resource_to_add = str_replace(['\n\r', '\n', '\r', PHP_EOL], '', $product->is_type('room') ? $product->get_admin_room_resource() : '');//echo preg_replace('/\s{2,}/', '', $product->get_admin_room_resource());
        ?>
        <?php $deposit_to_add = str_replace(['\n\r', '\n', '\r', PHP_EOL], '', $product->is_type('room') ? $product->get_admin_room_deposit() : '');//echo preg_replace('/\s{2,}/', '', $product->get_admin_room_resource());
        ?>

        <script>
            jQuery(document).ready(function ($) {
                $(".room_resources").sortable();
                $(".room_resources").disableSelection();

                $(document).on('click', '.remove_room_resource', function () {
                    $(this).closest('.room_resource').remove();
                });
                $('.add_room_resource').click(function () {
                    $('.room_resources').append('<?php echo $resource_to_add; ?>');
                });
            });

            jQuery(document).ready(function ($) {
                $(".room_deposits").sortable();
                $(".room_deposits").disableSelection();

                $(document).on('click', '.remove_room_deposit', function () {
                    $(this).closest('.room_deposit').remove();
                });
                $(document).on('click', '.add_room_deposit', function () {
                    $('.room_deposits').append('<?php echo $deposit_to_add; ?>');
                });

                $('.room_resources, .room_deposits').keydown(function (e) {
                    if (e.keyCode === 65 && e.ctrlKey) {
                        e.target.select()
                    }

                })
            });
        </script>
    <?php endif; ?>

        <?php
    }

    public function save_room_fields($post_id)
    {
        $property_code = !empty($_POST['property_code']) ? sanitize_text_field($_POST['property_code']) : '';
        update_post_meta($post_id, 'property_code', $property_code);

        if (!empty($_POST['room_features'])) {
            update_post_meta(
                $post_id,
                'room_features',
                sanitize_text_field(htmlentities($_POST['room_features']))
            );
        }

        if (!empty($_POST['room_checkin'])) {
            update_post_meta(
                $post_id,
                'room_checkin',
                sanitize_text_field(htmlentities($_POST['room_checkin']))
            );
        }

        if (!empty($_POST['room_checkout'])) {
            update_post_meta(
                $post_id,
                'room_checkout',
                sanitize_text_field(htmlentities($_POST['room_checkout']))
            );
        }

        if (!empty($_POST['room_resource_name']) && !empty($_POST['room_resource_value']) && !empty($_POST['room_resource_price'])) {
            $room_resources = [];

            foreach ($_POST['room_resource_name'] as $key => $room_resource_name) {
                $value = isset($_POST['room_resource_value'][$key]) ? sanitize_text_field($_POST['room_resource_value'][$key]) : '';
                $price = isset($_POST['room_resource_price'][$key]) ? sanitize_text_field($_POST['room_resource_price'][$key]) : '';

                if (!empty($value) && $price != '') {
                    $room_resources[$value] = [
                        'name' => $room_resource_name,
                        'value' => $value,
                        'price' => $price,
                    ];
                }
            }

            update_post_meta(
                $post_id,
                'room_resources',
                wp_json_encode($room_resources)
            );
        } else {
            delete_post_meta($post_id, 'room_resources');
        }

        if (!empty($_POST['room_deposit_name']) && !empty($_POST['room_deposit_value']) && !empty($_POST['room_deposit_price'])) {
            $room_deposits = [];

            foreach ($_POST['room_deposit_name'] as $key => $room_deposit_name) {
                $value = isset($_POST['room_deposit_value'][$key]) ? sanitize_text_field($_POST['room_deposit_value'][$key]) : '';
                $price = isset($_POST['room_deposit_price'][$key]) ? sanitize_text_field($_POST['room_deposit_price'][$key]) : '';

                if (!empty($value) && $price != '') {
                    $room_deposits[$value] = [
                        'name' => $room_deposit_name,
                        'value' => $value,
                        'price' => $price,
                    ];
                }
            }

            update_post_meta(
                $post_id,
                'room_deposits',
                wp_json_encode($room_deposits)
            );
        } else {
            delete_post_meta($post_id, 'room_deposits');
        }
    }

    public function room_rates_output()
    {
        global $product;
        if (!$product->is_type('room')) return;

        require_once __DIR__ . '/../../templates/rooms/rates/rates-table.php';
    }

    private function prepare_cart_items_for_booking($order)
    {
        if (!$order)
            return false;

        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $company = $order->get_billing_company();
        $country = $order->get_billing_country();
        $address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
        $postcode = $order->get_billing_postcode();
        $city = $order->get_billing_city();
        $phone = $order->get_billing_phone();
        $email = $order->get_billing_email();
        $note = $order->get_customer_note();

        $cart_items = WC()->cart->cart_contents;

        if (empty($cart_items))
            return false;

        $booking_data = (object)[
            'reservations' => (object)[
                'reservation' => []
            ]
        ];

        foreach ($cart_items as $cart_item_key => $cart_item) {
            $room_type_id = !empty($cart_item['room_type_id']) ? $cart_item['room_type_id'] : '';
            $room_start_date = !empty($cart_item['room_start_date']) ? $cart_item['room_start_date'] : '';
            $room_end_date = !empty($cart_item['room_end_date']) ? $cart_item['room_end_date'] : '';
            $room_guests = !empty($cart_item['room_guests']) ? $cart_item['room_guests'] : 2;
            $room_quantity = !empty($cart_item['quantity']) ? $cart_item['quantity'] : 0;
            $room_price = $cart_item['data']->get_price();

            $rate_plan_id = !empty($cart_item['rate_plan_id']) ? $cart_item['rate_plan_id'] : '';

            if (
                empty($room_start_date)
                || empty($room_end_date)
                || empty($room_type_id)
                || $room_quantity == 0
                || empty($rate_plan_id)
            ) continue;

            $hotel_term = odp_get_hotel_term_by_room_id($room_type_id);
            $hotel_name = $hotel_term ? $hotel_term->name : '';
            $hotel_property_code = $hotel_term ? odp_get_hotel_property_code($hotel_term->term_id) : '';

            if (empty($hotel_property_code) || empty($hotel_name))
                continue;

            $room_product_id = odp_get_room_product_id_by_sku($room_type_id);

            if (!$room_product_id)
                continue;

            $room_product = wc_get_product($room_product_id);

            if (!$room_product)
                continue;

            $room_name = $room_product->get_name();


            $args = [
                'start_date' => $room_start_date,
                'end_date' => $room_end_date,
                'guests' => $room_guests,
                'room_type_id' => $room_type_id
            ];

            $result = odp_api_connect(ODP_API_URL, 'get', $args);

            $room = !empty($result['content']->data[0]->room_types[0]) ? $result['content']->data[0]->room_types[0] : false;

            if (!$room)
                continue;

            $rates = array_filter($room->rates, function ($rate) use ($rate_plan_id) {
                return $rate->rate_plan_id == $rate_plan_id;
            });

            $key = key($rates);
            $rate = $rates[$key];

            $cart_item_total = $room_price * $room_quantity;

            $nightly_rates = $rate->nightly_rates;

            foreach ($nightly_rates as $key => $nightly_rate) {
                $nightly_rates[$key]->rate_id = $rate_plan_id;
            }

            $reservation = (object)[
                'deposit' => '0.00',
                'hotel_name' => $hotel_name,
                'commissionamount' => '0.00',
                'customer' => (object)[
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'telephone' => $phone,
                    'address' => $address,
                    'city' => $city,
                    'zip' => $postcode,
                    'countrycode' => $country,
                    'remarks' => $note,
                ],
                'room' => [],
                'booking_id' => rand(),
                'hotel_id' => $hotel_property_code,
                'currencycode' => odp_get_currency($result['content']->data),
                'site_id' => 'website',
                'company' => 'website',
                'channel_ref_id' => $order_id,
                'booking_date' => date("Y-m-d"),
                'status' => 'new',
                'confirmed' => (bool)false,
                'totalprice' => $cart_item_total
            ];

            for ($i = 1; $i <= $room_quantity; $i++) {
                $room = (object)[
                    'id' => $room_type_id,
                    'name' => $room_name,
                    'guest_firstname' => $first_name,
                    'guest_lastname' => $last_name,
                    'arrival_date' => $room_start_date,
                    'departure_date' => $room_end_date,
                    'currencycode' => odp_get_currency($result['content']->data),
                    'numberofguests' => $room_guests,
                    'numberofchild' => 0,
                    'totalprice' => $room_price,
                    'remarks' => '',
                    'numberofadult' => $room_guests,
                    'price' => $nightly_rates
                ];

                $reservation->room[] = $room;
            }

            $booking_data->reservations->reservation[] = $reservation;
        }


        return $booking_data;
    }

    public function room_checkout_process($order_id, $posted_data, $order)
    {
        $booking_data = $this->prepare_cart_items_for_booking($order);

        update_post_meta($order_id, 'odp_booking_data', $booking_data);

        $result = odp_api_connect(ODP_API_URL_BOOKING, 'post', $booking_data);

        if (!$result['state'])
            throw new Exception(esc_attr__("Can't reserve a room. API call error", '1day'));
    }

    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public function get_cart_item_name($name, $cart_item)
    {
        if ($cart_item['data']->is_type('room') && !empty($cart_item['rate_plan_name']) && !empty($cart_item['rate_plan_name']) && !empty($cart_item['rate_plan_name'])) {
            $addition = ' (' . prepare_date($cart_item['room_start_date'], 'Y-m-d', odp_get_date_format()) .
                ' - ' . prepare_date($cart_item['room_end_date'], 'Y-m-d', odp_get_date_format()) .
                ', ' . $cart_item['rate_plan_name'] . ')';

            return wp_kses_post($name . $addition);
        }
        return wp_kses_post($name);
    }

    public function get_order_item_name($name, $item)
    {
        if ($item->get_product()->is_type('room') && !empty($item['_rate_plan_name']) && !empty($item['_rate_plan_name']) && !empty($item['_rate_plan_name'])) {
            $addition = ' (' . prepare_date($item['_room_start_date'], 'Y-m-d', odp_get_date_format()) .
                ' - ' . prepare_date($item['_room_end_date'], 'Y-m-d', odp_get_date_format()) .
                ', ' . $item['_rate_plan_name'] . ')';

            return wp_kses_post($name . $addition);
        }
        return wp_kses_post($name);
    }

}

ODP_Init_Room_Product_Type::getInstance();
