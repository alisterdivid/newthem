<?php
/**
 * Email Addresses
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     3.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table id="addresses" cellspacing="0" cellpadding="0" align="center" style="width: 100%; vertical-align: top;" border="0">
	<tr>
		<td width="50%" valign="top" class="addresses-td order_items_table_column_pading_first">
			<p><strong><?php _e( "Billing address", 'email-control' ); ?>:</strong></p>
			<address class="address">
				<?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'email-control' ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ): ?>
					<p><?php echo esc_html( $order->get_billing_email() ); ?></p>
				<?php endif; ?>
			</address>
		</td>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
			<td width="50%" valign="top" class="addresses-td order_items_table_column_pading_last">
				<p><strong><?php _e( "Shipping address", 'email-control' ); ?>:</strong></p>
				<address class="address">
					<?php echo $shipping; ?>
				</address>
			</td>
		<?php endif; ?>
	</tr>
</table>
