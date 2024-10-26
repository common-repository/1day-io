<div class="options_group room_deposit">
    <div class="header">
        <div>
            <a class="button">
                <?php echo esc_attr__('Drag', '1day'); ?>
            </a>
        </div>

        <div class="room_deposit_inputs">
            <input type="text" name="room_deposit_name[]" value="<?php echo isset($args['name']) ? esc_attr($args['name']) : ''; ?>" placeholder="<?php echo esc_attr__('Name', '1day'); ?>">
            <input type="text" name="room_deposit_value[]" value="<?php echo isset($args['value']) ? esc_attr($args['value']) : ''; ?>" placeholder="<?php echo esc_attr__('Value', '1day'); ?>">
            <input type="text" name="room_deposit_price[]" value="<?php echo isset($args['price']) ? esc_attr($args['price']) : ''; ?>" placeholder="<?php echo esc_attr__('Price', '1day'); ?>">
        </div>

        <div>
            <a class="button remove_room_deposit">
                <?php echo esc_attr__('Remove', '1day'); ?>
            </a>
        </div>
    </div>
</div>