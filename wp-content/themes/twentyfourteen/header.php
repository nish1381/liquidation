<?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage liquidation_wp
 * @since liquidation 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	
	<!--custom fonts-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,800italic,800,700italic,700,600italic,600,400italic,300italic,300' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/style.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/css/reset.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/css/ui.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/css/common.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/css/fancybox/fancybox.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo get_template_directory_uri();?>/css/modules/promo-box.css' rel='stylesheet' type='text/css'>
	
	<?php wp_head(); ?>

	<!--jQuery library-->
	<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<!--include plugins-->
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.custom-select.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.tabs.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.openclose.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/autoscaling-menu.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/add-class.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.placeholder.js"></script>
	<!--init plugins-->
	<script src="<?php echo get_template_directory_uri(); ?>/js/init-plugins.js"></script>
	<!--common javascript-->
	<script src="<?php echo get_template_directory_uri(); ?>/js/common.js"></script>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/plugins/respond.min.js"></script>
	<![endif]-->
	

</head>
<body id="<?php echo get_option('current_page_template'); ?>">
	<div id="wrapper">
		<!--header starts-->
		<header id="header">
			<div class="container">
				<div class="navigation">
					<h1 class="logo"><a href="<?php echo  get_site_url(); ?>">Direct Liquidation</a></h1>
					<!--navigation starts-->
					<nav id="nav">
						<?php
						wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' )) ;?>
					</nav>
					<!--navigation ends-->
				</div>
				<!--register panel starts-->
				<div class="register-panel">
					<ul class="register-list">
						<li><a href="<?php echo wp_registration_url(); ?> ">Register</a></li>
						<li><a href="#">Login</a></li>
						<li class="logout hidden"><a href="#"><i class="ico"></i>Logout</a></li>
					</ul>
					
					<ul class="socials-list">
						<?php dynamic_sidebar( 'utility-nav' ); ?>
					</ul>
					<ul class="list-technical xl-hidden l-visible">
						<li class="logout-link"><a href="#"></a>
							<div class="drop-social">
								<ul class="socials-list">
									<li class="twitter"><a href="#">twitter</a></li>
									<li class="facebook"><a href="#">facebook</a></li>
									<li class="google"><a href="#">google</a></li>
								</ul>
							</div>
						</li>
						<li class="search-link"><a href="#"></a></li>
						<li class="login-link"><a href="#"></a></li>
					</ul>
				</div>
				<!--register panel ends-->
			</div>
		</header>
		<!--header ends-->
		<!--main starts-->
		<div id="main">
			<div class="btn-block log-in xl-hidden">
				<a href="#" class="btn green">LOG IN</a>
				<span>or</span>
				<a href="#" class="btn blue">REGISTER</a>
				<a href="#" class="btn out grey">LOG OUT</a>
			</div>
			<div class="box form-block">
				<!--search form starts-->
<!-- 				<form class="search-form" method="get">
					<fieldset>
						<input type="search" placeholder="Search ...">
						<?php categpry_select(); ?>
						<select id="with-placeholder-1">
							<option value="value-1" selected>All Conditions</option>
							<option value="value-2">Option 1</option>
							<option value="value-3">Option 2</option>
							<option value="value-4">Option 3</option>
							<option value="value-5">Option 4</option>
						</select>
						<input type="submit" value="SEARCH" class="btn orange">
					</fieldset>
				</form> -->
				<!--search form ends-->

<?php global $woocommerce, $wp_query;

//if ( 1 == $wp_query->found_posts || ! woocommerce_products_will_display() )
	//return;
?>
<form action="<?php echo esc_url( home_url( '/shop' ) ); ?>" class="first-form search-form woocommerce-ordering" method="get">

	<input value="<?php the_search_query(); ?>" name="s" id="s" type="search" placeholder="Search ..." onblur="this.value = this.value || this.defaultValue;" onfocus="this.value = '';">
	<input type="submit" id="searchsubmit" value="Search" />
	<!-- <input type="hidden" name="post_type" value="product" /> -->
		<?php //categpry_select(); ?>


</form>

<form action="<?php echo esc_url( home_url( '/shop' ) ); ?>" class="second-form search-form woocommerce-ordering" method="get">

 <?php
  $taxonomy     = 'product_cat';
  $orderby      = 'name';  
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no  
  $title        = '';  
  $empty        = 0;
$args = array(
  'taxonomy'     => $taxonomy,
  'orderby'      => $orderby,
  'show_count'   => $show_count,
  'pad_counts'   => $pad_counts,
  'hierarchical' => $hierarchical,
  'title_li'     => $title,
  'hide_empty'   => $empty
);
?>
<select id="categorys">
<option value="" selected>All Categories</option>
<?php $all_categories = get_categories( $args );
//print_r($all_categories);

							
foreach ($all_categories as $cat) {
    //print_r($cat);
    if($cat->category_parent == 0) {
        $category_id = $cat->term_id;

?>      
  
		<!-- <option value="<?php echo get_term_link($cat->slug, 'product_cat') ?>"><?php echo $cat->name; ?></option> -->
		<option value="<?php echo $cat->cat_ID ?>"><?php echo $cat->name; ?></option>


        <?php
        // $args2 = array(
        //   'taxonomy'     => $taxonomy,
        //   'child_of'     => 0,
        //   'parent'       => $category_id,
        //   'orderby'      => $orderby,
        //   'show_count'   => $show_count,
        //   'pad_counts'   => $pad_counts,
        //   'hierarchical' => $hierarchical,
        //   'title_li'     => $title,
        //   'hide_empty'   => $empty
        // );
        // $sub_cats = get_categories( $args2 );
        // if($sub_cats) {
        //     foreach($sub_cats as $sub_category) {
        //         echo  $sub_category->name ;
        //     }

        //} 
        ?>



    <?php }     
}
?>
</select>










	<select id="with-placeholder-1" name="orderby" class="orderby">
		<?php
			$catalog_orderby = apply_filters( 'woocommerce_catalog_orderby', array(
				'menu_order' => __( 'Default sorting', 'woocommerce' ),
				'popularity' => __( 'Sort by popularity', 'woocommerce' ),
				'rating'     => __( 'Sort by average rating', 'woocommerce' ),
				'date'       => __( 'Sort by newness', 'woocommerce' ),
				'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
				'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
				'name-desc'  => __( 'Sort by Name', 'woocommerce' )
			) );

			if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' )
				unset( $catalog_orderby['rating'] );

			foreach ( $catalog_orderby as $id => $name )
				echo '<option value="' . esc_attr( $id ) . '" ' . selected( $orderby, $id, false ) . '>' . esc_attr( $name ) . '</option>';
		?>
	</select>
	<input type="submit" id="submitold" value="SEARCH" class="btn orange">
	<?php
		// Keep query string vars intact
		foreach ( $_GET as $key => $val ) {
			if ( 'orderby' === $key || 'submit' === $key )
				continue;
			
			if ( is_array( $val ) ) {
				foreach( $val as $innerVal ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
				}
			
			} else {
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
			}
		}
	?>	
</form>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#submitold').click(function(){
			$('#searchsubmit').click()
		})
		$('#searchsubmit').hide();
		$('#categorys').change(function(){
			document.location.href= '?na=' + $(this).val();

				$.ajax({
				   type: 'POST',    
					url:'<?php echo "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>/?',
					data: $(this).val(),
					success: function(msg){
					    //alert('wow' + msg);
					}
				});
		});
	});
</script>
