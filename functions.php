<?php
/**
 * WP FlexiShop Two child theme functions and definitions
 *
 * @package WordPress
 * @subpackage WPFlexiShop_Two
 * @since WP FlexiShop Two 1.0
 */


//widgets
include dirname(__FILE__) . '/widgets/widgets.php';


//shorten the post title
add_filter('the_title', 'shorten_the_title');
function shorten_the_title($title){
	if(is_single()){
		return $title;
	}
	
	global $post;
	if($post->post_type == 'product'){
		$title = substr($title, 0, 19) . '...';
	}
	return $title;
}


/*
 * image resizer
 * */
add_filter('prima_image', 'rwo_prima_image');
function rwo_prima_image($img){	
	global $post;
	
	if($post->post_type == 'post' && is_single()){
		$width = "150";
		$height = "180";
		$url = get_post_meta($post->ID, 'ReviewAZON_MediumImage', true);
		return "<img class='attachment-thumbnail' src='$url' alt='$post->post_title' style='width:150px;height:180px;' />";	
	}
	
	if(is_single()){
		
		$width = "298";
		$height = "300";
		$url = get_post_meta($post->ID, 'ReviewAZON_LargeImage', true);
		return "<a class='zoom' href='$url'><img class='attachment-shop_single' src='$url' style='height:300px; width:298px' /></a>";
		
		return $img;
	}
	else{
		$width = "150";
		$height = "180";
		$url = get_post_meta($post->ID, 'ReviewAZON_MediumImage', true);
		return "<img class='attachment-thumbnail' src='$url' alt='$post->post_title' style='width:150px;height:180px;' />";	
	}
	
		
	
}

//add_filter('prima_image_args', 'rwo_prima_image_args');
function rwo_prima_image_args($args){
	/*
	global $post;
	$args['meta_key'] = 'ReviewAZON_MediumImage';	
	$args['size'] = 'thumbnail'; 
	return $args;
	*/ 
}


/*
 * return the listing ID
 * */
function get_amazon_listing_id($post_id){
	return get_post_meta($post_id, 'ReviewAZON_OfferListingId', true);
}


/*
 * @array = mulitidimentional array
 * returns an object
 * */
function wo_arrayToObject($array) {
    if(!is_array($array)) {
        return $array;
    }
    
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
      foreach ($array as $name=>$value) {
         $name = strtolower(trim($name));
         if (!empty($name)) {
            $object->$name = wo_arrayToObject($value);
         }
      }
      return $object; 
    }
    else {
      return FALSE;
    }
}


/*
 * get product id by asin
 * */
function get_product_id_byASIN($asin){
	global $wpdb;
	return $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'ReviewAZON_ASIN' AND meta_value = '$asin'");
}


/*
 * update the cart
 * */
function Reviewzon_remove_an_item(){
	if($_GET['Reviewzonaction'] == 'remove') :
		$cartCookie = Reviewzon_get_cart();
		if($cartCookie){
			$cart_item_id = array(
				$_GET['item-id'] => 0
			);
					
			
			$pas = new AmazonPAS();
			$response = $pas->cart_modify($cartCookie->cart->cartid, $cartCookie->cart->hmac, $cart_item_id, null, $cartCookie->cart->country);
			
			if($response->isOK()){
				global $woocommerce;
				$woocommerce->add_message("Item removed successfully");
			}
		}
	endif;
}


/*
 * returns the current cart from cookie
 * */

function Reviewzon_get_cart(){
	return json_decode(stripslashes($_COOKIE["wo_rzon_cart_info"]));
}

//update cart command
add_action( 'init', 'Reviewzon_cart_update' );
function Reviewzon_cart_update(){
	if($_POST['Reviewzon_cart_updated'] == "Y") :
		$cart_item_id = array();
		if(is_array($_POST['cart'])){
			foreach($_POST['cart'] as $item_id => $quantity){
				$cart_item_id[$item_id] = $quantity['qty'];
			}
			
			$cartCookie = Reviewzon_get_cart();
			if($cartCookie){				
				$pas = new AmazonPAS();
				$response = $pas->cart_modify($cartCookie->cart->cartid, $cartCookie->cart->hmac, $cart_item_id, null, $cartCookie->cart->country);				
				if($response->isOK()){
					global $woocommerce;
					$woocommerce->add_message("Cart Updated");
				}
			}
		}		
		
	endif;
	
}


/*
 * mini cart
 */

add_action('init', 'Reviewzon_init');
function Reviewzon_init(){
	remove_filter( 'wp_nav_menu_items', 'prima_minicart_nav_menu_items');
	add_filter( 'wp_nav_menu_items', 'Reviewzon_minicart_nav_menu_items', 10, 2);
}

function Reviewzon_minicart_nav_menu_items( $items, $args ) {
	if ( !prima_get_setting( 'minicart' ) )
		return $items;
	if ( $args->theme_location != 'topnav-menu' && $args->theme_location != 'loggedin-topnav-menu' )
		return $items;
	global $woocommerce;
	
	$cart_count = 0;
	$cartCookie = Reviewzon_get_cart();
	if($cartCookie){				
		$pas = new AmazonPAS();
		$response = $pas->cart_get($cartCookie->cart->cartid, $cartCookie->cart->hmac, null, $cartCookie->cart->country);			
		if($response->isOK()){
			if(isset($response->body->Cart->CartItems->CartItem)){			
				foreach($response->body->Cart->CartItems->CartItem as $cartItem){
					$cart_count += (int)$cartItem->Quantity;
				}
			}
		}
	}
		
	$items .= '<li id="basketlink">';
	$items .= '<a class="basket" href="'.$woocommerce->cart->get_cart_url().'">'.sprintf( __('Your Basket (%d)', 'primathemes'), $cart_count ).'</a>';
	if( !is_cart() && !is_checkout() ) :
		$items .= '<div id="minicart">';
		$items .= '<h4 class="minicart-cartcount">'.sprintf(_n('<strong>%d</strong> item', '<strong>%d</strong> items', $cart_count, 'primathemes'), $cart_count).' <a class="right" href="'.$woocommerce->cart->get_cart_url().'">'.__('View Cart &rarr;', 'primathemes').'</a></h4>';
		
		$items .= "<div class='widget_shopping_cart'>";
		$itmes .= "<h4 class='widget_title'>Cart</h4>";
		$items .= "<ul class='cart_list product_list_widget'>";
		if($cart_count > 0){
			foreach($response->body->Cart->CartItems->CartItem as $cartItem){
				$product_id = get_product_id_byASIN($cartItem->ASIN);
				$permalink = get_permalink($product_id);
				$link_title = (string)$cartItem->Title;
				$img_url = get_product_image($product_id);
				
				$items .= '<li> 
					<a title="'.$link_title.'" href="'.$permalink.'"> <img style="width:50px; height:50px" alt="thumbnail" src="'.$img_url.'" /> '.minicart_shorten_title($link_title).' </a>
					<span class="quantity">'.(string)$cartItem->Quantity. ' × ' . (string)$cartItem->Price->FormattedPrice;'</span>
				 </li>';
			}
		}
		else{
			$items .= '<li class="empty">No products in the cart.</li>';
		}
		
		$items .= "</ul>";
		$items .= "</div>";
				
		if($cart_count > 0){
			$items .= '<p class="total"><strong>Subtotal:</strong>'.(string)$response->body->Cart->SubTotal->FormattedPrice.'</p>';
			$items .= '<p>
				<a class="button checkout" href="'.get_permalink(get_option('woocommerce_shop_page_id')).'">'.__('Visit Shop &rarr;', 'woocommerce').'</a>
				<a class="button checkout" href="'.(string)$response->body->Cart->PurchaseURL.'">'.__('Checkout &rarr;', 'woocommerce').'</a>
			</p>';
		}
		else{
			$items .= '<p>				
				<a class="button checkout" href="'.get_permalink(get_option('woocommerce_shop_page_id')).'">'.__('Visit Shop &rarr;', 'woocommerce').'</a></p>';
		}
			 
		$items .= '</div>';
	endif;
    $items .= '</li>';
    return $items;
}

//ajax called function
function Reviewzon_minicart_nav_menu_items_ajax($items = '') {
			
	global $woocommerce;
	
	$cart_count = 0;
	$cartCookie = Reviewzon_get_cart();
	if($cartCookie){				
		$pas = new AmazonPAS();
		$response = $pas->cart_get($cartCookie->cart->cartid, $cartCookie->cart->hmac, null, $cartCookie->cart->country);			
		if($response->isOK()){
			if(isset($response->body->Cart->CartItems->CartItem)){			
				foreach($response->body->Cart->CartItems->CartItem as $cartItem){
					$cart_count += (int)$cartItem->Quantity;
				}
			}
		}
	}
		
	
	$items .= '<a class="basket" href="'.$woocommerce->cart->get_cart_url().'">'.sprintf( __('Your Basket (%d)', 'primathemes'), $cart_count ).'</a>';
	if( !is_cart() && !is_checkout() ) :
		$items .= '<div id="minicart">';
		$items .= '<h4 class="minicart-cartcount">'.sprintf(_n('<strong>%d</strong> item', '<strong>%d</strong> items', $cart_count, 'primathemes'), $cart_count).' <a class="right" href="'.$woocommerce->cart->get_cart_url().'">'.__('View Cart &rarr;', 'primathemes').'</a></h4>';
		
		$items .= "<div class='widget_shopping_cart'>";
		$itmes .= "<h4 class='widget_title'>Cart</h4>";
		$items .= "<ul class='cart_list product_list_widget'>";
		if($cart_count > 0){
			foreach($response->body->Cart->CartItems->CartItem as $cartItem){
				$product_id = get_product_id_byASIN($cartItem->ASIN);
				$permalink = get_permalink($product_id);
				$link_title = (string)$cartItem->Title;
				$img_url = get_product_image($product_id);
				
				$items .= '<li> 
					<a title="'.$link_title.'" href="'.$permalink.'"> <img style="width:50px; height:50px" alt="thumbnail" src="'.$img_url.'" /> '.minicart_shorten_title($link_title).' </a>
					<span class="quantity">'.(string)$cartItem->Quantity. ' × ' . (string)$cartItem->Price->FormattedPrice;'</span>
				 </li>';
			}
		}
		else{
			$items .= '<li class="empty">No products in the cart.</li>';
		}
		
		$items .= "</ul>";
		$items .= "</div>";
				
		if($cart_count > 0){
			$items .= '<p class="total"><strong>Subtotal:</strong>'.(string)$response->body->Cart->SubTotal->FormattedPrice.'</p>';
			$items .= '<p>
				<a class="button checkout" href="'.get_permalink(get_option('woocommerce_shop_page_id')).'">'.__('Visit Shop &rarr;', 'woocommerce').'</a>
				<a class="button checkout" href="'.(string)$response->body->Cart->PurchaseURL.'">'.__('Checkout &rarr;', 'woocommerce').'</a>
			</p>';
		}
		else{
			$items .= '<p>				
				<a class="button checkout" href="'.get_permalink(get_option('woocommerce_shop_page_id')).'">'.__('Visit Shop &rarr;', 'woocommerce').'</a></p>';
		}
			 
		$items .= '</div>';
	endif;
   
    return $items;
}


/*
 * return the product image
 * */
function get_product_image($product_id){
	return get_post_meta($product_id, 'ReviewAZON_SmallImage', true);
}


/*
 * shortent the title from minicart (basket)
 * */
function minicart_shorten_title($title){
	return substr($title, 0, 30) . '...';
}


/*
 * creating a custom naviation menu
 * */
add_action('init', 'register_a_new_header_menu');
function register_a_new_header_menu(){
  register_nav_menus(
    array( 'category-header-menu' => __( 'Header Menu + Product Category' ) )
  );
}

//filtering the navigation menu
add_filter('wp_nav_menu_header-menu_items', 'filter_header_menu', 10, 2);
function filter_header_menu($items, $args){
	
	$new_items = get_porduct_categories();
	
	
	//$new_items = '<li class="menu-item"><a href="#" onclick="return false;">Categories</a><ul class="sub-menu">'.$terms.'</ul></li>' ;	
	return $items . $new_items;
}

//add_filter('wp_nav_menu_objects', 'changing_the_nav_menu_object', 10, 2);
function changing_the_nav_menu_object($sorted_menu_items, $args){
	var_dump($sorted_menu_items);
	return $sorted_menu_items;
}


//get sorted product category
function get_porduct_categories(){
	$categories = get_product_parent_categories();
	//var_dump($categories);
	$items = '';
	foreach($categories as $cat){
		$items .= '<li class="menu-item"> <a href="'.get_term_link((int) $cat, 'product_cat').'">'.custom_get_term_name($cat) . '</a>';
		$child_categories = get_product_parent_categories($cat);
		//var_dump($child_categories);
		if($child_categories){ 
			$items .= '<ul class="sub-menu">';			
			foreach($child_categories as $c_cat){
				//var_dump($c_cat);
				$items .= '<li class="menu-item"><a href="'.get_term_link((int) $c_cat, 'product_cat').'"> '.custom_get_term_name($c_cat) . '</a>';				
				$gchild_categories = get_product_parent_categories($c_cat);
				//var_dump($gchild_categories);
				if($gchild_categories){
					$items .= '<ul class="sub-menu">';
					foreach($gchild_categories as $gc_cat){
						$items .= '<li class="menu-item"> <a href="'.get_term_link((int) $gc_cat, 'product_cat').'"> '.custom_get_term_name($gc_cat) . '</a> </li>';		
					}				
					$items .= '</ul>';
				}
				
				$items .= '</li>';
			}
			
			$items .= '</ul>' ;
		}	
		
		$items .= '</li>';
	}
	
	return $items;
}

function custom_get_term_name($id){
	global $wpdb;
	return $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '$id'");
}


/*
 * function to get the parent categories
 * */
function get_product_parent_categories($parent = 0){
	global $wpdb;	
	
	$sql = "SELECT $wpdb->terms.term_id FROM $wpdb->terms
			INNER JOIN $wpdb->term_taxonomy
			ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
			WHERE $wpdb->term_taxonomy.taxonomy = 'product_cat'
			AND $wpdb->term_taxonomy.parent = $parent
			AND $wpdb->term_taxonomy.count > 0
			ORDER BY $wpdb->terms.name ASC
	";
	
	
	
	
	return $wpdb->get_col($sql);
}


//show related products
function show_post_related_products(){
	include dirname(__FILE__) . '/post-related-products.php';
}
