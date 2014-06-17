<?php
/**
 * WooCommerce bid class
 *
 * The WooCommerce bid class stores bid data and handles bidding process. *
 *
 * @class 		WC_Bid
 * @version		1.0.0
 * @category	Class
 * 
 */
class WC_Bid {
	public $bid;

	/**
	 * Constructor for the bid class. Loads options and hooks in the init method.
	 *
	 * @access public
	 * @return void
     * 
	 */
	public function __construct() {
		add_action('init', array($this, 'init'), 5);
	}

	/**
	 * Loads the bid data from the PHP session during WordPress init and hooks in other methods.
	 *
	 * @access public
	 * @return void
     * 
	 */
	public function init() {
	}

	/**
	 * Place bid
	 *
	 * @param string $product_id contains the id of the product to add to the cart
	 * @return bool
     * 
	 */
	public function placebid($product_id, $bid) {
		global $woocommerce;
		$this -> bid = $bid;

		$product_data = get_product($product_id);
		

		if (!$product_data)
			return false;
		
		if (!is_user_logged_in()) {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
				wc_add_notice(sprintf(__('Sorry, you must be logged in to making bid. <a href="%s" class="button">Login &rarr;</a>', 'wc_simple_auctions'), get_permalink(woocommerce_get_page_id('myaccount'))), 'error');
				
			} else {
				$woocommerce -> add_error(sprintf(__('Sorry, you must be logged in for making bid. <a href="%s" class="button">Login &rarr;</a>', 'wc_simple_auctions'), get_permalink(woocommerce_get_page_id('myaccount'))));
			}		
			return false;
		}


		// Check if product is_finished
		if ($product_data -> is_closed()) {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
				wc_add_notice(sprintf(__('Sorry, auction for &quot;%s&quot; is finished', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			} else {
				$woocommerce -> add_error(sprintf(__('Sorry, the auction for &quot;%s&quot; is finished', 'wc_simple_auctions'), $product_data -> get_title()));
			}	
			return false;
		}

		// Check if product is_started
		if (!$product_data -> is_started()) {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
				wc_add_notice(sprintf(__('Sorry, the auction for &quot;%s&quot; has not started yet', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			} else {
				$woocommerce -> add_error(sprintf(__('Sorry, the auction for &quot;%s&quot; has not started yet', 'wc_simple_auctions'), $product_data -> get_title()));
			}	
			return false;
		}

		// Stock check - only check if we're managing stock and backorders are not allowed
		if (!$product_data -> is_in_stock()) {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
				wc_add_notice(sprintf(__('You cannot a place bid for &quot;%s&quot; because the product is out of stock.', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			} else {
				$woocommerce -> add_error(sprintf(__('You cannot a place bid for &quot;%s&quot; because the product is out of stock.', 'wc_simple_auctions'), $product_data -> get_title()));
			}	
			return false;
		}

		$current_user = wp_get_current_user();
		$auction_type = $product_data -> auction_type;

		// Check if bid is needed
		if ($current_user -> ID == $product_data -> auction_current_bider) {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
				wc_add_notice(sprintf(__('No need to bid. Your bid is winning! ', 'wc_simple_auctions'), $product_data -> get_title()));
			} else {
				$woocommerce -> add_message(sprintf(__('No need to bid. Your bid is winning! ', 'wc_simple_auctions'), $product_data -> get_title()));
			}
				return false;
				
		}
        
		if ($auction_type == 'normal') {
			
			
			if ( $product_data->bid_value() <= ($this -> bid )) {

				// Check for proxy bidding
				if ($product_data -> auction_proxy) {

					if ($this -> bid > $product_data -> auction_max_bid) {

						if ($product_data -> auction_reserved_price && $product_data -> is_reserve_met() === FALSE) {

							if ($this -> bid > $product_data -> auction_reserved_price) {

								$curent_bid = $product_data -> auction_reserved_price;

    						} else {
    								$curent_bid = $this -> bid;
    						}

						} else {
							$curent_bid = $product_data -> get_curent_bid() + $product_data -> auction_bid_increment;
						}

						$outbiddeduser = $product_data -> auction_current_bider;
						update_post_meta($product_id, '_auction_max_bid', $this -> bid);
						update_post_meta($product_id, '_auction_max_current_bider', $current_user -> ID);
						update_post_meta($product_id, '_auction_current_bid', $curent_bid);
						update_post_meta($product_id, '_auction_current_bider', $current_user -> ID);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 1));
						$this -> log_bid($product_id, $curent_bid, $current_user, 0);
						do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser) );
                        
					} else {
					    
						$this -> log_bid($product_id, $this -> bid, $current_user, 0);
						$proxy_bid = $this -> bid + $product_data -> auction_bid_increment;
						update_post_meta($product_id, '_auction_current_bid', $proxy_bid);
						update_post_meta($product_id, '_auction_current_bider', $product_data -> auction_max_current_bider);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 2));
						$this -> log_bid($product_id, $proxy_bid, get_userdata($product_data -> auction_max_current_bider), 1);
						
						if (version_compare($woocommerce->version, '2.1',  ">=")){
							wc_add_notice(sprintf(__('You have been outbid', 'wc_simple_auctions'), $product_data -> get_title()),'error');
						} else{
							$woocommerce -> add_error(sprintf(__('Your bid has been outbidded', 'wc_simple_auctions'), $product_data -> get_title()));	
						}
						
					}

				} else {
				    $outbiddeduser = $product_data -> auction_current_bider;
					$curent_bid = $product_data -> get_curent_bid();
					update_post_meta($product_id, '_auction_current_bid', $this -> bid);
					update_post_meta($product_id, '_auction_current_bider', $current_user -> ID);
					update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 1));
					do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser) );
					$this -> log_bid($product_id, $this -> bid, $current_user);
				}

			} else {
			    if (version_compare($woocommerce->version, '2.1',  ">=")){
			    	wc_add_notice(sprintf(__('Your bid for &quot;%s&quot; is smaller than the current bid. Your bid must be at least %d%s ', 'wc_simple_auctions'), $product_data -> get_title(),$minimumbid, get_woocommerce_currency_symbol()));
				}else{	
					$woocommerce -> add_error(sprintf(__('Your bid for &quot;%s&quot; is smaller than the current bid. Your bid must be at least %d%s ', 'wc_simple_auctions'), $product_data -> get_title(),$minimumbid, get_woocommerce_currency_symbol()));
				}
				return false;
			}

		} elseif ($auction_type == 'reverse') {
			
			if ($product_data->bid_value()  >= $bid) {

				// Check for proxy bidding
				if ($product_data -> auction_proxy ) {
					
				    
					if (  $this -> bid < (int)$product_data -> auction_max_bid OR  !$product_data ->auction_max_bid) {

						if ($product_data -> auction_reserved_price && $product_data -> is_reserve_met() === FALSE) {

							 if ($this -> bid < $product_data -> auction_reserved_price) {

								$curent_bid = $product_data -> auction_reserved_price;

						      } else {
								$curent_bid = $this -> bid;
							 }

						} else {
							$curent_bid = $product_data -> get_curent_bid() - $product_data -> auction_bid_increment;
						}

						$outbiddeduser = $product_data -> auction_current_bider;
						update_post_meta($product_id, '_auction_max_bid', $this -> bid);
						update_post_meta($product_id, '_auction_max_current_bider', $current_user -> ID);
						update_post_meta($product_id, '_auction_current_bid', $curent_bid);
						update_post_meta($product_id, '_auction_current_bider', $current_user -> ID);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 1));
						$this -> log_bid($product_id, $curent_bid, $current_user, 0);
						do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser) );
                        
					} else {
					    
						
						$this -> log_bid($product_id, $this -> bid, $current_user, 0);
						$proxy_bid = $this -> bid - $product_data -> auction_bid_increment;
						update_post_meta($product_id, '_auction_current_bid', $proxy_bid);
						update_post_meta($product_id, '_auction_current_bider', $product_data -> auction_max_current_bider);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 2));
						$this -> log_bid($product_id, $proxy_bid, get_userdata($product_data -> auction_max_current_bider), 1);
						
						if (version_compare($woocommerce->version, '2.1',  ">=")){
							wc_add_notice(sprintf(__('Your bid has been outbidded', 'wc_simple_auctions'), $product_data -> get_title()),'error');
						} else{
							$woocommerce -> add_error(sprintf(__('You have been outbid', 'wc_simple_auctions'), $product_data -> get_title()));	
						}
						
					}

				} else {
				    $outbiddeduser = $product_data -> auction_current_bider;
					$curent_bid = $product_data -> get_curent_bid();
					update_post_meta($product_id, '_auction_current_bid', $this -> bid);
					update_post_meta($product_id, '_auction_current_bider', $current_user -> ID);
					update_post_meta($product_id, '_auction_bid_count', absint($product_data -> auction_bid_count + 1));
					$this -> log_bid($product_id, $this -> bid, $current_user);
					do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser) );
				}

			} else {
				if (version_compare($woocommerce->version, '2.1',  ">=")){
					wc_add_notice(sprintf(__('Your bid for &quot;%s&quot; is larger than the current bid', 'wc_simple_auctions'), $product_data -> get_title()),'error');
				} else {
					$woocommerce -> add_error(sprintf(__('Your bid for &quot;%s&quot; is larger than the current bid', 'wc_simple_auctions'), $product_data -> get_title()));
				}
					
				return false;
			}
		} else {
			if (version_compare($woocommerce->version, '2.1',  ">=")){
					wc_add_notice(sprintf(__('There was no bid', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			} else {
				$woocommerce -> add_error(sprintf(__('There was no bid', 'wc_simple_auctions'), $product_data -> get_title()));
			}

			return false;
		}

		do_action('woocommerce_simple_auctions_place_bid', $product_id);

		return true;
	}

    /**
     * Log bid
     *
     * @param string, int, int, int
     * @return void
     * 
     */
	public function log_bid($product_id, $bid, $current_user, $proxy = 0) {
		
		global $wpdb;
		$log_bid = $wpdb -> insert($wpdb -> prefix . 'simple_auction_log', array('userid' => $current_user -> ID, 'auction_id' => $product_id, 'bid' => $bid, 'proxy' => $proxy), array('%d', '%d', '%f', '%d'));
	}

}