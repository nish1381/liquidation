<?php

class DL_Brands
{

    private static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('init', array($this, 'doInit'));
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'savePostData'));
    }

    public function doInit()
    {
        register_post_type("dl_brand",
            array(
                'labels' => array(
                    'name' => __( 'Brands' ),
                    'singular_name' => __( 'Brand' ),
                    'add_new_item' => __('New brand'),
                    'edit_item' => __('Edit brand'),
                    'new_item' => __('New brand'),
                    'view_item' => __('View brand'),
                    'search_items' => __('Search brands'),
                    'not_found' => __('No brands found'),
                    'not_found_in_trash' => __('No brands found in trash'),
                ),
                'public' => true,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'supports' => array('title', 'editor', 'custom-fields', 'thumbnail'),
                'rewrite' => array(
                    'slug' => 'brands'
                )
            )
        );
        register_taxonomy(
            'dl_brands',
            array('product'),
            array(
                'public' => false
            )
        );
    }

    public function addMetaBoxes() {
        add_meta_box(
            'dl_brands_list',
            __('Brands'),
            array($this, 'metaBox'),
            'product',
            'side'
        );
    }

    public function metaBox($post) {
        $postBrands = array();
        $postTerms = wp_get_object_terms($post->ID, 'dl_brands');
        foreach ($postTerms as $term) {
            $postBrands[$term->name] = $term->name;
        }
        $brands = get_posts(array(
            'posts_per_page' => '-1',
            'post_type' => 'dl_brand',
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'asc'
        ));
        wp_nonce_field( 'dl_brands_box', 'dl_brands_nonce' );
        echo '<ul class="categorychecklist form-no-clear">';
        foreach ($brands as $brand) {
            printf('<li class="popular-category">
                        <label class="selectit">
                            <input type="checkbox" name="dl_brands[]" value="%d"%s>
                            %s
                        </label>
                    </li>', $brand->ID, array_key_exists($brand->ID, $postBrands) ? ' checked="checked"' : '', htmlspecialchars($brand->post_title));
        }
        echo '</ul>';
    }

    public function savePostData($post_id) {
        if ( ! isset( $_POST['dl_brands_nonce'] ) )
            return;

        $nonce = $_POST['dl_brands_nonce'];

        if (!wp_verify_nonce($nonce, 'dl_brands_box'))
            return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        $post = get_post($post_id);

        if ($post->post_type != 'product') {
            return;
        }

        $brands = array();
        if (isset($_POST['dl_brands']) && is_array($_POST['dl_brands'])) {
            foreach ($_POST['dl_brands'] as $id) {
                $name = (string)$id;
                if (!term_exists($name, 'dl_brands')) {
                    wp_insert_term($name, 'dl_brands');
                }
                $brands[] = $name;
            }
        }
        wp_set_object_terms($post->ID, $brands, 'dl_brands');
    }

}
 