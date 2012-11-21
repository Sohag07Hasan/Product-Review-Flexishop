<?php

/**
 * The template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage WPFlexiShop_Two
 * @since WP FlexiShop Two 1.0
 */

get_header(); ?>

<header id="header" role="banner" class="clearfix">
  <div class="margin clearfix">
	<?php prima_page_title( '<h1>', '</h1>' ); ?>
	<?php prima_page_tagline( '<p class="headertagline">', '</p>' ); ?>
  </div>
</header>

<section id="main" role="main" class="clearfix">
  <div class="margin">
  
    <div class="content-wrap clearfix">
  
	<div id="content" class="clearfix">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	  
	 
		
		<?php 
			global $wp_query;//$post;
			$temp_query = clone $wp_query;
			
			
			/*
			
			$saved_post = $post; 
			
			show_post_related_products();
			$GLOBALS['post'] = $saved_post;
			$wp_query->post = $saved_post;
			setup_postdata($wp_query->post);
			*/
			
			show_post_related_products();
			$wp_query = clone $temp_query;
			
						
		?>
		
		 <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
	  
		<div class="entry">

		  <div class="postcontent">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<p class="page-link"><span>' . __( 'Pages:', 'primathemes' ) . '</span>', 'after' => '</p>' ) ); ?>
		  </div>

		  <?php if ( 'post' == get_post_type() ) : ?>
		  <p class="postmeta"><small>
			<?php 
			  printf( __( 'Posted <span class="metadate">on %1$s</span> <span class="metaauthor">by %2$s</span>', 'primathemes' ), get_the_date(), '<a class="url fn n" href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'" title="">'.get_the_author_meta( 'display_name' ).'</a>' );
			  if ( comments_open() && ! post_password_required() ) :
				echo '<span class="metacomment"> / ';
				comments_popup_link( __( 'No comments', 'primathemes' ), __( 'One comment', 'primathemes' ), __( '% comment', 'primathemes' ) );
				echo '</span>';
			  endif; 
			  edit_post_link( __( 'Edit', 'primathemes' ), ' / ', '' );
			?>
		  </small></p>
		  <?php endif; ?>

		</div>
	  </article>

	  <nav id="nav-single" class="navigation">
		<h3 class="assistive-text"><?php _e( 'Post navigation', 'primathemes' ); ?></h3>
		<span class="nav-previous"><?php previous_post_link( '%link', __( '<span class="meta-nav">&larr;</span> Previous', 'primathemes' ) ); ?></span>
		<span class="nav-next"><?php next_post_link( '%link', __( 'Next <span class="meta-nav">&rarr;</span>', 'primathemes' ) ); ?></span>
	  </nav>
	  
	  <?php comments_template( '', true ); ?>
	
	<?php endwhile; ?>

	<?php else: ?>
	
	  <?php get_template_part( 'content', '404' ); ?>
	  
	<?php endif; ?>
	</div>
	
	<?php prima_sidebar(); ?>
	
	</div>
	
	<?php prima_sidebar( 'mini' ); ?>
	
  </div>
</section>

<?php get_footer(); ?>
