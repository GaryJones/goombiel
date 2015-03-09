<?php
/*
 * Plugin Name: WooCommerce iPay88 Payment Gateway
 * Description: Allows you to use <a href="http://www.ipay88.com">iPay88</a> payment gateway with the WooCommerce plugin.
 * Version: 1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

class WC_Gateway_iPay88 extends WC_Payment_Gateway {

	public function __construct() {

		$this->id		= 'ipay88';
		$this->icon		= apply_filters('woocommerce_ipay88_icon', WC_iPay88::plugin_url() .'/assets/images/ipay88.png' );
		$this->has_fields 	= false;
		$this->method_title	= __( 'iPay88', 'wc_ipay88' );
		$this->paymenttype_options = array(
			'2'	=> __('Credit Card', 'wc_ipay88'),
			'6'	=> __('Maybank2U', 'wc_ipay88'),
			'8'	=> __('Alliance Online', 'wc_ipay88'),
			'10'	=> __('AmBank', 'wc_ipay88'),
			'14'	=> __('RHB', 'wc_ipay88'),
			'15'	=> __('Hong Leong Online', 'wc_ipay88'),
			'16'	=> __('FPX', 'wc_ipay88'),
			'17'	=> __('Mobile Money', 'wc_ipay88'),
			'20'	=> __('CIMB Click', 'wc_ipay88'),
			'22'	=> __('Web Cash', 'wc_ipay88'),
			'23'	=> __('MEPS Cash', 'wc_ipay88'),
			'33'	=> __('PayPal', 'wc_ipay88'),
			'103'	=> __('AffinBank', 'wc_ipay88'),
		);
		$this->types_mapping = array(
			'image' => array(
				'2'		=> 'credit-card',
				'6'		=> 'maybank2u',
				'8'		=> 'allianceonline',
				'10'	=> 'ambank',
				'14'	=> 'rhb	',
				'15'	=> 'hongleong',
				'16'	=> 'fpx',
				'17'	=> 'mobilemoney',
				'20'	=> 'cimb',
				'22'	=> 'webcash',
				'23'	=> 'mepscash',
				'33'	=> 'paypal',
				'103'	=> 'affinbank',
			),
			'id' => array(
				'2'		=> '_credit_card',
				'6'		=> '_maybank2u',
				'8'		=> '_alliance_online',
				'10'	=> '_ambank',
				'14'	=> '_rhb',
				'15'	=> '_hong_leong_online',
				'16'	=> '_fpx',
				'17'	=> '_mobile_money',
				'20'	=> '_cimb_click',
				'22'	=> '_web_cash',
				'23'	=> '_meps_cash',
				'33'	=> '_paypal',
				'103'	=> '_affinbank',
			)
		);

		$this->paymenttype_options_ph = array(
			'1'		=> __('Credit Card', 'wc_ipay88'),
			'11'	=> __('CIMB Clicks', 'wc_ipay88'),
			'4'		=> __('Mandiri Clickpay', 'wc_ipay88'),
			'7'		=> __('XL Tunai', 'wc_ipay88'),
		);
		$this->types_mapping_ph = array(
			'image' => array(
				'1'		=> 'credit-card',
				'11'	=> 'cimb',
				'4'		=> 'mandiriclickpay',
				'7'		=> 'xltunai',
			),
			'id' => array(
				'1'		=> '_credit_card',
				'11'	=> '_cimb',
				'4'		=> '_mandiriclickpay',
				'7'		=> '_xltunai',

			)
		);

		$this->image_ext = 'png';
		$this->hash_amount = 0;
		$this->formatted_amount = 0;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->title		= $this->settings['title'];
		$this->description	= $this->settings['description'];
		$this->enabled		= isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
		$this->MerchantCode	= $this->settings['MerchantCode'];
		$this->MerchantKey	= $this->settings['MerchantKey'];
		$this->debug		= isset( $this->settings['debug'] ) ? $this->settings['debug'] : 'no';
		$this->use_css		= isset( $this->settings['use_css'] ) ? $this->settings['use_css'] : 'no';
		$this->paymenttype_available	= isset( $this->settings['paymenttype_available'] ) ? $this->settings['paymenttype_available'] : array();
		$this->paymenttype_available_ph	= isset( $this->settings['paymenttype_available_ph'] ) ? $this->settings['paymenttype_available_ph'] : array();
		$this->gateway		= isset( $this->settings['gateway'] ) ? $this->settings['gateway'] : 'MY';
		$this->sandbox		= isset( $this->settings['sandbox'] ) ? $this->settings['sandbox'] : 'yes';

		if ( 'ID' == $this->gateway ) {
			if ( 'yes' == $this->sandbox ) {
				$this->url = 'https://sandbox.ipay88.co.id/epayment/entry.asp';
			} else {
				$this->url = 'https://payment.ipay88.co.id/epayment/entry.asp';
			}
		} else {
			$this->url = 'https://www.mobile88.com/epayment/entry.asp';
		}

		// Actions
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_status_response_ipay88' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Save options
		add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( ! $this->is_valid() ) {
			$this->enabled = 'no';
		}

		if ( 'MY' == $this->gateway && 'yes' == $this->use_css && ! empty ( $this->paymenttype_available ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_ipay88_checkout_styles' ) );
		}

	}

	/**
	 * Add the checkout page css to for the grouped payment options
	 */
	function add_ipay88_checkout_styles() {
		if ( is_checkout() ) {
			wp_register_style( 'ipay88-checkout-css', WC_iPay88::plugin_url() .'/assets/css/ipay88.css' );
			wp_enqueue_style( 'ipay88-checkout-css' );
		}
	}

	/**
	 * Check if the currency allows for gateway use
	 *
	 * @return boolean
	 **/
	function is_valid() {

		if ( 'ID' == $this->gateway ) {
			$allowed_currency = array( 'IDR', 'USD' );
		} else {
			$allowed_currency = array( 'MYR', 'USD', 'CNY', 'AUD', 'THB', 'CAD', 'EUR', 'GBP', 'SGD' );
		}

		if ( ! in_array( get_woocommerce_currency(), $allowed_currency ) ){
			return false;
		}

		return true;
	}

	/**
	 * Admin Panel Options
	 **/
	public function admin_options() {
		?>
		<h3><?php _e( 'iPay88', 'wc_ipay88' ); ?></h3>
		<p><?php _e( 'iPay88 is a payment gateway works by redirecting the customer to iPay88 server to make a payment and then returns the customer back to your "Thank you/Receipt" page.', 'wc_ipay88' ); ?></p>

		<table class="form-table">
			<?php
			if ( $this->is_valid() ) {

				// Generate the HTML For the settings form.
				$this->generate_settings_html();

			} else {

				if ( 'ID' == $this->gateway ) {
					$supported_currency = __( ' Supported currency is Indonesia Rupiah ( IDR )', 'wc_ipay88' );
				} else {
					$supported_currency = __( ' Supported currency is MYR, USD, CNY, AUD, THB, CAD, EUR, GBP, SGD', 'wc_ipay88' );
				}

			?>
				<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'wc_ipay88' ); ?></strong>: <?php _e( 'iPay88 does not support your store currency.', 'wc_ipay88' ); echo $supported_currency; ?></p></div>
			<?php
			}
			?>
		</table><!--/.form-table-->
		<?php
		ob_start();
		?>
		$('#woocommerce_ipay88_gateway').change(
			function() {
				var paymentTypesMY	= $(this).closest('tr').next().next().next().next().next();
				var paymentTypesID	= $(this).closest('tr').next().next().next().next().next().next();
				var groupTypes		= $(this).closest('tr').next().next().next().next().next().next().next();
				var sandbox		= $(this).closest('tr').next().next().next().next().next().next().next().next();

				if ( 'ID' == $(this).val() ) {
					paymentTypesMY.hide();
					paymentTypesID.show();
					groupTypes.hide();
					sandbox.show();
				} else {
					paymentTypesMY.show();
					paymentTypesID.hide();
					groupTypes.show();
					sandbox.hide();
				}
			}
		).change();

		<?php
		$javascript = ob_get_clean();
		WC_Compat_iPay88::wc_include_js( $javascript );

	} // End admin_options()

	/**
	 * Initialise Gateway Settings Form Fields
	 **/
	function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'type'		=> 'checkbox',
				'label'		=> __( 'Enable iPay88', 'wc_ipay88' ),
				'default'	=> 'no'
			),
			'gateway' => array(
				'title'		=> __( 'iPay88 Gateway In Use', 'wc_ipay88' ),
				'type'		=> 'select',
				'description'	=> __( 'Currently supported is Indonesia.', 'wc_ipay88' ),
				'css'		=> 'min-width:350px;',
				'class'		=> 'chosen_select',
				'default'	=> 'ID',
				'options'	=> array(
					'ID'	=> __('Indonesia', 'wc_ipay88')
				),
			),
			'title' => array(
				'title'		=> __( 'Method Title', 'wc_ipay88' ),
				'type'		=> 'text',
				'description'	=> __( 'This controls the title which the user sees during checkout.', 'wc_ipay88' ),
				'default'	=> __( 'iPay88', 'wc_ipay88' )
			),
			'description' => array(
				'title'		=> __( 'Description', 'wc_ipay88' ),
				'type'		=> 'textarea',
				'description'	=> __( 'This controls the description which the user sees during checkout.', 'wc_ipay88' ),
				'default'	=> __("Make a payment using your Credit/Debit card.", 'wc_ipay88')
			),
			'MerchantCode' => array(
				'title'		=> __( 'Merchant Code', 'wc_ipay88' ),
				'type'		=> 'text',
				'description'	=> __( "The Merchant Code provided by iPay88 and used to uniquely identify the Merchant.", 'wc_ipay88' ),
				'default'	=> ''
			),
			'MerchantKey' => array(
				'title'		=> __( 'Merchant Key', 'wc_ipay88' ),
				'type'		=> 'password',
				'description'	=> __( 'Provided by iPay88 and shared between iPay88 and merchant only.', 'wc_ipay88' ),
				'default'	=> ''
			),
			'paymenttype_available' => array(
				'title'		=> __( 'Available Payment Types', 'wc_ipay88' ),
				'type'		=> 'multiselect',
				'description'	=> __( 'Choose the payment types you can offer to the customers. The Payment types will be presented to the customer to pre-select on the Checkout page. OR do not choose any type to use the default selection on the iPay88 payment page.', 'wc_ipay88' ),
				'options'	=> $this->paymenttype_options,
				'css'		=> 'min-width:350px;',
				'class'		=> 'chosen_select',
				'default'	=> ''
			),
			'paymenttype_available_ph' => array(
				'title'		=> __( 'Available Payment Types', 'wc_ipay88' ),
				'type'		=> 'multiselect',
				'description'	=> __( 'Choose the payment types you can offer to the customers. The Payment types will be presented to the customer to pre-select on the Checkout page. Do not choose any type to use the default selection on the iPay88 payment page.', 'wc_ipay88' ),
				'options'	=> $this->paymenttype_options_ph,
				'css'		=> 'min-width:350px;',
				'class'		=> 'chosen_select',
				'default'	=> ''
			),
			'use_css' => array(
				'type'		=> 'checkbox',
				'label'		=> __( 'Group Payment Types', 'wc_ipay88' ),
				'description'	=> __( 'Check if you want to use the css packed with the plugin. It will group the payment types in three columns.', 'wc_ipay88' ),
				'default'	=> 'no'
			),
			'sandbox' => array(
				'label'		=> __( 'Enable Sandbox', 'wc_ipay88' ),
				'type'		=> 'checkbox',
				'description'		=> __( 'Sandbox mode provides you with a chance to test your gateway integration with iPay88. The payment requests will be send to the iPay88 sandbox URL.<br/>Disable to start accepting Live payments.', 'wc_ipay88' ),
				'default'	=> 'yes'
			),
			'debug' => array(
				'type'		=> 'checkbox',
				'label'		=> __( 'Debug Log. Recommended: Test Mode only', 'wc_ipay88' ),
				'default'	=> 'no',
				'description'	=> __( 'Debug log will provide you with most of the data and events generated by the payment process. Logged inside <code>woocommerce/logs/ipay88-'. sanitize_file_name( wp_hash( 'ipay88' ) ) .'.txt</code>.' ),
			)
		);

	} // End init_form_fields()

	/**
	 * Show Description in place of the payment fields
	 **/
	function payment_fields() {

		if ( $this->description ) echo wpautop( wptexturize( $this->description ) );

		ob_start();

		if ( 'ID' == $this->gateway ) {
			$this->get_ph_gateway_payment_types_html();
		} else {
			$this->get_my_gateway_payment_types_html();
		}

		$html = ob_get_clean();

		echo $html;
	}

	/**
	 * Will generate the Payment Types html for iPay88 Indonesia.
	 */
	function get_ph_gateway_payment_types_html() {
		if ( ! empty( $this->paymenttype_available_ph ) ) {
			?>
			<p class="form-row">
				<label for="ipay88_payment_type"><?php _e('Payment Type', 'wc_ipay88'); ?> <span class="required">*</span></label>
			</p>
			<?php

				echo '<div class="ipay88_ph_gateway ipay88_opt_container" >';
				foreach ( $this->paymenttype_available_ph as $number ) {
					echo '<p style="margin-bottom:5px;">';
					echo '<input type="radio" id="ipay88'.$this->types_mapping_ph['id'][ $number ].'"';
					echo 'name="ipay88_payment_type" value="'.$number.'">';
					echo '<label for="ipay88'.$this->types_mapping_ph['id'][ $number ].'">';
						echo '<img alt="'.$this->paymenttype_options_ph[ $number ].'" src="'. WC_Compat_iPay88::force_https( WC_iPay88::plugin_url() ) .'/assets/images/'.$this->types_mapping_ph['image'][ $number ].'.'.$this->image_ext .'">';
					echo '</label>';
					echo '</p>';
				}
				echo '</div>';

		}
	}

	/**
	 * Will generate the Payment Types html for iPay88 Malaysia.
	 */
	function get_my_gateway_payment_types_html() {
		if ( ! empty( $this->paymenttype_available ) ) {
			?>
			<p class="form-row">
				<label for="ipay88_payment_type"><?php _e('Payment Type', 'wc_ipay88'); ?> <span class="required">*</span></label>
			</p>
			<?php

			$credit_options = array('2');
			if ( (bool) array_intersect( $credit_options, $this->paymenttype_available ) ) {
				echo '<div class="ipay88_credit_card ipay88_opt_container" >';
				if ( 'yes' == $this->use_css )
					echo '<p class="ipay88_title_opt">'. __( 'Credit/Debit Card', 'wc_ipay88' ) .'</p>';

				foreach ( $credit_options as $number ) {
					if ( in_array( $number, $this->paymenttype_available ) ) {
						echo '<p style="margin-bottom:5px;">';
						echo '<input type="radio" id="ipay88'.$this->types_mapping['id'][ $number ].'"';
						echo 'name="ipay88_payment_type" value="'.$number.'">';
						echo '<label for="ipay88'.$this->types_mapping['id'][ $number ].'">';
							echo '<img alt="'.$this->paymenttype_options[ $number ].'" src="'. WC_Compat_iPay88::force_https( WC_iPay88::plugin_url() ) .'/assets/images/'.$this->types_mapping['image'][ $number ].'.'.$this->image_ext .'">';
						echo '</label>';
						echo '</p>';
					}
				}
				echo '</div>';
			}

			$bank_transfer_options = array('6','8','10','14','15','16','20','103');
			if ( (bool) array_intersect( $bank_transfer_options, $this->paymenttype_available ) ) {
				echo '<div class="ipay88_online_bank_transfer ipay88_opt_container" >';
				if ( 'yes' == $this->use_css )
					echo '<p class="ipay88_title_opt">'. __( 'Online Bank Transfer', 'wc_ipay88' ) .'</p>';

				foreach ( $bank_transfer_options as $number ) {
					if ( in_array( $number, $this->paymenttype_available ) ) {
						echo '<p style="margin-bottom:5px;">';
						echo '<input type="radio" id="ipay88'.$this->types_mapping['id'][ $number ].'"';
						echo 'name="ipay88_payment_type" value="'.$number.'">';
						echo '<label for="ipay88'.$this->types_mapping['id'][ $number ].'">';
							echo '<img alt="'.$this->paymenttype_options[ $number ].'" src="'. WC_Compat_iPay88::force_https( WC_iPay88::plugin_url() ) .'/assets/images/'.$this->types_mapping['image'][ $number ].'.'.$this->image_ext .'">';
						echo '</label>';
						echo '</p>';
					}
				}
				echo '</div>';
			}

			$other_options = array('17','22','23','33');
			if ( (bool) array_intersect( $other_options, $this->paymenttype_available ) ) {
				echo '<div class="ipay88_other_options ipay88_opt_container" >';
				if ( 'yes' == $this->use_css )
					echo '<p class="ipay88_title_opt">'. __( 'Other Options', 'wc_ipay88' ) .'</p>';

				foreach ( $other_options as $number ) {
					if ( in_array( $number, $this->paymenttype_available ) ) {
						echo '<p style="margin-bottom:5px;">';
						echo '<input type="radio" id="ipay88'.$this->types_mapping['id'][ $number ].'"';
						echo 'name="ipay88_payment_type" value="'.$number.'">';
						echo '<label for="ipay88'.$this->types_mapping['id'][ $number ].'">';
							echo '<img alt="'.$this->paymenttype_options[ $number ].'" src="'. WC_Compat_iPay88::force_https( WC_iPay88::plugin_url()  ) .'/assets/images/'.$this->types_mapping['image'][ $number ].'.'.$this->image_ext .'">';
						echo '</label>';
						echo '</p>';
					}
				}

				echo '</div>';
			}
		}
	}

	/**
	 * Validate payment fields
	 **/
	function validate_fields() {
		$this->posted_payment_type = null != WC_iPay88::get_post( 'ipay88_payment_type' ) ? WC_iPay88::get_post( 'ipay88_payment_type' ) : '0' ;
		$this->check_payment_fields( $this->posted_payment_type );

		//Note the credit card fields check was passed
		$this->check_pass = true;

		if( ! WC_Compat_iPay88::wc_notice_count( 'error' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Generate iPay88 form
	 **/
	function generate_ipay88_form( $order_id ) {

		$order = new WC_Order( $order_id );

		//Debug log
		if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Generating payment form for order #' . $order_id );

		$desc = '';
		if ( 0 < sizeof( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item ) {
				if ( $item['qty'] ) {
					$item_name = $item['name'];

					$item_meta = WC_Compat_iPay88::get_order_item_meta( $item );

					if ($meta = $item_meta->display( true, true )) {
							$item_name .= ' ('.$meta.')';
					}

					$desc .= $item['qty'] .' x '. $item_name . ', ';
				}
			}
			//Add the description
			$desc = substr($desc, 0, -2 );
		}

		$currency = get_woocommerce_currency();

		// Format the order total
		$this->format_amount( $order->get_total() );

		$ipay88_args = array(
			'MerchantCode'	=> $this->MerchantCode,
			'RefNo'		=> str_replace( '#', '', $order->get_order_number() ),
			'Amount'	=> $this->formatted_amount,
			'Currency'	=> $currency,
			'ProdDesc'	=> $desc,
			'UserName'      => $order->billing_first_name .' '. $order->billing_last_name,
			'UserEmail'	=> $order->billing_email,
			'UserContact'	=> $order->billing_phone,
			'ResponseURL'	=> WC_Compat_iPay88::force_https( add_query_arg('wc-api', 'WC_Gateway_iPay88', home_url( '/' ) ) ),
			'BackendURL'	=> WC_Compat_iPay88::force_https( add_query_arg('iPay88_response', 'backend', add_query_arg('wc-api', 'WC_Gateway_iPay88', home_url( '/' ) )) ),
		);

		$payment_type = WC_iPay88::get_get( 'ptype' );
		if ( null != $payment_type && 0 != $payment_type ) {
			$ipay88_args['PaymentId'] = $payment_type;
		}

		//Add signature
		$ipay88_args['signature'] = $this->generate_sha1_signature( $ipay88_args, false );

		//Debug log
		if ( 'yes' == $this->debug ) {
			WC_iPay88::add_debug_log( 'Order form parameters: ' . print_r( $ipay88_args, true ) );
		}

		$ipay88_form_array = array();

		foreach ( $ipay88_args as $key => $value ) {
			$ipay88_form_array[] = '<input type="hidden" name="'. esc_attr( $key ) .'" value="'. esc_attr( $value ) .'" />';
		}

		WC_Compat_iPay88::wc_include_js('
			jQuery("body").block({
				message: "<img src=\"' . esc_url( WC_Compat_iPay88::force_https( WC_Compat_iPay88::get_wc_global()->plugin_url() ) ) . '/assets/images/ajax-loader.gif\" alt=\"Redirecting...\"'
			. ' style=\"float:left; margin-right: 10px;\" />'. __( 'Thank you for your order. We are now redirecting you to iPay88 to make payment.', 'wc_ipay88' ) .'",
				overlayCSS: {
					background: "#fff",
					opacity: 0.6
				},
				css: {
					padding:        20,
					textAlign:      "center",
					color:          "#555",
					border:         "3px solid #aaa",
					backgroundColor:"#fff",
					cursor:         "wait",
					lineHeight:		"32px",
					zIndex:         "9999999"
				}
			});
			jQuery("#submit_ipay88_payment_form").click();
		');

		return '<form action="'. esc_url( $this->url ) .'" method="post" id="ipay88_payment_form" target="_top">
			' . implode('', $ipay88_form_array) . '
			<input type="submit" class="button-alt" id="submit_ipay88_payment_form" value="'. __( 'Pay via iPay88', 'wc_ipay88' ) .'" />'
			. '<a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'. __( 'Cancel order &amp; restore cart', 'wc_ipay88' ) .'</a>
			</form>';

	}

	/**
	 * Process the payment
	 **/
	function process_payment( $order_id ) {
		if ( ! $this->check_pass ) {
			$this->posted_payment_type = null != WC_iPay88::get_post( 'ipay88_payment_type' ) ? WC_iPay88::get_post( 'ipay88_payment_type' ) : '0' ;
			$this->check_payment_fields( $this->posted_payment_type );
		}

		if( ! WC_Compat_iPay88::wc_notice_count( 'error' ) ) {
			$order = new WC_Order( $order_id );
			 return array(
				'result' => 'success',
				'redirect' =>	add_query_arg( 'ptype', $this->posted_payment_type, $order->get_checkout_payment_url( true ) )
			 );
		}
	}

	/**
	 * receipt_page
	 **/
	function receipt_page( $order ) {

		echo '<p>'. __( 'Thank you for your order, please click the button below to pay with iPay88.', 'wc_ipay88' ) .'</p>';

		echo $this->generate_ipay88_form( $order );

	}

	/**
	 * Check and validate the received response
	 **/
	function validate_response() {

		//Debug log
		if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Validating response...' );

		$signature = $this->generate_sha1_signature( $_POST );

		//Debug log
		if ( 'yes' == $this->debug ) {
			WC_iPay88::add_debug_log( 'Generated response signature is: '. $signature );
			WC_iPay88::add_debug_log( 'Received post signature is: '. WC_iPay88::get_post( 'Signature' ) );
		}

		if ( WC_iPay88::get_post( 'Signature' ) == $signature ) {

			$order_id = $this->get_order_id( WC_iPay88::get_post('RefNo') );

			$order = new WC_Order( (int) $order_id );

			//Debug log
			if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Signature validation passed.' );

			//if ( number_format( $order->get_total(), 2, '', '' ) == $this->hash_amount ) {
			if ( number_format( $order->get_total(), 2, '', '' ) == WC_iPay88::get_post( 'Amount' ) ) {
				//Debug log
				if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Amount validation passed.' );

				return true;
			}

			//Debug log
			if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Amount validation failed.' );

			return false;

		} else {

			//Debug log
			if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Signature validation failed.' );

			return false;
		}

	}

	/**
	 * Check for iPay88 Payment Response.
	 * Process Payment based on the Response.
	 **/
	function check_status_response_ipay88() {

		$posted = stripslashes_deep( $_POST );

		$is_backend_notification = ( WC_iPay88::get_get( 'iPay88_response' ) == 'backend' );

		$received_ok = 'ID' == $this->gateway ? 'RECEIVEOK' : 'OK';

		//Debug log
		if ( 'yes' == $this->debug ) {
			// Backend notification will get the OK response
			if ( $is_backend_notification ) {
				WC_iPay88::add_debug_log( 'Backend response.');
			}
			WC_iPay88::add_debug_log( 'Payment response received. Response is: ' . print_r( $_POST, true ) );
		}

		if ( $this->validate_response() ) {

			$refno = WC_iPay88::get_post( 'RefNo' );
			$transid = WC_iPay88::get_post( 'TransId' );
			$estatus = WC_iPay88::get_post( 'Status' );
			$errdesc = WC_iPay88::get_post( 'ErrDesc' );

			$order_id = $this->get_order_id( $refno );

			$order = new WC_Order( (int) $order_id );

			$redirect_url = $this->get_return_url( $order );

			// Check if the order was already processed
			if ( 'completed' == $order->status || 'processing' == $order->status ) {

				// Debug log
				if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Payment already processed. Aborting.' );

				// Backend notification will get the OK response
				if ( $is_backend_notification ) {
					echo $received_ok;
				} else {
					// Normal Payment notification need to be redirected to the "Thank You" page.
					wp_safe_redirect( $redirect_url );
				}
				exit;
			}

			switch ( $estatus ) :
				case 1 : // Successful payment

					// Update order
					$order->add_order_note( sprintf( __( 'iPay88 Payment Completed.'
							. ' Transaction Reference Number: %s.', 'wc_ipay88' ),
							$transid ) );

					// Debug log
					if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Payment completed.' );

					WC_Compat_iPay88::empty_cart();

					$order->payment_complete();

					break;
				case 2 : // Failed payment
				default :
					// Debug log
					if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Payment failed.' );

					// Update order
					$order->add_order_note( sprintf( __( 'iPay88 Payment Failed.
										   Error Description: %s
										   Transaction Reference Number: %s.', 'wc_ipay88' ),
										   $errdesc, $transid) );

					$order->update_status( 'failed' );

					// Add error to show the customer and the cancel URL
					WC_Compat_iPay88::wc_add_notice( __( 'Your Payment Failed.
							Please try again or use another payment option.', 'wc_ipay88' ), 'error' );

					break;
			endswitch;

			// Backend notification will get the OK response
			if ( $is_backend_notification ) {
				echo $received_ok;
			} else {
				// Normal Payment notification needs to be redirected to the "Thank You" page.
				wp_safe_redirect( $redirect_url );
			}
			exit;
		}

	}

	/**
	 * Generate the sha1 control signature. <br/>
	 * Used in both the request and the response to validate the authenticity of the message.
	 *
	 * @global object $woocommerce
	 * @param array $params The request or response parameters
	 * @param bool $is_response Are the parameters from the response message
	 * @return string The sha1 generated string
	 **/
	private function generate_sha1_signature( $params, $is_response = true ) {

		$string = '';
		if ( $is_response ) {
			$this->format_amount( str_replace( ',', '', $params['Amount'] ) );
			$string = $this->MerchantKey . $this->MerchantCode . $params['PaymentId'] . $params['RefNo'] . $this->hash_amount/100 . $params['Currency'] . $params['Status'];
		} else {
			$string = $this->MerchantKey . $this->MerchantCode . $params['RefNo'] . $this->hash_amount . $params['Currency'];
		}

		//Debug log
		if ( 'yes' == $this->debug ) WC_iPay88::add_debug_log( 'Signature string is: '. $string );

		return base64_encode( $this->hex2bin( sha1( $string ) ) );
	}

	function hex2bin( $hexSource ) {
		$bin = '';
		for ( $i = 0; $i < strlen( $hexSource ); $i = $i + 2 ) {
			$bin .= chr( hexdec( substr( $hexSource, $i, 2 ) ) );
		}

		return $bin;
	}

	/**
	 * Check the Payment method is submitted and is valid
	 *
	 * @global type $woocommerce
	 * @param type $payment_type
	 */
	private function check_payment_fields( $payment_type = '0' ) {

		// Check only if there are available payment types
		if ( 'ID' == $this->gateway ) {
			if ( ! empty( $this->paymenttype_available_ph ) ) {
				if ( '0' == $payment_type ) {
					WC_Compat_iPay88::wc_add_notice( __( 'Payment type is required.', 'wc_ipay88' ), 'error' );
					return;
				}

				if ( ! in_array( $payment_type, $this->paymenttype_available_ph ) ) {
					WC_Compat_iPay88::wc_add_notice( __( 'Wrong payment type. Please try again.', 'wc_ipay88' ), 'error' );
					return;
				}
			}
		} else {
			if ( ! empty( $this->paymenttype_available ) ) {
				if ( '0' == $payment_type ) {
					WC_Compat_iPay88::wc_add_notice( __( 'Payment type is required.', 'wc_ipay88' ), 'error' );
					return;
				}

				if ( ! in_array( $payment_type, $this->paymenttype_available ) ) {
					WC_Compat_iPay88::wc_add_notice( __( 'Wrong payment type. Please try again.', 'wc_ipay88' ), 'error' );
					return;
				}
			}
		}
	}

	/**
	 * Format the two amounts we need.
	 * One for hashing
	 * One for request parameter
	 *
	 * @param double $amount
	 */
	function format_amount( $amount ) {
		if ( is_numeric( $amount ) ) {
			$this->hash_amount = number_format( $amount, 2, '', '' );
			$this->formatted_amount = number_format( $amount, 2, '', '' );
		}
	}

	/**
	 * Get the order ID. Check to see if SON and SONP is enabled and
	 *
	 * @global type $wc_seq_order_number
	 * @global type $wc_seq_order_number_pro
	 * @param type $order_number
	 * @return type
	 */
	private function get_order_id( $order_number ) {

		@ob_start();

		// Find the order ID from the custom order number, if we have SON or SONP enabled
		if ( class_exists( 'WC_Seq_Order_Number' ) ) {

			global $wc_seq_order_number;

			$order_id = $wc_seq_order_number->find_order_by_order_number( $order_number );

			if ( 0 === $order_id ) {
				$order_id = $order_number;
			}

		} elseif ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			global $wc_seq_order_number_pro;

			$order_id = $wc_seq_order_number_pro->find_order_by_order_number( $order_number );

			if ( 0 === $order_id ) {
				$order_id = $order_number;
			}

		} else {
			$order_id = $order_number;
		}

		// Remove any error notices generated during the process
		@ob_clean();

		return $order_id;

	}

} //end ipay88 class

