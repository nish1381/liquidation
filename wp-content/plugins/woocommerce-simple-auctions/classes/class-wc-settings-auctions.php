<?php
/**
 * WooCommerce Account Settings
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (  class_exists( 'WC_Settings_Page' ) ) :

/**
 * WC_Settings_Accounts
 */
class WC_Settings_Simple_Auctions extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'simple_auctions';
		$this->label = __( 'Auctions', 'wc_simple_auctions' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters( 'woocommerce_' . $this->id . '_settings', array(

			array(	'title' => __( 'Simple auction options', 'wc_simple_auctions' ), 'type' => 'title','desc' => '', 'id' => 'simple_auction_options' ),
                                        array(
											'title' 			=> __( 'Past auctions', 'wc_simple_auctions' ),
											'desc' 			=> __( 'Show finished auctions.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_finished_enabled',
											'default' 			=> 'no'											
										),
										array(
											'title' 			=> __( 'Future auctions', 'wc_simple_auctions' ),
											'desc' 			=> __( 'Show auctions that did not start yet.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_future_enabled',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> __( "Do not show auctions on shop page", 'wc_simple_auctions' ),
											'desc' 			=> __( 'Do not mix auctions and regular products on shop page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_shop',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> __( "Do not show auctions on product category page", 'wc_simple_auctions' ),
											'desc' 			=> __( 'Do not mix auctions and regular products on product category page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_cat',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> __( "Do not show auctions on product tag page", 'wc_simple_auctions' ),
											'desc' 			=> __( 'Do not mix auctions and regular products on product tag page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_tag',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> __( "Countdown format", 'wc_simple_auctions' ),
											'desc'				=> __( "The format for the countdown display. Default is yowdHMS", 'wc_simple_auctions' ),
											'desc_tip' 			=> __( "Use the following characters (in order) to indicate which periods you want to display: 'Y' for years, 'O' for months, 'W' for weeks, 'D' for days, 'H' for hours, 'M' for minutes, 'S' for seconds.

Use upper-case characters for mandatory periods, or the corresponding lower-case characters for optional periods, i.e. only display if non-zero. Once one optional period is shown, all the ones after that are also shown.", 'wc_simple_auctions' ),
											'type' 				=> 'text',
											'id'				=> 'simple_auctions_countdown_format',
											'default' 			=> 'yowdHMS'
										),
										array(
												'title' => __( 'Auctions Base Page', 'wc_simple_auctions' ),
												'desc' 		=> __( 'Set the base page for your auctions - this is where your auction archive will be.', 'wc_simple_auctions' ),
												'id' 		=> 'woocommerce_auction_page_id',
												'type' 		=> 'single_select_page',
												'default'	=> '',
												'class'		=> 'chosen_select_nostd',
												'css' 		=> 'min-width:300px;',
												'desc_tip'	=>  true
											),
										array( 'type' => 'sectionend', 'id' => 'simple_auction_options'),

		)); // End pages settings
	}
}
return new WC_Settings_Simple_Auctions();

endif;



