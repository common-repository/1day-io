<?php global $product; ?>

<?php $filter_id = !empty($_GET['filter_id']) ? sanitize_text_field($_GET['filter_id']) : ''; ?>

<?php
$args = [
    'start_date' => prepare_date(odp_gc('start_date'), odp_get_date_format(), 'Y-m-d'),
    'end_date' => prepare_date(odp_gc('end_date'), odp_get_date_format(), 'Y-m-d'),
    'guests' => odp_gc('guests'),
    'room_type_id' => $product->get_sku()
];

$result = odp_api_connect(ODP_API_URL, 'get', $args);
?>

<?php if ($result['state'] && !empty($result['content']->data[0]->room_types[0])) : ?>
    <?php
    $room = $result['content']->data[0]->room_types[0];

    $roomHasTaxes = false;
    foreach ($room->rates as $i => $rate) {
        if (!empty($rate->room_taxes)) {
            $roomHasTaxes = true;
            break;
        }
    }
    ?>

    <h3 class="room_rates_title"><?php echo esc_attr__('Choose room rates', '1day'); ?></h3>
    <table class="rates" <?php echo !empty($_GET['filter_id']) ? "id='rates_{$filter_id}'" : ""; ?>
           room_type_id="<?php echo esc_attr($product->get_sku()); ?>"
           room_product_id="<?php echo esc_attr($product->get_id()); ?>">
        <thead>
        <tr>
            <th class="check_column"></th>
            <th><?php echo esc_attr__('Rates', '1day'); ?></th>
            <th><?php echo esc_attr__('Amount', '1day'); ?></th>
            <?php if($roomHasTaxes) : ?>
            <th><?php echo esc_attr__('Taxes', '1day'); ?></th>
            <th><?php echo esc_attr__('Total Room Rates', '1day'); ?></th>
            <?php endif; ?>
            <th><?php echo esc_attr__('Quantity', '1day'); ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
<?php else: ?>
    <?php __('Sorry, the room is not available', '1day'); ?>
<?php endif; ?>

<?php if (!empty($_GET['filter_id'])) : ?>
    <style>
        #rates_<?php echo esc_attr($filter_id); ?> .odp_btn,
        #rates_<?php echo esc_attr($filter_id); ?> button,
        #rates_<?php echo esc_attr($filter_id); ?> input[type=submit] {
            background: <?php echo esc_attr(odp_get_filter_results_settings($filter_id, 'buttons_background')); ?>;
            color: <?php echo esc_attr(odp_get_filter_results_settings($filter_id, 'buttons_font_color')); ?>;
        }
    </style>
<?php endif; ?>
