<?php

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

?>
    <header class="woocommerce-products-header">
        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
            <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
        <?php endif; ?>

        <?php do_action( 'woocommerce_archive_description' ); ?>
    </header>
<?php

$hotel_page_id = odp_get_hotel_page_id();

if(!empty($hotel_page_id)){

    echo apply_filters('the_content', get_the_content(null, false, $hotel_page_id));
}

do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );