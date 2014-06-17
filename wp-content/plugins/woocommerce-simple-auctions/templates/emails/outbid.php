<?php
/**
 * Customer outbid email
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$product_data = get_product(  $product_id );
?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p><?php printf( __( "Hi there. Your bid for <a href='%s'>%s</a> has been outbid. Current bid is: %d%s", 'wc_simple_auctions' ),get_permalink($product_id), $product_data->get_title(), $product_data->get_curent_bid() , get_woocommerce_currency_symbol() ); ?></p>

<?php do_action('woocommerce_email_footer'); ?>