<?php
/**
 * Email Downloads.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

?><h2 class="woocommerce-order-downloads__title"><?php _e( 'Downloads', 'email-control' ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
		<tr>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>
				<th class="td" scope="col" style="text-align:<?php echo $text_align; ?>;"><?php echo esc_html( $column_name ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<?php foreach ( $downloads as $download ) : ?>
		<tr>
			<?php foreach ( $columns as $column_id => $column_name ) : ?>
					<td class="td" style="text-align:<?php echo $text_align; ?>;"><?php
					if ( has_action( 'woocommerce_email_downloads_column_' . $column_id ) ) {
						do_action( 'woocommerce_email_downloads_column_' . $column_id, $download );
					} else {
						switch ( $column_id ) {
							case 'download-product' : ?>
								<a href="<?php echo esc_url( get_permalink( $download['product_id'] ) ); ?>"><?php echo esc_html( $download['product_name'] ); ?></a>
								<?php
							break;
							case 'download-file' : ?>
								<a href="<?php echo esc_url( $download['download_url'] ); ?>" class="woocommerce-MyAccount-downloads-file button alt"><?php echo esc_html( $download['download_name'] ); ?></a>
								<?php
							break;
							case 'download-expires' : ?>
								<?php if ( ! empty( $download['access_expires'] ) ) : ?>
									<time datetime="<?php echo date( 'Y-m-d', strtotime( $download['access_expires'] ) ); ?>" title="<?php echo esc_attr( strtotime( $download['access_expires'] ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ); ?></time>
								<?php else : ?>
									<?php _e( 'Never', 'email-control' ); ?>
								<?php endif;
							break;
						}
					}
				?></td>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</table>