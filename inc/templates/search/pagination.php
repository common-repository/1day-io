<?php if(!empty($pages) && $pages > 1) : ?>
<div class="paging">
    <?php for ($i = 1; $i <= $pages; $i++) : ?>
        <?php if($i == $current_page) : ?>
            <span><?php echo esc_attr($i); ?></span>
        <?php else : ?>
            <a href="<?php echo esc_url(add_query_arg('pa', $i, esc_url(odp_current_url()))); ?>"><?php echo esc_attr($i); ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>