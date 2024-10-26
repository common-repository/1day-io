<?php global $hotel_points; ?>

<?php if(!empty($hotel_points)) : ?>

    <div class="odp_hotels_map_wrapper">
        <h4><?php echo esc_attr__('Location', '1day'); ?></h4>

            <div class="odp_hotels_map" id="odp_hotels_map">

                <?php foreach ($hotel_points as $point) : ?>
                    <?php if(!empty($point->lat) && !empty($point->lng)) : ?>
                        <div class="hotel_marker" data-lat="<?php echo esc_attr($point->lat); ?>" data-lng="<?php echo esc_attr($point->lng); ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>

            </div>
    </div>

<?php else : ?>

    <div class="odp_search_results-empty">
        <?php echo esc_attr__('Empty map results. Please, change search criteria', '1day'); ?>
    </div>

<?php endif; ?>