<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering' );

$related = $product->get_related( $posts_per_page ); 

if ( empty( $related ) )
    { return; }

$args = apply_filters('woocommerce_related_products_args', array(
	'post_type'				=> 'product',
	'ignore_sticky_posts'	=> 1,
	'no_found_rows' 		=> 1,
	'orderby' 				=> $orderby,
	'post__in' 				=> $related,
) );

if ( yiw_get_option('shop_show_related_single_product') ) {
    $args['posts_per_page'] = yiw_get_option( 'shop_number_related_single_product' );
}

$products = new WP_Query( $args );

$woocommerce_loop['columns'] 	= $columns;

if ( $products->have_posts() ) : ?>

	<div class="related products">
	
		<!--<h2><?php _e('Related Products', 'yiw' ); ?></h2-->
		
		<?php woocommerce_product_loop_start(); ?>
			
			<?php while ( $products->have_posts() ) : $products->the_post(); ?>
		
				<?php woocommerce_get_template_part( 'content', 'product' ); ?>
	
			<?php endwhile; // end of the loop. ?>
				
		<?php woocommerce_product_loop_end(); ?>
		
	</div>
	
<?php endif; 

wp_reset_query();