<div class="booking_total">
    <div class="order_total" id="order_total">
    <b><?php echo esc_attr__('Total', '1day'); ?></b> <span>
            <span class="currency"><?php echo odp_get_currency_symbol(); ?></span>
            <span class="total"></span>
        </span></div>

    <form action="<?php echo esc_url(get_site_url() . '/checkout/'); ?>" method="post">

        <input name="add-to-cart" type="hidden" value="<?php echo esc_attr($product->get_id()); ?>" />
        <input name="rate_plan_id" type="hidden" value="" />
        <input name="rate_plan_name" type="hidden" value="" />
        <input name="room_type_id" type="hidden" value="<?php echo esc_attr($room_id); ?>" />
        <input name="room_resources" type="hidden" value="" />
        <input name="currency" type="hidden" value="<?php echo odp_get_currency(); ?>" />
        <?php echo woocommerce_quantity_input(array(), $product, false); ?>
        <button type="submit" name="submit"><?php echo esc_attr__('Book', '1day'); ?></button>

    </form>
</div>
