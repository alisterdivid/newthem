<?php
/**
 * @package WC_Product_Customer_List
 * @version 2.6.3
 */

// Create Shortcode customer_list

// Use the shortcode: [customer_list product="1111" hide_titles="false" order_status="wc-completed" order_number="false" order_date="false" billing_first_name="true" billing_last_name="true" billing_company="false" billing_email="false" billing_phone="false" billing_address_1="false" billing_address_2="false" billing_city="false" billing_state="false" billing_postalcode="false" billing_country="false" shipping_first_name="false" shipping_last_name="false" shipping_company="false" shipping_address_1="false" shipping_address_2="false" shipping_city="false" shipping_state="false" shipping_postalcode="false" shipping_country="false" customer_message="false" customer_id="false" customer_username="false" order_status="false" order_payment="false" order_shipping="false" order_coupon="false" order_total="false" order_qty="false"]

function wpcl_shortcode($atts) {
	ob_start();

	// Attributes
	$atts = shortcode_atts(
		array(
			'product' => get_the_id(), // Get current product if no product specified
			'order_status' => 'wc-completed',
			'show_titles' => 'false',
			'order_number' => 'false',
			'order_date' => 'false',
			'billing_first_name' => 'true',
			'billing_last_name' => 'true',
			'billing_company' => 'false',
			'billing_email' => 'false',
			'billing_phone' => 'false',
			'billing_address_1' => 'false',
			'billing_address_2' => 'false',
			'billing_city' => 'false',
			'billing_state' => 'false',
			'billing_postalcode' => 'false',
			'billing_country' => 'false',
			'shipping_first_name' => 'false',
			'shipping_last_name' => 'false',
			'shipping_company' => 'false',
			'shipping_address_1' => 'false',
			'shipping_address_2' => 'false',
			'shipping_city' => 'false',
			'shipping_state' => 'false',
			'shipping_postalcode' => 'false',
			'shipping_country' => 'false',
			'customer_message' => 'false',
			'customer_id' => 'false',
			'customer_username' => 'false',
			'order_status' => 'false',
			'order_payment' => 'false',
			'order_shipping' => 'false',
			'order_coupon' => 'false',
			'order_total' => 'false',
			'order_qty' => 'false',
			'order_qty_total' => 'false',
		),
		$atts,
		'customer_list'
	);

	// Attributes in var
	$post_id = $atts['product'];
	$order_status = $atts['order_number'];
	$show_titles = $atts['show_titles'];
	$order_number = $atts['order_number'];
	$order_date = $atts['order_date'];
	$billing_first_name = $atts['billing_first_name'];
	$billing_last_name = $atts['billing_last_name'];
	$billing_company = $atts['billing_company'];
	$billing_email = $atts['billing_email'];
	$billing_phone = $atts['billing_phone'];
	$billing_address_1 = $atts['billing_address_1'];
	$billing_address_2 = $atts['billing_address_2'];
	$billing_city = $atts['billing_city'];
	$billing_state = $atts['billing_state'];
	$billing_postalcode = $atts['billing_postalcode'];
	$billing_country = $atts['billing_country'];
	$shipping_first_name = $atts['shipping_first_name'];
	$shipping_last_name = $atts['shipping_last_name'];
	$shipping_company = $atts['shipping_company'];
	$shipping_address_1 = $atts['shipping_address_1'];
	$shipping_address_2 = $atts['shipping_address_2'];
	$shipping_city = $atts['shipping_city'];
	$shipping_state = $atts['shipping_state'];
	$shipping_postalcode = $atts['shipping_postalcode'];
	$shipping_country = $atts['shipping_country'];
	$customer_message = $atts['customer_message'];
	$customer_id = $atts['customer_id'];
	$customer_username = $atts['customer_username'];
	$order_status = $atts['order_status'];
	$order_payment = $atts['order_payment'];
	$order_shipping = $atts['order_shipping'];
	$order_coupon = $atts['order_coupon'];
	$order_total = $atts['order_total'];
	$order_qty = $atts['order_qty'];
	$order_qty_total = $atts['order_qty_total'];

	global $sitepress, $post, $wpdb;

	// Check for translated products if WPML is activated
	if(isset($sitepress)) {
		$trid = $sitepress->get_element_trid($post_id, 'post_product');
		$translations = $sitepress->get_element_translations($trid, 'product');
		$post_id = Array();
		foreach( $translations as $lang=>$translation){
			$post_id[] = $translation->element_id;
		}
	}

	// Query the orders related to the product
	$pieces = explode(",", $order_status);
	$order_statuses = array_map( 'esc_sql', (array) $pieces );
	$order_statuses_string = "'" . implode( "', '", $order_statuses ) . "'";
	$post_id_arr = array_map( 'esc_sql', (array) $post_id );
	$post_string = "'" . implode( "', '", $post_id_arr ) . "'";

	$item_sales = $wpdb->get_results( $wpdb->prepare(
		"SELECT o.ID as order_id, oi.order_item_id FROM
		{$wpdb->prefix}woocommerce_order_itemmeta oim
		INNER JOIN {$wpdb->prefix}woocommerce_order_items oi
		ON oim.order_item_id = oi.order_item_id
		INNER JOIN $wpdb->posts o
		ON oi.order_id = o.ID
		WHERE oim.meta_key = %s
		AND oim.meta_value IN ( $post_string )
		AND o.post_status IN ( $order_statuses_string )
		AND o.post_type NOT IN ('shop_order_refund')
		ORDER BY o.ID DESC",
		'_product_id'
	));

	// Get selected columns from the options page
	$product = WC()->product_factory->get_product( $post_id );
	$columns = array();
	if($order_number == 'true' ) { $columns[] = __('Order', 'wc-product-customer-list'); }
	if($order_date == 'true' ) { $columns[] = __('Date', 'wc-product-customer-list'); }
	if($billing_first_name == 'true' ) { $columns[] = __('Billing First name', 'wc-product-customer-list'); }
	if($billing_last_name == 'true' ) { $columns[] = __('Billing Last name', 'wc-product-customer-list'); }
	if($billing_company == 'true' ) { $columns[] = __('Billing Company', 'wc-product-customer-list'); }
	if($billing_email == 'true' ) { $columns[] = __('Billing E-mail', 'wc-product-customer-list'); }
	if($billing_phone == 'true' ) { $columns[] = __('Billing Phone', 'wc-product-customer-list'); }
	if($billing_address_1 == 'true' ) { $columns[] = __('Billing Address 1', 'wc-product-customer-list'); }
	if($billing_address_2 == 'true' ) { $columns[] = __('Billing Address 2', 'wc-product-customer-list'); }
	if($billing_city == 'true' ) { $columns[] = __('Billing City', 'wc-product-customer-list'); }
	if($billing_state == 'true' ) { $columns[] = __('Billing State', 'wc-product-customer-list'); }
	if($billing_postalcode == 'true' ) { $columns[] = __('Billing Postal Code / Zip', 'wc-product-customer-list'); }
	if($billing_country == 'true' ) { $columns[] = __('Billing Country', 'wc-product-customer-list'); }
	if($shipping_first_name == 'true' ) { $columns[] = __('Shipping First name', 'wc-product-customer-list'); }
	if($shipping_last_name == 'true' ) { $columns[] = __('Shipping Last name','wc-product-customer-list'); }
	if($shipping_company == 'true' ) { $columns[] = __('Shipping Company', 'wc-product-customer-list'); }
	if($shipping_address_1 == 'true' ) { $columns[] = __('Shipping Address 1', 'wc-product-customer-list'); }
	if($shipping_address_2 == 'true' ) { $columns[] = __('Shipping Address 2', 'wc-product-customer-list'); }
	if($shipping_city == 'true' ) { $columns[] = __('Shipping City', 'wc-product-customer-list'); }
	if($shipping_state == 'true' ) { $columns[] = __('Shipping State', 'wc-product-customer-list'); }
	if($shipping_postalcode == 'true' ) { $columns[] = __('Shipping Postal Code / Zip', 'wc-product-customer-list'); }
	if($shipping_country == 'true' ) { $columns[] = __('Shipping Country', 'wc-product-customer-list'); }
	if($customer_message == 'true' ) { $columns[] = __('Customer Message', 'wc-product-customer-list'); }
	if($customer_id == 'true' ) { $columns[] = __('Customer ID', 'wc-product-customer-list'); }
	if($customer_username == 'true' ) { $columns[] = __('Customer username', 'wc-product-customer-list'); }
	if($order_status == 'true' ) { $columns[] = __('Order Status', 'wc-product-customer-list'); }
	if($order_payment == 'true' ) { $columns[] = __('Payment method', 'wc-product-customer-list'); }
	if($order_shipping == 'true' ) { $columns[] = __('Shipping method', 'wc-product-customer-list'); }
	if($order_coupon == 'true' ) { $columns[] = __('Coupons used', 'wc-product-customer-list'); }
	if($product->get_type() == 'variable' ) { $columns[] = __('Variation', 'wc-product-customer-list'); }
	if($order_total == 'true' ) { $columns[] = __('Order total', 'wc-product-customer-list'); }
	if($order_qty == 'true' ) { $columns[] = __('Qty', 'wc-product-customer-list'); }

	if($item_sales) {
		$productcount = array();
		?>
		<table id="list-table" style="width:100%">
			<?php if($show_titles == 'true') { ?>
			<thead>
				<tr>
					<?php foreach($columns as $column) { ?>
					<th>
						<strong><?php echo $column; ?></strong>
					</th>
					<?php } ?>
				</tr>
			</thead>
			<?php } ?>
			<tbody>
				<?php
				foreach( $item_sales as $sale ) {
					$order = wc_get_order( $sale->order_id );
					$formatted_total = $order->get_formatted_order_total();

					// Get quantity
					$refunded_qty = 0;
					$items = $order->get_items();
					foreach ($items as $item_id => $item) {
						if($item['product_id'] == $post->ID) {
							$refunded_qty += $order->get_qty_refunded_for_item($item_id);
						}
					}
					$quantity = wc_get_order_item_meta( $sale->order_item_id, '_qty', true );
					$quantity += $refunded_qty;

					// Check for partially refunded orders
					if($quantity == 0 && get_option( 'wpcl_order_partial_refunds', 'no' ) == 'yes') {

					// Order has been partially refunded
					} else {
						?>
						<tr>
							<?php if($order_number == 'true') { ?>
							<td>
								<?php echo '<a href="' . admin_url( 'post.php' ) . '?post=' . $sale->order_id . '&action=edit" target="_blank">' . $sale->order_id . '</a>'; ?>
							</td>
							<?php } ?>
							<?php if($order_date == 'true') { ?>
							<td>
								<?php echo date_format($order->get_date_created(), 'Y-m-d'); ?>
							</td>
							<?php } ?>
							<?php if($billing_first_name == 'true') { ?>
							<td>
								<?php echo $order->get_billing_first_name(); ?>
							</td>
							<?php } ?>
							<?php if($billing_last_name == 'true') { ?>
							<td>
								<?php echo $order->get_billing_last_name(); ?>
							</td>
							<?php } ?>
							<?php if($billing_company == 'true') { ?>
							<td>
								<?php echo $order->get_billing_company(); ?>
							</td>
							<?php } ?>
							<?php if($billing_email == 'true') { ?>
							<td>
								<?php echo '<a href="mailto:' . $order->get_billing_email() . '">' . $order->get_billing_email() . '</a>'; ?>
							</td>
							<?php } ?>
							<?php if($billing_phone == 'true') { ?>
							<td>
								<?php echo '<a href="tel:' . $order->get_billing_phone() . '">' . $order->get_billing_phone() . '</a>'; ?>
							</td>
							<?php } ?>
							<?php if($billing_address_1 == 'true') { ?>
							<td>
								<?php echo $order->get_billing_address_1(); ?>
							</td>
							<?php } ?>
							<?php if($billing_address_2 == 'true') { ?>
							<td>
								<?php echo $order->get_billing_address_2(); ?>
							</td>
							<?php } ?>
							<?php if($billing_city == 'true') { ?>
							<td>
								<?php echo $order->get_billing_city(); ?>
							</td>
							<?php } ?>
							<?php if($billing_state == 'true') { ?>
							<td>
								<?php echo $order->get_billing_state(); ?>
							</td>
							<?php } ?>
							<?php if($billing_postalcode == 'true') { ?>
							<td>
								<?php echo $order->get_billing_postcode(); ?>
							</td>
							<?php } ?>
							<?php if($billing_country == 'true') { ?>
							<td>
								<?php echo $order->get_billing_country(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_first_name == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_first_name(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_last_name == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_last_name(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_company == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_company(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_address_1 == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_address_1(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_address_2 == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_address_2(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_city == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_city(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_state == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_state(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_postalcode == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_postcode(); ?>
							</td>
							<?php } ?>
							<?php if($shipping_country == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_country(); ?>
							</td>
							<?php } ?>
							<?php if($customer_message == 'true') { ?>
							<td>
								<?php echo $order->get_customer_note(); ?>
							</td>
							<?php } ?>
							<?php if($customer_id == 'true') { ?>
							<td>
								<?php 
									if($order->get_customer_id()) {
										echo '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $order->get_customer_id() . '" target="_blank">' . $order->get_customer_id() . '</a>';
									}
								?>
							</td>
							<?php } ?>
							<?php if($customer_username == 'true') { ?>
							<td>
								<?php 
									$customerid = $order->get_customer_id();
									if($customerid) {
										$user_info = get_userdata($customerid);
										echo '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $order->get_customer_id() . '" target="_blank">' . $user_info->user_login . '</a>';
									}
								?>
							</td>
							<?php } ?>
							<?php if($order_status == 'true') { ?>
							<td>
								<?php
									$status = wc_get_order_status_name($order->get_status());
									echo $status;
								?>
							</td>
							<?php } ?>
							<?php if($order_payment == 'true') { ?>
							<td>
								<?php echo $order->get_payment_method_title(); ?>
							</td>
							<?php } ?>
							<?php if($order_shipping == 'true') { ?>
							<td>
								<?php echo $order->get_shipping_method() ; ?>
							</td>
							<?php } ?>
							<?php if($order_coupon == 'true') { ?>
							<td>
								<?php
									$coupons = $order->get_used_coupons();
									echo implode(', ',$coupons);
								?>
							</td>
							<?php } ?>

							<?php if( 'variable' == $product->get_type() ) {
								$item = $order->get_item($sale->order_item_id);
							?>
							<td>
								<?php 
									foreach($item->get_meta_data() as $itemvariation) {
										echo '<strong>' . wc_attribute_label($itemvariation->key) . '</strong>: &nbsp;' . wc_attribute_label($itemvariation->value) . '<br />';
									}
								?>
							</td>
							<?php }  ?>
							<?php if($order_total == 'true') { ?>
							<td>
								<?php echo $order->get_formatted_order_total(); ?>
							</td>
							<?php } ?>
							<?php if($order_qty == 'true') {
									$productcount[] = $quantity;
							?>
							<td>
								<?php echo $quantity;  ?>
							</td>
							<?php } ?>
						</tr>
					<?php 
					} // End partial refund check
				} // End foreach
				?>
			</tbody>
		</table>
		<?php if($order_qty_total == 'true') { ?>
		<p class="total">
			<?php echo '<strong>' . __('Total', 'wc-product-customer-list') . ' : </strong>' . array_sum($productcount); ?>
		</p>
		<?php } ?>

	<?php } else {
		_e('This product currently has no customers', 'wc-product-customer-list');
	}
	return ob_get_clean();
}
add_shortcode( 'customer_list', 'wpcl_shortcode' );