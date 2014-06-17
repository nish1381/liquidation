<?php
/**
 * Customer remind to pay email (plain)
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

$product_data = get_product( $product_id );

echo $email_heading . "\n\n";

printf(__("Congratulations! You have the won auction for %s. Your bid was: %d%s. Please click on this link to pay for your auction", 'wc_simple_auctions'),  $product_data -> get_title(), $current_bid, get_woocommerce_currency_symbol()); 
echo "\n\n";
echo add_query_arg("pay-auction",$product_id, $checkout_url);
echo "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );