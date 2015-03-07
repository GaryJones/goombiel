<?php
/**
 * Loop Add to Cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
global $post, $product, $woocommerce_loop;

if ( isset( $woocommerce_loop['style'] ) )
    $style = $woocommerce_loop['style'];
else
    $style = yiw_get_option( 'shop_products_style', 'ribbon' );                

if( $product->get_price() === '' && $product->product_type!=='external' || ! $product->is_purchasable()) return;
?>

<?php if (!$product->is_in_stock()) : ?>  
		
	<div class="buttons"><a href="<?php echo get_permalink($post->ID); ?>" data-product_id="<?php echo $product->id ?>" class="add-to-cart product_type_<?php echo $product->product_type ?>"><?php echo apply_filters('out_of_stock_add_to_cart_text', __('Read More', 'yiw' )); ?></a></div>

<?php 
else :
		
	switch ($product->product_type) :
		case "variable" :
		        $link 	= apply_filters( 'variable_add_to_cart_url', get_permalink( $product->id ) );
			$label 	= apply_filters('variable_add_to_cart_text', __('Select options', 'yiw'));
		break;
		case "grouped" :
			$link 	= apply_filters( 'grouped_add_to_cart_url', get_permalink( $product->id ) );
			$label 	= apply_filters('grouped_add_to_cart_text', __('View options', 'yiw'));
		break;
		case "external" :
			$link 	= apply_filters( 'external_add_to_cart_url', get_permalink( $product->id ) );
			$label 	= apply_filters('external_add_to_cart_text', __('Read More', 'yiw'));
		break;
		default :
			$link 	= apply_filters( 'add_to_cart_url', esc_url( $product->add_to_cart_url() ) );
			$label 	= apply_filters('add_to_cart_text', yiw_get_option( 'shop_button_addtocart_label', __('Add to cart', 'yiw')));
		break;
	endswitch; ?>
	
	<div class="buttons">
        <?php if ( $style == 'traditional' ) : ?><a href="<?php echo get_permalink($product->id); ?>" class="details"><?php echo yiw_get_option( 'shop_button_details_label' ) ?></a><?php endif; ?>
	    <a href="<?php echo $link ?>" rel="nofollow" data-product_id="<?php echo $product->id ?>" class="add-to-cart add_to_cart_button product_type_<?php echo $product->product_type ?>"><?php echo $label ?></a><?php
	?></div><?php

endif; 
?>