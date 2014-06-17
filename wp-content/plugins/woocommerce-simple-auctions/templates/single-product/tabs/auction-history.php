<?php
/**
 * Auction history tab
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $post, $product;

$heading = esc_html( apply_filters('woocommerce_auction_history_heading', __( 'Auction History', 'wc_simple_auctions' ) ) );

?>

<h2><?php echo $heading; ?></h2>


<?php if(($product->is_closed() === TRUE ) and ($product->is_started() === TRUE )) : ?>
    
	<p><?php _e('Auction has finished', 'wc_simple_auctions') ?></p>
	<?php if ($product->auction_fail_reason == '1'){
		 _e('Auction failed because there were no bids', 'wc_simple_auctions');
	} elseif($product->auction_fail_reason == '2'){
		_e('Auction failed because item did not make it to reserve price', 'wc_simple_auctions');
	}
	
	if($product->auction_closed == '3'){?>
		<p><?php _e('Product sold for buy now price', 'wc_simple_auctions') ?>: <span><?php echo $product->regular_price.get_woocommerce_currency_symbol() ?></span></p>
	<?php }elseif($product->auction_current_bider){ ?>
		<p><?php _e('Highest bidder was', 'wc_simple_auctions') ?>: <span><?php echo get_userdata($product->auction_current_bider)->display_name ?></span></p>
	<?php } ?>
						
<?php endif; ?>	


<?php if($auction_history = apply_filters('woocommerce__auction_history_data', $product->auction_history())): ?>
<table>
	<thead>
		<tr>
			<th><?php _e('Date', 'wc_simple_auctions') ?></th>
			<th><?php _e('Bid', 'wc_simple_auctions') ?></th>
			<th><?php _e('User', 'wc_simple_auctions') ?></th>
			<th><?php _e('Auto', 'wc_simple_auctions') ?></th>
		</tr>
	</thead>
	
	<?php foreach ($auction_history as $history_value) {
		echo "<tr>";
		echo "<td class='date'>$history_value->date</td>";
		echo "<td cllass='bid'>$history_value->bid</td>";
		echo "<td cllass='username'>".get_userdata($history_value->userid)->display_name."</td>";
		if ($history_value->proxy == 1)
			echo " <td class='proxy'>".__('Auto', 'wc_simple_auctions')."</td>";
		else 
			echo " <td class='proxy'></td>";
		echo "</tr>";
	}?>
	<tr class="start">
		<?php if ($product->is_started() === TRUE ){
			echo '<td class="date">'.$product->get_auction_start_time().'</td>'; 
			echo '<td colspan="3" class="started">';
			echo apply_filters('auction_history_started_text', __( 'Auction started', 'wc_simple_auctions' ), $product->product_type);
			echo '</td>';
			
		} else {
			echo '<td  class="date">'.$product->get_auction_start_time().'</td>'; 
			echo '<td colspan="3"  class="starting">';
			echo apply_filters('auction_history_starting_text', __( 'Auction starting', 'wc_simple_auctions' ), $product->product_type);
			echo '</td>' ;
		}?>
	</tr>
</table>
<?php endif;?>