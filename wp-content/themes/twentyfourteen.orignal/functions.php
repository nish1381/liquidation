<?php 
	/* Liquidation
    */
define('WOOCOMMERCE_USE_CSS', false);
add_theme_support( 'woocommerce' );
/**

 * Sets up the content width value based on the theme's design and stylesheet.

 */

if ( ! isset( $content_width ) )
	$content_width = 604;




/**
 * Twenty Fourteen only works in WordPress 3.6 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '3.6', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

if ( ! function_exists( 'twentyfourteen_setup' ) ) :
/**
 * Twenty Fourteen setup.
 *
 * Set up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 *
 * @since Twenty Fourteen 1.0
 */
function twentyfourteen_setup() {

	/*
	 * Make Twenty Fourteen available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Fourteen, use a find and
	 * replace to change 'twentyfourteen' to the name of your theme in all
	 * template files.
	 */
	load_theme_textdomain( 'twentyfourteen', get_template_directory() . '/languages' );


	// Enable support for Post Thumbnails, and declare two sizes.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 672, 372, true );
	add_image_size( 'twentyfourteen-full-width', 1038, 576, true );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary'   => __( 'Top primary menu', 'twentyfourteen' ),
		'secondary' => __( 'Secondary menu in left sidebar', 'twentyfourteen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list',
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery',
	) );

	// This theme allows users to set a custom background.
	add_theme_support( 'custom-background', apply_filters( 'twentyfourteen_custom_background_args', array(
		'default-color' => 'f5f5f5',
	) ) );

	// Add support for featured content.
	add_theme_support( 'featured-content', array(
		'featured_content_filter' => 'twentyfourteen_get_featured_posts',
		'max_posts' => 6,
	) );

	// This theme uses its own gallery styles.
	add_filter( 'use_default_gallery_style', '__return_false' );
}
endif; // twentyfourteen_setup
add_action( 'after_setup_theme', 'twentyfourteen_setup' );

/**
 * Adjust content_width value for image attachment template.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return void
 */
function twentyfourteen_content_width() {
	if ( is_attachment() && wp_attachment_is_image() ) {
		$GLOBALS['content_width'] = 810;
	}
}
add_action( 'template_redirect', 'twentyfourteen_content_width' );

/**
 * Getter function for Featured Content Plugin.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return array An array of WP_Post objects.
 */
function twentyfourteen_get_featured_posts() {
	/**
	 * Filter the featured posts to return in Twenty Fourteen.
	 *
	 * @since Twenty Fourteen 1.0
	 *
	 * @param array|bool $posts Array of featured posts, otherwise false.
	 */
	return apply_filters( 'twentyfourteen_get_featured_posts', array() );
}

/**
 * A helper conditional function that returns a boolean value.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return bool Whether there are featured posts.
 */
function twentyfourteen_has_featured_posts() {
	return ! is_paged() && (bool) twentyfourteen_get_featured_posts();
}

/**
 * Register three Twenty Fourteen widget areas.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return void
 */
function twentyfourteen_widgets_init() {
	require get_template_directory() . '/inc/widgets.php';
	register_widget( 'Twenty_Fourteen_Ephemera_Widget' );
	register_sidebar( array(
		'name' => __( 'Utility Menu', 'toolbox' ),
		'id' => 'utility-nav',
		'description' => __( 'An optional secondary menu located above the header', 'toolbox' ),
		'before_widget' => '<div class="widget %2$s">',
		'after_widget' => "</div>",
	) );
	register_sidebar( array(
		'name'          => __( 'Primary Sidebar', 'Liquidation' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Main sidebar that appears on the left.', 'Liquidation' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'Content Sidebar', 'Liquidation' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Additional sidebar that appears on the right.', 'Liquidation' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'Liquidation' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Appears in the footer section of the site.', 'Liquidation' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'twentyfourteen_widgets_init' );

/**
 * Register Lato Google font for Twenty Fourteen.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return string
 */
function twentyfourteen_font_url() {
	$font_url = '';
	/*
	 * Translators: If there are characters in your language that are not supported
	 * by Lato, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'Liquidation' ) ) {
		$font_url = add_query_arg( 'family', urlencode( 'Lato:300,400,700,900,300italic,400italic,700italic' ), "//fonts.googleapis.com/css" );
	}

	return $font_url;
}

/**
 * Enqueue scripts and styles for the front end.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return void
 */
function twentyfourteen_scripts() {
//	wp_enqueue_script( 'twentyfourteen-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), '20131209', true );
}
add_action( 'wp_enqueue_scripts', 'twentyfourteen_scripts' );

if ( ! function_exists( 'twentyfourteen_the_attached_image' ) ) :
/**
 * Print the attached image with a link to the next attached image.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return void
 */
function twentyfourteen_the_attached_image() {
	$post                = get_post();
	/**
	 * Filter the default Twenty Fourteen attachment size.
	 *
	 * @since Twenty Fourteen 1.0
	 *
	 * @param array $dimensions {
	 *     An array of height and width dimensions.
	 *
	 *     @type int $height Height of the image in pixels. Default 810.
	 *     @type int $width  Width of the image in pixels. Default 810.
	 * }
	 */
	$attachment_size     = apply_filters( 'twentyfourteen_attachment_size', array( 810, 810 ) );
	$next_attachment_url = wp_get_attachment_url();

	/*
	 * Grab the IDs of all the image attachments in a gallery so we can get the URL
	 * of the next adjacent image in a gallery, or the first image (if we're
	 * looking at the last image in a gallery), or, in a gallery of one, just the
	 * link to that image file.
	 */
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID',
	) );

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id ) {
			$next_attachment_url = get_attachment_link( $next_id );
		}

		// or get the URL of the first image attachment.
		else {
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
		}
	}

	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;

if ( ! function_exists( 'twentyfourteen_list_authors' ) ) :
/**
 * Print a list of all site contributors who published at least one post.
 *
 * @since Twenty Fourteen 1.0
 *
 * @return void
 */
function twentyfourteen_list_authors() {
	$contributor_ids = get_users( array(
		'fields'  => 'ID',
		'orderby' => 'post_count',
		'order'   => 'DESC',
		'who'     => 'authors',
	) );

	foreach ( $contributor_ids as $contributor_id ) :
		$post_count = count_user_posts( $contributor_id );

		// Move on if user has not published a post (yet).
		if ( ! $post_count ) {
			continue;
		}
	?>

	<div class="contributor">
		<div class="contributor-info">
			<div class="contributor-avatar"><?php echo get_avatar( $contributor_id, 132 ); ?></div>
			<div class="contributor-summary">
				<h2 class="contributor-name"><?php echo get_the_author_meta( 'display_name', $contributor_id ); ?></h2>
				<p class="contributor-bio">
					<?php echo get_the_author_meta( 'description', $contributor_id ); ?>
				</p>
				<a class="contributor-posts-link" href="<?php echo esc_url( get_author_posts_url( $contributor_id ) ); ?>">
					<?php printf( _n( '%d Article', '%d Articles', $post_count, 'twentyfourteen' ), $post_count ); ?>
				</a>
			</div><!-- .contributor-summary -->
		</div><!-- .contributor-info -->
	</div><!-- .contributor -->

	<?php
	endforeach;
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Presence of header image.
 * 3. Index views.
 * 4. Full-width content layout.
 * 5. Presence of footer widgets.
 * 6. Single views.
 * 7. Featured content layout.
 *
 * @since Twenty Fourteen 1.0
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 */
function twentyfourteen_body_classes( $classes ) {
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( get_header_image() ) {
		$classes[] = 'header-image';
	} else {
		$classes[] = 'masthead-fixed';
	}

	if ( is_archive() || is_search() || is_home() ) {
		$classes[] = 'list-view';
	}

	if ( ( ! is_active_sidebar( 'sidebar-2' ) )
		|| is_page_template( 'page-templates/full-width.php' )
		|| is_page_template( 'page-templates/contributors.php' )
		|| is_attachment() ) {
		$classes[] = 'full-width';
	}

	if ( is_active_sidebar( 'sidebar-3' ) ) {
		$classes[] = 'footer-widgets';
	}

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'singular';
	}

	if ( is_front_page() && 'slider' == get_theme_mod( 'featured_content_layout' ) ) {
		$classes[] = 'slider';
	} elseif ( is_front_page() ) {
		$classes[] = 'grid';
	}

	return $classes;
}
add_filter( 'body_class', 'twentyfourteen_body_classes' );

/**
 * Extend the default WordPress post classes.
 *
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 *
 * @since Twenty Fourteen 1.0
 *
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 */
function twentyfourteen_post_classes( $classes ) {
	if ( ! post_password_required() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}

	return $classes;
}
add_filter( 'post_class', 'twentyfourteen_post_classes' );

/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @since Twenty Fourteen 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function twentyfourteen_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentyfourteen' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'twentyfourteen_wp_title', 10, 2 );

// Implement Custom Header features.
require get_template_directory() . '/inc/custom-header.php';

// Custom template tags for this theme.
require get_template_directory() . '/inc/template-tags.php';

// Add Theme Customizer functionality.
require get_template_directory() . '/inc/customizer.php';

/*
 * Add Featured Content functionality.
 *
 * To overwrite in a plugin, define your own Featured_Content class on or
 * before the 'setup_theme' hook.
 */
if ( ! class_exists( 'Featured_Content' ) && 'plugins.php' !== $GLOBALS['pagenow'] ) {
	require get_template_directory() . '/inc/featured-content.php';
}


/**
 * @param string $where
 * @param WP_Query $query
 * @return string
 */
function dl_posts_where_search($where, $query) {
    /** @var wpdb $wpdb */
    global $wpdb;
    $searchQuery = $query->get('_dl_search_query');
    $searchDescriptions = $query->get('_dl_search_descriptions');
    if (!empty($searchQuery)) {
        $likeValue = '%'.esc_sql(like_escape($searchQuery)).'%';
        $likeQuery = sprintf("%s.post_title LIKE '%s'", $wpdb->posts, $likeValue);
        if ($searchDescriptions) {
            $likeQuery .= sprintf(" OR %s.post_content LIKE '%s'", $wpdb->posts, $likeValue);
            $likeQuery .= sprintf(" OR %s.post_excerpt LIKE '%s'", $wpdb->posts, $likeValue);
        }
        $where .= ' AND ('.$likeQuery.')';
    }
    return $where;
}

function dl_posts_joins_for_my_offers($join) {
    /** @var wpdb $wpdb */
    global $wpdb;
    if ($join != '') {
        $join .= ' ';
    }
    $join .= "JOIN $wpdb->posts posts_parent ON $wpdb->posts.post_parent = posts_parent.ID AND posts_parent.post_status = 'publish' LEFT JOIN $wpdb->postmeta post_parent_meta ON post_parent_meta.post_id = posts_parent.ID AND post_parent_meta.meta_key='_dl_status'";
    return $join;
}

function dl_posts_where_for_my_offers($where) {
    if ($where != '') {
        $where .= ' AND ';
    }
    $where .= '(post_parent_meta.meta_value IS NULL OR post_parent_meta.meta_value != "cancelled")';
    return $where;
}

function dl_products_page_link($page, $params) {
    $params['page'] = $page;
    return '/availability-list/?'.build_query($params);
}

function dl_my_auctions_page_link($page, $params) {
    $params['page'] = $page;
    return '/my-auctions/?'.build_query($params);
}

function dl_my_auctions_offers_page_link($page, $params) {
    $params['page'] = $page;
    return '/offers-manage/?'.build_query($params);
}

function dl_my_auctions_offers_offers_page_link($page, $params) {
    $params['page'] = $page;
    return '/offers-manage-list/?'.build_query($params);
}

function dl_my_wishes_page_link($page, $params) {
    $params['page'] = $page;
    return '/watch-list/?'.build_query($params);
}

function dl_my_offers_page_link($page, $params) {
    $params['page'] = $page;
    return '/offers/?'.build_query($params);
}

function dl_get_country_title($id) {
    $countries = get_option("dl_countries", array());
    return isset($countries[$id]) ? $countries[$id]['name'] : '';
}

function dl_get_state_title($id) {
    $states = get_option("dl_states", array());
    return isset($states[$id]) ? $states[$id]['name'] : '';
}

function dl_enqueue_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'dl_enqueue_scripts');

function dl_html_tag($name, $attributes, $content = '') {
    $result = "<$name";
    foreach ($attributes as $attr => $value) {
        $result .= sprintf(' %s="%s"', $attr, esc_attr($value));
    }
    if (is_null($content)) {
        $result .=  " />";
    } else {
        $result .=  ">$content</$name>";
    }
    return $result;
}

function dl_html_select($params) {
    $options = isset($params['options']) ? $params['options'] : array();
    $selectedValue = isset($params['value']) ? $params['value'] : array();
    $content = array();
    foreach ($options as $value => $data) {
        $attr = array('value' => $value);
        if ($value == $selectedValue) {
            $attr['selected'] = 'selected';
        }
        if (is_array($data)) {
            $title = $data['title'];
            $attr = array_merge($attr, $data['attr']);
        } else {
            $title = $data;
        }
        $content[] = dl_html_tag('option', $attr, esc_html($title));
    }
    $attr = isset($params['attr']) ? $params['attr'] : array();
    return dl_html_tag('select', $attr, implode('', $content));
}

function dl_product_bid_link(DL_Product $product) {
    return get_permalink($product->getPost());
    //return sprintf('/offers-short/?id=%d', $product->getPost()->ID);
}

function dl_product_seller_link(DL_Product $product) {
    return '';
}

function dl_product_seller_name(DL_Product $product) {
    $ownerId = $product->getPost()->post_author;
    $owner = get_userdata($ownerId);
    if (empty($owner)) {
        return '&nbsp;';
    }
    return $owner->user_login;
}

function dl_product_category(DL_Product $product) {
    $category = wp_get_post_terms($product->getPost()->ID, 'product_cat');
    return empty($category) ? '' : $category[0]->name;
}

function dl_render_new_product_option($option) {
    $name = '_dl_option_'.$option['id'];
?>
    <div class="col">
        <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($option['name']); ?>:</label>
        <?php $type = isset($option['type']) ? $option['type'] : 'string'; ?>
        <?php switch ($type) {
            case 'bool':
                $attr = array(
                    'name' => $name,
                    'type' => 'checkbox',
                    'id' => $name,
                    'value' => '1'
                );
                if (isset($_POST[$name]) && $_POST[$name] == '1') {
                    $attr['checked'] = 'checked';
                }
                echo dl_html_tag('input', $attr, null);
                break;
            case 'enum':
                echo dl_html_select(array(
                    'value' => isset($_POST[$name]) ? $_POST[$_POST[$name]] : '',
                    'options' => array_combine($option['values'], $option['values']),
                    'attr' => array(
                        'id' => $name,
                        'name' => $name,
                    )
                ));
                break;
            default:
                echo dl_html_tag('input', array(
                    'name' => $name,
                    'type' => 'text',
                    'id' => $name,
                    'value' => isset($_POST[$name]) ? $_POST[$name] : ''
                ), null);
        } ?>
   </div>
<?php
}

function dl_email_alerts_checkbox($name, $title) {
    $user = wp_get_current_user();
    if (empty($user)) {
        return '';
    }
    $attr = array(
        'type' => 'checkbox',
        'value' => '1',
        'name' => $name,
        'id' => $name
    );
    if ($user->get($name) == '1') {
        $attr['checked'] = 'checked';
    }
    return dl_html_tag('input', $attr, null) . dl_html_tag('label', array('for' => $name), $title);
}

function dl_blog_the_category() {
    global $post;
    $postTerms = wp_get_object_terms($post->ID, 'dl_blog_categories');
    $terms = array();
    foreach ($postTerms as $term) {
        $terms[] = sprintf('<a href="%2$s">%1$s</a>', esc_html($term->name), esc_attr(get_term_link($term)));
    }
    echo implode($terms, ', ');
}

function dl_blog_get_authors() {
    global $wpdb;

    $defaults = array(
        'orderby' => 'name', 'order' => 'ASC', 'number' => '',
        'optioncount' => false, 'exclude_admin' => true,
        'show_fullname' => false, 'hide_empty' => true,
        'feed' => '', 'feed_image' => '', 'feed_type' => '', 'echo' => true,
        'style' => 'list', 'html' => true
    );

    $args = wp_parse_args( '', $defaults );
    extract( $args, EXTR_SKIP );

    $return = '';

    $query_args = wp_array_slice_assoc( $args, array( 'orderby', 'order', 'number' ) );
    $query_args['fields'] = 'ids';
    $authors = get_users( $query_args );

    $author_count = array();
    foreach ( (array) $wpdb->get_results("SELECT DISTINCT post_author, COUNT(ID) AS count FROM $wpdb->posts WHERE post_type = 'dl_blog' AND " . get_private_posts_cap_sql( 'post' ) . " GROUP BY post_author") as $row )
        $author_count[$row->post_author] = $row->count;

    $result = array();

    foreach ( $authors as $author_id ) {
        $author = get_userdata( $author_id );

        if ( $exclude_admin && 'admin' == $author->display_name )
            continue;

        $posts = isset( $author_count[$author->ID] ) ? $author_count[$author->ID] : 0;

        if ( !$posts && $hide_empty )
            continue;
        $author->count = $posts;
        $result[] = $author;
    }
    return $result;
}


function categpry_select() {
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
	?>
	<select id="with-placeholder">
							<option value="value-1" selected>All Categories</option>
							<?php 
							
						
	foreach ($all_categories as $cat) {
	 
	    if($cat->category_parent == 0) {
	    	?>

	    	<option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>

	    	<?php
	    }
	}
	?>
	</select>
	<?php

}
  
function showReadMore($title, $len) {
	echo esc_attr(substr($title, 0, 53));
	if (strlen($title) > $len ) {
		echo "...";	
	}		
}

// function register_link_url( $url ) {
//   	if ( ! is_user_logged_in() ) {
// 		if ( get_option('users_can_register') )
// 			$url = '<li><a href="' . get_bloginfo( 'url' ) . "/register-3" . '">' . __('Register') . '</a></li>';
// 		else
// 			$url = '';
// 	} else {
// 		$url = '<li><a href="' . admin_url() . '">' . __('Site Admin') . '</a></li>';
// 	}

//    return $url;
//    }
// add_filter( 'register', 'register_link_url', 10, 2 );

/**
 * Example showing how to limit search results to pages and posts, and not allowing specific posts/pages
 */





// function jc_filter_search_results($query) {
// 	// check to see if search
// 	if ($query->is_search) {
// 		// only search the paeg post type
// 		$query->set('post_type', 'product');
// 		// dont search these pages
// 	}
// 	return $query;
// }
// add_filter('pre_get_posts','jc_filter_search_results');


// function jc_search_post_excerpt($where = ''){
 
//     global $wp_the_query;
 
//     // escape if not woocommerce search query
//     if ( empty( $wp_the_query->query_vars['wc_query'] ) || empty( $wp_the_query->query_vars['s'] ) )
//             return $where;
 
//     $where = preg_replace("/post_title LIKE ('%[^%]+%')/", "post_title LIKE $1) 
//                 OR (post_content LIKE $1)
//                 OR (jcmt1.meta_key = '_sku' AND CAST(jcmt1.meta_value AS CHAR) LIKE $1 ", $where);
 
//     return $where;
// }
add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
 
function woo_remove_product_tabs( $tabs ) {
 
    // unset( $tabs['description'] );      	// Remove the description tab
    unset( $tabs['reviews'] ); 			// Remove the reviews tab
    unset( $tabs['additional_information'] );  	// Remove the additional information tab
    unset( $tabs['simle_auction_history'] );  	// Remove the additional information tab

    return $tabs;
 
}

// Remove default WooCommerce breadcrumbs and add Yoast ones instead
remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20, 0);
$slug = basename(get_permalink());
if($slug=="")
add_action('woocommerce_after_main_content','woocommerce_bottom_product_display',15);

function woocommerce_bottom_product_display(){
	wc_get_template( 'single-product/static_blog_customer.php' );
}

?>



