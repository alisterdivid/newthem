<?php
/**
 * Additional Customer Details
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( ! empty( $fields ) ) : ?>
	<h3><?php _e( "Customer Details", 'email-control' ); ?></h3>
	
	<?php foreach ( $fields as $field ) : ?>
		<p><strong><?php echo wp_kses_post( $field['label'] ); ?>:</strong> <span class="text"><?php echo wp_kses_post( $field['value'] ); ?></span></p>
	<?php endforeach; ?>
<?php endif; ?>
