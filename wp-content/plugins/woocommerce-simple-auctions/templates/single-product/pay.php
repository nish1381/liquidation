<?php
/**
 * Auction pay
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product, $post;

$user_id = get_current_user_id();

if ( ($user_id == $product->auction_current_bider && $product-> auction_closed == '2' && !$product->auction_payed ) ) :
?>

    <p><?php _e('Congratulations you have won this auction!', 'wc_simple_auctions') ?></p>
    
    <p><a href="<?php echo apply_filters( 'woocommerce_simple_auction_pay_now_button',add_query_arg("pay-auction",$product->id, simple_auction_get_checkout_url())); ?>" class="button"><?php _e('Pay Now', 'wc_simple_auctions') ?></a></p>

<?php endif; ?>