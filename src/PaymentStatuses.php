<?php

namespace Pronamic\WordPress\Pay\Extensions\ClassiPress;

/**
 * Title: Payment statuses
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 1.0.3
 * @since   1.0.0
 */
class PaymentStatuses {
	/**
	 * Indiactor for the 'Completed' payment status
	 *
	 * @var string
	 * @see https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/admin/admin-options.php?at=3.2.1#cl-2717
	 */
	const COMPLETED = 'Completed';

	/**
	 * Indiactor for the 'Pending' payment status
	 *
	 * @var string
	 * @see https://bitbucket.org/Pronamic/classipress/src/bc1334736c6e/includes/admin/admin-options.php?at=3.2.1#cl-2730
	 */
	const PENDING = 'Pending';
}
