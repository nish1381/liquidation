<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer Outbid Email
 *
 * Customer note emails are sent when you add a note to an order.
 *
 * @class 		WC_Email_SA_Outbid
 * @extends 	WC_Email
 */

class WC_Email_SA_Auction_Win extends WC_Email {

	
	/** @var string */
	var $winning_bid;

	/** @var string */
	var $title;

	/** @var string */
	var $auction_id;
	
	/** @var string */
	var $checkout_url;
	
	

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		global $woocommerce_auctions;

		$this->id 				= 'auction_win';
		$this->title 			= __( 'Auction Win', 'wc_simple_auctions' );
		$this->description		= __( 'Auction won emails are sent when a user wins the auction.', 'wc_simple_auctions' );

		$this->template_html 	= 'emails/auction_win.php';
		$this->template_plain 	= 'emails/plain/auction_win.php';
		$this->template_base	= $woocommerce_auctions->plugin_path. 'templates/';

		$this->subject 			= __( 'Auction won on {blogname}', 'wc_simple_auctions');
		$this->heading      	= __( 'You have won the auction!', 'wc_simple_auctions');
		
		$this->checkout_url 	= simple_auction_get_checkout_url();

		// Triggers
		//die(); 
		add_action( 'woocommerce_simple_auction_won_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $product_id ) {
		global $woocommerce;
		
		
		if ( $product_id ) {
				$product_data = get_product(  $product_id );
		
				$customer_user = absint( get_post_meta( $product_id, '_auction_current_bider', true ) );
			
				if ( $product_data ) {
					if ( $customer_user ) {
							$this->object 		= new WP_User( $customer_user );
							$this->recipient	= $this->object->user_email;
							
					}
						
					$this->auction_id = $product_id;
					$this->winning_bid = $product_data->get_curent_bid();		
				}
			
		}	
		
		
		
		if ( ! $this->is_enabled() || ! $this->get_recipient() )
			return;
		
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		woocommerce_get_template( 	$this->template_html, array(
				'email_heading' 		=> $this->get_heading(),
				'blogname'				=> $this->get_blogname(),
				'current_bid' 			=> $this->winning_bid,
				'product_id'			=> $this->auction_id,
				'checkout_url'			=> $this->checkout_url 
			) );
		
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		woocommerce_get_template( $this->template_plain, array(
				'email_heading' 		=> $this->get_heading(),
				'blogname'				=> $this->get_blogname(),
				'current_bid' 			=> $this->winning_bid,
				'product_id'			=> $this->auction_id,
				'checkout_url'			=> $this->checkout_url 
				
			) );
		return ob_get_clean();
	}
}