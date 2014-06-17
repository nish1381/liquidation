<?php
/**
 * Auction bid
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product, $post;
$current_user = wp_get_current_user();
?>

	
<p class="auction-condition"><?php echo apply_filters('conditiond_text', __( 'Item condition:', 'wc_simple_auctions' ), $product->product_type); ?><span class="curent-bid"> <?php echo $product->get_condition(); ?></span></p>


<?php if(($product->is_closed() === FALSE ) and ($product->is_started() === TRUE )) : ?>			
		
	<div class="auction-time" id="countdown"><?php echo apply_filters('time_text', __( 'Time left:', 'wc_simple_auctions' ), $product->product_type); ?> 
		<div class="main-auction auction-time-countdown" data-time="<?php echo $product->get_seconds_remaining() ?>" data-auctionid="<?php echo $product->id ?>" data-format="<?php echo get_option( 'simple_auctions_countdown_format' ) ?>"></div>
	</div>

	<div class='auction-ajax-change'>
	    
		<p class="auction-end"><?php echo apply_filters('time_left_text', __( 'Auction ends:', 'wc_simple_auctions' ), $product->product_type); ?> <?php echo $product->get_auction_end_time(); ?> <br />
			<?php printf(__('Timezone: %s','wc_simple_auctions') , get_option('timezone_string') ? get_option('timezone_string') : __('UTC+','wc_simple_auctions').get_option('gmt_offset')) ?>
		</p>
		
		<?php if ($product->auction_bid_count == 0){?>
		    <p class="auction-bid"><?php echo apply_filters('starting_bid_text', __( 'Starting bid:', 'wc_simple_auctions' )); ?> <?php echo get_woocommerce_currency_symbol(); ?><span class="starting-bid amount"> <?php echo $product->get_curent_bid(); ?></span> </p>
		<?php } ?>
		
		<?php if ($product->auction_bid_count > 0){?>
		    <p class="auction-bid"><?php echo apply_filters('curent_bid_text', __( 'Current bid:', 'wc_simple_auctions' )); ?> <?php echo get_woocommerce_currency_symbol(); ?><span class="curent-bid amount"> <?php echo $product->get_curent_bid(); ?></span> <span class="number-of-bids">[<?php echo $product->auction_bid_count; ?> bids]</span></p>
		<?php } ?>
		
		<?php if(($product->is_reserved() === TRUE) &&( $product->is_reserve_met() === FALSE )  ) : ?>
			<p class="reserve hold"><?php echo apply_filters('reserve_bid_text', __( "Reserve price has not been met", 'wc_simple_auctions' )); ?></p>
		<?php endif; ?>	
		
		<?php if(($product->is_reserved() === TRUE) &&( $product->is_reserve_met() === TRUE )  ) : ?>
			<p class="reserve free"><?php echo apply_filters('reserve_met_bid_text', __( "Reserve price has been met", 'wc_simple_auctions' )); ?></p>
		<?php endif; ?>
		
		<?php if($product->auction_type == 'reverse' ) : ?>
			<p class="reverse"><?php echo apply_filters('reverse_auction_text', __( "This is reverse auction.", 'wc_simple_auctions' )); ?></p>
		<?php endif; ?>	
		<?php do_action('woocommerce_before_bid_form'); ?>
		<form class="auction_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>">
			
			<?php do_action('woocommerce_before_bid_button'); ?>
			
			<input type="hidden" name="bid" value="<?php echo esc_attr( $product->id ); ?>" />	
			<?php if($product->auction_type == 'reverse' ) : ?>
				
			<input type="number" name="bid_value" value="<?php echo $product->bid_value() ?>" max="<?php echo $product->bid_value()  ?>"  step="<?php echo ($product->auction_bid_increment) ? $product->auction_bid_increment : '0.01' ?>" size="<?php echo strlen($product->get_curent_bid())+2 ?>" title="bid"  class="input-text  bid text left">
		 	<button type="submit" class="bid_button button alt"><?php echo apply_filters('bid_text', __( 'Bid', 'wc_simple_auctions' ), $product->product_type); ?></button>
		 		
			<?php else : ?>	
		 			 	
			<input type="number" name="bid_value" value="<?php echo $product->bid_value()  ?>" min="<?php echo $product->bid_value()  ?>"  step="<?php echo ($product->auction_bid_increment) ? $product->auction_bid_increment : '0.01' ?>" size="<?php echo strlen($product->get_curent_bid())+2 ?>" title="bid"  class="input-text  bid text left">
		 	<button type="submit" class="bid_button button alt"><?php echo apply_filters('bid_text', __( 'Bid', 'wc_simple_auctions' ), $product->product_type); ?></button>
		 	<?php endif; ?>
		 	
		 	<input type="hidden" name="place-bid" value="<?php echo $product->id; ?>" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
			<?php do_action('woocommerce_after_bid_button'); ?>
		</form>
		<?php do_action('woocommerce_after_bid_form'); ?>
	</div>			 	

<?php elseif (($product->is_closed() === FALSE ) and ($product->is_started() === FALSE )):?>
	
	<div class="auction-time" id="countdown"><?php echo apply_filters('auction_starts_text', __( 'Auction starts in:', 'wc_simple_auctions' ), $product->product_type); ?> 
		<div class="auction-time-countdown" data-time="<?php echo $product->get_seconds_to_auction() ?>" data-format="<?php echo get_option( 'simple_auctions_countdown_format' ) ?>"></div>
	</div>
	
	<p class="auction-end"><?php echo apply_filters('time_text', __( 'Auction start:', 'wc_simple_auctions' ), $product->product_type); ?> <?php echo $product->get_auction_start_time(); ?></p>
	<p class="auction-end"><?php echo apply_filters('time_text', __( 'Auction ends:', 'wc_simple_auctions' ), $product->product_type); ?> <?php echo $product->get_auction_end_time(); ?></p>
	
<?php endif; ?>


	



