<?php

/**
 * Title: Payment statuses
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.3
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_ClassiPress_PaymentStatuses {
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
