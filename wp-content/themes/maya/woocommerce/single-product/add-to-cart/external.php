<?php
/**
 * External Add to Cart
 */
 
global $woocommerce, $product;

$product_url = get_post_meta( $product->id, '_product_url', true );

if (!$product_url) return;
?>

<?php do_action('woocommerce_before_add_to_cart_button'); ?>

<p class="cart"><a href="<?php echo $product_url; ?>" rel="nofollow" class="button alt"><?php echo apply_filters('single_add_to_cart_text', $button_text, 'external'); ?></a></p>

<?php do_action('woocommerce_after_add_to_cart_button'); ?>