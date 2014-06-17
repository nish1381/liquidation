<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'shop' ); ?>

	<?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		//do_action( 'woocommerce_before_main_content' );
	?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<!-- <h1 class="page-title"><?php woocommerce_page_title(); ?></h1> -->

		<?php endif; ?>

		<?php do_action( 'woocommerce_archive_description' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>


				<!--box active auctions starts-->
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
												        <li class="cell-<?php echo $i, $class; ?>">
												        	<a href="<?php get_permalink();?>">
													        	<?php the_post_thumbnail(); ?>
																<span itemprop="name"><?php echo esc_attr($loop->post->post_title); ?></span>
															</a>
														</li>
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
											<?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );?>
											<a href="<?php get_permalink();?>">
													<img src="<?php echo $url?>" width="74" height="55" alt="" itemprop="image">
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





				<?php while ( have_posts() ) : the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>


			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>

<?php get_footer( 'shop' ); ?>