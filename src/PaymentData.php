<?php

namespace Pronamic\WordPress\Pay\Extensions\ClassiPress;

use Pronamic\WordPress\Pay\Payments\PaymentData as Pay_PaymentData;
use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;

/**
 * Title: ClassiPress iDEAL data proxy
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 */
class PaymentData extends Pay_PaymentData {
	/**
	 * Order values
	 *
	 * @var array
	 */
	private $order_values;

	/**
	 * Construct and initializes an ClassiPress iDEAL data proxy
	 *
	 * @param array $order_values Order values.
	 */
	public function __construct( $order_values ) {
		parent::__construct();

		$this->order_values = $order_values;
	}

	/**
	 * Get source indicatir
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'classipress';
	}

	/**
	 * Get description
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		/* translators: %s: order id */
		return sprintf( __( 'Advertisement %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get order ID
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		$order_id = null;

		if ( isset( $this->order_values['oid'] ) ) {
			$order_id = $this->order_values['oid'];
		} elseif ( isset( $this->order_values['txn_id'] ) ) {
			$order_id = $this->order_values['txn_id'];
		}

		return $order_id;
	}

	/**
	 * Get items
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 * @return Items
	 */
	public function get_items() {
		// Items.
		$items = new Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount).
		$amount = 0;
		if ( isset( $this->order_values['mc_gross'] ) ) {
			$amount = $this->order_values['mc_gross'];
		} elseif ( isset( $this->order_values['item_amount'] ) ) {
			$amount = $this->order_values['item_amount'];
		}

		$item = new Item();
		$item->set_number( $this->order_values['item_number'] );
		$item->set_description( $this->order_values['item_name'] );
		$item->set_price( $amount );
		$item->set_quantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	/**
	 * Get alphabetic currency code.
	 *
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return get_option( 'cp_curr_pay_type' );
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function get_email() {
		$user_id = $this->order_values['user_id'];

		return get_the_author_meta( 'user_email', $user_id );
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_customer_name() {
		$user_id = $this->order_values['user_id'];

		return get_the_author_meta( 'first_name', $user_id ) . ' ' . get_the_author_meta( 'last_name', $user_id );
	}

	/**
	 * Get address.
	 *
	 * @return mixed|null
	 */
	public function get_address() {
		return $this->order_values['cp_street'];
	}

	/**
	 * Get city.
	 *
	 * @return mixed|null
	 */
	public function get_city() {
		return $this->order_values['cp_city'];
	}

	/**
	 * Get ZIP.
	 *
	 * @return mixed|null
	 */
	public function get_zip() {
		return $this->order_values['cp_zipcode'];
	}

	/**
	 * Get notify URL
	 *
	 * @return string
	 */
	private function get_notify_url() {
		// @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2380
		if ( isset( $this->order_values['notify_url'] ) ) {
			$url = $this->order_values['notify_url'];
		} else {
			/*
			 * We query the order info sometimes directly from the database,
			 * if we do this the 'notify_url' isn't directly available
			 */
			if ( Order::is_advertisement( $this->order_values ) ) {
				/*
				 * Advertisement.
				 *
				 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2380
				 */
				$url = add_query_arg(
					array(
						'invoice' => $this->order_values['txn_id'],
						'aid'     => $this->order_values['ad_id'],
					),
					home_url( '/' )
				);
			} else {
				/*
				 * Advertisement package.
				 *
				 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2408
				 */
				$url = add_query_arg(
					array(
						'invoice' => $this->order_values['txn_id'],
						'uid'     => $this->order_values['user_id'],
					),
					home_url( '/' )
				);
			}
		}

		return $url;
	}

	/**
	 * Get notify URL
	 *
	 * @return string
	 */
	private function get_return_url() {
		// @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2381
		if ( isset( $this->order_values['return_url'] ) ) {
			$url = $this->order_values['return_url'];
		} else {
			/*
			 * We query the order info sometimes directly from the database,
			 * if we do this the 'return_url' isn't directly available
			 *
			 * ClassiPress has order information about adding an advertisement,
			 * but also has order information about advertisement packages.
			 *
			 * If the advertisement post ID is empty we know the order
			 * information is about an advertisement package.
			 *
			 * ClassiPress is doing in similar check in the following file:
			 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/gateways/gateway.php?at=3.2.1#cl-31
			 */
			if ( Order::is_advertisement( $this->order_values ) ) {
				/*
				 * Advertisement.
				 *
				 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2381
				 */
				$url = add_query_arg(
					array(
						'pid' => $this->order_values['txn_id'],
						'aid' => $this->order_values['ad_id'],
					),
					CP_ADD_NEW_CONFIRM_URL
				);
			} else {
				/*
				 * Advertisement package.
				 *
				 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/theme-functions.php?at=3.2.1#cl-2409
				 */
				$url = add_query_arg(
					array(
						'oid' => $this->order_values['txn_id'],
						// In some ClassiPress installation the 'wp_cp_order_info' table doesn't have an 'user_id' column.
						'uid' => ( isset( $this->order_values['user_id'] ) ? $this->order_values['user_id'] : false ),
					),
					CP_MEMBERSHIP_PURCHASE_CONFIRM_URL
				);
			}
		}

		return $url;
	}

	/**
	 * Get normal return URL.
	 *
	 * @return string
	 */
	public function get_normal_return_url() {
		return $this->get_notify_url();
	}

	/**
	 * Get cancel URL.
	 *
	 * @return string
	 */
	public function get_cancel_url() {
		return $this->get_notify_url();
	}

	/**
	 * Get success URL.
	 *
	 * @return string
	 */
	public function get_success_url() {
		return $this->get_return_url();
	}

	/**
	 * Get error URL.
	 *
	 * @return string
	 */
	public function get_error_url() {
		return $this->get_notify_url();
	}
}
