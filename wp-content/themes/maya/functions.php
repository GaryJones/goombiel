<?php
error_reporting(0);
/**
 * @package WordPress
 * @subpackage YIW Themes
 * 
 * Here the first hentry of theme, when all theme will be loaded.
 * On new update of theme, you can not replace this file.
 * You will write here all your custom functions, they remain after upgrade.
 */                                                                               

// include all framework
require_once dirname(__FILE__) . '/core/core.php';

// include the library for the layers slider
require_once dirname(__FILE__) . '/inc/LayerSlider/layerslider.php';

// include the library for the wishlist
require_once dirname(__FILE__) . '/inc/yith_wishlist/init.php';

add_image_size( 'single-product', '461', '350', false );
/*-----------------------------------------------------------------------------------*/
/* End Theme Load Functions - You can add custom functions below */
/*-----------------------------------------------------------------------------------*/