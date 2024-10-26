<div class="room_booking_data">
    <?php if(!empty($hotel_name)) : ?>
        <?php if (odp_get_hide_hotel_name() != 'yes') : ?>
            <div class="hotel_name">
                <h3><?php echo esc_attr__('Hotel: ', '1day'); ?><?php echo esc_attr($hotel_name); ?></h3>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="booking_check">
        <div>
            <b><?php echo esc_attr__('Check-in', '1day'); ?></b>
            <div class="time">
                <?php echo esc_attr($room_checkin); ?>

            </div>
            <div class="date">
                <?php echo esc_attr($start_date); ?>
            </div>
        </div>
        <div>
            <b><?php echo esc_attr__('Check-out', '1day'); ?></b>
            <div class="time">
                <?php echo esc_attr($room_checkout); ?>

            </div>
            <div class="date">
                <?php echo esc_attr($end_date); ?>
            </div>
        </div>
    </div>

    <div class="booking_guests">
        <?php echo esc_attr($guests) . ' ' . esc_attr__('Adult(s)', '1day'); ?>
    </div>
</div>