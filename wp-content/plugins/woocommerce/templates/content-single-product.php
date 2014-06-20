<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
?>



			<!--box promo starts-->
			<div class="box promo" itemscope itemtype="http://schema.org/Offer">
				<h2 class="accessability">Promo info</h2>
				<p><a href="#"><span itemprop="name">Apple, Samsung, Nintendo</span> - iPad, Tablets, Gaming Systems and Mixed Electronics - 452 Units - Latest Models - Untested Customer Returns - <span itemprop="price">Retail $42,379.80</span></a></p>
			</div>
			<!--box promo ends-->

<div class="box proposal-for-auction">
				<section class="promo-box" itemscope itemtype="http://schema.org/Offer">
					<?php global $woocommerce, $product, $post;
						$current_user = wp_get_current_user();?>
					<?php if(($product->is_closed() === FALSE ) and ($product->is_started() === TRUE )) : ?>	
					<h2 class="accessability" itemprop="name">Apple, Samsung, Nintendo</h2>

						<!-- <div class="auction-time" id="countdown"><?php echo apply_filters('time_text', __( 'Time left:', 'wc_simple_auctions' ), $product->product_type); ?> 
							<div class="main-auction auction-time-countdown" data-time="<?php echo $product->get_seconds_remaining() ?>" data-auctionid="<?php echo $product->id ?>" data-format="<?php echo get_option( 'simple_auctions_countdown_format' ) ?>"></div>
						</div> -->
					<time class="title" datetime="<?php echo date("h:i:s", time())?>">
						<?php 
							//echo date("h:i:s", time());  
						?>
						<?php the_field('_auction_dates_to'); ?>
						

					</time>

						<div class='auction-ajax-change'>
						    
							<!-- <p class="auction-end"><?php echo apply_filters('time_left_text', __( 'Auction ends:', 'wc_simple_auctions' ), $product->product_type); ?> <?php echo $product->get_auction_end_time(); ?> <br />
								<?php printf(__('Timezone: %s','wc_simple_auctions') , get_option('timezone_string') ? get_option('timezone_string') : __('UTC+','wc_simple_auctions').get_option('gmt_offset')) ?>
							</p> -->
							
							<?php if ($product->auction_bid_count == 0){?>
							    <!-- <p class="auction-bid"><?php echo apply_filters('starting_bid_text', __( 'Starting bid:', 'wc_simple_auctions' )); ?> <?php echo get_woocommerce_currency_symbol(); ?><span class="starting-bid amount"> <?php echo $product->get_curent_bid(); ?></span> </p> -->
							<?php } ?>
							
							<?php if ($product->auction_bid_count > 0){?>
							    <!-- <p class="auction-bid"><?php echo apply_filters('curent_bid_text', __( 'Current bid:', 'wc_simple_auctions' )); ?> <?php echo get_woocommerce_currency_symbol(); ?><span class="curent-bid amount"> <?php echo $product->get_curent_bid(); ?></span> <span class="number-of-bids">[<?php echo $product->auction_bid_count; ?> bids]</span></p> -->
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
							<form class="auction_form cart  entry-form" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>">
								<fieldset>
									<?php do_action('woocommerce_before_bid_button'); ?>
									
									<input type="hidden" name="bid" value="<?php echo esc_attr( $product->id ); ?>" />	
									<?php if($product->auction_type == 'reverse' ) : ?>
								<div class="illustration-element">		
									<input type="number" name="bid_value" value="<?php echo $product->bid_value() ?>" max="<?php echo $product->bid_value()  ?>"  step="<?php echo ($product->auction_bid_increment) ? $product->auction_bid_increment : '0.01' ?>" size="<?php echo strlen($product->get_curent_bid())+2 ?>" title="bid"  class="input-text  bid text left">
								</div>
								 	<button type="submit" class="bid_button button alt btn green"><?php echo apply_filters('bid_text', __( 'PLACE A BID', 'wc_simple_auctions' ), $product->product_type); ?></button>
								 		
									<?php else : ?>	
								<div class="illustration-element">			 	
									<input type="number" name="bid_value" value="<?php echo $product->bid_value()  ?>" min="<?php echo $product->bid_value()  ?>"  step="<?php echo ($product->auction_bid_increment) ? $product->auction_bid_increment : '0.01' ?>" size="<?php echo strlen($product->get_curent_bid())+2 ?>" title="bid"  class="input-text  bid text left">
								</div> 	
								 	<button type="submit" class="bid_button button alt btn green"><?php echo apply_filters('bid_text', __( 'PLACE A BID', 'wc_simple_auctions' ), $product->product_type); ?></button>
								 	<?php endif; ?>
								 	
								 	<input type="hidden" name="place-bid" value="<?php echo $product->id; ?>" />
									<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
									<?php do_action('woocommerce_after_bid_button'); ?>
									<!-- <label id="text">Auction Close Time: <time datetime="13-04-18" itemprop="priceValidUntil"><?php the_field('field_5395ec92b7fff'); ?> PDT</time></label>
									<div class="illustration-element">
										<input type="text" placeholder="">
									</div>
									<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" /> -->
									<!-- <input type="submit" value="PLACE A BID" class="btn green"> -->
								</fieldset>	
							</form>



							<form style="display:none" class="buy-now cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>">
							<?php 
							    global $woocommerce, $product, $post;
							    do_action('woocommerce_before_add_to_cart_button');
						        
							 	if ( ! $product->is_sold_individually() )
							 			woocommerce_quantity_input( array(
							 				'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
							 				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
							 			) );
							 ?>

						 	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

						 	<!-- <button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text',sprintf(__( 'Buy now for %s', 'wc_simple_auctions' ),woocommerce_price($product->regular_price)), $product->product_type); ?></button> -->
						 	<button type="submit" class="single_add_to_cart_button button alt btn green"><?php echo apply_filters('single_add_to_cart_text',sprintf(__( 'Buy now for %s', 'wc_simple_auctions' ),woocommerce_price($product->get_curent_bid())), $product->product_type); ?></button>
						 	
							
							<div>
								<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
								<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
							</div>

							<?php do_action('woocommerce_after_add_to_cart_button'); ?>

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






					<div class="holder">
						<div class="frame"><dl>
								<dt>Starting BID:</dt>
								<dd itemprop="price"><?php echo get_woocommerce_currency_symbol(); ?><?php echo get_post_meta($post->ID, '_auction_start_price', true )?></dd>
							</dl>
							<dl>
								<dt>Current BID:</dt>
								<dd><?php echo get_woocommerce_currency_symbol(); ?><?php echo $product->get_curent_bid(); ?></dd>
							</dl>
						</div> 
						<div class="frame">
							<dl class="alt">
								<dt>% of MRSP:</dt>
								<dd><?php echo (get_post_meta($post->ID, '_dl_msrp', true )/get_post_meta($post->ID, '_regular_price', true ))*100?></dd>
							</dl>
							<dl class="alt">
								<dt>MRSP Price:</dt>
								<dd><?php echo get_post_meta($post->ID, '_dl_msrp', true )?></dd>
							</dl>
						</div>
					</div>
					<strong class="id">Auction ID <?php echo $post->ID?></strong>
				</section>
				<div class="info-block m-hidden">
					<h2 class="accessability">Info block</h2>
					<div class="visual">
						<ul class="slideset">
							<li class="active">

							<?php 
								global $post, $product, $woocommerce;

								$attachment_ids = $product->get_gallery_attachment_ids();

								if ( $attachment_ids ) {
									?>
									<ul><li><?php

										$loop = 0;
										$position = 1;
										$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

										foreach ( $attachment_ids as $attachment_id ) {

											$classes = array( 'zoom' );

											if ( $loop == 0 || $loop % $columns == 0 )
												$classes[] = 'first';

											if ( ( $loop + 1 ) % $columns == 0 )
												$classes[] = 'last';

											$image_link = wp_get_attachment_url( $attachment_id );
											//var_dump($image_link);

											if ( ! $image_link )
												continue;

											$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
		
											$image_class = esc_attr( implode( ' ', $classes ) );
											$image_title = esc_attr( get_the_title( $attachment_id ) );?>

											<li class="illustration-<?php echo $position?>"><a href="<?php echo $image_link;?>" data-rel="prettyPhoto[product-gallery]" class="open-fancybox"><img src="<?php echo $image_link;?>" width="151" height="101" alt=""></a></li>
				<!-- 							<li class="illustration-2"><a href="images/content/img19.png" class="open-fancybox"><img src="images/content/img19.png" width="219" height="161" class="open-fancybox" alt=""></a></li>
											<li class="illustration-3"><a href="images/content/img21.jpg" class="open-fancybox"><img src="images/content/img21.jpg" width="159" height="112" alt=""></a></li> -->
											<?php 
											//echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<li> <a href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]">%s</a></li>', $image_link, $image_class, $image_title, $image ), $attachment_id, $post->ID, $image_class );
											$position++;
											$loop++;
										}

									?></li></ul>
									<?php
								}
							?>





								<!-- <ul>
									<li class="illustration-1"><a href="images/content/img20.jpg" class="open-fancybox"><img src="images/content/img20.jpg" width="151" height="101" alt=""></a></li>
									<li class="illustration-2"><a href="images/content/img19.png" class="open-fancybox"><img src="images/content/img19.png" width="219" height="161" class="open-fancybox" alt=""></a></li>
									<li class="illustration-3"><a href="images/content/img21.jpg" class="open-fancybox"><img src="images/content/img21.jpg" width="159" height="112" alt=""></a></li>
								</ul> -->
							</li>
							<!-- <li>
								<ul>
									<li class="illustration-1"><a href="images/content/img20.jpg" class="open-fancybox"><img src="images/content/img20.jpg" width="151" height="101" alt=""></a></li>
									<li class="illustration-2"><a href="images/content/img19.png" class="open-fancybox"><img src="images/content/img19.png" width="219" height="161" class="open-fancybox" alt=""></a></li>
									<li class="illustration-3"><a href="images/content/img21.jpg" class="open-fancybox"><img src="images/content/img21.jpg" width="159" height="112" alt=""></a></li>
								</ul>
							</li>
							<li>
								<ul>
									<li class="illustration-1"><a href="images/content/img20.jpg" class="open-fancybox"><img src="images/content/img20.jpg" width="151" height="101" alt=""></a></li>
									<li class="illustration-2"><a href="images/content/img19.png" class="open-fancybox"><img src="images/content/img19.png" width="219" height="161" class="open-fancybox" alt=""></a></li>
									<li class="illustration-3"><a href="images/content/img21.jpg" class="open-fancybox"><img src="images/content/img21.jpg" width="159" height="112" alt=""></a></li>
								</ul>
							</li> -->
						</ul>
					</div>
					<div class="col">
						<ul class="proposal">
							<li>
								<p><?php the_content()?></p>
							</li>
							<li>
								<dl>
									<dt>Seller:</dt>
									<dd><?php echo get_post_meta($post->ID, 'seller', true )?></dd>
								</dl>
							</li>
							<li>
								<div class="holder">
									<ul class="social-widgets">
										<li><img src="../images/content/img31.jpg" width="70" height="24" alt=""></li>
										<li><img src="../images/content/img32.jpg" width="75" height="20" alt=""></li>
										<li><img src="../images/content/img33.jpg" width="84" height="20" alt=""></li>
									</ul>
								</div>
								<div class="sub alt xl-hidden l-visible sm-hidden s-visible"><a href="#">Subscribe</a>
									<div class="drop">
										<div class="drop-holder">
											<form action="#" class="subscription-form">
												<fieldset>
													<label for="text3"><strong>Subscribe</strong> to our newsletter</label>
													<input type="text" placeholder="Email Address" id="text3">
													<input type="submit" value="subscribe" class="btn orange">
												</fieldset>
											</form>
										</div>
									</div>
								</div>
							</li>
							<li>
								<ul class="technical-list">
									<li class="wishlist"><a href="#">Add to wishlist</a></li>
									<li class="quotes"><a href="#">Obtain shipping quotes</a>
										<div class="drop">
											<div class="drop-holder">
												<header class="heading">
													<h2>Obtain Shipping Quote</h2>
													<div class="close">
														<a href="#">close</a>
													</div>
												</header>
												<div class="holder">
													<p>In order to provide you with the most accurate shipping quote for this auction, please verify the information below. The final shipping cost may vary from the estimate, and it is calculated only for the winning bidder after the auction has closed. Duties and taxes are the sole responsibility of the buyer and are not included in the shipping quote. </p>
													<dl>
														<dt>Attention:</dt>
														<dd>Do not use a PO Box address. Packages cannot be shipped to PO Box addresses.</dd>
													</dl>
												</div>
												<form action="#" class="order-form">
													<fieldset>
														<ul>
															<li>
																<dl>
																	<dt class="first-col">Auction Title:</dt>
																	<dd>Bathroom Vanity Cabinets, Elongated Toilet Bowls &amp; More - Retail Price $1,749.71.</dd>
																</dl>
															</li>
															<li>
																<dl>
																	<dt class="first-col">Auction ID:</dt>
																	<dd>7018590</dd>
																</dl>
															</li>
															<li>
																<dl>
																	<dt class="first-col">Number of Lots:</dt>
																	<dd>1</dd>
																</dl>
															</li>
															<li>
																<dl>
																	<dt class="first-col alt">Destination:</dt>
																	<dd class="second-col">
																		<input type="text" placeholder="" id="cod">
																		<label for="cod">Zip or Postal Code.</label>
																	</dd>
																</dl>
															</li>
															<li>
																<dl>
																	<dt class="first-col alt">Country:</dt>
																	<dd class="second-col">
																		<select id="with-placeholder-2">
																			<option value="value-1" selected>United States of America</option>
																			<option value="value-2">Option 1</option>
																			<option value="value-3">Option 2</option>
																			<option value="value-4">Option 3</option>
																			<option value="value-5">Option 4</option>
																		</select>
																	</dd>
																</dl>
															</li>
														</ul>
														<div class="row">
															<input type="submit" value="CALCULATE SHIPPING" class="btn orange">
														</div>
													</fieldset>
												</form>
											</div>
										</div>
									</li>
									<li class="mail"><a href="#">Email to a friend</a></li>
								</ul>
							</li>
							<li class="l-hidden">
								<div class="sub"><a href="#">Subscribe</a>
									<div class="drop">
										<div class="drop-holder">
<!-- 											<form action="#" class="subscription-form">
												<fieldset>
													<label for="text5"><strong>Subscribe</strong> to our newsletter</label>
													<input type="text" placeholder="Email Address" id="text5">
													<input type="submit" value="subscribe" class="btn orange">
												</fieldset>
											</form> -->
											<script type="text/javascript">
												//<![CDATA[
												if (typeof newsletter_check !== "function") {
												window.newsletter_check = function (f) {
												    var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{1,})+\.)+([a-zA-Z0-9]{2,})+$/;
												    if (!re.test(f.elements["ne"].value)) {
												        alert("The email is not correct");
												        return false;
												    }
												    if (f.elements["ny"] && !f.elements["ny"].checked) {
												        alert("You must accept the privacy statement");
												        return false;
												    }
												    return true;
												}
												}
												//]]>
											</script>
												<form action="<?php echo get_site_url()?>/wp-content/plugins/newsletter/do/subscribe.php" onsubmit="return newsletter_check(this)" class="subscription-form">
													<fieldset>
														<label for="text3"><strong>Subscribe</strong> to our newsletter</label>
														<input type="text" name="ne" placeholder="Email Address" id="text3">
														<input type="submit" value="subscribe" class="btn orange">
													</fieldset>
												</form>


										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>



<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
		/**
		 * woocommerce_before_single_product_summary hook
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		//do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary">

		<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */
			//do_action( 'woocommerce_single_product_summary' );
		?>

	</div><!-- .summary -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_output_related_products - 20
		 */
		//do_action( 'woocommerce_after_single_product_summary' );
	?>

	<!-- <meta itemprop="url" content="<?php the_permalink(); ?>" /> -->

<!-- </div> --><!-- #product-<?php the_ID(); ?> -->

<?php //do_action( 'woocommerce_after_single_product' ); ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.woocommerce-message').hide();
		if((jQuery('.woocommerce-message').text()) =='No need to bid. Your bid is winning! '){
			jQuery('.auction_form').hide();
			jQuery('.buy-now').show();
		}
		if(jQuery('.buy-now').is(':visible')){
			jQuery('.reserve').hide();
		}
	});
</script>