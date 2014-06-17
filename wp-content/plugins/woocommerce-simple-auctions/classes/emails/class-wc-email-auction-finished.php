<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin auction finished
 *
 * Admin email sent when auction is finished
 *
 * @class 		WC_Email_SA_Auction_Finished
 * @extends 	WC_Email
 */

class WC_Email_SA_Auction_Finished extends WC_Email {

	

	/** @var string */
	var $title;

	/** @var string */
	var $auction_id;
	
	/** @var string */
	var $winning_bid;
	
	
	//** @var object */
	var $winning_user;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		global $woocommerce_auctions;
		
		$this->id 				= 'auction_finished';
		$this->title 			= __( 'Auction Finish', 'wc_simple_auctions' );
		$this->description		= __( 'Auction finish emails are sent to admin when auction finish.', 'wc_simple_auctions' );

		$this->template_html 	= 'emails/auction_finish.php';
		$this->template_plain 	= 'emails/plain/auction_finish.php';
		$this->template_base	= $woocommerce_auctions->plugin_path.  'templates/';

		$this->subject 			= __( 'Auction Finished on {blogname}', 'wc_simple_auctions');
		$this->heading      	= __( 'Auction Finished!', 'wc_simple_auctions');

		// Triggers
		//die(); 
		add_action( 'woocommerce_simple_auction_close_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();
		
		// Other settings
		$this->recipient = $this->get_option( 'recipient' );

		if ( ! $this->recipient )
			$this->recipient = get_option( 'admin_email' );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $auction_id ) {
		global $woocommerce;
		
		if ( $auction_id ) {
											
			$this->auction_id = $auction_id;
						
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
				'product_id'			=> $this->auction_id,
				
				
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
				'product_id'			=> $this->auction_id,
				
				
			) );
		return ob_get_clean();
	}
}