<div id="odp_search_form_wrapper_<?php echo esc_attr($filter_id); ?>" class="odp_search_form_wrapper <?php echo $filter_form_settings['one_line'] == 'true' ? 'in_line' : 'in_column'; ?>" >

    <form id="odp_search_form_<?php echo esc_attr($filter_id); ?>" class="odp_search_form" action="<?php echo !empty($filter_form_settings['url']) ? esc_url($filter_form_settings['url']) : ''; ?>" method="get">

        <input type="hidden" name="filter_id" value="<?php echo esc_attr($filter_id); ?>"/>

        <div class="field datepicker_field">
            <label><?php echo esc_attr__('Dates (from - to)', '1day'); ?></label>
            <input type="text" class="datepicker" id="datepicker" autocomplete="off"/>
        </div>
        <div class="field guests_field">
            <label><?php echo esc_attr__('Guests', '1day'); ?></label>
            <select name="guests">
                <option value="1" <?php echo esc_attr(odp_is_selected('guests', '1')); ?>>1</option>
                <option value="2" <?php echo esc_attr(odp_is_selected('guests', '2')); ?>>2</option>
                <option value="3" <?php echo esc_attr(odp_is_selected('guests', '3')); ?>>3</option>
                <option value="4" <?php echo esc_attr(odp_is_selected('guests', '4')); ?>>4</option>
                <option value="5" <?php echo esc_attr(odp_is_selected('guests', '5')); ?>>5</option>
            </select>
        </div>
        <?php if($filter_form_settings['hide_sorting'] != 'yes') : ?>
        <div class="field ordering_field">
            <label><?php echo esc_attr__('Sorting', '1day'); ?></label>
            <select name="sort">
                <option value="asc" <?php echo esc_attr(odp_is_selected('sort', 'asc')); ?>><?php echo esc_attr__('Price (from low to high)', '1day'); ?></option>
                <option value="desc" <?php echo esc_attr(odp_is_selected('sort', 'desc')); ?>><?php echo esc_attr__('Price (from high to low)', '1day'); ?></option>
            </select>
        </div>
        <?php else : ?>
            <?php
            $options = odp_sorting_options();
            $default_sorting = $filter_form_settings['default_sorting'];
            if(empty($default_sorting))
                $default_sorting = array_keys($options)[0];
            ?>
            <input type="hidden" name="sort" value="<?php echo esc_attr($default_sorting); ?>" />
        <?php endif; ?>
        <div class="field submit_field">
            <?php
            $btn_text = $filter_form_settings['btn_text'];
            if(empty($btn_text)) $btn_text = __('Book', '1day');
            ?>
            <input type="submit" name="search_submit" value="<?php echo esc_attr($btn_text); ?>"/>
        </div>
        <input type="hidden" name="start_date" value="<?php echo esc_attr(odp_gc('start_date')); ?>" />
        <input type="hidden" name="end_date" value="<?php echo esc_attr(odp_gc('end_date')); ?>" />
        <input type="hidden" name="format" value="<?php echo esc_attr(convertPhpToJsMomentFormat(odp_get_date_format())); ?>" />
    </form>
    <style>
        <?php
        $styles = [
                'center' => 'margin-left: auto; margin-right: auto;',
                'left' => 'margin-right: auto; margin-left: initial;',
                'right' => 'margin-left: auto; margin-right: initial;',
        ];
        ?>
        #odp_search_form_wrapper_<?php echo esc_attr($filter_id); ?>{
            <?php if(!empty($filter_form_settings['bg_color'])) echo 'background: ' . esc_attr($filter_form_settings['bg_color']) . ';'; ?>
            <?php if(!empty($filter_form_settings['widget_width'])) echo 'width: ' . esc_attr($filter_form_settings['widget_width']) . '; max-width: ' . esc_attr($filter_form_settings['widget_width']) . ';'; ?>
            <?php echo esc_attr($styles[$filter_form_settings['alignment']]); ?>
            <?php if(!empty($filter_form_settings['border_radius'])) echo 'border-radius: ' . esc_attr($filter_form_settings['border_radius']) . ';'; ?>
        }

        <?php if(!empty($filter_form_settings['widget_width'])) : ?>
            @media only screen and (max-width: <?php echo esc_attr($filter_form_settings['widget_width']); ?>) {
                #odp_search_form_wrapper_<?php echo esc_attr($filter_id); ?> {
                    max-width: 100%;
                }
            }
        <?php endif; ?>

        #odp_search_form_<?php echo esc_attr($filter_id); ?> button,
        #odp_search_form_<?php echo esc_attr($filter_id); ?> input[type=submit],
        #odp_search_form_<?php echo esc_attr($filter_id); ?> .odp_btn{
            <?php if(!empty($filter_form_settings['btn_color'])) echo 'background: ' . esc_attr($filter_form_settings['btn_color']) . ';'; ?>
            <?php if(!empty($filter_form_settings['btn_font_color'])) echo 'color: ' . esc_attr($filter_form_settings['btn_font_color']) . ';'; ?>
            <?php if(!empty($filter_form_settings['btn_border_radius'])) echo 'border-radius: ' . esc_attr($filter_form_settings['btn_border_radius']) . ';'; ?>
        }

        #odp_search_form_<?php echo esc_attr($filter_id); ?> label{
            <?php if(!empty($filter_form_settings['font_color'])) echo 'color: ' . esc_attr($filter_form_settings['font_color']) . ';'; ?>
        }

    </style>

</div>