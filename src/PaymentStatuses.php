<?php

namespace Pronamic\WordPress\Pay\Extensions\ClassiPress;

/**
 * Title: Payment statuses
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class PaymentStatuses {
	/**
	 * Indiactor for the 'Completed' payment status
	 *
	 * @var string
	 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/admin/admin-options.php?at=3.2.1#cl-2717
	 */
	const COMPLETED = 'Completed';

	/**
	 * Indiactor for the 'Pending' payment status
	 *
	 * @var string
	 * @link https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/admin/admin-options.php?at=3.2.1#cl-2730
	 */
	const PENDING = 'Pending';
}
