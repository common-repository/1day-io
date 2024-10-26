<?php if (empty($items->room_types)) : ?>
    <div class="odp_search_results-empty">
        <?php echo esc_attr__('Empty search results', '1day'); ?>
    </div>
<?php else : ?>


    <div class="odp_search_results-table">
        <?php if (!empty($property_code)) : ?>
            <?php $hotel_term = odp_get_hotel_term_by_property_code(sanitize_text_field($property_code)); ?>

            <?php if (!is_wp_error($hotel_term) && !empty($hotel_term)) : ?>
                <?php if (ODP_MULTIPLE_HOTELS && $filter_results_settings['hide_hotel_name'] != 'yes') : ?>
                    <h3><?php echo esc_attr__('Hotel', '1day') . ' '; ?><?php echo esc_attr($hotel_term->name); ?></h3>
                <?php endif; ?>

                <?php if (ODP_MULTIPLE_HOTELS && $filter_results_settings['hide_hotel_image'] != 'yes') : ?>
                    <?php $image = odp_get_hotel_img($hotel_term->term_id) ?>

                    <?php if ($image) : ?>
                        <img src="<?php echo esc_url($image); ?>" alt=""/>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (ODP_MULTIPLE_HOTELS && $filter_results_settings['hide_hotel_description'] != 'yes') : ?>
                    <?php $description = odp_get_hotel_description($hotel_term->term_id); ?>
                    <?php if (!empty($description)) : ?>
                        <div class="hotel_description">
                            <?php echo wp_kses($description, 'default'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>


            <?php endif; ?>

        <?php endif; ?>
        <div class="tr head">
            <div class="td title"><b><?php echo esc_attr__('Rooms', '1day'); ?></b></div>
            <div class="td features"><b><?php echo esc_attr__('Features', '1day'); ?></b></div>
            <div class="td cost"><b><?php echo esc_attr__('Price', '1day'); ?></b></div>
        </div>

        <?php foreach ($items->room_types as $room) : $room_product_id = odp_get_room_product_id_by_room_id($room->id); ?>

            <div class="tr" item="<?php echo esc_attr($room->id); ?>">
                <div class="td title">

                    <?php if ($room_product_id) : $room_img = odp_get_room_img($room_product_id); ?>
                        <?php if ($room_img) : ?>
                            <img src="<?php echo esc_url($room_img); ?>" alt=""/>
                        <?php endif; ?>
                    <?php endif; ?>

                    <h4><?php echo esc_attr($room->name); ?></h4>
                </div>
                <div class="td features"><?php echo wp_kses(odp_get_room_features($room_product_id), 'default'); ?></div>
                <div class="td cost">
                    <div class="cost-wrapper">
                        <?php if (!empty($room->rates)) : ?>
                            <?php
                            $room_url = !empty($room->room_url) ? $room->room_url : '';
                            ?>
                            <div class="cost">
                            <span><?php echo esc_attr__('From', '1day'); ?> <?php echo
                                odp_get_currency_symbol($items->room_types, false) . odp_get_room_lowest_rate($room); ?></span><?php echo esc_attr__('/night', '1day'); ?>
                            </div>
                            <div>
                                <a class="odp_btn <?php if (!empty($filter_results_settings['buttons_class'])) echo esc_attr($filter_results_settings['buttons_class']); ?>"
                                   href="<?php echo esc_url(odp_get_room_page_url($room_product_id, $room_url)); ?>"><?php echo esc_attr__('Show Rates', '1day'); ?></a>
                            </div>
                        <?php else: ?>
                            <?php echo esc_attr__('Not available', '1day'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>
