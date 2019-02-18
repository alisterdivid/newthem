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
		<td class="addresses-td" width="50%" valign="top">
			<h3><?php _e( "Billing Address", 'email-control' ); ?></h3>
			<address class="address">
				<?php echo $order->get_formatted_billing_address(); ?>
			</address>
		</td>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
			<td class="addresses-td" width="50%" valign="top">
				<h3><?php _e( "Shipping Address", 'email-control' ); ?></h3>
				<address class="address">
					<?php echo $shipping; ?>
				</address>
			</td>
		<?php endif; ?>
	</tr>
</table>
