<?php
/**
 * Loop Add to Cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

$user_id  = get_current_user_id();

if ( $user_id == $product->auction_current_bider && $product-> auction_closed == '2' && !$product->auction_payed ) : ?>

	<a href="<?php echo apply_filters( 'woocommerce_simple_auction_pay_now_button',simple_auction_get_checkout_url().'?pay-auction='.$product->id ); ?>" class="button"><?php  _e( 'Pay Now', 'wc_simple_auctions' ) ; ?></a>

<?php endif; ?>