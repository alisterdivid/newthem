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

$text_align = is_rtl() ? 'right' : 'left';

?>
<table id="addresses" cellspacing="0" cellpadding="0" align="center" style="width: 100%; vertical-align: top;" border="0">
	<tr>
		<td class="addresses-td" width="50%" valign="top">
			<h3><?php _e( "Billing Address", 'email-control' ); ?></h3>
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
			<td class="addresses-td" width="50%" valign="top">
				<h3><?php _e( "Shipping Address", 'email-control' ); ?></h3>
				<address class="address">
					<?php echo $shipping; ?>
				</address>
			</td>
		<?php endif; ?>
	</tr>
</table>
