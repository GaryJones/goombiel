<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop;

get_header('shop'); ?>
	  
	<?php
        // Remove breadcrumb if needed
        $post_id = yiw_post_id();
        if( get_post_meta( $post_id, '_show_breadcrumbs_page', true ) == 'no' ) {
            remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
        }

        if( get_post_meta( $post_id, '_slogan_page', true ) ) {
            get_template_part('slogan');
        }
		/** 
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */	 
		do_action('woocommerce_before_main_content');
	?>

		<h1 class="page-title"><?php if( get_post_meta( yiw_post_id(), '_show_title_page', true ) == 'yes' ) woocommerce_page_title(); ?></h1>
		
		<div class="clear"></div>
				
		<?php if ( is_tax() && get_query_var( 'paged' ) == 0 ) : ?>
			<?php echo '<div class="term-description">' . yiw_addp( term_description() ) . '</div>'; ?>
		<?php elseif ( ! is_search() && get_query_var( 'paged' ) == 0 && ! empty( $shop_page ) && is_object( $shop_page ) ) : ?>
			<?php echo '<div class="page-description">' . apply_filters( 'the_content', $shop_page->post_content ) . '</div>'; ?>
		<?php endif; ?>
				
		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_pagination hook
				 *
				 * @hooked woocommerce_pagination - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>
		
			<?php woocommerce_product_loop_start(); ?>
			
				<?php woocommerce_product_subcategories(); $woocommerce_loop['loop'] = 0; $woocommerce_loop['columns'] = 0; ?>
		
				<?php while ( have_posts() ) : the_post(); ?>
		
					<?php woocommerce_get_template_part( 'content', 'product' ); ?>
		
				<?php endwhile; // end of the loop. ?>
				
			<?php woocommerce_product_loop_end(); ?>

			<?php do_action('woocommerce_after_shop_loop'); ?>
		
		<?php else : ?>
		
			<?php if ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
                <div class="clear"></div>
				<p><?php _e( 'No products found which match your selection.', 'woocommerce' ); ?></p>
					
			<?php endif; ?>
		
		<?php endif; ?>
		
		<div class="clear"></div>

		<?php 
			/** 
			 * woocommerce_pagination hook
			 *
			 * @hooked woocommerce_pagination - 10
			 * @hooked woocommerce_catalog_ordering - 20
			 */		
			do_action( 'woocommerce_pagination' ); 
		?>

	<?php
		/** 
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */	 
		do_action('woocommerce_after_main_content'); 
	?>

	<?php 
		/** 
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */		
		do_action('woocommerce_sidebar'); 
	?>

<?php get_template_part( 'extra-content' ) ?>

<?php get_footer('shop'); ?>