<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one
 * of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query,
 * e.g., it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Liquidation
 * @since liquidation_wp
 */

get_header();?>


			<!--box product categories starts-->
			<div class="box product-categories">
				<h2 class="accessability">Product Categories</h2>
				<div class="frame">
					<ul class="tabset">
						<li><a href="#tab1" class="active">Categories</a></li>
						<li><a href="#tab2" >Featured</a></li>
					</ul>
					<div class="sub"><a href="#">Subscribe</a>
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
				</div>
				<div class="tab-list">
					<div id="tab1" class="tab">
						<ul class="product-list" itemscope itemtype="http://schema.org/Products">
							<?php 
								$args = array(
								  'taxonomy'     => 'product_cat',
								  'orderby'      => 'name',
								  'show_count'   => 0,
								  'pad_counts'   => 0,
								  'hierarchical' => 1,
								  'title_li'     => '',
								  'hide_empty'   => 0
								);	

								$all_categories = get_categories( $args );
								
								foreach ($all_categories as $cat) {
	 //var_dump($cat);exit;
	    							if($cat->category_parent == 0) {
	    								?>

	    								<li class="hasdrop">
								<a href="#" itemprop="name"><?php echo $cat->name; ?></a>
								<section class="drop">
									<div class="drop-holder">
										<div class="holder">
											<div class="electronics">
												<h3><?php echo $cat->name; ?></h3>
												<ul itemscope itemtype="http://schema.org/ProductModel">
													 <?php
        $args = array( 'post_type' => 'product', 'posts_per_page' => 4, 'product_cat' => $cat->slug, 'orderby' => 'rand' );
        $loop = new WP_Query( $args );
   //     var_dump($loop);

        $i = 0;
        while ($loop->have_posts()) : $loop->the_post();
        $i++;
        $class = '';
        if ($i == 2) 
        	$class = " sm-hidden";
       	else if ($i == 3)
       		$class = " m-hidden";
       	else if ($i == 4) 
       		$class =" l-hidden";


        ?>
        											<li class="cell-<?php echo $i, $class; ?>"><a href="<?php get_permalink();?>">
        												<?php the_post_thumbnail(); ?>
<span itemprop="name"><?php echo esc_attr($loop->post->post_title); ?></span></a></li>
        					<?php endwhile;wp_reset_query(); ?>
												</ul>
											</div>
											<div class="categories" itemscope itemtype="http://schema.org/ProductModel">
												<h4>Additional Sub-categories</h4>
												<div class="holder">
													<?php

												 $subcategories = get_categories('taxonomy=product_cat&child_of=' . $cat->term_id . '&hide_empty');
													$i = 0;
													//var_dump($subcategories);
													?><ul>
													<?php
													foreach ($subcategories as $sub) {
														$class  = '';

														/*if (0 == $i % 3) $class = "m-hidden";
														if (1 == $i % 3) $class = "sm-hidden";
														if (2 == $i % 3) $class = "sm-hidden";
 														*/
														?>

													
													<li class="<?php echo $class; ?>"><a href="<?php get_category_link($sub->term_id); ?>" itemprop="name"><?php echo $sub->name ;?></a></li>

													<?php 
														$i++;
														if (0 == $i % 3) {
															?>
													</ul><ul>

														<?php	
														} 
													}
													?>
												</ul>
													
													<a href="#" class="more">see all</a>
												</div>
											</div>
										</div>
									</div>
								</section> 
							</li>
	    								<?php
	    								
	    							}
	    						}
							?>
							
							
						</ul>
						<a href="#" class="all">see all categories</a>
					</div>

					<div id="tab2" class="tab">
						<h3 class="accessability">Featured products</h3>


						<section class="featured-products-block" itemscope itemtype="http://schema.org/Offer">
							<ul class="featured-list">

								

								<?php 

								$args = array( 
									'post_status' => 'publish',
									'post_type' => 'product',
									'meta_key' => '_featured',
									'meta_value' => 'yes',
									'posts_per_page' => 6
									);

								$my_query = new WP_Query( $args);
								$i = 0;
 								while ($my_query ->have_posts()) : $my_query ->the_post();
 								 $i ++;
 								 $product = get_product( get_the_ID() );
 							//	 var_dump($product);
							

?>
							
								<li class="cell-<?php echo $i;?>">
									<div class="container-holder">
										<div class="col">
											<a href="<?php get_permalink();?>">
													<?php the_post_thumbnail(); ?></a>
											<a href="#" class="btn green" itemprop="url">BID NOW!</a>
										</div>
										<div class="holder">
											<h4 itemprop="name"><a href="<?php get_permalink();?>"><?php showReadMore($my_query->post->post_title, 53); ?></a></h4>
											<span itemprop="price">MRSP: $<?php echo   $product->get_price();?></span>
										</div>
									</div>
								</li>
							<?php
							if (0 == $i % 3) :
								?>
						</ul>
								<ul class="featured-list sm-hidden">
						<?php endif;	endwhile; ?>

							</ul>				

							
							<div class="holder-alt"><a href="#" class="more">see all</a></div>
						</section>
					</div>
				</div>
			</div>
			<!--box product categories ends-->
			<!--box active auctions starts-->
			<div class="box active-auctions">
				<h2>Popular Active Auctions</h2>
					<ul class="titles l-hidden m-hidden">
						<li class="first-title">Picture</li>
						<li class="second-title">Title</li>
						<li class="third-title">Condition</li>
						<li class="fourth-title">Seller</li>
						<li class="fifth-title">QTY</li>
						<li class="sixth-title">Starting BID</li>
						<li class="seveth-title">MSRP</li>
						<li class="eighth-title">% of MSRP</li>
						<li class="ninth-title">Bids</li>
						<li class="tenth-title">Location</li>
						<li class="elenenth-title">End Time</li>
					</ul>
					<ul class="table-products" itemscope itemtype="http://schema.org/Product">
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img5.jpg" width="66" height="39" alt="" class="image-1" itemprop="image"></a></li>
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
													<dl class="tenth-line">
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil">Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li> 
							</ul>
						</li>
						<li>
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
														<dt class="xl-hidden l-visible ">Seller:</dt>
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
													<dl class="tenth-line">
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil">Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li> 
							</ul>
						</li>
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img5.jpg" width="66" height="39" alt="" class="image-1" itemprop="image"></a></li>
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
													<dl class="tenth-line">
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil">Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li> 
							</ul>
						</li>
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img7.jpg" width="53" height="53" alt="" class="image-3" itemprop="image"></a></li>
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
													<dl class="tenth-line">
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil">Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li> 
							</ul>
						</li>
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img5.jpg" width="66" height="39" alt="" class="image-1" itemprop="image"></a></li>
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
													<dl class="tenth-line">
														<dt>End Time:</dt>
														<dd itemprop="priceValidUntil">Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
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
														<dd>Today 12:03 AM</dd>
													</dl>
												</li>
											</ul>
										</li>
									</ul>
								</li> 
							</ul>
						</li>
						<li>
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
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img5.jpg" width="66" height="39" alt="" class="image-1" itemprop="image"></a></li>
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
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img7.jpg" width="53" height="53" alt="" class="image-3" itemprop="image"></a></li>
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
						<li>
							<ul class="products">
								<li>
									<ul>
										<li class="image-col"><a href="#"><img src="images/content/img5.jpg" width="66" height="39" alt="" class="image-1" itemprop="image"></a></li>
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
						<li>
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
					</ul>
				<div class="holder">
					<a href="#" class="more">view more listings</a>
				</div>
			</div>
			<!--box active auctions ends-->
			<!--box popular categories starts-->
			<div class="box popular-categories">
				<h2>Popular Categories</h2>
				<div class="holder">
					<div class="cell-1" itemscope itemtype="http://schema.org/Product">
						<a href="#"><img src="images/content/img17.jpg" width="62" height="50" alt="" itemprop="image"><span itemprop="name">TVs</span></a>
					</div>
					<div class="cell-2" itemscope itemtype="http://schema.org/Product">
						<a href="#"><img src="images/content/img14.jpg" width="73" height="49" alt="" itemprop="image"><span itemprop="name">Computers <br><span class="kind">&amp; Monitors</span></span></a>
					</div>
					<div class="cell-3" itemscope itemtype="http://schema.org/Product">
						<a href="#"><img src="images/content/img15.jpg" width="64" height="54" alt="" itemprop="image"><span itemprop="name">Tablets</span></a>
					</div>
					<div class="cell-4" itemscope itemtype="http://schema.org/Product">
						<a href="#"><img src="images/content/img16.jpg" width="73" height="46" alt="" itemprop="image"><span itemprop="name">Media <br><span class="kind">Players</span></span></a>
					</div>
					<div class="cell-5" itemscope itemtype="http://schema.org/Product">
						<a href="#"><img src="images/content/img18.jpg" width="49" height="48" alt="" itemprop="image"><span itemprop="name">Mixed <br><span class="kind">Pallets</span></span></a>
					</div>
				</div>
			</div>
			<!--box popular categories ends-->
			<!--box testimonial starts-->
			<div class="box">
				<section class="testimonials">
					<h2>Customer testimonials</h2>
					<blockquote>
						<q>Lorem ipsum dolor sit amet, consectetur adipisicing elit, tempor incididunt ut labore et dolore magna. Ut enim ad quis nostrud dolor sit amet, consectetur adipisicing elit, minim veniam, quis nostrud dolor sit amet,...</q>
						<cite>By John Doe - <span>CEO &amp; Founder</span></cite>
					</blockquote>
					<blockquote>
						<q>Lorem ipsum dolor sit amet, consectetur adipisicing elit, tempor incididunt ut labore et dolore magna. Ut enim ad quis nostrud dolor sit amet, consectetur adipisicing elit, minim veniam, quis nostrud dolor sit amet,...</q>
						<cite>By John Doe - <span>CEO &amp; Founder</span></cite>
					</blockquote>
					<blockquote class="l-hidden">
						<q>Lorem ipsum dolor sit amet, consectetur adipisicing elit, tempor incididunt ut labore et dolore magna. Ut enim ad quis nostrud dolor sit amet, consectetur adipisicing elit, minim veniam, quis nostrud dolor sit amet,...</q>
						<cite>By John Doe - <span>CEO &amp; Founder</span></cite>
					</blockquote>
				</section>
				<section class="press l-hidden">
					<h2>Press</h2>
					<article class="news" itemscope itemtype="http://schema.org/NewsArticle">
						<time datetime="13-04-16" itemprop="datePublished">16.04.13</time>
						<h3 itemprop="headline">Target awards The Recon Group</h3>
						<p itemprop="articleBody">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit consect etur adipisicing elit, sed do eiu tem ipsum dolor sit amet ...</p>
						<div class="holder">
							<a href="#" class="more">read more ...</a>
						</div>
					</article>
					<article class="news" itemscope itemtype="http://schema.org/NewsArticle">
						<time datetime="13-04-16" itemprop="datePublished">16.04.13</time>
						<h3 itemprop="headline">Target awards The Recon Group</h3>
						<p itemprop="articleBody">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit consect etur adipisicing elit, sed do eiu tem ipsum dolor sit amet ...</p>
						<div class="holder">
							<a href="#" class="more">read more ...</a>
						</div>
					</article>
					<article class="news" itemscope itemtype="http://schema.org/NewsArticle">
						<time datetime="13-04-16" itemprop="datePublished">16.04.13</time>
						<h3 itemprop="headline">Target awards The Recon Group</h3>
						<p itemprop="articleBody">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit  ...</p>
						<div class="holder">
							<a href="#" class="more">read more ...</a>
						</div>
					</article>
				</section>
			</div>
			<div class="box xl-hidden l-visible press">
				<section class="press">
					<h2>Press</h2>
					<div class="holder-alt">
						<article class="news">
							<time datetime="13-04-16">16.04.13</time>
							<h3>Target awards The Recon Group</h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit consect etur adipisicing elit, sed do eiu tem ipsum dolor sit amet ...</p>
							<div class="holder">
								<a href="more" class="more">read more ...</a>
							</div>
						</article>
						<article class="news">
							<time datetime="13-04-16">16.04.13</time>
							<h3>Target awards The Recon Group</h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit consect etur adipisicing elit, sed do eiu tem ipsum dolor sit amet ...</p>
							<div class="holder">
								<a href="more" class="more">read more ...</a>
							</div>
						</article>
						<article class="news">
							<time datetime="13-04-16">16.04.13</time>
							<h3>Target awards The Recon Group</h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiu tem ipsum dolor sit amet, consectetur adipisicing elit  ...</p>
							<div class="holder">
								<a href="more" class="more">read more ...</a>
							</div>
						</article>
					</div>
				</section>
			</div>
			<!--box testimonial ends-->
			<!--social list starts-->
			<ul class="social-list">
				<li class="facebook"><a href="#">facebook</a></li>
				<li class="twitter"><a href="#">twitter</a></li>
				<li class="google"><a href="#">google</a></li>
			</ul>
			<!--social list ends-->
<?php
get_footer();
