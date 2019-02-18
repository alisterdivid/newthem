<?php
/**
 * Customer Reset Password email
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$email_heading = get_option( 'ec_woocommerce_customer_reset_password_heading' );
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo get_option( 'ec_woocommerce_customer_reset_password_main_text' ); ?>

<p></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
