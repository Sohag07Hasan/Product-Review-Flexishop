<?php

class Flexi_Related_Products extends WP_Widget{
	
	function __construct(){
		$widget_ops = array('classname' => 'widget_related_products', 'description' => __( "Show products related to post") );
		parent::__construct('related-products', __('Post Related Products'), $widget_ops);
		$this->alt_option_name = 'widget_related_products';
	}
	
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of products to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
	
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
				
		return $instance;
	}
	
	
	function widget($args, $instance) {
		if(is_single()) :		
			extract($args);

			$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);
			if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
				$number = 10;
			
			global $post;
			$products = $this->get_related_products($post, $number);
						
?>
			<?php echo $before_widget; ?>
			<?php if ( $title ) echo $before_title . $title . $after_title; ?>
			<?php 
				if ( $products->have_posts() ) :
					echo "<ul>";
					while ( $products->have_posts() ) : $products->the_post();
						echo '<li> <a href="'.get_permalink().'">' . get_the_title() . ' </a></li>';
					endwhile;
					echo "</ul>";
				endif;
			?>
			
			<?php echo $after_widget; ?>

<?php
		endif;
	}
	
	
	/**
	 * get related products
	 * 
	function get_related_products($post, $limit){
		global $wpdb;
		$sql = "SELECT term_taxonomy_id FROM $wpdb->term_relationships
			INNER JOIN $wpdb->term_taxonomy
			ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
			WHERE $wpdb->term_taxonomy.taxonomy = 'post_tag'
			AND $wpdb->term_relationships.object_id = '$post->ID'	
		";
		
		$tag_ids = $wpdb->get_col($sql);
		$tags = array();
		if($tag_ids){
			foreach($tag_ids as $id){
				$tags[] = $wpdb->get_var("SELECT slug FROM $wpdb->terms WHERE term_id = '$id'");
			}
		}
		
		var_dump($tags);
		
		$products = new WP_Query(array('post_type'=>'product', 'posts_per_page'=>$limit, 'tag_slug__in'=>$tags));
		
		return $products;
	}
	*/ 
	/**
	 * get related products
	 * */
	function get_related_products($post, $limit = 10){
		$post_tags = wp_get_object_terms($post->ID, 'product_tag');
		$post_tag_slugs = array();
		foreach($post_tags as $term){
			$post_tag_slugs[] = $term->slug;
		}
		//return $post_tag_slugs;
		$products = new WP_Query(array(
			'post_type' => 'product',
			'posts_per_page' => $limit,
			
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field' => 'slug',
					'terms' => $post_tag_slugs
				)
			)
		));
		
		return $products;
	}
}
