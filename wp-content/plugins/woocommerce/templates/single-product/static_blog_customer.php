<!--box popular categories starts-->
			<div class="box popular-categories">
				<h2>Popular Categories</h2>
				<div class="holder">

					<?php
						$args = array(
						  'taxonomy'     => 'product_cat',
						  'orderby'      => 'name',
						  'show_count'   => 0,
						  'pad_counts'   => 0,
						  'hierarchical' => 1,
						  'title_li'     => '',
						  'hide_empty'   => 0,
						  'number'	 => 7
						);

						$all_categories = get_categories( $args );

						foreach ($all_categories as $cat) {

							$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
						    $image = wp_get_attachment_url( $thumbnail_id );?>

						    <?php
								$the_cat = get_the_category();
								$category_name = $the_cat[0]->cat_name;
								$category_description = $the_cat[0]->category_description;
								$category_link = get_category_link( $the_cat[0]->cat_ID );
								?>

							<div class="cell-1" itemscope itemtype="http://schema.org/Product">
								<a href="<?php echo $category_link ?>"><img src="<?php echo $image?>" width="62" height="50" alt="" itemprop="image"><span itemprop="name"><?php echo $cat->name;?></span></a>
							</div>
						<?php }?>

				</div>
			</div>
			<!--box popular categories ends-->


			<div class="box">
				<section class="testimonials">
					<h2>Customer testimonials</h2>

					<?php
					//WordPress loop for custom post type
					 $my_query = new WP_Query('post_type=dl_testimonial&posts_per_page=3');
					      while ($my_query->have_posts()) : $my_query->the_post(); ?>
					 <blockquote>
						<q><?php the_content(); ?></q>
						<cite><?php echo get_post_meta($post->ID, 'author', true )?> - <span><?php echo get_post_meta($post->ID, 'regency', true )?></span></cite>
					</blockquote>
					<!-- <blockquote class="l-hidden">
						<q><?php the_content(); ?></q>
						<cite><?php echo get_post_meta($post->ID, 'author', true )?> - <span><?php echo get_post_meta($post->ID, 'author', true )?></span></cite>
					</blockquote> -->

					<?php endwhile;  wp_reset_query(); ?>
				</section>
				<section class="press l-hidden">
					<h2>Press</h2>


					<?php
					//WordPress loop for custom post type
					$my_query = new WP_Query('post_type=dl_blog&posts_per_page=3');
					    while ($my_query->have_posts()) : $my_query->the_post(); ?>
					<article class="news" itemscope itemtype="http://schema.org/NewsArticle">
						<time datetime="13-04-16" itemprop="datePublished"><?php the_time('g:i:s') ?></time>
						<h3 itemprop="headline"><?php the_title()?></h3>
						<p itemprop="articleBody"><?php the_content()?></p>
						<div class="holder">
							<a href="<?php the_permalink()?>" class="more">read more ...</a>
						</div>
					</article>

						<?php endwhile;  wp_reset_query(); ?>
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