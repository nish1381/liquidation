<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Auction Product Class
 *
 * @class WC_Product_Auction
 * 
 */ 
class WC_Product_Auction extends WC_Product {
	
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $product
     * 
	 */
	public function __construct( $product ) {
		$this->product_type = 'auction';
		$this->auction_item_condition_array = array(
			'new' => 'New',
			'used'=> 'Used');
		parent::__construct( $product );		
		$this->is_closed();	
	}
		
	/**
	 * Checks if a product is auction
	 *
	 * @access public
	 * @return bool
     * 
	 */	 
	function is_auction() {
		return $this->product_type == 'auction' ? true : false;
	}

	/**
	 * Get current bid
	 *
	 * @access public
	 * @return int
     * 
	 */	 
	function get_curent_bid() {
		if($this->product_type == 'auction' ){
			if ($this->auction_current_bid){
				return $this->auction_current_bid;
			}
			return $this->auction_start_price;
		}
	}

    /**
     * Get bid increment
     *
     * @access public
     * @return mixed
     * 
     */  	
	function get_increase_bid_value() {
		if($this->product_type == 'auction' ){
			if ($this->auction_bid_increment){
				return $this->auction_bid_increment;
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Get auction condition
     *
     * @access public
     * @return mixed
     * 
     */      
	function get_condition() {
		if($this->product_type == 'auction' ){
			if ($this->auction_item_condition){
				return $this->auction_item_condition_array[$this->auction_item_condition];
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Get auction end time
     *
     * @access public
     * @return mixed
     * 
     */      
	function get_auction_end_time() {
		if($this->product_type == 'auction' ){
			if ($this->auction_dates_to){
				return $this->auction_dates_to;
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Get auction start time
     *
     * @access public
     * @return mixed
     * 
     */     
	function get_auction_start_time() {
		if($this->product_type == 'auction' ){
			if ($this->auction_dates_to){
				return $this->auction_dates_from;
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Get remaining seconds till auction end
     *
     * @access public
     * @return mixed
     * 
     */      
	function get_seconds_remaining() {
		if($this->product_type == 'auction' ){
			if ($this->auction_dates_to){
				
				return strtotime($this->auction_dates_to)  -  (get_option( 'gmt_offset' )*3600);
				//return strtotime($this->auction_dates_to) - current_time('timestamp');
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Get seconds till auction starts
     *
     * @access public
     * @return mixed
     * 
     */      
	function get_seconds_to_auction() {
		if($this->product_type == 'auction' ){
			if ($this->auction_dates_from){
				return strtotime($this->auction_dates_from) - (get_option( 'gmt_offset' )*3600);					
				//return strtotime($this->auction_dates_from) - current_time('timestamp');
			} else {
				return FALSE;
			}			
		}
	}
    
    /**
     * Has auction started
     *
     * @access public
     * @return mixed
     * 
     */      
	function is_started() {
		if (isset($this->auction_dates_from) && $this->auction_dates_from ){
			$date1 = new DateTime($this->auction_dates_from);
			$date2 = new DateTime(current_time('mysql'));
			return ($date1 < $date2) ;				
		} else {
			return FALSE;
		}
	}
    
    /**
     * Does auction have reserve price
     *
     * @access public
     * @return bool
     * 
     */      
	function is_reserved() {
		if (isset($this->auction_reserved_price) && $this->auction_reserved_price){
			return TRUE;
		} else {
			return FALSE;
		}
	}		
    
    /**
     * Has auction met reserve price
     *
     * @access public
     * @return mixed
     * 
     */      
	function is_reserve_met() {
		if (isset($this->auction_reserved_price) && $this->auction_reserved_price){
			if($this->auction_type == 'reverse' ){
				return ( $this->auction_reserved_price >= $this->auction_current_bid);
			} else {
				return ( $this->auction_reserved_price <= $this->auction_current_bid);
			}
		}			
		return TRUE;
	}
    
    /**
     * Has auction finished
     *
     * @access public
     * @return mixed
     * 
     */      
	function is_finished() {		
		if (isset($this->auction_dates_to) && $this->auction_dates_to ){
			$date1 = new DateTime($this->auction_dates_to);
			$date2 = new DateTime(current_time('mysql'));
			return ($date1 < $date2) ;
			
		} else {
				return FALSE;
		}
	}
	
    /**
     * Is auction closed
     *
     * @access public
     * @return bool
     * 
     */      
	function is_closed() {
		
		if (isset($this->auction_closed)){
		    
				return TRUE;
            
		} else {
		    
			if ($this->is_finished() && $this->is_started() ){
				
				global $woocommerce, $product, $post;								
				
				if ( !$this->auction_current_bider && !$this->auction_current_bid){
					update_post_meta( $this->id, '_auction_closed', '1');
					update_post_meta( $this->id, '_auction_fail_reason', '1');
					$order_id = FALSE;
					do_action('woocommerce_simple_auction_close',  $this->id);
					do_action('woocommerce_simple_auction_fail', array('auction_id' => $this->id , 'reason' => __('There was no bid','wc_simple_auctions') ));
					return FALSE;
				}
				if ( $this->is_reserve_met() == FALSE){
					update_post_meta( $this->id, '_auction_closed', '1');
					update_post_meta( $this->id, '_auction_fail_reason', '2');
					$order_id = FALSE;
					do_action('woocommerce_simple_auction_close',  $this->id);
					do_action('woocommerce_simple_auction_reserve_fail', array('user_id' => $this->auction_current_bider,'product_id' => $this->id )); 
					do_action('woocommerce_simple_auction_fail', array('auction_id' => $this->id , 'reason' => __('The item didn\'t make it to reserve price','wc_simple_auctions') ));
					return FALSE;
				}
				update_post_meta( $this->id, '_auction_closed', '2');
				add_user_meta( $this->id, '_auction_win', $this->auction_current_bider);				
				do_action('woocommerce_simple_auction_close', $this->id);
				do_action('woocommerce_simple_auction_won', $this->id);
				
				return TRUE;
				
			} else {
			    
				return FALSE;
                
			}	
		}
	}
	
    /**
     * Get auction history
     *
     * @access public
     * @return object
     * 
     */     
	function auction_history() {			
		global $wpdb;
		if($this->auction_type == 'reverse' ){
			$history = $wpdb->get_results( 'SELECT * 	FROM '.$wpdb->prefix.'simple_auction_log  WHERE auction_id =' . $this->id .' ORDER BY  `date` desc , `bid`  asc  ');
		} else {			
			$history = $wpdb->get_results( 'SELECT * 	FROM '.$wpdb->prefix.'simple_auction_log  WHERE auction_id =' . $this->id .' ORDER BY  `date` desc , `bid`  desc  ');
		}	
		return $history;
	}
	
	/**
	 * Returns price in html format.
	 *
	 * @access public
	 * @param string $price (default: '')
	 * @return string
     * 
	 */
	public function get_price_html( $price = '' ) {		
		if ($this->is_closed() && $this->is_started() ){
			if ($this->auction_closed == '3')
				$price = __('<span class="sold-for auction">Sold for</span>: ','wc_simple_auctions').woocommerce_price($this->get_price());
			else
				$price = __('<span class="winned-for auction">Winning bid:</span> ','wc_simple_auctions').woocommerce_price($this->auction_current_bid);
			
		} elseif(!$this->is_started()){
			$price = __('<span class="starting auction">Starting bid:</span> ','wc_simple_auctions').woocommerce_price($this->get_curent_bid());
		} else {
			$price = __('<span class="current auction">Current bid:</span> ','wc_simple_auctions').woocommerce_price($this->get_curent_bid());
		}			
		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}
	
	/**
	 * Returns product's price.
	 *
	 * @access public
	 * @return string
     * 
	 */
	function get_price() {
		
		if ($this->is_closed()){
			
			if ($this->auction_closed == '3'){
				return apply_filters( 'woocommerce_get_price', $this->regular_price, $this );
			}
			if ($this->is_reserve_met()) {
				
				return apply_filters( 'woocommerce_get_price', $this->auction_current_bid, $this );
			}
		}	
		return apply_filters( 'woocommerce_get_price', $this->price, $this );
	}
	
	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_url() {
		return apply_filters( 'woocommerce_product_add_to_cart_url', get_permalink( $this->id ), $this );
	}

	/**
	 * Get the add to cart button text
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {
		if (!$this->is_finished() && $this->is_started() ){
					$text = __( 'Bid now', 'wc_simple_auctions' ) ;
				} elseif($this->is_finished()  ){
					$text = __( 'Auction finished', 'wc_simple_auctions' ) ;
				} elseif(!$this->is_finished() && !$this->is_started()  ){
					$text =  __( 'Auction not started', 'wc_simple_auctions' ) ;
				} 

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}
	
	/**
	 * Get the bid value
	 *
	 * @access public
	 * @return string
	 */
	public function bid_value() {
		$auction_bid_increment = ($this->auction_bid_increment) ? $this->auction_bid_increment : 1;
		
		
		if((int)$this->auction_bid_count == '0'   ){
			return $this->get_curent_bid();
		} else  {
			if($this->auction_type == 'reverse' ){
				return $this->get_curent_bid() - $auction_bid_increment;
			}else{
				return $this->get_curent_bid() + $auction_bid_increment;
			}
		}
		
		return FALSE;
	}
}