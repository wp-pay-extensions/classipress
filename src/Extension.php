<?php

namespace Pronamic\WordPress\Pay\Extensions\ClassiPress;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util;

/**
 * Title: ClassiPress iDEAL Add-On
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'classipress';

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		add_action( 'appthemes_init', array( __CLASS__, 'appthemes_init' ) );

		/*
		 * We have to add this action on bootstrap, because we can't
		 * deterime earlier we are dealing with ClassiPress
		 */
		if ( is_admin() ) {
			add_action( 'cp_action_gateway_values', array( __CLASS__, 'gateway_values' ) );
		}
	}

	/**
	 * Initialize
	 */
	public static function appthemes_init() {
		global $app_theme;

		if ( 'ClassiPress' === $app_theme ) {
			add_action( 'cp_action_payment_method', array( __CLASS__, 'payment_method' ) );
			add_action( 'cp_action_gateway', array( __CLASS__, 'gateway_process' ) );

			add_action( 'template_redirect', array( __CLASS__, 'process_gateway' ) );

			add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, array( __CLASS__, 'redirect_url' ), 10, 2 );
			add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'update_status' ), 10, 1 );
			add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( __CLASS__, 'source_text' ), 10, 2 );
			add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( __CLASS__, 'source_description' ), 10, 2 );
			add_filter( 'pronamic_payment_source_url_' . self::SLUG, array( __CLASS__, 'source_url' ), 10, 2 );
		}
	}

	/**
	 * Gateway value
	 */
	public static function gateway_values() {
		global $app_abbr;

		// Gateway values.
		global $action_gateway_values;

		$action_gateway_values = array(
			// Tab Start.
			array(
				'type'    => 'tab',
				'tabname' => __( 'iDEAL', 'pronamic_ideal' ),
				'id'      => '',
			),
			// Title.
			array(
				'type' => 'title',
				'name' => __( 'iDEAL Options', 'pronamic_ideal' ),
				'id'   => '',
			),
			// Logo/Picture.
			array(
				'type' => 'logo',
				'name' => sprintf( '<img src="%s" alt="" />', plugins_url( 'images/ideal/icon-32x32.png', Plugin::$file ) ),
				'id'   => '',
			),
			// Enable.
			array(
				'type'    => 'select',
				'name'    => __( 'Enable iDEAL', 'pronamic_ideal' ),
				'options' => array(
					'yes' => __( 'Yes', 'pronamic_ideal' ),
					'no'  => __( 'No', 'pronamic_ideal' ),
				),
				'id'      => $app_abbr . '_pronamic_ideal_enable',
			),
			// Configuration.
			array(
				'type'    => 'select',
				'name'    => __( 'iDEAL Configuration', 'pronamic_ideal' ),
				'options' => Plugin::get_config_select_options(),
				'id'      => $app_abbr . '_pronamic_ideal_config_id',
			),
			array(
				'type' => 'tabend',
				'id'   => '',
			),
		);
	}

	/**
	 * Get config id.
	 *
	 * @return mixed
	 */
	private static function get_config_id() {
		global $app_abbr;

		$config_id = get_option( $app_abbr . '_pronamic_ideal_config_id' );

		return $config_id;
	}

	/**
	 * Get gateway.
	 *
	 * @return \Pronamic\WordPress\Pay\Core\Gateway
	 */
	private static function get_gateway() {
		$config_id = self::get_config_id();

		$gateway = Plugin::get_gateway( $config_id );

		return $gateway;
	}

	/**
	 * Add the option to the payment drop-down list on checkout
	 */
	public static function payment_method() {
		global $app_abbr;

		if ( 'yes' === get_option( $app_abbr . '_pronamic_ideal_enable' ) ) {
			printf( // WPCS: xss ok.
				'<option value="pronamic_ideal">%s</option>',
				PaymentMethods::get_name( PaymentMethods::IDEAL )
			);
		}
	}

	/**
	 * Process gateway
	 */
	public static function process_gateway() {
		if ( ! filter_has_var( INPUT_POST, 'classipress_pronamic_ideal' ) ) {
			return;
		}

		$gateway = self::get_gateway();

		if ( ! $gateway ) {
			return;
		}

		$id = filter_input( INPUT_POST, 'oid', FILTER_SANITIZE_STRING );

		$order = ClassiPress::get_order_by_id( $id );

		$data = new PaymentData( $order );

		$payment = Plugin::start( self::get_config_id(), $gateway, $data );

		wp_safe_redirect( $payment->get_pay_redirect_url() );

		exit;
	}

	/**
	 * Process gateway
	 *
	 * @param array $order_values Order.
	 */
	public static function gateway_process( $order_values ) {
		// If gateway wasn't selected then immediately return.
		if ( 'pronamic_ideal' !== $order_values['cp_payment_method'] ) {
			return;
		}

		// Add transaction entry.
		$transaction_id = ClassiPress::add_transaction_entry( $order_values );

		// Handle gateway.
		$gateway = self::get_config_id();

		if ( ! $gateway ) {
			return;
		}

		$data = new PaymentData( $order_values );

		// Hide the checkout page container HTML element.
		echo '<style type="text/css">.thankyou center { display: none; }</style>';

		?>
		<form class="form_step" method="post" action="">
			<?php

			echo Util::html_hidden_fields( // WPCS: xss ok.
				array( // WPCS: xss ok.
					'cp_payment_method' => 'pronamic_ideal',
					'oid'               => $data->get_order_id(),
				)
			);

			echo $gateway->get_input_html(); // WPCS: xss ok.

			?>

			<p class="btn1">
				<?php

				printf(
					'<input class="ideal-button" type="submit" name="classipress_pronamic_ideal" value="%s" />',
					esc_attr( __( 'Pay with iDEAL', 'pronamic_ideal' ) )
				);

				?>
			</p>
		</form>
		<?php
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @since unreleased
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function redirect_url( $url, Payment $payment ) {
		$id = $payment->get_source_id();

		$order = ClassiPress::get_order_by_id( $id );

		$data = new PaymentData( $order );

		$url = $data->get_normal_return_url();

		switch ( $payment->status ) {
			case Statuses::CANCELLED:
				break;
			case Statuses::EXPIRED:
				break;
			case Statuses::FAILURE:
				break;
			case Statuses::SUCCESS:
				$url = $data->get_success_url();

				break;
			case Statuses::OPEN:
				break;
			default:
				break;
		}

		return $url;
	}

	/**
	 * Update lead status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 */
	public static function update_status( Payment $payment ) {
		$id = $payment->get_source_id();

		$order = ClassiPress::get_order_by_id( $id );

		switch ( $payment->status ) {
			case Statuses::CANCELLED:
				break;
			case Statuses::EXPIRED:
				break;
			case Statuses::FAILURE:
				break;
			case Statuses::SUCCESS:
				if ( ! Order::is_completed( $order ) ) {
					ClassiPress::process_ad_order( $id );

					ClassiPress::process_membership_order( $order );

					ClassiPress::update_payment_status_by_txn_id( $id, PaymentStatuses::COMPLETED );
				}

				break;
			case Statuses::OPEN:
				break;
			default:
				break;
		}
	}

	/**
	 * Source column.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		$text = __( 'ClassiPress', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg( 'page', 'transactions', admin_url( 'admin.php' ) ),
			/* translators: %s: payment source id */
			sprintf( __( 'Order #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Payment.
	 *
	 * @return string
	 */
	public static function source_description( $description, Payment $payment ) {
		return __( 'ClassiPress Order', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_url( $url, Payment $payment ) {
		return add_query_arg( 'page', 'transactions', admin_url( 'admin.php' ) );
	}
}
