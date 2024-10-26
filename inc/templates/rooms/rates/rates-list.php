<?php if($room) : ?>
    <?php foreach($room->rates as $i => $rate) : ?>
        <tr>
            <td class="check_column">
                <label class="switch">
                    <input type="radio" <?php echo $i == 0 ? 'checked' : ''; ?> value="<?php echo !empty($room_resource['value']) ? esc_attr($room_resource['value']) : ''; ?>" name="room_resource[]">
                    <div class="slider round"></div>
                </label>
            </td>
            <td>
                <div class="rate_plan_name"><?php echo esc_attr($rate->rate_plan_name); ?></div>
                <div class="mobile">
                    <?php echo esc_attr(__('Amount', '1day')); ?>: <?php echo esc_attr((float)$rate->room_subtotal); ?><br>
                    <?php if($roomHasTaxes) : ?>
                    <?php echo esc_attr(__('Taxes', '1day')); ?>: <?php echo esc_attr((float)$rate->room_tax); ?><br>
                    <?php echo esc_attr(__('Total', '1day')); ?>: <?php echo esc_attr((float)$rate->room_subtotal + (float)$rate->room_tax); ?>
                    <?php endif; ?>
                </div>
            </td>

            <td><?php echo esc_attr((float)$rate->room_subtotal); ?></td>
            <?php if($roomHasTaxes) : ?>
            <td><?php echo esc_attr((float)$rate->room_tax); ?></td>
            <td><?php echo esc_attr((float)$rate->room_subtotal + (float)$rate->room_tax); ?></td>
            <?php endif; ?>
            <td>
                <?php if($room->quantity > 0) : ?>
                    <form action="<?php echo esc_url(get_site_url() . '/checkout/'); ?>" method="post" >
                        <input name="add-to-cart" type="hidden" value="<?php echo esc_attr($product_id); ?>" />
                        <input name="filter_id" type="hidden" value="<?php echo esc_attr($filter_id); ?>" />
                        <input name="rate_plan_id" type="hidden" value="<?php echo esc_attr($rate->rate_plan_id); ?>" />
                        <input name="rate_plan_name" type="hidden" value="<?php echo esc_attr($rate->rate_plan_name); ?>" />
                        <input name="room_type_id" type="hidden" value="<?php echo esc_attr($room->id); ?>" />
                        <input name="room_resources" type="hidden" value="<?php echo esc_attr($resources); ?>" />
                        <input name="room_subtotal" type="hidden" value="<?php echo esc_attr($rate->room_subtotal); ?>" />
                        <input name="room_tax" type="hidden" value="<?php echo esc_attr($rate->room_tax); ?>" />
                        <input name="room_total" type="hidden" value="<?php echo esc_attr($rate->room_total); ?>" />
                        <input name="room_quantity" type="number" value="1" />
                    </form>
                <?php else : ?>
                    <b><?php echo esc_attr__('Booked out', '1day'); ?></b>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>