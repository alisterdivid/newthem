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
				<?php echo $order->get_formatted_billing_address(); ?>
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
