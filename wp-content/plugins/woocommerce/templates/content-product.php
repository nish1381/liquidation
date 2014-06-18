<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Ensure visibility
if ( ! $product || ! $product->is_visible() )
	return;

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )
	$classes[] = 'last';
?>
<!-- <li <?php post_class( $classes ); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<a href="<?php the_permalink(); ?>">

		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
		?>

		<h3><?php the_title(); ?></h3>

		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
		?>

	</a>

	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

</li> -->



<li>
						<ul class="products">
							<li>
								<ul>
									<?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );?>
									<li class="image-col"><a href="<?php echo the_permalink()?>"><img src="<?php echo $url; ?>" width="73" height="58" alt="" class="image-2" itemprop="image"></a></li>
									<li class="secondcol"><a href="<?php echo the_permalink()?>" itemprop="name"><?php the_title()?></a></li>
									<li class="thirdcol">
										<a href="<?php echo the_permalink()?>" itemprop="url" class="btn green">bid</a>
										<a href="#" class="btn orange xl-hidden l-visible"><span class="close">more</span><span class="open">less</span> <i class="ico"></i></a>
									</li>
								</ul>
							</li>
							<li class="slide" itemscope itemtype="http://schema.org/ProductOffer">
								<ul>
									<li class="first-column">
										<ul class="slide-inside">
											<li>
												<dl class="first-line">
													<dt class="xl-hidden l-visible">Condition:</dt>
													<dd itemprop="itemCondition"><?php echo get_post_meta($post->ID, 'condition', true )?></dd>
												</dl>
											</li>
											<li>
												<dl class="second-line">
													<dt class="xl-hidden l-visible">Seller:</dt>
													<dd><a href="#" itemprop="seller"><?php echo get_post_meta($post->ID, 'seller', true )?><!-- <span class="l-hidden">Mcintire</span> --></a></dd>
												</dl>
											</li>
											<li>
												<dl class="third-line">
													<dt class="xl-hidden l-visible">QTY:</dt>
													<dd><?php echo get_post_meta($post->ID, '_dl_quantity', true )?></dd>
												</dl>
											</li>
											<li class="xl-hidden m-visible">
												<dl class="fourth-line">
													<dt class="xl-hidden l-visible">Starting BID:</dt>
													<dd itemprop="price"><?php echo $product->get_price_html()?></dd>
												</dl>
											</li>
											<li class="xl-hidden m-visible">
												<dl class="fifth-line">
													<dt class="xl-hidden l-visible">MRSP:</dt>
													<dd><?php echo get_post_meta($product, '_dl_msrp', true )?></dd>
												</dl>
											</li>
											<li class="xl-hidden s-visible">
												<dl class="sixth-line">
													<dt class="xl-hidden l-visible">% of MRSP:</dt>
													<dd><?php echo  ($product->get_price_html() / get_post_meta($product, '_dl_msrp', true )) *100?></dd>
												</dl>
											</li>
											<li class="xl-hidden s-visible">
												<dl>
													<dt>Bids:</dt>
													<dd>38</dd>
												</dl>
											</li>
											<li class="xl-hidden s-visible">
												<dl>
													<dt>Location:</dt>
													<dd><?php echo get_post_meta($product, '_dl_location', true )?></dd>
												</dl>
											</li>
											<li class="xl-hidden s-visible">
												<dl>
													<dt>End Time:</dt>
													<dd itemprop="priceValidUntil"><span class="kind">lol</span></dd>
												</dl>
											</li>
										</ul>
									</li>
									<li class="second-column s-hidden">
										<ul class="slide-inside">
											<li class="m-hidden">
												<dl class="fourth-line">
													<dt class="xl-hidden l-visible">Starting BID:</dt>
													<dd><?php echo get_option('woocommerce_currency')?><?php the_field('_auction_start_price'); ?></dd>
												</dl>
											</li>
											<li class="m-hidden">
												<dl class="fifth-line">
													<dt class="xl-hidden l-visible">MRSP:</dt>
													<dd><?php echo get_option('woocommerce_currency')?><?php echo get_post_meta($post->ID, '_dl_msrp', true )?></dd>
												</dl>
											</li>
											<li class="sixth">
												<dl class="sixth-line">
													<dt class="xl-hidden l-visible">% of MRSP:</dt>
													<dd><?php echo (get_post_meta($post->ID, '_dl_msrp', true )/get_post_meta($post->ID, '_regular_price', true ))*100?></dd>
												</dl>
											</li>
											<li class="l-hidden m-visible">
												<dl class="eighth-line">
													<dt class="xl-hidden l-visible">Bids:</dt>
													<dd>38</dd>
												</dl>
											</li>
											<li class="l-hidden m-visible">
												<dl class="ninth-line">
													<dt class="xl-hidden l-visible">Location:</dt>
													<dd><?php echo get_post_meta($post->ID, '_dl_location', true )?></dd>
												</dl>
											</li>
											<li class="l-hidden m-visible">
												<dl class="tenth-line">
													<dt class="xl-hidden l-visible">End Time:</dt>
													<dd><span class="kind"><?php the_field('_auction_dates_to'); ?></span></dd>
												</dl>
											</li>
										</ul>
									</li>
									<li class="third-column xl-hidden l-visible m-hidden">
										<ul class="slide-inside">
											<li>
												<dl class="eighth-line">
													<dt class="xl-hidden l-visible m-hidden s-visible">Bids:</dt>
													<dd>38</dd>
												</dl>
											</li>
											<li>
												<dl class="ninth-line">
													<dt class="xl-hidden l-visible m-hidden s-visible">Location:</dt>
													<dd>Alabama</dd>
												</dl>
											</li>
											<li>
												<dl class="tenth-line">
													<dt class="xl-hidden l-visible m-hidden s-visible">End Time:</dt>
													<dd><span class="kind">15.04.13 12:03 AM</span></dd>
												</dl>
											</li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
					</li>


					<li class="xl-hidden">
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img6.jpg" width="73" height="58" alt="" class="image-2" itemprop="image"></a></li>
										<li class="secondcol"><a href="#" itemprop="name">B Grade - Samsung, Sanyo &amp; More 40" - 46" LED/LCD TVs…</a></li>
										<li class="thirdcol">
											<a href="#" itemprop="url" class="btn green">bid</a>
											<a href="#" class="btn orange xl-hidden l-visible"><span class="close">more</span><span class="open">less</span> <i class="ico"></i></a>
										</li>
									</ul>
								</li>
								<li class="slide" itemscope itemtype="http://schema.org/ProductOffer">
									<ul>
										<li class="first-column">
											<ul class="slide-inside">
												<li>
													<dl class="first-line">
														<dt class="xl-hidden l-visible">Condition:</dt>
														<dd itemprop="itemCondition">Good condition</dd>
													</dl>
												</li>
												<li>
													<dl class="second-line">
														<dt class="xl-hidden l-visible">Seller:</dt>
														<dd><a href="#" itemprop="seller">John Doe <span class="l-hidden">Mcintire</span></a></dd>
													</dl>
												</li>
												<li>
													<dl class="third-line">
														<dt class="xl-hidden l-visible">QTY:</dt>
														<dd>25</dd>
													</dl>
												</li>
												<li class="xl-hidden m-visible">
													<dl class="fourth-line">
														<dt class="xl-hidden l-visible">Starting BID:</dt>
														<dd itemprop="price">$1,550.00</dd>
													</dl>
												</li>
												<li class="xl-hidden m-visible">
													<dl class="fifth-line">
														<dt class="xl-hidden l-visible">MRSP:</dt>
														<dd>$1,600.99</dd>
													</dl>
												</li>
												<li class="xl-hidden s-visible">
													<dl class="sixth-line">
														<dt class="xl-hidden l-visible">% of MRSP:</dt>
														<dd>90%</dd>
													</dl>
												</li>
												<li class="xl-hidden s-visible">
													<dl>
														<dt>Bids:</dt>
														<dd>38</dd>
													</dl>
												</li>
												<li class="xl-hidden s-visible">
													<dl>
														<dt>Location:</dt>
														<dd>Alabama</dd>
													</dl>
												</li>
												<li class="xl-hidden s-visible">
													<dl>
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil"><span class="kind">15.04.13 12:03 AM</span></dd>
													</dl>
												</li>
											</ul>
										</li>
										<li class="second-column s-hidden">
											<ul class="slide-inside">
												<li class="m-hidden">
													<dl class="fourth-line">
														<dt class="xl-hidden l-visible">Starting BID:</dt>
														<dd>$1,550.00</dd>
													</dl>
												</li>
												<li class="m-hidden">
													<dl class="fifth-line">
														<dt class="xl-hidden l-visible">MRSP:</dt>
														<dd>$1,600.99</dd>
													</dl>
												</li>
												<li class="sixth">
													<dl class="sixth-line">
														<dt class="xl-hidden l-visible">% of MRSP:</dt>
														<dd>90%</dd>
													</dl>
												</li>
												<li class="l-hidden m-visible">
													<dl class="eighth-line">
														<dt class="xl-hidden l-visible">Bids:</dt>
														<dd>38</dd>
													</dl>
												</li>
												<li class="l-hidden m-visible">
													<dl class="ninth-line">
														<dt class="xl-hidden l-visible">Location:</dt>
														<dd>Alabama</dd>
													</dl>
												</li>
												<li class="l-hidden m-visible">
													<dl class="tenth-line">
														<dt class="xl-hidden l-visible">End Time:</dt>
														<dd><span class="kind">15.04.13 12:03 AM</span></dd>
													</dl>
												</li>
											</ul>
										</li>
										<li class="third-column xl-hidden l-visible m-hidden">
											<ul class="slide-inside">
												<li>
													<dl class="eighth-line">
														<dt class="xl-hidden l-visible m-hidden s-visible">Bids:</dt>
														<dd>38</dd>
													</dl>
												</li>
												<li>
													<dl class="ninth-line">
														<dt class="xl-hidden l-visible m-hidden s-visible">Location:</dt>
														<dd>Alabama</dd>
													</dl>
												</li>
												<li>
													<dl class="tenth-line">
														<dt class="xl-hidden l-visible m-hidden s-visible">End Time:</dt>
														<dd><span class="kind">15.04.13 12:03 AM</span></dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li>
							</ul>
						</li>
