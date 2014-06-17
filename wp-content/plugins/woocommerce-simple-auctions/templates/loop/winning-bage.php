<?php
/**
 * Loop add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

$user_id  = get_current_user_id();

if ( $user_id == $product->auction_current_bider && !$product-> auction_closed) :
    
	echo apply_filters('woocommerce_simple_auction_winning_bage', '<span class="winning">'.__( 'Winning!', 'wc_simple_auctions' ).'</span>', $product);

endif; 

?>