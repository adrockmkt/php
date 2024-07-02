add_action(
	'simpay_payment_receipt_viewed',
	/**
	 * Runs the first time the payment confirmation page is viewed.
	 * 
	 * @param array<string, mixed> $payment_confirmation_data
	 */
	function( $payment_confirmation_data ) {
		// Payment customer data (not used in this example).
		$customer = $payment_confirmation_data['customer'];

		// One-time payment data.
		$payment = current( $payment_confirmation_data['paymentintents'] );

		// Make sure the payment intent exists.
		if ( ! $payment ) {
			error_log( 'No payment data available in confirmation data.' );
			return;
		}

		// Make sure the transaction was successful.
		if ( 'succeeded' !== $payment->status ) {
			error_log( sprintf( 'Payment status is not successful: %s', $payment->status ) );
			return;
		}

		// Make sure the transaction ID is not empty.
		if ( empty( $payment->id ) ) {
			error_log( 'Payment ID is empty.' );
			return;
		}

		// Make sure the payment amount is greater than 0.
		if ( $payment->amount <= 0 ) {
			error_log( 'Payment amount is not greater than 0.' );
			return;
		}

		// Determine the donation type and value
		$donation_type = ''; // mensal or anual
		$donation_value = $payment->amount / 100; // amount in BRL

		// Set donation type based on the amount
		switch ($donation_value) {
			case 20:
			case 50:
			case 100:
				$donation_type = 'mensal';
				break;
			case 200:
			case 500:
			case 1000:
				$donation_type = 'anual';
				break;
			default:
				error_log( 'Unknown donation value: ' . $donation_value );
				return;
		}

		$event_name = sprintf( 'purchase_%s_%d', $donation_type, $donation_value );

		printf(
			'<script>
				console.log("Sending %s event to GA4");
				gtag("event", "%s", {
					"transaction_id": "%s",
					"value": %s,
					"currency": "%s"
				});
			</script>',
			esc_js( $event_name ),
			esc_js( $event_name ),
			esc_js( $payment->id ),
			esc_js( $donation_value ),
			esc_js( strtoupper( $payment->currency ) )
		);
	}
);
