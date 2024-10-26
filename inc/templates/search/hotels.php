
<?php if(empty($items)) : ?>

    <div class="odp_search_results-empty">
        <?php echo esc_attr__('Empty search results', '1day'); ?>
    </div>

<?php else : ?>


    <div class="odp_search_results-table">
        <div class="tr head">
            <div class="td title"><b><?php echo esc_attr__('Hotels', '1day'); ?></b></div>
            <div class="td features"><b><?php echo esc_attr__('Description', '1day'); ?></b></div>
            <div class="td cost"><b><?php echo esc_attr__('Price', '1day'); ?></b></div>
        </div>
        <?php foreach($items as $hotel) : $hotel_term = odp_get_hotel_term_by_property_code($hotel->property_code); ?>

        <div class="tr" item="<?php echo esc_attr($hotel->property_code); ?>">
            <div class="td title">

                <?php if($hotel_term) : $hotel_image_url = odp_get_hotel_img($hotel_term->term_id); ?>
                    <?php if($hotel_image_url) : ?>
                        <img src="<?php echo esc_url($hotel_image_url); ?>" alt="<?php echo esc_attr($hotel->property_name); ?>"/>
                    <?php endif; ?>
                <?php endif; ?>

                <h4><?php echo esc_attr($hotel->property_name); ?></h4>

            </div>
            <?php
            $features = $hotel_term ?
            odp_get_hotel_short_description($hotel_term->term_id) :
            '';
            ?>
            <div class="td features"><?php echo wp_kses($features, 'default'); ?></div>
            <div class="td cost">
                <div class="cost-wrapper">
                    <?php $lowest_rate = odp_get_hotel_lowest_rate($hotel); ?>
                    <?php if($lowest_rate) : ?>
                    <div class="cost"><span><?php echo esc_attr__('From', '1day'); ?> <?php echo
                            odp_get_currency_symbol($items) . $lowest_rate; ?></span><?php echo esc_attr__('/night', '1day'); ?></div>
                    <div><a class="odp_btn <?php if(!empty($filter_results_settings['buttons_class'])) echo esc_attr($filter_results_settings['buttons_class']); ?>" href="<?php echo $hotel_term ? esc_url(get_term_link($hotel_term->term_id)) : esc_url(odp_get_hotel_page_url($hotel->property_code)); ?>"><?php echo esc_attr__('Show Rooms', '1day'); ?></a></div>
                    <?php else : ?>
                        <?php echo esc_attr__('Not available', '1day'); ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <style>

    </style>

    <?php include_once __DIR__ . '/pagination.php'; ?>


<?php endif; ?>

