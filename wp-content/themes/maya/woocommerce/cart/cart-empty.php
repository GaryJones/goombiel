<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<p><?php _e( 'Your cart is currently empty.', 'yiw' ) ?></p>

<?php do_action('woocommerce_cart_is_empty'); ?>

<p><a class="button" href="<?php echo apply_filters( 'yiw_empty_cart_redirect_link', get_permalink(woocommerce_get_page_id('shop')) ); ?>"><?php echo apply_filters( 'yiw_return_to_shop_text', __('&larr; Return To Shop', 'yiw' ) ) ?></a></p>