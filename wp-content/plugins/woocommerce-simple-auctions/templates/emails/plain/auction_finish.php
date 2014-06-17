<?php
/**
 * Admin auction fail email (plain)
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

$product_data = get_product(  $product_id );
$user_info = get_userdata($product_data->auction_win);

echo $email_heading . "\n\n";

printf( __( "The auction for %s finished. Winning bid is %d%s ", 'wc_simple_auctions' ), $product_data->get_title(),$product_data->get_curent_bid(), get_woocommerce_currency_symbol() );  
echo "\n\n";
echo get_permalink($product_id);
echo "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );