<?php
/*
 * Plugin Name: WooCommerce Simple Auction
 * Plugin URI: http://www.wpgenie.org/woocommerce-simple-auctions/
 * Description: Easily extend WooCommerce with auction features and functionalities. 
 * Version: 1.0.8
  * Author: wpgenie
 * Author URI: http://www.wpgenie.org/
 * Requires at least: 3.3
 * Tested up to: 3.8
 *
 * Text Domain: wc_simple_auctions
 * Domain Path: /lang/
 * 
 * Copyright: 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );


// Required minimum version of WordPress.
if(!function_exists('woo_simple_auction_required')){
	function woo_simple_auction_required(){
		global $wp_version;
		$plugin = plugin_basename(__FILE__);
		$plugin_data = get_plugin_data(__FILE__, false);

		if(version_compare($wp_version, "3.3", "<")){
			if(is_plugin_active($plugin)){
				deactivate_plugins($plugin);
				wp_die("'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress Admin</a>.");
			}
		}
	}
	add_action('admin_init', 'woo_simple_auction_required');
}

// Checks if the WooCommerce plugins is installed and active.
if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){

	/* Localisation */
	$locale = apply_filters('plugin_locale', get_locale(), 'wc_simple_auctions');
	load_textdomain('wc_simple_auctions', WP_PLUGIN_DIR."/".plugin_basename(dirname(__FILE__)).'/lang/wc_simple_auctions-'.$locale.'.mo');
	load_plugin_textdomain('wc_simple_auctions', false, dirname(plugin_basename(__FILE__)).'/lang/');

	if(!class_exists('WooCommerce_simple_auction')){
		class WooCommerce_simple_auction{

			public  $plugin_prefix;
			public  $plugin_url;
			public  $plugin_path;
			public  $plugin_basefile;
			public  $auction_types;
			public  $auction_item_condition;
			private $tab_data = false;				
			public 	$bid;
			public 	$emails;			
			

			/**
			 * Gets things started by adding an action to initialize this plugin once
			 * WooCommerce is known to be active and initialized
             * 			 
			 */
			public function __construct(){
				$this->plugin_prefix    = 'wc_simple_auctions';
				$this->plugin_basefile  = plugin_basename(__FILE__);
				$this->plugin_url       = plugin_dir_url($this->plugin_basefile);
				$this->plugin_path      = trailingslashit(dirname(__FILE__));
				
				
				$this->auction_types            = array( 'normal'  => 'Normal', 'reverse' =>'Reverse' );
				$this->auction_item_condition   = array( 'new' => 'New', 'used'=> 'Used' );
				
				add_action('woocommerce_init', array(&$this, 'init'));
				require_once( ABSPATH .'wp-includes/pluggable.php');				
			}
			
			/**
			 * Run plugin installation
			 * WooCommerce is known to be active and initialized	
             * 		 
			 */
			public static function install(){
				
				global $wpdb;
				$data_table = $wpdb->prefix."simple_auction_log";
				$sql = " CREATE TABLE IF NOT EXISTS $data_table (
  						`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						  `userid` bigint(20) unsigned NOT NULL,
						  `auction_id` bigint(20) unsigned DEFAULT NULL,
						  `bid` decimal(10,2) DEFAULT NULL,
						  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						  `proxy` tinyint(1) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						);";
				
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				wp_insert_term( 'auction', 'product_type' );
				wp_schedule_event( time(), 'twicedaily', 'simple_auction_send_reminders_email' );
				
				if (get_option( 'simple_auctions_finished_enabled') == FALSE)
					add_option( 'simple_auctions_finished_enabled', 'no');
				
				if (get_option( 'simple_auctions_future_enabled') == FALSE)
					add_option( 'simple_auctions_future_enabled', 'yes');
				
				if (get_option( 'simple_auctions_dont_mix_shop') == FALSE)
					add_option( 'simple_auctions_dont_mix_shop', 'yes');
					
				if (get_option( 'simple_auctions_dont_mix_shop') == FALSE)	
					add_option( 'simple_auctions_dont_mix_shop', 'yowdHMS');
				
			}
			
			/**
			 * Run plugin deactivation
			 * 
			 */
			 public static function deactivation(){
			 	wp_clear_scheduled_hook('simple_auction_send_reminders_email' );
			 }

			/**
			 * Init WooCommerce Simple Auction plugin once we know WooCommerce is active
             * 
			 */
			public function init(){

				global $woocommerce;
				
				$this->includes();
				
				add_action('widgets_init', array( $this, 'register_widgets' ) );
				add_action('woocommerce_email',array($this, 'add_to_mail_class') );				
				add_filter('plugin_row_meta', array($this, 'add_support_link'), 10, 2);
				add_action('woocommerce_product_write_panel_tabs', array($this, 'product_write_panel_tab'));
				add_action('woocommerce_product_write_panels', array($this, 'product_write_panel'));
				add_filter('product_type_selector', array($this, 'add_product_type'));
				add_action('woocommerce_process_product_meta', array($this, 'product_save_data'), 80, 2);
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_script') );
				add_action('woocommerce_email',array($this, 'add_to_mail_class') );
				add_action('init', array($this, 'woocommerce_simple_auctions_place_bid') );
				add_action('init', array($this, 'simple_auctions_cron') );
				add_filter('woocommerce_locate_template',  array($this,'woocommerce_locate_template'), 10, 3 );
				add_filter('woocommerce_is_purchasable', array($this,'auction_is_purchasable'), 10, 2);
				add_action('woocommerce_email' , array($this,'add_to_mail_class'));
				add_action('woocommerce_order_status_changed', array($this,'auction_payed'),10,3);
				add_action('woocommerce_checkout_update_order_meta', array($this,'auction_order'),10,2);
				add_action("wp_ajax_finish_auction", array($this,"ajax_finish_auction"));
				add_action("wp_ajax_delete_bid", array($this,"wp_ajax_delete_bid"));
				add_action("woocommerce_duplicate_product", array($this,"woocommerce_duplicate_product"));
				
				if (is_admin()){
					add_filter('manage_product_posts_columns', array($this, 'woocommerce_simple_auctions_order_column_auction'));  
					add_action('manage_product_posts_custom_column',array($this,  'woocommerce_simple_auctions_order_column_auction_content'), 10, 2);
					add_filter('manage_edit-shop_order_columns', array($this, 'woocommerce_simple_auctions_order_column_auction'),20);  
					add_action('manage_shop_order_posts_custom_column',array($this,  'woocommerce_simple_auctions_order_column_auction_content'), 10, 2);
					if (version_compare($woocommerce->version, '2.1',  ">=")){
						add_filter('woocommerce_get_settings_pages', array($this, 'auction_settings_class') );
					} else{
						add_filter('woocommerce_settings_tabs_array',   array( $this, 'add_setting_tab' ) );
						add_filter('woocommerce_settings_tabs_simple_auctions',   array($this, 'add_settings_tab_content') ); 
						add_filter('woocommerce_update_options_simple_auctions',  array($this, 'update_settings_tab_content') );
					}
					add_filter('woocommerce_get_settings_pages', array($this, 'auction_settings_class') );
					add_action('add_meta_boxes',  array($this, 'woocommerce_simple_auctions_meta'));
					add_action('admin_notices',  array($this, 'woocommerce_simple_auctions_admin_notice'));
					add_action('admin_init',  array($this, 'woocommerce_simple_auctions_ignore_notices'));
					
					 if ( current_user_can( 'delete_posts' ) )
       						 add_action( 'delete_post', array($this,'del_auction_logs'),10 );
				}	  
				
 
 
 				// Classes / actions loaded for the frontend and for ajax requests
				if ( ! is_admin() || defined('DOING_AJAX') ) {
					$this->bid = new WC_bid();
					
					add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_script') );
					add_action('woocommerce_product_tabs', array($this, 'auction_tab'));
					add_action('woocommerce_product_tab_panels', array($this, 'auction_tab_panel'));
					add_action('woocommerce_product_is_visible', array($this, 'filter_auctions'), 10, 2);
					add_action('woocommerce_before_single_product', 'woocommerce__simple_auctions_winning_bid_message', 1);
					add_action('woocommerce_after_shop_loop_item', array($this, 'add_pay_button'), 60);
					add_action('woocommerce_before_shop_loop_item_title', array($this, 'add_winning_bage'), 60);
					add_action('woocommerce_before_shop_loop_item_title', array($this, 'add_auction_bage'), 60);
					add_action('init',  array( $this, 'add_product_to_cart' ) );
					add_filter('template_include', array( $this,'auctions_page_template'), 99 );
					add_filter('body_class', array( $this, 'output_body_class' ) );
					add_action('woocommerce_product_query', array( $this, 'remove_auctions_from_woocommerce_product_query' ), 2 );
					add_filter('woocommerce_product_query', array( $this, 'pre_get_posts' ),10,2 );
					add_filter('pre_get_posts', array( $this, 'auction_arhive_pre_get_posts' ) );
					add_filter('posts_join', array( $this,'posts_join'), 10, 2 );
					add_filter('posts_where', array( $this,'posts_where'), 10, 2 );
					add_shortcode('woocommerce_simple_auctions_my_auctions', array( $this, 'shortcode_my_auctions' ) );
				}
				
				$email_actions = array( 'woocommerce_simple_auctions_outbid','woocommerce_simple_auction_won', 'woocommerce_simple_auction_fail','woocommerce_simple_auction_reserve_fail','woocommerce_simple_auction_pay_reminder','woocommerce_simple_auction_close_buynow','woocommerce_simple_auction_close');
				foreach ( $email_actions as $action ) add_action( $action, array( $woocommerce, 'send_transactional_email') );
			}

			/**
			 * Include WooCommerce Simple Auction files
			 * 
			 * @access public
			 * @return void
             * 
			 */
			public function includes(){
				require_once( 'classes/class-wc-product-auction.php' );
				require_once( 'classes/dashboard.php' );
				
				require_once( 'woocommerce-simple-auctions-functions.php' );
				
				$this->dashboard = new WooCommerce_simple_auction_Dashboard();				
				if ( defined('DOING_AJAX') )
					$this->ajax_includes();
				if ( ! is_admin() || defined('DOING_AJAX') )
					$this->frontend_includes();

			}
			/**
			 * Include required ajax files
			 *
			 * @access public
			 * @return void
             * 
			 */
			public function ajax_includes() {
				include_once( 'woocommerce-simple-ajax.php' ); // Ajax functions for admin and the front-end
			}
			
			
			/**
			 * Add to mail class
			 * 
			 * @access public
			 * @return object
             * 
			 */
			public function add_to_mail_class($emails){				
				
				include_once( 'classes/emails/class-wc-email-auction-wining.php' );
				include_once( 'classes/emails/class-wc-email-auction-failed.php' );
				include_once( 'classes/emails/class-wc-email-outbid-note.php' );
				include_once( 'classes/emails/class-wc-email-customer-reserve-failed.php' );
				include_once( 'classes/emails/class-wc-email-auction-reminde-to-pay.php' );
				include_once( 'classes/emails/class-wc-email-auction-buy-now.php' );
				include_once( 'classes/emails/class-wc-email-auction-finished.php' );
				$emails->emails['WC_Email_SA_Outbid_Note'] = new WC_Email_SA_Outbid_Note();
				$emails->emails['WC_Email_SA_Auction_Win'] = new WC_Email_SA_Auction_Win();
				$emails->emails['WC_Email_SA_Reminde_to_pay'] = new WC_Email_SA_Auction_Reminde_to_pay();
				$emails->emails['WC_Email_SA_Auction_Failed'] = new WC_Email_SA_Auction_Failed();
				$emails->emails['WC_Email_SA_Reserve_Failed'] = new WC_Email_SA_Auction_Reserve_Failed();
				$emails->emails['WC_Email_SA_Auction_Buy_Now'] = new WC_Email_SA_Auction_Buy_Now();
				$emails->emails['WC_Email_SA_Auction_Finished'] = new WC_Email_SA_Auction_Finished();
				return $emails;				
			}

			/**
			 * register_widgets function
			 *
			 * @access public
			 * @return void
             * 
			 */
			function register_widgets() {
				// Include - no need to use autoload as WP loads them anyway
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-featured-auctions.php' );
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-random-auctions.php' );
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-recent-auction.php' );
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-recently-auctions.php' );
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-ending-soon-auction.php' );
				include_once( 'classes/widgets/class-woocommerce-simple-auctions-widget-my-auctions.php' );
		
				// Register widgets
				register_widget( 'WC_SA_Widget_Recent_Auction' );
				register_widget( 'WC_SA_Widget_Featured_Auction' );
				register_widget( 'WC_SA_Widget_Random_Auction' );
				register_widget( 'WC_SA_Widget_Recently_Viewed_Auction' );
				register_widget( 'WC_SA_Widget_Ending_Soon_Auction' );
				register_widget( 'WC_SA_Widget_My_Auction' );
				
			}
 				
			/**
			 * Include required frontend files
             * 			 
			 * @access public
			 * @return void
             * 
			 */
			public function frontend_includes() {
				// Functions
				require_once( 'woocommerce-simple-auctions-templating.php' );
				require_once( 'woocommerce-simple-auctions-hooks.php' );
				// Classes
				require_once( 'classes/class-wc-bid.php' );
				require_once( 'classes/woocommerce-simple-auctions-shortcode-my-auctions.php' );
				require_once( 'classes/woocommerce-simple-auctions-shortcodes.php' );
				$this->shortcodes = new WC_Shortcode_Simple_Auction();	
			}
			
			/**
			 * Add link to plugin page
             * 
			 * @access public
			 * @param  array, string
			 * @return array
             * 
			 */
			public function add_support_link($links, $file){
				if(!current_user_can('install_plugins')){
					return $links;
				}
				if($file == $this->plugin_basefile){
					$links[] = '<a href="http://wpgenie.org/woocommerce-simple-auctions/documentation/" target="_blank">'.__('Docs', 'wc_simple_auctions').'</a>';
					$links[] = '<a href="http://codecanyon.net/user/wpgenie#contact" target="_blank">'.__('Support', 'wc_simple_auctions').'</a>';
					$links[] = '<a href="http://codecanyon.net/user/wpgenie/" target="_blank">'.__('More WooCommerce Extensions', 'wc_simple_auctions').'</a>';
				}
				return $links;
			}
            
			/**
			 * Add admin notice
             * 
			 * @access public
			 * @param  array, string
			 * @return array
             * 
			 */
			public function woocommerce_simple_auctions_admin_notice(){
				global $current_user;
				if ( current_user_can( 'manage_options' ) ) {
	        		$user_id = $current_user->ID;
					if(get_option('Woocommerce_simple_auction_cron_check') != "yes" && ! get_user_meta($user_id, 'cron_check_ignore_notice')){
						echo '<div class="updated">
					   	<p>'.sprintf (__('Woocommerce Simple Auction recommends that you set up a cron job to check finished: <b>%s/?auction-cron=check</b>. Set it to every minute| <a href="%s">Hide Notice</a>','wc_simple_auctions'),get_bloginfo('url'),add_query_arg( 'cron_check_ignore', '0' )).'</p>
						</div>';
					}
					if(get_option('Woocommerce_simple_auction_cron_mail') != "yes" && ! get_user_meta($user_id, 'cron_mail_ignore_notice')){
						echo '<div class="updated">
					   	<p>'.sprintf (__('Woocommerce Simple Auction recommends that you set up a cron job to send emails: <b>%s/?auction-cron=mail</b>. Set it every 2 hours | <a href="%s">Hide Notice</a>','wc_simple_auctions'),get_bloginfo('url'),add_query_arg( 'cron_mail_ignore', '0' )).'</p>
						</div>';
					}
				}	
			}			
            
			/**
			 * Add user meta to ignor notice about crons.
			 * @access public
             * 
			 */
			public function woocommerce_simple_auctions_ignore_notices(){
				global $current_user;
        		$user_id = $current_user->ID;
        		
		        /* If user clicks to ignore the notice, add that to their user meta */
		        if ( isset($_GET['cron_check_ignore']) && '0' == $_GET['cron_check_ignore'] ) {
		            add_user_meta($user_id, 'cron_check_ignore_notice', 'true', true);
		    	}				
				if ( isset($_GET['cron_mail_ignore']) && '0' == $_GET['cron_mail_ignore'] ) {
				 	add_user_meta($user_id, 'cron_mail_ignore_notice', 'true', true);
				}
											
			}
			

			/**
			 * Add product type
			 * @param array
			 * @return array
             * 
			 */
			public function add_product_type($types){
				$types[ 'auction' ] = __( 'Auction', 'wc_simple_auctions' );
				return $types;
			}

			/**
			 * Add admin script
			 * @access public
			 * @return void
             * 
			 */
			public function admin_enqueue_script($hook){
				global $post_type;
				 if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
	    			if( 'product' == get_post_type() ){
					    wp_register_script(
							'simple-auction-admin', 
							$this->plugin_url.'/js/simple-auction-admin.js', 
							array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker','timepicker-addon'),
							'1',
							true
						);	
						wp_localize_script( 'simple-auction-admin', 'SA_Ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'SA_nonce' => wp_create_nonce('SAajax-nonce') ));
				
						wp_enqueue_script( 'simple-auction-admin' ); 	
						 wp_enqueue_script(
							'timepicker-addon', 
							$this->plugin_url.'/js/jquery-ui-timepicker-addon.js', 
							array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
							'1',
							true
						);					
						wp_enqueue_style( 'jquery-ui-datepicker' );
					}	
				 }
				 wp_enqueue_style('simple-auction-admin', $this->plugin_url.'/css/admin.css');
			}
            
			/**
			 * Add frontend scripts
			 * @access public
			 * @return void
             * 
			 */
			public function frontend_enqueue_script(){
			    
				wp_localize_script( 'my_voter_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) ); 				
                
			    wp_enqueue_script( 'simple-auction-countdown', $this->plugin_url.'js/jquery.countdown.js', array('jquery'), '1', FALSE );
				
				wp_register_script('simple-auction-countdown-language', $this->plugin_url.'js/jquery.countdown.language.js', array('jquery','simple-auction-countdown'), '1', FALSE );
				
				$language_data = array( 'labels' =>array(
													'Years' => __('Years', 'wc_simple_auctions'),
													'Months' => __('Months', 'wc_simple_auctions'),
													'Weeks' => __('Weeks', 'wc_simple_auctions'),
													'Days' => __('Days', 'wc_simple_auctions'),
													'Hours' => __('Hours', 'wc_simple_auctions'),
													'Minutes' => __('Minutes', 'wc_simple_auctions'),
													'Seconds' => __('Seconds', 'wc_simple_auctions'),
													),
										'labels1' => array(
													'Year' => __('Year', 'wc_simple_auctions'),
													'Month' => __('Month', 'wc_simple_auctions'),
													'Week' => __('Week', 'wc_simple_auctions'),
													'Day' => __('Day', 'wc_simple_auctions'),
													'Hour' => __('Hour', 'wc_simple_auctions'),
													'Minute' => __('Minute', 'wc_simple_auctions'),
													'Second' => __('Second', 'wc_simple_auctions'),
													),
										'compactLabels'	=>	array(
													'y' => __('y', 'wc_simple_auctions'),
													'm' => __('m', 'wc_simple_auctions'),
													'w' => __('w', 'wc_simple_auctions'),
													'd' => __('d', 'wc_simple_auctions'),
														)
										);
                			
				wp_localize_script( 'simple-auction-countdown-language', 'data', $language_data);
				
				wp_enqueue_script( 'simple-auction-countdown-language' );
				
				wp_enqueue_script('simple-auction-modernizer', $this->plugin_url.'js/modernizr-latest.js', array('jquery'), '1', FALSE ); 
				wp_enqueue_script( 'jquery-ui-spinner' );					
				
				wp_register_script('simple-auction-frontend', $this->plugin_url.'js/simple-auction-frontend.js', array('jquery','simple-auction-countdown','simple-auction-modernizer', 'jquery-ui-spinner'), '1', FALSE );
				
				$custom_data = array( 'finished' => __('Auction has finished!', 'wc_simple_auctions'), 'gtm_offset' => get_option( 'gmt_offset' ) );
                
				wp_localize_script( 'simple-auction-frontend', 'data', $custom_data);
				
				wp_localize_script( 'simple-auction-frontend', 'SA_Ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
				
				wp_enqueue_script( 'simple-auction-frontend' ); 	
									
				wp_enqueue_style('simple-auction', $this->plugin_url.'css/frontend.css');
				
				
			}		

			/**
			 * Write the auction tab on the product view page for WooCommerce v2.0+
			 * In WooCommerce these are handled by templates.
			 * 
			 * @access public
			 * @param  array
			 * @return array
             * 
			 */
			public function auction_tab($tabs){
				global $product;
				if('auction' == $product->product_type){
					$tabs['simle_auction_history'] = array(
												'title'    => __('Auction history', 'wc_simple_auctions'),
												'priority' => 25,
												'callback' => array($this, 'auction_tab_callback'),
												'content'  =>'auction-history'
						);					
				}
				return $tabs;
			}			
			
			/**
			 * Auction call back from auction_tab
			 * 
			 * @access public
			 * @param  array
			 * @return void
             * 
			 */
			public function auction_tab_callback($tabs){
				woocommerce_get_template( 'single-product/tabs/auction-history.php' );
			}

			/**
			 * Adds a new tab to the Product Data postbox in the admin product interface
			 * 
			 * @return void
             * 
			 */
			public function product_write_panel_tab(){
				$tab_icon   = $this->plugin_url.'images/auction.png';
				$style      = 'padding:5px 5px 5px 28px; background-image:url('.$tab_icon.'); background-repeat:no-repeat; background-position:5px 7px;';
				$active_style = '';
				
				?>
				<style type="text/css">
				#woocommerce-product-data ul.product_data_tabs li.auction_tab a { <?php echo $style; ?> }
				<?php echo $active_style; ?>
				</style>
				<?php
				echo "<li class=\"auction_tab hide show_if_auction hide_if_grouped hide_if_external hide_if_variable hide_if_simple\"><a href=\"#auction_tab\">".__('Auction', 'wc_simple_auctions')."</a></li>";
			}
            
			/**
			 * Adds the panel to the Product Data postbox in the product interface
			 * 
			 * @return void
             * 
			 */
			public function product_write_panel(){
				global $post;
				
				// Pull the video tab data out of the database
				$tab_data = maybe_unserialize(get_post_meta($post->ID, 'woo_auction_tab', true));
				if(empty($tab_data)){
					$tab_data[] = array();
				}
				echo '<div id="auction_tab" class="panel woocommerce_options_panel">';
				woocommerce_wp_select( array( 'id' => '_auction_item_condition', 'label' => __( 'Item condition', 'wc_simple_auctions' ), 'options' => $this->auction_item_condition ) );
				woocommerce_wp_select( array( 'id' => '_auction_type', 'label' => __( 'Auction type', 'wc_simple_auctions' ), 'options' => $this->auction_types ) );
				woocommerce_wp_checkbox( array( 'id' => '_auction_proxy', 'wrapper_class' => '', 'label' => __('Proxy bidding?', 'wc_simple_auctions' ), 'description' => __( 'Enable proxy bidding', 'wc_simple_auctions' ) ) );
				woocommerce_wp_text_input( array( 'id' => '_auction_start_price', 'class' => 'wc_input_price short', 'label' => __( 'Start Price', 'wc_simple_auctions' ) . ' ('.get_woocommerce_currency_symbol().')', 'type' => 'number', 'custom_attributes' => array(
					'step' 	=> 'any',
					'min'	=> '0'
				) ) );
				woocommerce_wp_text_input( array( 'id' => '_auction_bid_increment', 'class' => 'wc_input_price short', 'label' => __( 'Bid increment', 'wc_simple_auctions' ) . ' ('.get_woocommerce_currency_symbol().')', 'type' => 'number', 'custom_attributes' => array(
					'step' 	=> 'any',
					'min'	=> '0'
				) ) );
				woocommerce_wp_text_input( array( 'id' => '_auction_reserved_price', 'class' => 'wc_input_price short', 'label' => __( 'Reserve price', 'wc_simple_auctions' ) . ' ('.get_woocommerce_currency_symbol().')', 'type' => 'number', 'custom_attributes' => array(
					'step' 	=> 'any',
					'min'	=> '0'
				),'desc_tip' => 'true', 
										'description' => __( 'A reserve price is the lowest price at which you are willing to sell your item. If you don’t want to sell your item below a certain price, you can set a reserve price. The amount of your reserve price is not disclosed to your bidders, but they will see that your auction has a reserve price and whether or not the reserve has been met. If a bidder does not meet that price, you are not obligated to sell your item. ', 'wc_simple_auctions' ) ) );
				woocommerce_wp_text_input( 
										array( 
										'id' => '_regular_price', 
										'class' => 'wc_input_price short', 
										'label' => __( 'Buy it now price', 'wc_simple_auctions' ) . ' ('.get_woocommerce_currency_symbol().')', 
										'type' => 'number', 
										'custom_attributes' => array('step' => 'any', 'min'	=> '0'),
										'desc_tip' => 'true', 
										'description' => __( 'Buy it now disappears when bid exceeds the Buy now price for normal auction, or is lower than reverse auction', 'wc_simple_auctions' ) 
				 ) );
				
				$auction_dates_from 	= ( $date = get_post_meta( $post->ID, '_auction_dates_from', true ) ) ?  $date  : '';
				$auction_dates_to 	= ( $date = get_post_meta( $post->ID, '_auction_dates_to', true ) ) ?  $date  : '';
								
				echo '	<p class="form-field auction_dates_fields">
							<label for="_auction_dates_from">' . __( 'Auction Dates', 'wc_simple_auctions' ) . '</label>
							<input type="text" class="short datetimepicker" name="_auction_dates_from" id="_auction_dates_from" value="' . $auction_dates_from . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'wc_simple_auctions' ) . ' YYYY-MM-DD HH:MM" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
							<input type="text" class="short datetimepicker" name="_auction_dates_to" id="_auction_dates_to" value="' . $auction_dates_to . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'wc_simple_auctions' ) . '  YYYY-MM-DD HH:MM" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
						</p>';
				echo "</div>";
			}

			/**
			 * Saves the data inputed into the product boxes, as post meta data
			 * 
			 * 
			 * @param int $post_id the post (product) identifier
			 * @param stdClass $post the post (product)
             * 
			 */
			public function product_save_data($post_id, $post){
				global $wpdb, $woocommerce, $woocommerce_errors;
				$product_type = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
				
				if (
				 $product_type == 'auction' ) {				 	
				 	update_post_meta( $post_id, '_manage_stock', 'yes'  );
					if(!isset($_POST['_stock']) OR $_POST['_stock'] =='')
				 		update_post_meta( $post_id, '_stock', '1'  );
				 	update_post_meta( $post_id, '_backorders', 'no'  );
					update_post_meta( $post_id, '_sold_individually', 'yes'  );
					update_post_meta( $post_id, '_auction_item_condition', stripslashes( $_POST['_auction_item_condition'] ) );
					update_post_meta( $post_id, '_auction_type', stripslashes( $_POST['_auction_type'] ) );
					update_post_meta( $post_id, '_auction_proxy', stripslashes( $_POST['_auction_proxy'] ) );
					update_post_meta( $post_id, '_auction_start_price', stripslashes( $_POST['_auction_start_price'] ) );
					update_post_meta( $post_id, '_auction_bid_increment', stripslashes( $_POST['_auction_bid_increment'] ) );
					update_post_meta( $post_id, '_auction_reserved_price', stripslashes( $_POST['_auction_reserved_price'] ) );
					update_post_meta( $post_id, '_regular_price', stripslashes( $_POST['_regular_price'] ) );
					update_post_meta( $post_id, '_auction_dates_from', stripslashes( $_POST['_auction_dates_from'] ) );
					update_post_meta( $post_id, '_auction_dates_to', stripslashes( $_POST['_auction_dates_to'] ) );
				}
			}
			
			/**
			 * Templating with plugin folder
			 * 
			 * @param int $post_id the post (product) identifier
			 * @param stdClass $post the post (product)
             * 
			 */	
			function woocommerce_locate_template( $template, $template_name, $template_path ) {
 
				  global $woocommerce;
				 
				  $_template = $template;
				  if ( ! $template_path ) $template_path = $woocommerce->template_url;
				  $plugin_path  = $this->plugin_path . 'templates/';				
				 
				  // Look within passed path within the theme - this is priority
				  $template = locate_template(
				    array(
				      $template_path . $template_name,
				      $template_name
				    )				 
				  );				 
				 
				  // Modification: Get the template from this plugin, if it exists
				  if ( ! $template && file_exists( $plugin_path . $template_name ) )
				    $template = $plugin_path . $template_name;
                  
				  // Use default template				 
				  if ( ! $template )				 
				    $template = $_template;				 
				 
				  // Return what we found
				  return $template;				 
			}
			
			/**
			 * Place bid action
			 *
			 * Checks for a valid request, does validation (via hooks) and then redirects if valid.
			 *
			 * @access public
			 * @param bool $url (default: false)
			 * @return void
             * 
			 */
			function woocommerce_simple_auctions_place_bid( $url = false ) {
			
				if ( empty( $_REQUEST['place-bid'] ) || ! is_numeric( $_REQUEST['place-bid'] ) )
					return;
				
				global $woocommerce;				
				
				$product_id          = apply_filters( 'woocommerce_place_bid_product_id', absint( $_REQUEST['place-bid'] ) );
				$bid         		 = apply_filters( 'woocommerce_place_bid_bid',  abs($_REQUEST['bid_value'])  );
				$was_place_bid   	 = false;
				$placed_bid       	 = array();
				$placing_bid     	 = get_product( $product_id );
				$place_bid_handler 	 = apply_filters( 'woocommerce_place_bid_handler', $placing_bid->product_type, $placing_bid );
				$quantity			 = 1;
                
				if ('auction' === $place_bid_handler ){
					
					// Place bid
		    		if ( $this->bid->placebid( $product_id,$bid) ) {
		    			woocommerce__simple_auctions_place_bid_message( $product_id );
		    			$was_place_bid = true;
		    			$placed_bid[] = $product_id;
		    		}
					if (version_compare($woocommerce->version, '2.1',  ">=")){
						
						if (wc_notice_count( 'error' ) == 0 ){
							wp_safe_redirect(  remove_query_arg( array( 'place-bid', 'quantity', 'product_id' ), wp_get_referer() )  );
							exit;
						}
						return;
						
					} else {
						wp_safe_redirect(  remove_query_arg( array( 'place-bid', 'quantity', 'product_id' ), wp_get_referer() )  );		
						exit;
					}
					
				} else {
					if (version_compare($woocommerce->version, '2.1',  ">=")){
						wc_add_notice(__( 'Item is not for auction', 'wc_simple_auctions' ),'error');
					} else {
						$woocommerce->add_error( __( 'Item is not for auction', 'wc_simple_auctions' ) );
					}					
					
            		return;					
				}												
			}

			/**
			 * Close auction action
			 *
			 * Checks for a valid request, does validation (via hooks) and then redirects if valid.
			 *
			 * @access public
			 * @param bool $url (default: false)
			 * @return void
             * 
			 */			
			public function add_product_to_cart() {
				
				if ( ! is_admin() ) {
					
					if ( ! empty( $_GET['pay-auction'] ) ) {
						
						global $woocommerce;
						$current_user = wp_get_current_user();
						
						$woocommerce->cart->empty_cart();
						$product_id = $_GET['pay-auction'];
						$product_data = get_product($product_id);
						
						if(!$product_data){
							wp_redirect( home_url() );
							exit;
						}
						if (!is_user_logged_in()) {
							header('Location: '.wp_login_url( $woocommerce->cart->get_checkout_url() .'?pay-auction='.$product_id ));
							exit;
						}
												
						if ($current_user -> ID != $product_data -> auction_current_bider) {
							$woocommerce -> add_error(sprintf(__('You can not buy this item because you did not win the auction! ', 'wc_simple_auctions'), $product_data -> get_title()));
							return false;
						}
                        				
						$woocommerce->cart->add_to_cart( $product_id );
                        
					}
				}
			}
            
			/**
			 * Is auction payable
			 *
			 * Checks for a valid user who have won auction
			 *
			 * @access public
			 * @param bool object (default: false)
			 * @return bool
             * 
			 */			
			function auction_is_purchasable( $is_purchasable, $object ) {
				
				if($object->product_type == 'auction' ){
					if (!$object -> auction_closed  && $object -> get_price() !== '') {
						
								return TRUE;
					}
					
					if (!is_user_logged_in()) {
								return false;
							}
					
					$current_user = wp_get_current_user();
					if ($current_user -> ID != $object -> auction_current_bider ) {
								return false;
					}
					
					if (!$object -> auction_closed ) {
								return false;
					}
					if ($object -> auction_closed != '2') {
								return false;
					}
					
					return TRUE;
				}	
				
				return $is_purchasable;
			}            
			
			/**
			 * Add auction column in product list in wp-admin 
			 *
			 * @access public
			 * @param array
			 * @return array
             * 
			 */
			function woocommerce_simple_auctions_order_column_auction($defaults){
			    $defaults['auction'] =  "<img src='".$this->plugin_url.'images/auction.png'."' alt='".__('Auction','wc_simple_auctions')."' />";
				return $defaults;
			}
			
			/**
			 * Add auction icons in product list in wp-admin  
			 *
			 * @access public
			 * @param string, string
			 * @return void
             * 
			 */
			function woocommerce_simple_auctions_order_column_auction_content($column_name, $post_ID) {
				
			    if ($column_name == 'auction') {
			    	$class ='';
					
			    	$product_data = get_product(  $post_ID );  
			       if($product_data->product_type == 'auction' ){
			       	if($product_data->is_closed())
			       		$class .= ' finished ';
					if($product_data->auction_fail_reason == '1')
						$class .= ' no_bid fail ';
					if($product_data->auction_fail_reason == '2')
						$class .= ' no_reserve fail';
					if($product_data->auction_closed == '3')
						$class .= ' sold ';
					if($product_data->auction_payed)
						$class .= ' payed ';
			       	echo "<img src='".$this->plugin_url.'images/auction-white.png'."' alt='".__('Auction','wc_simple_auctions')."' class='$class' />";
			       }
				   if ( get_post_meta( $post_ID , '_auction' , TRUE)){
				   		echo "<img src='".$this->plugin_url.'images/auction.png'."' alt='".__('Auction','wc_simple_auctions')."' class='order' />";
				   }				          
			    }  
			}
			
			/**
			 * Add settings tab in WooCommerce settings page
			 *
			 * @access public
			 * @param  array
			 * @return array
             * 
			 */
			function add_setting_tab ( $tabs ) {
				    $tabs[ 'simple_auctions' ] = __( 'Auctions', 'wc_simple_auctions' );
					
				    return $tabs;
			}

			/**
			 * Adding content in tab for WooCommerce settings page
			 *
			 * @access public
			 * @return void
             * 
			 */
			function add_settings_tab_content () {
			    woocommerce_admin_fields( $this->init_form_fields() );
			}
			
			/**
			 * Update content in tab for WooCommerce settings page
			 *
			 * @access public
			 * @return void
             * 
			 */
			function update_settings_tab_content () {
			   global $woocommerce_settings;
			    woocommerce_update_options( $this->init_form_fields() );
			}
			
			/**
			 *  WooCommerce settings content
			 *
			 * @return array (preferences array for woocommerce backend)
             * 
			 */
			function init_form_fields() {
				 $woocommerce_settings['simple_auctions'] = array(
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
                                       );
				return $woocommerce_settings['simple_auctions'];
			 }
			 
			 function auction_settings_class($settings){
			 		
			 	
			 		$settings[] = include( 'classes/class-wc-settings-auctions.php' );
			 	
			 	return $settings;
			 }
			
			/**
			 *  Filter auctions based on settings 
			 *
			 * @access public
			 * @param  bolean, string
			 * @return bolean
             * 
			 */
			function filter_auctions($visible, $product_id){
				global $product;
				if (!$product)
					return $visible;
				if ($product->product_type != 'auction')
					return $visible;
				
				$simple_auctions_finished_enabled = get_option( 'simple_auctions_finished_enabled' );
				$simple_auctions_future_enabled = get_option( 'simple_auctions_future_enabled' );
				$simple_auctions_dont_mix_shop = get_option( 'simple_auctions_dont_mix_shop' );
				
				if( $simple_auctions_future_enabled != 'yes'  && $visible == TRUE){
					
					$visible =  $product->is_started();
				}
				
				if( $simple_auctions_finished_enabled != 'yes' && $visible == TRUE){
					$visible = !$product->is_finished();
				}
				
				if( $product->auction_closed == '2'){
					$user_id  = get_current_user_id();	
					if($user_id == $product->auction_current_bider ){
						$visible = TRUE;
					}
						
				} 				
				return $visible;
			}			
			
			
 
			/**
			 *  Shortcode for my auctions
			 *
			 * @access public
			 * @param  array
			 * @return 
             * 
			 */
			function shortcode_my_auctions($atts) {
				global $woocommerce;
				return $woocommerce->shortcode_wrapper( array( 'WC_Shortcode_Simple_Auction_My_Auctions', 'output' ), $atts );
			} 				
			
			/**
			 *  Add meta box to the product editing screen
			 *
			 * @access public
             * 
			 */
			function woocommerce_simple_auctions_meta() {
				global $woocommerce, $post;
				$product_data = get_product(  $post->ID );
				if($product_data->product_type == 'auction'){
					add_meta_box('Auction', __( 'Auction', 'wc_simple_auctions' ), array($this,'woocommerce_simple_auctions_meta_callback'), 'product');
				}	
			} 
					
			/**
			 *  Callback for adding a meta box to the product editing screen used in woocommerce_simple_auctions_meta
			 *
			 * @access public
             * 
			 */
			function woocommerce_simple_auctions_meta_callback(){
				
				global $woocommerce, $post;
					$product_data = get_product(  $post->ID );
					$heading = esc_html( apply_filters('woocommerce_auction_history_heading', __( 'Auction History', 'wc_simple_auctions' ) ) );
					?>
					<?php if(($product_data->is_closed() === TRUE ) and ($product_data->is_started() === TRUE )) : ?>
						<p><?php _e('Auction has finished', 'wc_simple_auctions') ?></p>
						<?php if ($product_data->auction_fail_reason == '1'){
							 _e('Auction failed because there were no bids', 'wc_simple_auctions');
						} elseif($product_data->auction_fail_reason == '2'){
							_e('Auction failed because item did not make it to reserve price', 'wc_simple_auctions');
						}
						if($product_data->auction_closed == '3'){?>
						<p><?php _e('Product sold for buy now price', 'wc_simple_auctions') ?>: <span><?php echo $product_data->regular_price.get_woocommerce_currency_symbol() ?></span></p>
						<?php } elseif ($product_data->auction_current_bider){ ?>
						<p><?php _e('Higest bidder was', 'wc_simple_auctions') ?>: <span><a href='<?php get_edit_user_link($history_value->userid)?>'><?php echo get_userdata($product_data->auction_current_bider)->display_name ?></a></span></p>
						<p><?php _e('Higest bid was', 'wc_simple_auctions') ?>: <span><?php echo $product_data->get_curent_bid().get_woocommerce_currency_symbol()?></span></p>
						
						<?php if($product_data->auction_payed){ ?>
						<p><?php _e('Order has been paid, order ID is', 'wc_simple_auctions') ?>: <span><a href='post.php?&action=edit&post=<?php echo $product_data->order_id?>'><?php echo $product_data->order_id?></a></span></p>	
						<?php } elseif($product_data->order_id){
							$order_status = wp_get_post_terms( $product_data->order_id, 'shop_order_status' );
							if ( $order_status ) {
								$order_status = current( $order_status );
								$order_status = sanitize_title( $order_status->slug );
							} else {
								$order_status = sanitize_title( apply_filters( 'woocommerce_default_order_status', 'pending' ) );
							}
							
							?>
						<p><?php _e('Order has been made, order status is', 'wc_simple_auctions') ?>: <a href='post.php?&action=edit&post=<?php echo $product_data->order_id?>'><?php echo $order_status?></a><span>	
						<?php } ?>	
						<p></p>
						<?php } ?>
						<?php if($product_data->number_of_sent_mails){ 
								$dates_of_sent_mail 	= get_post_meta( $product_data->id, '_dates_of_sent_mails', FALSE );
							?>
							<p><?php _e('Number of sent reminder emails', 'wc_simple_auctions') ?>: <span> <?php echo $product_data->number_of_sent_mails ?></span></p>
							<p><?php _e('Last reminder mail was sent on', 'wc_simple_auctions') ?>: <span> <?php echo date('Y-m-d', end($dates_of_sent_mail)) ?></span></p>
							<p class="reminder-status"><?php _e('Reminder status', 'wc_simple_auctions') ?>: <?php if($product_data->stop_mails) {?><span class="error"><?php _e('Stopped', 'wc_simple_auctions') ?></span><?php } else {?><span class="ok"><?php _e('Running', 'wc_simple_auctions') ?></span><?php } ?> </p>
						<?php }?>	
						
					<?php endif; ?>	
					
					<h2><?php echo $heading; ?></h2>
					<table class="auction-table">
					<?php if($auction_history = apply_filters('woocommerce__auction_history_data', $product_data->auction_history())):?>
							
							<thead>
								<tr>
									<th><?php _e('Date', 'wc_simple_auctions') ?></th>
									<th><?php _e('Bid', 'wc_simple_auctions') ?></th>
									<th><?php _e('User', 'wc_simple_auctions') ?></th>
									<th><?php _e('Auto', 'wc_simple_auctions') ?></th>
									<th class="actions"><?php _e('Actions', 'wc_simple_auctions') ?></th>
								</tr>
							</thead>
							
							<?php foreach ($auction_history as $history_value) {
								echo "<tr>";
								echo "<td class='date'>$history_value->date</td>";
								echo "<td class='bid'>$history_value->bid</td>";
								echo "<td class='username'><a href='".get_edit_user_link($history_value->userid)."'>".get_userdata($history_value->userid)->display_name."</a></td>";
								if ($history_value->proxy == 1)
									echo " <td class='proxy'>".__('Auto', 'wc_simple_auctions')."</td>";
								else 
									echo " <td class='proxy'></td>";
								
								echo "<td class='action'> <a href='#' data-id='".$history_value->id."' data-postid='".$post->ID."'   >".__('Delete', 'wc_simple_auctions')."</a></td>";
								echo "</tr>";
								
								
							}?>
							<tr class="start">
								<?php if ($product_data->is_started() === TRUE ){
									echo '<td class="date">'.$product_data->get_auction_start_time().'</td>'; 
									echo '<td colspan="3"  class="started">';
									echo apply_filters('auction_history_started_text', __( 'Auction started', 'wc_simple_auctions' ), $product_data->product_type);
									echo '</td>';
									
								} else {
									echo '<td  class="date">'.$product_data->get_auction_start_time().'</td>'; 
									echo '<td colspan="3"  class="starting">';
									echo apply_filters('auction_history_starting_text', __( 'Auction starting', 'wc_simple_auctions' ), $product_data->product_type);
									echo '</td>' ;
								}?>
							</tr>
							
						<?php endif;?>
					</table>
					</ul><?php 
			}

			/**
			 *  Add pay button for auctions that user won
			 *
			 * @access public
             * 
			 */
			function add_pay_button(){
				if ( is_user_logged_in() ) 
					woocommerce_get_template( 'loop/pay-button.php' );
			}
            
			/**
             *  Add winning badge for auctions that current user is winning
			 *
			 * @access public
             * 
			 */
			function add_winning_bage(){
				if ( is_user_logged_in() ) 
					woocommerce_get_template( 'loop/winning-bage.php' );
			}

			/**
			 *   Add auction badge for auction product
			 *
			 * @access public
             * 
			 */
			function add_auction_bage(){				
					woocommerce_get_template( 'loop/auction-bage.php' );
			}
			
			/** 
			 * Get template for auctions archive page
			 * 
			 * @access public
			 * @param string
			 * @return string 
             * 
			 */
			function auctions_page_template( $template ) {
				
				if ( is_page( woocommerce_get_page_id('auction')  )  ) {
					remove_action('woocommerce_before_shop_loop',  'woocommerce_catalog_ordering', 30,0 );
					woocommerce_get_template('archive-product-auctions.php');
					return FALSE;
				}
			
				return $template;
			}
			
			/** 
			 * Output body classes for auctions archive page
			 * 
			 * @access public
			 * @param array
			 * @return array 
             * 
			 */
			function output_body_class($classes){
				if ( is_page( woocommerce_get_page_id('auction') )  ) {
					$classes [] = 'woocommerce auctions-page';
				}
				return $classes;
			}
			
			/** 
			 * Modify product query based on settings
			 * 
			 * @access public
			 * @param object
			 * @return object 
             * 
			 */
			function remove_auctions_from_woocommerce_product_query( $q){
				
				// We only want to affect the main query
				if ( ! $q->is_main_query() )
				return;
				
				if 	( ! $q->is_post_type_archive( 'product' ) && ! $q->is_tax( get_object_taxonomies( 'product' ) ) )
		   		return;
				
				$simple_auctions_dont_mix_shop = get_option( 'simple_auctions_dont_mix_shop' );
				$simple_auctions_dont_mix_cat = get_option( 'simple_auctions_dont_mix_cat' );
				$simple_auctions_dont_mix_tag = get_option( 'simple_auctions_dont_mix_tag' );
				
				
				if($simple_auctions_dont_mix_cat != 'yes' && is_product_category() )
					return;
				if($simple_auctions_dont_mix_tag != 'yes' && is_product_tag())
					return;
				
				$simple_auctions_dont_mix_shop = get_option( 'simple_auctions_dont_mix_shop' );
					
					if($simple_auctions_dont_mix_shop == 'yes'){
						$taxquery = $q->get('tax_query');	
						$taxquery []= 
					        array(
					            'taxonomy' => 'product_type',
					            'field' => 'slug',
					            'terms' => 'auction',
					            'operator'=> 'NOT IN'				        
					    );
						
				    $q->set( 'tax_query', $taxquery );
						
				}
			}
			
						
			
			/** 
			 * Modify query based on settings
			 * 
			 * @access public
			 * @param object
			 * @return object 
             * 
			 */
			function pre_get_posts($q){
			    
				$auction = array();
				$simple_auctions_finished_enabled   = get_option( 'simple_auctions_finished_enabled' );
				$simple_auctions_future_enabled     = get_option( 'simple_auctions_future_enabled' );
				$simple_auctions_dont_mix_shop 		= get_option( 'simple_auctions_dont_mix_shop' );
				$simple_auctions_dont_mix_cat 		= get_option( 'simple_auctions_dont_mix_cat' );
				$simple_auctions_dont_mix_tag 		= get_option( 'simple_auctions_dont_mix_tag' );

				$d = date("Y-m-d H:m");
				
				if( $simple_auctions_future_enabled != 'yes' && (!isset($q->query['show_future_auctions']) or !$q->query['show_future_auctions'] ) ){				
					
					$q->set('auction_meta_query_future', true);
				}
				
				if( $simple_auctions_finished_enabled != 'yes' && (!isset($q->query['show_past_auctions']) or !$q->query['show_past_auctions'] )){					
					
					$q->set('auction_meta_query_past', true);
				}
				
				
				if($simple_auctions_dont_mix_cat != 'yes' && is_product_category() )
					return;
				if($simple_auctions_dont_mix_tag != 'yes' && is_product_tag())
					return;
				
				if(!isset($q->query['auction_arhive'])){
					if($simple_auctions_dont_mix_shop == 'yes'){
							$taxquery = $q->get('tax_query');	
							$taxquery []= 
						        array(
						            'taxonomy' => 'product_type',
						            'field' => 'slug',
						            'terms' => 'auction',
						            'operator'=> 'NOT IN'				        
						    );
							
							$q->set( 'tax_query', $taxquery );
					
					}
				}
			}
			
			function auction_arhive_pre_get_posts($q){				
				if ( isset ( $q->query['auction_arhive']) OR (!isset ( $q->query['auction_arhive']) && (isset ( $q->query['post_type']) &&   $q->query['post_type'] =='product' && ! $q->is_main_query())) ) {
					$this->pre_get_posts($q);					
				}
			}
			
			/** 
			 * Modify post join to work with auctions
			 * 
			 * @access public
			 * @param object
			 * @return object 
             * 
			 */
			function posts_join( $join, $query ) {
					
				    if ( empty( $query->query_vars['auction_meta_query_future'] ) AND empty( $query->query_vars['auction_meta_query_past'] ) )
				        return $join;											
				
				    global $wpdb;
				
				    $new_join = "";
					if	(!empty( $query->query_vars['auction_meta_query_future'] ))		
				     		$new_join .= "    INNER  JOIN {$wpdb->postmeta} pm1 ON 1=1 AND pm1.post_id = {$wpdb->posts}.ID";
					if	(!empty( $query->query_vars['auction_meta_query_past'] ))		
				     		$new_join .= "    INNER   JOIN {$wpdb->postmeta} pm2 ON 1=1 AND pm2.post_id = {$wpdb->posts}.ID
				     		";
				          
				    return $join . ' ' . $new_join;
			}
			
			/** 
			 * Modify post where to work with auctions
			 * 
			 * @access public
			 * @param object
			 * @return object 
             * 
			 */
			function posts_where($where, $query){
				global $wpdb;
				
				if ( empty( $query->query_vars['auction_meta_query_future'] ) AND  empty( $query->query_vars['auction_meta_query_past'] )  )
				        return $where;
				
					$d = date("Y-m-d H:m");
					$term_id = get_term_by('name', 'auction', 'product_type')->term_id;
			 		$new_where ="AND ( ";
				if	(!empty( $query->query_vars['auction_meta_query_future'] ))	
			 			$new_where .=	"	(
			 							(pm1.meta_key = '_auction_dates_from' AND CAST(pm1.meta_value AS DATETIME) < '$d') 
			 							OR ( ".$wpdb -> prefix ."posts.ID NOT IN ( SELECT object_id FROM ".$wpdb -> prefix ."term_relationships WHERE term_taxonomy_id IN ($term_id) ))
									)"; 
					if ( !empty( $query->query_vars['auction_meta_query_future'] ) AND  !empty( $query->query_vars['auction_meta_query_past'] )  )				
									$new_where .= " AND "; 
					if	(!empty( $query->query_vars['auction_meta_query_past'] ))					
									$new_where .= " (
									(pm2.meta_key = '_auction_dates_to' AND CAST(pm2.meta_value AS DATETIME) > '$d')  
										OR ( ".$wpdb -> prefix ."posts.ID NOT IN ( SELECT object_id FROM ".$wpdb -> prefix ."term_relationships WHERE term_taxonomy_id IN ($term_id) ))
									)";
					$new_where .=	")";
					
				 return $where . ' '. $new_where;
			}

			/**
			 * Send reminders email with wp_schedule_event 
			 *
			 * @access public
			 * @return void
             * 
			 */
			function send_reminders_email(){
				global $woocommerce;
				$remind_to_pay_settings	= get_option( 'woocommerce_remind_to_pay_settings' );
				if ($remind_to_pay_settings['enabled'] != 'yes')
					exit();
				$interval = (isset($remind_to_pay_settings['interval'])) ? (int)$remind_to_pay_settings['interval'] : 7;
				$stopsending = (isset($remind_to_pay_settings['stopsending'])) ? (int)$remind_to_pay_settings['stopsending'] : 5;
				$args = array(
				'post_type' 		=> 'product',
				'posts_per_page' 	=> '100	',
				'meta_query' => array(
				       array(
				           'key' => '_auction_closed',
				           'value' => '2',
				       ),
				       array(
				           'key' => '_auction_payed',
				           'compare' => 'NOT EXISTS'
				       ),
				       array(
				           'key' => '_stop_mails',
				           'compare' => 'NOT EXISTS'
				       ),
				       array(
				           'key' => '_auction_payed',
				           'compare' => 'NOT EXISTS'
				       )					   
				   )
				);
			
				$the_query  = new WP_Query( $args );
				
				if ( $the_query->have_posts() ) {
					
					while ( $the_query->have_posts()): 
					    
					    $the_query->the_post() ;
    					$number_of_sent_mail 	= get_post_meta( $the_query->post->ID, '_number_of_sent_mails', true );
    					$dates_of_sent_mail 	= get_post_meta( $the_query->post->ID, '_dates_of_sent_mails', FALSE );
    					$n_days                 = (int) $remind_to_pay_settings['interval'];
                        
    					if ( (int) $number_of_sent_mail >= $stopsending ){
    					    
    						update_post_meta( $the_query->post->ID, '_stop_mails', '1' );
                            
    					} elseif( !$dates_of_sent_mail or ((int) end($dates_of_sent_mail) < strtotime('-'.$interval.' days'))) {
    					    
    						update_post_meta( $the_query->post->ID, '_number_of_sent_mails', $number_of_sent_mail+1 );	
    						add_post_meta( $the_query->post->ID, '_dates_of_sent_mails', time() , FALSE);							
    						do_action('woocommerce_simple_auction_pay_reminder', $the_query->post->ID);
                            
    					}
                        					  	
					endwhile;
					wp_reset_postdata();					
				}
			}

			/**
			 * Auction paid
			 *
			 * Checks for a auction product in order to verify that it is paid and assign order id to auction product and auction paid meta
			 *
			 * @access public
			 * @param int, string, string
			 * @return void
             * 
			 */
			function auction_payed($order_id,$old_status, $new_status){
				
				if($new_status != 'completed' )
					return FALSE;
				$order  = new WC_Order( $order_id );				
		
				if ( $order) {
					
					if ( $order_items =  $order->get_items()) {
						
						foreach ( $order_items as $item_id => $item ) {
							$item_meta 	= $order->get_item_meta( $item_id );
							$product_data = get_product($item_meta["_product_id"][0]);
							if($product_data->product_type == 'auction' ){
								update_post_meta( $item_meta["_product_id"][0], '_auction_payed', 1, true );
								update_post_meta( $item_meta["_product_id"][0], '_order_id', $order_id, true );
								update_post_meta( $item_meta["_product_id"][0], '_stop_mails', '1' );
							}									
						}	
					}
				}	
                
			}

			/**
			 * Auction order
			 *
			 * Checks for auction product in order and assign order id to auction product
			 *
			 * @access public
			 * @param int, array
			 * @return void
			 */
			function auction_order($order_id,$posteddata){
				$order  = new WC_Order( $order_id );
				
				if ( $order) {
				
					if ( $order_items =  $order->get_items()) {
				
						foreach ( $order_items as $item_id => $item ) {
						$item_meta 	= $order->get_item_meta( $item_id );
							$product_data = get_product($item_meta["_product_id"][0]);
							if($product_data->product_type == 'auction' ){
								update_post_meta( $order_id, '_auction', '1' );
								update_post_meta( $item_meta["_product_id"][0], '_order_id', $order_id, true );
								update_post_meta( $item_meta["_product_id"][0], '_stop_mails', '1' );
								if(!$product_data->is_finished()){
									update_post_meta( $item_meta["_product_id"][0], '_auction_closed', '3' );
									update_post_meta( $item_meta["_product_id"][0], '_buy_now', '1' );
									update_post_meta( $item_meta["_product_id"][0], '_auction_dates_to', date('Y-m-h h:s') );
									do_action('woocommerce_simple_auction_close_buynow',  $product_data->id);
									
								}
							}	
							
						}	
					}
				}			
			}

			/**
			 * Delete logs when auction is deleted
			 *
			 * @access public
			 * @param  string
			 * @return void
             * 
			 */
			function del_auction_logs( $post_id){
				global $wpdb;
			    if ( $wpdb->get_var( $wpdb->prepare( 'SELECT auction_id FROM '.$wpdb -> prefix .'simple_auction_log WHERE auction_id = %d', $post_id ) ) )
			        return $wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb -> prefix .'simple_auction_log WHERE auction_id = %d', $post_id ) );
			    
				return true;
			}
			
			/**
			 * Ajax finish auction
			 *
			 * Function for finishing auction with ajax when countdown is down to zero
			 *
			 * @access public
			 * @param  array
			 * @return string
             * 
			 */
			function ajax_finish_auction(){
				 
				if($_POST["post_id"]){
					$product_data = get_product($_POST["post_id"]);
					$product_data-> is_closed();
					if (isset($_POST["ret"]) && $_POST["ret"] !='0'){
						if($product_data->is_reserved()){
							if(!$product_data->is_reserve_met()){
								
								_e( "Reserve price has not been met", 'wc_simple_auctions' );
								die();
							}
						} 
						if($product_data->auction_current_bider){
							echo "<div>";
							printf(__( "Winning bid is %d%s by %s.", 'wc_simple_auctions' ), $product_data->get_curent_bid(), get_woocommerce_currency_symbol(), get_userdata($product_data->auction_current_bider)->display_name );
							echo "</div>";
						}	
					}
					
				}
				die();
				
			}
			
			/**
			 * Ajax delete bid
			 *
			 * Function for deleting bid in wp admin
			 *
			 * @access public
			 * @param  array
			 * @return string
             * 
			 */
			function wp_ajax_delete_bid(){
				global $wpdb;
				if ( !current_user_can('edit_product', $_POST["postid"]))  die ();
				
				if($_POST["postid"] && $_POST["logid"]){
					$product_data = get_product($_POST["postid"]);
					$log = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."simple_auction_log WHERE id=%d", $_POST["logid"]) );
					if(!is_null($log)){
						if($product_data -> auction_type == 'normal'){
							if(($log->bid == $product_data->auction_current_bid) && ($log->userid == $product_data->auction_current_bider)){
								$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."simple_auction_log WHERE id= %d", $_POST["logid"]) );
								$newbid =$wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."simple_auction_log WHERE auction_id =%d ORDER BY  `date` desc , `bid`  desc ", $_POST["postid"]) );
								if(!is_null($newbid)){
									update_post_meta($_POST["postid"], '_auction_current_bid', $newbid->bid);
									update_post_meta($_POST["postid"], '_auction_current_bider', $newbid -> userid);
									delete_post_meta($_POST["postid"], '_auction_max_bid');
									delete_post_meta($_POST["postid"], '_auction_max_current_bider');
									do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid, 'new_max_bider_id ' => $newbid -> userid));
								} else {
									delete_post_meta($_POST["postid"], '_auction_current_bid');
									delete_post_meta($_POST["postid"], '_auction_current_bider');
									do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid, 'new_max_bider_id ' => FALSE));
								}
								update_post_meta($_POST["postid"], '_auction_bid_count', absint($product_data -> auction_bid_count - 1));
								
									
								echo 'deleted';
								exit;
							} else{
								$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."simple_auction_log WHERE id= %d", $_POST["logid"]) );
								update_post_meta($_POST["postid"], '_auction_bid_count', absint($product_data -> auction_bid_count - 1));
								do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid));
								echo 'deleted';
								exit;
							}
							
						} elseif($product_data -> auction_type == 'reverse') {
							if(($log->bid == $product_data->auction_current_bid) && ($log->userid == $product_data->auction_current_bider)){
								$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."simple_auction_log WHERE id= %d", $_POST["logid"]) );
								$newbid =$wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."simple_auction_log WHERE auction_id =%d ORDER BY  `date` desc , `bid`  asc ", $_POST["postid"]) );
								if(!is_null($newbid)){
									update_post_meta($_POST["postid"], '_auction_current_bid', $newbid->bid);
									update_post_meta($_POST["postid"], '_auction_current_bider', $newbid -> userid);
									delete_post_meta($_POST["postid"], '_auction_max_bid');
									delete_post_meta($_POST["postid"], '_auction_max_current_bider');
									do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid, 'new_max_bider_id ' => $newbid -> userid));
								} else {
									delete_post_meta($_POST["postid"], '_auction_current_bid');
									delete_post_meta($_POST["postid"], '_auction_current_bider');
									do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid, 'new_max_bider_id ' => FALSE));
								}
								update_post_meta($_POST["postid"], '_auction_bid_count', absint($product_data -> auction_bid_count - 1));
								
								echo 'deleted';
								exit;
							} else{
								$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."simple_auction_log  WHERE id= %d", $_POST["logid"]) );
								update_post_meta($_POST["postid"], '_auction_bid_count', absint($product_data -> auction_bid_count - 1));
								do_action('simple_auction_delete_bid',  array( 'product_id' => $_POST["postid"] ,  'delete_user_id' => $log->userid));
								echo 'deleted';
								exit;
							}
							
						}
						echo 'failed';
						exit;
					}
					echo 'failed';
					exit;
				}
				echo 'failed';
				exit;
				
			}

			/**
			 * Duplicate post
			 *
			 * Clear metadata when copy auction
			 *
			 * @access public
			 * @param  array
			 * @return string
             * 
			 */
			 function woocommerce_duplicate_product($postid){
			 	$product = get_product($postid);
				if (!$product)
					return FALSE;
				if ($product->product_type != 'auction')
					return FALSE;
				delete_post_meta($postid, '_auction_current_bid');
				delete_post_meta($postid, '_auction_current_bider');
				delete_post_meta($postid, '_auction_max_bid');
				delete_post_meta($postid, '_auction_max_current_bider');
				delete_post_meta($postid, '_auction_bid_count');
				delete_post_meta($postid, '_auction_closed');
				delete_post_meta($postid, '_auction_fail_reason');
				delete_post_meta($postid, '_auction_dates_to');
				delete_post_meta($postid, '_auction_dates_from');
				delete_post_meta($postid, '_order_id');
				delete_post_meta($postid, '_stop_mails');
				
				return TRUE;
				
			 }

			/**
			 * Cron action
			 *
			 * Checks for a valid request, check auctions and closes auction if finished
			 *
			 * @access public
			 * @param bool $url (default: false)
			 * @return void
             * 
			 */
			function simple_auctions_cron( $url = false ) {
			
				if ( empty( $_REQUEST['auction-cron'] ) )
					return;
				if ($_REQUEST['auction-cron'] == 'check'){
					update_option('Woocommerce_simple_auction_cron_check','yes');
					set_time_limit(0);
	  				ignore_user_abort(1);
					global $woocommerce;
					$args = array(
    					'post_type' 		=> 'product',
    					'posts_per_page' 	=> '20	',
    					'meta_query' => array(
    					       array(
    					           'key' => '_auction_closed',
    					           'compare' => 'NOT EXISTS'
    					       ),
            					'meta_key' 	=> '_auction_dates_to',
            					'orderby'  	=> 'meta_value',
            					'order'  	=> 'ASC',
            					'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction'))
    					   )
					);
					for($i=0; $i<3; $i++) {
					$the_query  = new WP_Query( $args );
						$time=microtime(1);
						if ( $the_query->have_posts() ) {
							while ( $the_query->have_posts()): $the_query->the_post() ;
							if ($product_data->product_type == 'auction'){
								$product_data = get_product($the_query->post->ID);
								$product_data-> is_closed();
							}	
							endwhile;
						}
						$time=microtime(1)-$time;
						$i<3 and sleep(20-$time);
				  }
				}
				if ($_REQUEST['auction-cron'] == 'mails'){
					update_option('Woocommerce_simple_auction_cron_mail','yes');
					set_time_limit(0);
	  				ignore_user_abort(1);
					$remind_to_pay_settings	= get_option( 'woocommerce_remind_to_pay_settings' );
					if ($remind_to_pay_settings['enabled'] != 'yes')
						exit();
					$interval = (isset($remind_to_pay_settings['interval'])) ? (int)$remind_to_pay_settings['interval'] : 7;
					$stopsending = (isset($remind_to_pay_settings['stopsending'])) ? (int)$remind_to_pay_settings['stopsending'] : 5;
					$args = array(
					'post_type' 		=> 'product',
					'posts_per_page' 	=> '100	',
					'meta_query' => array(
					       array(
					           'key' => '_auction_closed',
					           'value' => '2',
					       ),
					       array(
					           'key' => '_auction_payed',
					           'compare' => 'NOT EXISTS'
					       ),
					       array(
					           'key' => '_stop_mails',
					           'compare' => 'NOT EXISTS'
					       ),
					       array(
					           'key' => '_auction_payed',
					           'compare' => 'NOT EXISTS'
					       )						   
					   )
					);
					
					$the_query  = new WP_Query( $args );
					
					if ( $the_query->have_posts() ) {
						
						while ( $the_query->have_posts()): 
						    
						    $the_query->the_post() ;
    						$number_of_sent_mail 	= get_post_meta( $the_query->post->ID, '_number_of_sent_mails', TRUE );
    						$dates_of_sent_mail 	= get_post_meta( $the_query->post->ID, '_dates_of_sent_mails', FALSE );
    						$n_days                 = (int) $remind_to_pay_settings['interval'];
                        
							if ( (int) $number_of_sent_mail >= $stopsending ){
							    
								update_post_meta( $the_query->post->ID, '_stop_mails', '1' );
                                
							} elseif( !$dates_of_sent_mail or ((int) end($dates_of_sent_mail) < strtotime('-'.$interval.' days')) ) {
							    
								update_post_meta( $the_query->post->ID, '_number_of_sent_mails', $number_of_sent_mail+1 );	
								add_post_meta( $the_query->post->ID, '_dates_of_sent_mails', time() , FALSE);
								
								do_action('woocommerce_simple_auction_pay_reminder', $the_query->post->ID);
							}
						  	
						endwhile;
						wp_reset_postdata();						
					}
				}
			    exit;								
			}			
		}
	}
	
    // Instantiate plugin class and add it to the set of globals.
	$woocommerce_auctions = new WooCommerce_simple_auction();
	register_activation_hook( __FILE__, array( 'WooCommerce_simple_auction', 'install' ) );
	register_deactivation_hook( __FILE__, array( 'WooCommerce_simple_auction', 'deactivation' ) );
	
	function simple_auction_send_reminders_email()
	{
			$woocommerce_auctions->send_reminders_email();
	}
	
}
else{
	add_action('admin_notices', 'wc_auction_error_notice');
	function wc_auction_error_notice(){
		global $current_screen;
		if($current_screen->parent_base == 'plugins'){
			echo '<div class="error"><p>WooCommerce Simple Auctions '.__('requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.', 'wc_simple_auctions').'</p></div>';
		}
	}
	$plugin = plugin_basename(__FILE__);
	
	if(is_plugin_active($plugin)){
	 	deactivate_plugins( $plugin);
	}	
	
		
}