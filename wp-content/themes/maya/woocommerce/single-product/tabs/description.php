<?php
/**
 * Description tab
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $post;

if ( $post->post_content ) : ?>	
	
		<?php $heading = esc_html( apply_filters('woocommerce_product_description_heading', __('Product Description', 'yiw' ))); ?>
		
		<h2><?php echo $heading; ?></h2>
		
		<?php the_content(); ?>
        
        <?php
        if( yiw_get_option( 'shop_show_share_socials' ) ) :
            echo do_shortcode( '[share title="' . yiw_get_option( 'shop_share_title' ) . '" socials="' . yiw_get_option( 'shop_share_socials' ) . '"]' );
        endif;
        ?>	
	
<?php endif; ?>