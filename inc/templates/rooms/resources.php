<?php if(!empty($room_resources)) : ?>

    <?php $room_resources = json_decode($room_resources, true); ?>

    <?php if(is_array($room_resources)) : ?>

        <div class="room_resources">

            <h3><?php echo esc_attr($room_resources_title); ?></h3>

            <?php foreach ($room_resources as $room_resource) : ?>

                <div class="room_resource">
                    <div class="room_resource_check">
                        <label class="switch">
                            <input type="checkbox" value="<?php echo !empty($room_resource['value']) ? esc_attr($room_resource['value']) : ''; ?>" name="room_resource">
                            <div class="slider round"></div>
                            <input name="resource_price" type="hidden" value="<?php echo esc_attr($room_resource['price']); ?>"/>
                        </label>
                    </div>
                    <div class="room_resource_name"><?php echo !empty($room_resource['name']) ? esc_attr($room_resource['name']) : ''; ?></div>
                    <div class="room_resource_price"><?php echo !empty($room_resource['price']) ? wc_price($room_resource['price']) : ''; ?></div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif;?>

<?php endif; ?>
