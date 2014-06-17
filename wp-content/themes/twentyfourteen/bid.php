<?php
/*
Template Name: bid
*/


get_header( 'shop' ); ?>


	<div class="box promo" itemscope itemtype="http://schema.org/Offer">
				<h2 class="accessability">Promo info</h2>
				<p><a href="#"><span itemprop="name">Apple, Samsung, Nintendo</span> - iPad, Tablets, Gaming Systems and Mixed Electronics - 452 Units - Latest Models - Untested Customer Returns - <span itemprop="price">Retail $42,379.80</span></a></p>
	</div>
<div class="box active-auctions">
<?php echo do_shortcode("[wdm_auction_listing]");?>
</div>


<?php get_footer( 'shop' ); ?>