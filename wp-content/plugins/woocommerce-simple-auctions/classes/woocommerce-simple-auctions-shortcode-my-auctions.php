<?php
/**
 * Shortcode [woocommerce_simple_auctions_my_auctions]
 *
 */

class WC_Shortcode_Simple_Auction_My_Auctions {

	/**
	 * Get shortcode content
	 *
	 * @access public
	 * @param array $atts
	 * @return string
     * 
	 */
	public static function get( $atts ) {
		global $woocommerce;
		return $woocommerce->shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return void
     * 
	 */
	public static function output( $atts ) {
		global $woocommerce, $wpdb;

		if ( ! is_user_logged_in() ) return;			

			$user_id  = get_current_user_id();
			$postids = array();
			$userauction	 = $wpdb->get_results("SELECT DISTINCT auction_id FROM ".$wpdb->prefix."simple_auction_log WHERE userid = $user_id ",ARRAY_N );
			if(isset($userauction) && !empty($userauction)){
				foreach ($userauction as $auction) {
					$postids []= $auction[0];
					
				}
			}
			
			?>
			<div class="simple-auctions active-auctions clearfix">
				<h2><?php _e( 'Active auctions', 'wc_simple_auctions' ); ?></h2>
				
				<?php
				
				$args = array(
					'post__in' 			=> $postids ,
					'post_type' 		=> 'product',
					'posts_per_page' 	=> '-1',
					'tax_query' 		=> array(
						array(
							'taxonomy' => 'product_type',
							'field' => 'slug',
							'terms' => 'auction'
						)
					),
					'meta_query' => array(
											      
					        array(
					           'key' => '_auction_closed',
					        
					           'compare' => 'NOT EXISTS'
					       )
					   ),
					'auction_arhive' => TRUE,      
					'show_past_auctions' 	=>  TRUE,      
				);
				//var_dump($args);
				$activeloop = new WP_Query( $args );
				//var_dump($activeloop);
				if ( $activeloop->have_posts() ) {
				    woocommerce_product_loop_start();
					while ( $activeloop->have_posts() ):$activeloop->the_post();
						woocommerce_get_template_part( 'content', 'product' );
					endwhile;
					woocommerce_product_loop_end(); 
				        
				} else {
					_e("You are not participating in auction.","wc_simple_auctions" );
				}
	
				wp_reset_postdata();
				
				?>			
			</div>
			<div class="simple-auctions active-auctions clearfix">
				<h2><?php _e( 'Won auctions', 'wc_simple_auctions' ); ?></h2>
				
				<?php
				$args = array(
					'post_type' 		=> 'product',
					'posts_per_page' 	=> '-1',
					'meta_query' => array(
					       array(
					           'key' => '_auction_closed',
					           'value' => '2',
					       ),
					        array(
					           'key' => '_auction_current_bider',
					           'value' => $user_id,
					       )
					   ),
					'show_past_auctions' 	=>  TRUE,
					'auction_arhive' => TRUE,     
				);
				
				$winningloop = new WP_Query( $args );
	
				if ( $winningloop->have_posts() ) {
				       woocommerce_product_loop_start();
					while ( $winningloop->have_posts()): $winningloop->the_post() ;
						woocommerce_get_template_part( 'content', 'product' );
					endwhile;
				        woocommerce_product_loop_end(); 
				} else {
					_e("You did not win any auctions yet.","wc_simple_auctions" );
				}
	
				wp_reset_postdata();
				echo "</div>";
						
				}
			
}