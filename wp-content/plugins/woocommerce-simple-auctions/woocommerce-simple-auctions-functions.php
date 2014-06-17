<?php
/**
 * WooCommerce Simple Auctions Functions
 *
 * Hooked-in functions for WooCommerce Simple Auctions related events on the front-end.
 *
 */


/**
 * Placed bid message
 *
 * @access public
 * @return void
 * 
 */
function woocommerce__simple_auctions_place_bid_message( $product_id ) {
	global $woocommerce;

	$message = sprintf( __( 'Successfully placed bid for &quot;%s&quot; ', 'wc_simple_auctions' ), get_the_title( $product_id ) );
	if (version_compare($woocommerce->version, '2.1',  ">=")){
		wc_add_notice ( apply_filters('woocommerce_simple_auctions_placed_bid_message', $message) );
	} else {
		$woocommerce->add_message( apply_filters('woocommerce_simple_auctions_placed_bid_message', $message) );
	}	
	
}


/**
 * Your bid is winning message
 *
 * @access public
 * @return void
 * 
 */
function woocommerce__simple_auctions_winning_bid_message( $product_id ) {
	global $product, $woocommerce;
	if ($product->product_type != 'auction')
					return FALSE;
	if ($product->is_closed())
					return FALSE;
	$current_user = wp_get_current_user();
	
	if (!$current_user-> ID)
					return FALSE;
	
	$message =   __('No need to bid. Your bid is winning! ', 'wc_simple_auctions');
	if (version_compare($woocommerce->version, '2.1',  ">=")){
		if ($current_user -> ID == $product -> auction_current_bider &&  wc_notice_count () == 0   ) {
			wc_add_notice( apply_filters('woocommerce_simple_auctions_winning_bid_message', $message) );
		}	
	} else {
		if ($current_user -> ID == $product -> auction_current_bider &&  $woocommerce->message_count() == 0   ) {
			$woocommerce->add_message( apply_filters('woocommerce_simple_auctions_winning_bid_message', $message) );
		}	
	}	
}


/**
 * Gets the url for the checkout page
 *
 * @return string url to page
 */
function simple_auction_get_checkout_url() {
	$checkout_page_id = woocommerce_get_page_id('checkout');
	$checkout_url     = '';
	if ( $checkout_page_id ) {
		if ( is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes' )
			$checkout_url = str_replace( 'http:', 'https:', get_permalink( $checkout_page_id ) );
		else
			$checkout_url = get_permalink( $checkout_page_id );
	}
	return apply_filters( 'woocommerce_get_checkout_url', $checkout_url );
}