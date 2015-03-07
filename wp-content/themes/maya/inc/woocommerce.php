<?php 
/**
 * All functions and hooks for jigoshop plugin  
 *
 * @package WordPress
 * @subpackage YIW Themes
 * @since 1.4
 */          
 
// global flag to know that woocommerce is active
$yiw_is_woocommerce = true; 
 
include 'shortcodes-woocommerce.php';   

remove_action( 'woocommerce_pagination', 'woocommerce_catalog_ordering', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
function yiw_woocommerce_ordering() {
    if ( ! is_single() && yiw_get_option( 'shop_show_woocommerce_ordering' ) ) woocommerce_catalog_ordering();
    
}
add_action( 'woocommerce_before_main_content' , 'yiw_woocommerce_ordering' );

// Add woocommerce support
add_theme_support('woocommerce');

// add the sale icon inside the product detail image container
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_sale_flash');   

// active the price filter
global $woocommerce;
if(version_compare($woocommerce->version,"2.0.0") < 0 ) add_action('init', 'woocommerce_price_filter_init');
add_filter('loop_shop_post_in', 'woocommerce_price_filter');      
   
// add body class
add_filter( 'body_class', create_function( '$classes', '$classes[] = "shop-".yiw_get_option( "shop_products_style", "ribbon" ); return $classes;' ) ); 

// remove the add to cart option
function yiw_remove_add_to_cart() {
    if ( yiw_get_option('shop_show_button_add_to_cart_single_page') ) return;
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}
add_action('init', 'yiw_remove_add_to_cart');

// since woocommerce 1.6 - add the style to <ul> products list
function yiw_add_style_products_list( $content ) {
    return str_replace( '<ul class="products">', '<ul class="products ' . yiw_get_option( 'shop_products_style', 'ribbon' ) . '">', $content );    
}
add_filter( 'the_content', 'yiw_add_style_products_list', 99 );

//add image size for the product categories in woocommerce api
//add_filter( 'woocommerce_get_image_size_shop_category_image_width',  create_function( '', 'return get_option("woocommerce_category_image_width");' ) );
add_filter( 'woocommerce_get_image_size_shop_category', 'yiw_shop_category_image' );
function yiw_shop_category_image( $size ) {
    return get_option( 'shop_category_image_size', array() );
}

function yiw_set_posts_per_page( $cols ) {        
    $items = yiw_get_option( 'shop_products_per_page', $cols );         
    return $items == 0 ? -1 : $items;
}
add_filter('loop_shop_per_page', 'yiw_set_posts_per_page');

function yiw_add_style_woocommerce() {
    global $pagenow;
    if( $pagenow != 'widgets.php' ) {
        wp_enqueue_style( 'jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    }
}
add_action( 'init', 'yiw_add_style_woocommerce' );

function yiw_add_to_cart_success_ajax( $datas ) {
    global $woocommerce;       
	
	// quantity
	$qty = 0;
	if (sizeof($woocommerce->cart->get_cart())>0) : foreach ($woocommerce->cart->get_cart() as $item_id => $values) :
	
		$qty += $values['quantity'];  
	
	endforeach; endif;                     
	
	if ( $qty == 1 )
	   $label = __( 'item', 'yiw' );
	else             
	   $label = __( 'items', 'yiw' );
	
	ob_start();
	echo '<ul class="cart_list product_list_widget hide_cart_widget_if_empty">';
	if (sizeof($woocommerce->cart->get_cart())>0) : 
		foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) :
			$_product = $cart_item['data'];
			if ($_product->exists() && $cart_item['quantity']>0) :
				echo '<li><a href="'.get_permalink($cart_item['product_id']).'">';
				
				echo $_product->get_image();
				
				echo apply_filters('woocommerce_cart_widget_product_title', $_product->get_title(), $_product).'</a>';
				
   				echo $woocommerce->cart->get_item_data( $cart_item );
				
				echo '<span class="quantity">' .$cart_item['quantity'].' &times; '.apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $_product->price ), $values, $cart_item_key ).'</span></li>';
			endif;
		endforeach; 
	else: 
		echo '<li class="empty">'.__('No products in the cart.', 'yiw' ).'</li>'; 
	endif;
	echo '</ul>';
	if ($qty == 1) :
		echo '<p class="total"><strong>' . __('Subtotal', 'yiw' ) . ':</strong> '. $woocommerce->cart->get_cart_total() . '</p>';
			
		do_action( 'woocommerce_widget_shopping_cart_before_buttons' );
			
		echo '<p class="buttons"><a href="'.$woocommerce->cart->get_cart_url().'" class="button">'.__('View Cart &rarr;', 'yiw' ).'</a> <a href="'.$woocommerce->cart->get_checkout_url().'" class="button checkout">'.__('Checkout &rarr;', 'yiw' ).'</a></p>';
	endif;
    $widget = ob_get_clean();
		
    //$datas['span.minicart'] = '<span class="minicart">' . $qty . ' ' . $label . '</span>';
    $datas['.quick-cart .widget_shopping_cart .cart_list'] = $widget;
    $datas['.widget_shopping_cart .total .amount'] = $woocommerce->cart->get_cart_total();
    $datas['#cart'] = '<div id="cart">' . yiw_minicart(false) . '</div>';
    
    return $datas;
}
add_filter( 'add_to_cart_fragments', 'yiw_add_to_cart_success_ajax' );

function yiw_woocommerce_javascript_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($){   
        $('body').bind('added_to_cart', function(){
            $('.add_to_cart_button.added').text('<?php echo apply_filters( 'yiw_added_to_cart_text', __( 'ADDED', 'yiw' ) ); ?>');
        });               
    });
    </script>
    <?php
}
add_action( 'wp_head', 'yiw_woocommerce_javascript_scripts' );

function yit_woocommerce_compare_link() {
    if(function_exists('woo_add_compare_button')) echo str_replace( 'button ', 'button alt ', woo_add_compare_button() ), '<a class="woo_compare_button_go"></a>';
}
add_action( 'woocommerce_single_product_summary', 'yit_woocommerce_compare_link', 35 );


/** SHOP
-------------------------------------------------------------------- */

// decide the layout for the shop pages
function yiw_shop_layouts( $default_layout ) {
    $is_shop_page = ( get_option('woocommerce_shop_page_id') != false ) ? is_page( get_option('woocommerce_shop_page_id') ) : false;
    if ( is_tax('product_cat') || is_post_type_archive('product') || $is_shop_page )
        return YIW_DEFAULT_LAYOUT_PAGE_SHOP;    
    else
        return $default_layout;
}
add_filter( 'yiw_layout_page', 'yiw_shop_layouts' );

// generate the main width for content and sidebar
function yiw_layout_widths() {
    global $content_width, $post;
    
    $sidebar = YIW_SIDEBAR_WIDTH;
    
    $post_id = isset( $post->ID ) ? $post->ID : 0;
    
    if ( ! is_search() && get_post_type() == 'product' || get_post_meta( $post_id, '_sidebar_choose_page', true ) == 'Shop Sidebar' )
        $sidebar = YIW_SIDEBAR_SHOP_WIDTH;
    
    $content_width = YIW_MAIN_WIDTH - ( $sidebar + 40 );
    
    ?>
        #content { width:<?php echo $content_width ?>px; }
        #sidebar { width:<?php echo $sidebar ?>px; }        
        #sidebar.shop { width:<?php echo YIW_SIDEBAR_SHOP_WIDTH ?>px; }
    <?php
}
//add_action( 'yiw_custom_styles', 'yiw_layout_widths' );

function yiw_minicart( $echo = true ) {
    global $woocommerce;
    
    ob_start();
	
	// quantity
	$qty = 0;
	if (sizeof($woocommerce->cart->get_cart())>0) : foreach ($woocommerce->cart->get_cart() as $item_id => $values) :
	
		$qty += $values['quantity'];
	
	endforeach; endif;
	
	if ( $qty == 1 )
	   $label = __( 'item', 'yiw' );
	else             
	   $label = __( 'items', 'yiw' );  ?>
	   
	<a class="widget_shopping_cart trigger" href="<?php echo $woocommerce->cart->get_cart_url() ?>">
		<span class="minicart"><?php echo $qty ?> <?php echo $label ?> </span>
	</a>
	
	<?php if ( yiw_get_option('topbar_cart_ribbon_hover') ) : ?>
	<div class="quick-cart">
    	<ul class="cart_list product_list_widget"><?php
    	
    	if (sizeof($woocommerce->cart->get_cart())>0) :
            foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) :
                $_product = $cart_item['data'];
				if ($_product->exists() && $cart_item['quantity']>0) : ?>
				    <li>
                        <a href="<?php echo get_permalink($cart_item['product_id']) ?>"><?php echo apply_filters('woocommerce_cart_widget_product_title', $_product->get_title(), $_product) ?></a>
                        <span class="price"><?php echo apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $_product->price ), $values, $cart_item_key ); ?></span>
                    </li><?php
				endif;
            endforeach;
        else : ?>
            <li class="empty"><?php _e('No products in the cart.', 'yiw' ) ?></li><?php
        endif;   
    	
    	if (sizeof($woocommerce->cart->get_cart())>0) : ?>
    	   <li class="totals"><?php _e( 'Subtotal', 'yiw' ) ?><span class="price"><?php echo $woocommerce->cart->get_cart_total(); ?></span></li><?php
    	endif; ?>
    	
    	   <li class="view-cart-button"><a class="view-cart-button" href="<?php echo $woocommerce->cart->get_cart_url(); ?>"><?php echo apply_filters( 'yiw_topbar_minicart_view_cart', __( 'View cart', 'yiw' ) ) ?></a></li>
    	
    	</ul>
    	
    </div><?php
    endif;
    
    $html = ob_get_clean();
    
    if ( $echo )
        echo $html;
    else
        return $html;
}     

// Decide if show the price and/or the button add to cart, on the product detail page
function yiw_remove_ecommerce() {
    if ( ! yiw_get_option( 'shop_show_button_add_to_cart_single_page', 1 ) )                         
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 ); 
    if ( ! yiw_get_option( 'shop_show_price_single_page', 1 ) )                       
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
}
add_action( 'wp_head', 'yiw_remove_ecommerce', 1 );

/**
 * LAYOUT
 */
function yiw_shop_layout_pages_before() {
    $layout = yiw_layout_page();
    if ( get_post_type() == 'product' && is_tax( 'product-category' ) )
        $layout = 'sidebar-no';          
    elseif ( get_post_type() == 'product' && is_single() )          
        $layout = yiw_get_option( 'shop_layout_page_single', 'sidebar-no' ); 
    elseif ( is_shop() || is_product_category() )
        $layout = ( $l=get_post_meta( get_option( 'woocommerce_shop_page_id' ), '_layout_page', true )) ? $l : YIW_DEFAULT_LAYOUT_PAGE;  
    ?><div id="primary" class="layout-<?php echo $layout ?> group">
        <div class="inner group"><?php    
    
    if ( $layout == 'sidebar-no' ) {
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        add_filter('loop_shop_columns', create_function('$columns', 'return $columns+1;'));
    }
} 

function yiw_shop_layout_pages_after() {
    ?></div></div><?php    
}                                                                   
  
add_action( 'woocommerce_before_main_content', 'yiw_shop_layout_pages_before', 1 );
add_action( 'woocommerce_sidebar', 'yiw_shop_layout_pages_after', 99 );
                    
/**
 * SIZES
 */ 

// shop small
function yiw_shop_small_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_catalog'); return $size['width']; }	
function yiw_shop_small_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_catalog'); return $size['height']; }   
// shop thumbnail
function yiw_shop_thumbnail_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_thumbnail'); return $size['width']; }	
function yiw_shop_thumbnail_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_thumbnail'); return $size['height']; } 
// shop large
function yiw_shop_large_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_single'); return $size['width']; }	
function yiw_shop_large_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_single'); return $size['height']; } 
// shop category
function yiw_shop_category_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_category'); return $size['width']; }	
function yiw_shop_category_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_category'); return $size['height']; }	
function yiw_shop_category_crop() { global $woocommerce; $size = $woocommerce->get_image_size('shop_category'); return $size['crop']; } 
	
/**
 * Init images
 */
function yiw_image_sizes() {
    global $woocommerce;
    
	// Image sizes
	$shop_category_crop 	= (get_option('category_image_crop')==1) ? true : false;

    $size = $woocommerce->get_image_size('shop_category');

	$size['width'] 	= isset( $size['width'] ) ? $size['width'] : '225';
	$size['height'] = isset( $size['height'] ) ? $size['height'] : '225';
	$size['crop'] 	= isset( $size['crop'] ) ? $size['crop'] : 1;
    
	add_image_size( 'shop_category', $size['width'], $size['height'], $size['crop'] );
} 
add_action( 'woocommerce_init', 'yiw_image_sizes' );

// print style for small thumb size
function yiw_size_images_style() {
	?>
	.shop-traditional .products li { width:<?php echo yiw_shop_small_w() + ( yiw_get_option( 'shop_border_thumbnail' ) ? 14 : 0 ) ?>px !important; }
	.shop-traditional .products li img { width:<?php echo yiw_shop_small_w() ?>px; }
    .shop-traditional .products li.category img { width:<?php echo yiw_shop_category_w() ?>px; }
	.shop-ribbon .products li { width:<?php echo yiw_shop_small_w() + 5 ?>px !important; }
    .shop-ribbon .products li.category { width: auto !important; }
	.products li a strong { width:<?php echo yiw_shop_small_w() - 30 ?>px !important; }
	/*..shop-traditional .products li a img { width:<?php echo yiw_shop_small_w() ?>px !important; }  removed for the category images */
	div.product div.images { width:<?php echo ( yiw_shop_large_w() + 14 ) / 720 * 100 ?>%; }
	.layout-sidebar-no div.product div.images { width:<?php echo ( yiw_shop_large_w() + 14 ) / 960 * 100 ?>%; }
	div.product div.images img { width:<?php echo yiw_shop_large_w() ?>px; }
	.layout-sidebar-no div.product div.summary { width:<?php echo ( 960 - ( yiw_shop_large_w() + 14 ) - 20 ) / 960 * 100 ?>%; }
	.layout-sidebar-right div.product div.summary, .layout-sidebar-left div.product div.summary { width:<?php echo ( 720 - ( yiw_shop_large_w() + 14 ) - 20 ) / 720 * 100 ?>%; }
	.layout-sidebar-no .product.hentry > span.onsale { right:<?php echo 960 - ( yiw_shop_large_w() + 14 ) - 10 ?>px; left:auto; }
	.layout-sidebar-right .product.hentry > span.onsale, .layout-sidebar-left .product.hentry > span.onsale { right:<?php echo 720 - ( yiw_shop_large_w() + 14 ) - 10 ?>px; left:auto; }     
	<?php
}
add_action( 'yiw_custom_styles', 'yiw_size_images_style' );

/**
 * PRODUCT PAGE
 */     
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60);

if ( !function_exists('woocommerce_output_related_products') ) {
    function woocommerce_output_related_products() {
        echo '<div id="related-products">';
        echo '<h3>', apply_filters( 'yiw_related_products_text', __( 'Related Products', 'yiw' ) ), '</h3>';

        $cols = $prod = yiw_layout_page() == 'sidebar-no' ? 5 : 4;

        if ( yiw_get_option('shop_show_related_single_product') ) {
            $prod = yiw_get_option( 'shop_number_related_single_product' );
            $cols = yiw_get_option( 'shop_columns_related_single_product' );
        }

        woocommerce_related_products( apply_filters('related_products_posts_per_page', $prod), apply_filters('related_products_columns', $cols) );

        echo '</div>';
    }
}
// number of products
function yiw_items_list_pruducts() {
    return 8;
}
//add_filter( 'loop_shop_per_page', 'yiw_items_list_pruducts' );



/** NAV MENU
-------------------------------------------------------------------- */

add_action('admin_init', array('yiwProductsPricesFilter', 'admin_init'));

class yiwProductsPricesFilter {
	// We cannot call #add_meta_box yet as it has not been defined,
    // therefore we will call it in the admin_init hook
	static function admin_init() {
        global $woocommerce;
		if ( ! isset( $woocommerce ) || basename($_SERVER['PHP_SELF']) != 'nav-menus.php' )
			return;
			                                                    
		wp_enqueue_script('nav-menu-query', get_template_directory_uri() . '/inc/admin_scripts/metabox_nav_menu.js', 'nav-menu', false, true);
		add_meta_box('products-by-prices', 'Prices Filter', array(__CLASS__, 'nav_menu_meta_box'), 'nav-menus', 'side', 'low');
	}

	function nav_menu_meta_box() { ?>
	<div class="prices">        
		<input type="hidden" name="woocommerce_currency" id="woocommerce_currency" value="<?php echo get_woocommerce_currency_symbol( get_option('woocommerce_currency') ) ?>" />
		<input type="hidden" name="woocommerce_shop_url" id="woocommerce_shop_url" value="<?php echo get_option('permalink_structure') == '' ? site_url() . '/?post_type=product' : get_permalink( get_option('woocommerce_shop_page_id') ) ?>" />
		<input type="hidden" name="menu-item[-1][menu-item-url]" value="" />
		<input type="hidden" name="menu-item[-1][menu-item-title]" value="" />
		<input type="hidden" name="menu-item[-1][menu-item-type]" value="custom" />
		
		<p>
		    <?php _e( sprintf( 'The values are already expressed in %s', get_woocommerce_currency_symbol( get_option('woocommerce_currency') ) ), 'yiw' ) ?>
		</p>
		
		<p>
			<label class="howto" for="prices_filter_from">
				<span><?php _e('From'); ?></span>
				<input id="prices_filter_from" name="prices_filter_from" type="text" class="regular-text menu-item-textbox input-with-default-title" title="<?php esc_attr_e('From'); ?>" />
			</label>
		</p>

		<p style="display: block; margin: 1em 0; clear: both;">
			<label class="howto" for="prices_filter_to">
				<span><?php _e('To'); ?></span>
				<input id="prices_filter_to" name="prices_filter_to" type="text" class="regular-text menu-item-textbox input-with-default-title" title="<?php esc_attr_e('To'); ?>" />
			</label>
		</p>

		<p class="button-controls">
			<span class="add-to-menu">
				<img class="waiting" style="display: none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				<input type="submit" class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" />
			</span>
		</p>

	</div>
<?php
	}
}     

/**
 * Add 'On Sale Filter to Product list in Admin
 */
add_filter( 'parse_query', 'on_sale_filter' );
function on_sale_filter( $query ) {
    global $pagenow, $typenow, $wp_query;

    if ( $typenow=='product' && isset($_GET['onsale_check']) && $_GET['onsale_check'] ) :

        if ( $_GET['onsale_check'] == 'yes' ) :
            $query->query_vars['meta_compare']  =  '>';
            $query->query_vars['meta_value']    =  0;
            $query->query_vars['meta_key']      =  '_sale_price';
        endif;

        if ( $_GET['onsale_check'] == 'no' ) :
            $query->query_vars['meta_value']    = '';
            $query->query_vars['meta_key']      =  '_sale_price';
        endif;

    endif;
}

add_action('restrict_manage_posts','woocommerce_products_by_on_sale');
function woocommerce_products_by_on_sale() {
    global $typenow, $wp_query;
    if ( $typenow=='product' ) :

        $onsale_check_yes = '';
        $onsale_check_no  = '';

        if ( isset( $_GET['onsale_check'] ) && $_GET['onsale_check'] == 'yes' ) :
            $onsale_check_yes = ' selected="selected"';
        endif;

        if ( isset( $_GET['onsale_check'] ) && $_GET['onsale_check'] == 'no' ) :
            $onsale_check_no = ' selected="selected"';
        endif;

        $output  = "<select name='onsale_check' id='dropdown_onsale_check'>";
        $output .= '<option value="">'.__('Show all products (Sale Filter)', 'woothemes').'</option>';
        $output .= '<option value="yes"'.$onsale_check_yes.'>'.__('Show products on sale', 'woothemes').'</option>';
        $output .= '<option value="no"'.$onsale_check_no.'>'.__('Show products not on sale', 'woothemes').'</option>';
        $output .= '</select>';

        echo $output;

    endif;
}


// ADD IMAGE CATEGORY OPTION
function yiw_add_cateogry_image_size( $options ) {
    $tmp = array_pop( $options );
    
    $options[] = array(  
		'name' => __( 'Category Thumbnails', 'woocommerce' ),
		'desc' 		=> __('This size is usually used for the category list on the product page.', 'yiw' ),
		'id' 		=> 'shop_category_image_size',
		'css' 		=> '',
		'type' 		=> 'image_width',
		'default'	=> array(
            'width'  => 225,
            'height' => 225,
            'crop'   => true
        ),
        'std'       => '225',
		'desc_tip'	=>  true,
	);      
	
	$options[] = $tmp;

    return $options;   
}
add_filter( 'woocommerce_catalog_settings', 'yiw_add_cateogry_image_size' );

function woocommerce_subcategory_thumbnail( $category  ) {
	global $woocommerce;

	$small_thumbnail_size  = apply_filters( 'single_product_small_thumbnail_size', 'shop_category' );
	$image_width     = yiw_shop_category_w();
	$image_height    = yiw_shop_category_h();

	$thumbnail_id  = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );

	if ( $thumbnail_id ) {
		$image = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size  );
		$image = $image[0];
	} else {
		$image = woocommerce_placeholder_img_src();
	}

	echo '<img src="' . $image . '" alt="' . $category->name . '" width="' . $image_width . '" height="' . $image_height . '" />';
}

if( !function_exists( 'yiw_out_of_stock_flash' ) ) :
function yiw_out_of_stock_flash() {
    woocommerce_get_template( 'loop/out-of-stock-flash.php' );
}
endif;
add_action( 'woocommerce_before_shop_loop_item_title', 'yiw_out_of_stock_flash', 10 );

if( !function_exists( 'yiw_out_of_stock_flash_single_product' ) ) :
function yiw_out_of_stock_flash_single_product() {
    woocommerce_get_template( 'single-product/out-of-stock-flash.php' );
}
endif;
add_action( 'woocommerce_before_single_product_summary', 'yiw_out_of_stock_flash_single_product', 10 );

function yiw_star_rating() {

   global $woocommerce,$post, $wpdb;
   echo do_shortcode( '[rating id="' . $post->ID . '"]' );
}        

/**
 * Update woocommerce options after update from 1.6 to 2.0
 */
function yiw_woocommerce_update() {
	global $woocommerce; 
	
	$field = 'yiw_woocommerce_update_' . get_template();
	
	if( get_option($field) == false && version_compare($woocommerce->version,"2.0.0",'>=') ) {
		update_option($field, time());

		//woocommerce 2.0
		update_option( 
			'shop_thumbnail_image_size', 
			array( 
				'width' => get_option('woocommerce_thumbnail_image_width', 90 ),
				'height' => get_option('woocommerce_thumbnail_image_height', 90 ),
				'crop' => get_option('woocommerce_thumbnail_image_crop', 1)
			)
		);
		
		update_option( 
			'shop_single_image_size', 
			array( 
				'width' => get_option('woocommerce_single_image_width', 500 ),
				'height' => get_option('woocommerce_single_image_height', 380 ),
				'crop' => get_option('woocommerce_single_image_crop', 1)
			) 
		); 
		
		update_option( 
			'shop_catalog_image_size', 
			array( 
				'width' => get_option('woocommerce_catalog_image_width', 150 ),
				'height' => get_option('woocommerce_catalog_image_height', 150 ),
				'crop' => get_option('woocommerce_catalog_image_crop', 1)
			) 
		);
		
		update_option( 
			'shop_category_image_size',
			array( 
				'width' => get_option('shop_category_image_image_width', 225 ),
				'height' => get_option('shop_category_image_image_height', 155 ),
				'crop' => get_option('shop_category_image_image_crop', 1)
			) 
		);
	}
}          
add_action( 'admin_init', 'yiw_woocommerce_update' ); //update image names after woocommerce update

// Restore position of country field in the checkout
function woocommerce_restore_billing_fields_order( $fields ) {
    //if ( ! yiw_get_option('shop-fields-order') ) return;
    
    $fields['billing_city']['class'][0] = 'form-row-first';
	$fields['billing_state']['class'][0] = 'form-row-last';
    $fields['billing_country']['class'][0] = '';
    
    $country = $fields['billing_country'];
    unset( $fields['billing_country'] );
    yiw_array_splice_assoc( $fields, array('billing_country' => $country), 'billing_postcode' );
    
    return $fields;
}               
add_filter( 'woocommerce_billing_fields' , 'woocommerce_restore_billing_fields_order' );

function woocommerce_restore_shipping_fields_order( $fields ) {
    //if ( ! yiw_get_option('shop-fields-order') ) return;
    
    $fields['shipping_city']['class'][0] = 'form-row-first';
	$fields['shipping_state']['class'][0] = 'form-row-last';
    $fields['shipping_country']['class'][0] = '';
    
    $country = $fields['shipping_country'];
    unset( $fields['shipping_country'] );
    yiw_array_splice_assoc( $fields, array('shipping_country' => $country), 'shipping_postcode' );
    
    return $fields;
}      
add_filter( 'woocommerce_shipping_fields' , 'woocommerce_restore_shipping_fields_order' );


add_action( 'admin_init', 'yiw_woocommerce_update' ); //update image names after woocommerce update

add_filter( 'yiw_sample_data_tables',  'yit_save_woocommerce_tables' );
add_filter( 'yiw_sample_data_options', 'yit_save_woocommerce_options' );
add_filter( 'yiw_sample_data_options', 'yit_save_wishlist_options' );
add_filter( 'yiw_sample_data_options', 'yit_add_plugins_options' );

/**
 * Backup woocoomerce options when create the export gz
 *
 */
function yit_save_woocommerce_tables( $tables ) {
    $tables[] = 'woocommerce_termmeta';
    $tables[] = 'woocommerce_attribute_taxonomies';
    return $tables;
}

/**
 * Backup woocoomerce options when create the export gz
 *
 */
function yit_save_woocommerce_options( $options ) {
    $options[] = 'woocommerce\_%';
    $options[] = '_wc_needs_pages';
    return $options;
}

/**
 * Backup woocoomerce wishlist when create the export gz
 *
 */
function yit_save_wishlist_options( $options ) {
    $options[] = 'yith\_wcwl\_%';
    $options[] = 'yith-wcwl-%';
    return $options;
}
/**
 * Backup options of plugins when create the export gz
 *
 */
function yit_add_plugins_options( $options ) {
    $options[] = 'yith_woocompare_%';
    $options[] = 'yith_wcmg_%';

    return $options;
}

/* compare */
global $yith_woocompare;
if ( isset($yith_woocompare) ) {
    remove_action( 'woocommerce_after_shop_loop_item', array( $yith_woocompare->obj, 'add_compare_link' ), 20 );
    if ( get_option( 'yith_woocompare_compare_button_in_products_list' ) == 'yes' ) add_action( 'woocommerce_after_shop_loop_item_title', array( $yith_woocompare->obj, 'add_compare_link' ), 20 );
}