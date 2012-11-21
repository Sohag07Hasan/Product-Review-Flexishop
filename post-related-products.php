<?php

$post_id = get_the_ID();

$product_tags = wp_get_object_terms($post_id, 'product_tag');
$post_tag_slugs = array();

if($product_tags) :
	foreach($product_tags as $term){
		$post_tag_slugs[] = $term->slug;
	}
	//return $post_tag_slugs;
	$products = new WP_Query(array(
		'post_type' => 'product',
		'posts_per_page' => 4,
		
		'tax_query' => array(
			array(
				'taxonomy' => 'product_tag',
				'field' => 'slug',
				'terms' => $post_tag_slugs
			)
		)
	));
	
endif;



?>

<section id="related-products">
	<h2 class="horizontalheading"><span><?php _e('Related Products', 'woocommerce'); ?></span></h2>
	
	<?php if($products->have_posts()) :	?>
		<ul class="products products-col"> 
				
			<?php 				
				
				foreach($products->posts as $p) :
												
					$url = get_post_meta($p->ID, 'ReviewAZON_MediumImage', true);
					$img = "<img class='attachment-thumbnail' src='$url' alt='$p->post_title' style='width:140px;height:170px;' />";	
					
			?>
					<li class="product" style="float: left; width: 210px;" >
						<div class="product-image-box">
							<a href="<?php echo get_permalink($p->ID); ?>">
								<?php echo $img; ?>			
							</a>
						</div>
						<span class="onsale">Sale!</span>
						
						<a href="<?php echo get_permalink($p->ID); ?>">
							<h3> 
								<?php
									echo substr($p->post_title, 0, 25) . '...';
								?>
							</h3>
						</a>
						
						<span class="span">
							<del style="padding: 5px;" class="amount"> <?php echo get_post_meta($p->ID, '_regular_price', true); ?> </del>
							<ins style="padding: 5px;" class="amount"> <?php echo get_post_meta($p->ID, '_sale_price', true); ?> </ins>
						</span>					
						
					</li>
				<?php endforeach; ?>
		
		</ul>
	<?php endif; ?>
	
</section>

