<?php
/*
 * Plugin Name: WooCommerce - iPay88 Payment Gateway
 * Description: Allows you to use iPay88 payment gateway with the WooCommerce plugin.
 * Version: 1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
    require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '0d38de1dde5a04dea0109743b5629d8f', '18724' );

if ( ! is_woocommerce_active() ) { return; }

class WC_iPay88 {

	/**
	 * WC Logger object
	 * @var object
	 */
	private static $log;

	/**
	 * Plugin URL
	 * @var type
	 */
	private static $plugin_url;

	/**
	 * Plugin Path
	 * @var string
	 */
	private static $plugin_path;

	public function __construct() {

		// Add required files
		add_action( 'woocommerce_init', array( $this, 'load_gateway_files' ), 15 );

		add_action( 'init', array( $this, 'load_text_domain' ) );

		add_filter('woocommerce_payment_gateways', array( $this, 'add_ipay88_gateway' ) );

		// Add a 'Settings' link to the plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_support_link' ), 10, 4 );

	}

	/**
	 * Localisation
	 **/
	public function load_text_domain() {
		// Use the string for text domain. WPML does not pick it up, if a variable is used
		load_plugin_textdomain( 'wc_ipay88', false, self::plugin_path() . '/languages/' );
	}

	/**
	 * Add 'Settings' link to the plugin actions links
	 *
	 * @since 1.1
	 * @return array associative array of plugin action links
	 */
	public function settings_support_link( $actions, $plugin_file, $plugin_data, $context ) {

		$gateway = $this->get_gateway_class();

		return array_merge(
			array ( 'settings' => '<a href="' . WC_Compat_iPay88::gateway_settings_page( $gateway ) . '">' . __( 'Settings', 'wc_ipay88' ) . '</a>' ),
			$actions
		);
	}

	/**
	 * Get the correct gateway class name to load
	 *
	 * @since 1.1
	 * @return string Class name
	 */
	private function get_gateway_class() {
		return 'WC_Gateway_iPay88';
	}

	/**
	 * Load gateway files
	 *
	 * @since 1.1
	 */
	public function load_gateway_files() {

		include_once( 'includes/class-wc-gateway-ipay88.php' );
		include_once( 'includes/class-wc-compat-ipay88.php' );

	}

	/**
	 * Savely get POST variables
	 *
	 * @since 1.1
	 * @param string $name POST variable name
	 * @return string The variable value
	 */
	public static function get_post( $name ) {
		if ( isset( $_POST[ $name ] ) ) {
		    return $_POST[ $name ];
		}
		return null;
	}

	/**
	 * Savely get GET variables
	 *
	 * @since 1.1
	 * @param string $name GET variable name
	 * @return string The variable value
	 */
	public static function get_get( $name ) {
		if ( isset( $_GET[ $name ] ) ) {
		    return $_GET[ $name ];
		}
		return null;
	}

	/**
	 * Add the gateway to WooCommerce
	 *
	 * @since 1.1
	 * @param array $methods
	 * @return array
	 */
	function add_ipay88_gateway( $methods ) {
		$methods[] = $this->get_gateway_class();

		return $methods;
	}

	/**
	 * Add debug log message
	 *
	 * @since 1.1
	 * @param string $message
	 */
	public static function add_debug_log( $message ) {
		if ( ! is_object( self::$log ) ) {
			self::$log = WC_Compat_iPay88::get_wc_logger();
		}

		self::$log->add( 'ipay88', $message );
	}

	/**
	 * Get the plugin url
	 *
	 * @since 1.1
	 * @return string
	 */
	public static function plugin_url() {
		if ( self::$plugin_url ) {
			return self::$plugin_url;
		}

		return self::$plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path
	 *
	 * @since 1.1
	 * @return string
	 */
	public static function plugin_path() {
		if ( self::$plugin_path ) {
			return self::$plugin_path;
		}

		return self::$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

} new WC_iPay88();