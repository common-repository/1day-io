<?php
add_action( 'add_meta_boxes' , 'remove_post_custom_fields', 99 );
function remove_post_custom_fields(){
    remove_meta_box('tagsdiv-hotel', 'product', 'side');
}