<?php

namespace Pronamic\WordPress\Pay\Extensions\ClassiPress;

/**
 * Title: Order
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Order {
	/**
	 * Check if the order info is about an advertisement
	 *
	 * @param array $order Order.
	 *
	 * @return boolean
	 */
	public static function is_advertisement( array $order ) {
		return isset( $order['ad_id'] ) && ! empty( $order['ad_id'] );
	}

	/**
	 * Check if the order info is about an package
	 *
	 * @param array $order Order.
	 *
	 * @return boolean
	 */
	public static function is_package( array $order ) {
		return ! self::is_advertisement( $order );
	}

	/**
	 * Check if the order is completed
	 *
	 * @param array $order Order.
	 *
	 * @return boolean
	 */
	public static function is_completed( array $order ) {
		return isset( $order['payment_status'] ) && PaymentStatuses::COMPLETED === $order['payment_status'];
	}
}
