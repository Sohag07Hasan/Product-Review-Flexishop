<?php

//related products
include dirname(__FILE__) . '/related-products.php';
add_action('widgets_init', 'initialize_custom_widgets');
function initialize_custom_widgets(){
	register_widget('Flexi_Related_Products');
}
