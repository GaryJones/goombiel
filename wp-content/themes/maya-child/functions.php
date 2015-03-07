<?php
/**
 * @package WordPress
 * @subpackage YIW Themes
 * 
 * Here the first hentry of theme, when all theme will be loaded.
 * On new update of theme, you can not replace this file.
 * You will write here all your custom functions, they remain after upgrade.
 */                                                                               




function showCartButton(){
	global $woocommerce;
	echo "var cartCount = ".$woocommerce->cart->cart_contents_count.";";
}
function showMyAccountButton(){
	// hawa banyak maunya
	if ( is_user_logged_in() ) { 
		echo "var LoggedIn = true;";
	 } else {
	 	echo "var LoggedIn = false;";
	 }
}
