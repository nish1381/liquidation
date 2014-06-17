<?php

class DL_Blog
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
    }

    public function doInit()
    {
        register_post_type("dl_blog",
            array(
                'labels' => array(
                    'name' => __( 'Blog' ),
                    'singular_name' => __( 'Blog post' ),
                    'add_new_item' => __('New post'),
                    'edit_item' => __('Edit post'),
                    'new_item' => __('New post'),
                    'view_item' => __('View post'),
                    'search_items' => __('Search posts'),
                    'not_found' => __('No posts found'),
                    'not_found_in_trash' => __('No posts found in trash'),
                ),
                'public' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'supports' => array('title', 'editor', 'author', 'excerpt', 'comments', 'custom-fields', 'thumbnail'),
                'rewrite' => array(
                    'slug' => 'liquidation-102'
                )
            )
        );
        register_taxonomy('dl_blog_categories', 'dl_blog', array(
            'hierarchical' => true,
            'labels' => array(
                'name' => __( 'Blog Category'),
                'singular_name' => __('Blog Category'),
                'search_items' =>  __( 'Search Blog Categories' ),
                'all_items' => __( 'All Blog Categories' ),
                'parent_item' => __( 'Parent Blog Category' ),
                'parent_item_colon' => __( 'Parent Blog Category:' ),
                'edit_item' => __( 'Edit Blog Category' ),
                'update_item' => __( 'Update Blog Category' ),
                'add_new_item' => __( 'Add New Blog Category' ),
                'new_item_name' => __( 'New Blog Category Name' ),
                'menu_name' => __( 'Blog Categories' ),
            ),
            'rewrite' => array(
                'slug' => 'liquidation-102-category',
                'hierarchical' => true
            ),
        ));
        register_taxonomy('dl_blog_tags', 'dl_blog', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __( 'Blog Tag'),
                'singular_name' => __('Blog Tag'),
                'search_items' =>  __( 'Search Blog Tags' ),
                'all_items' => __( 'All Blog Tags' ),
                'parent_item' => __( 'Parent Blog Tag' ),
                'edit_item' => __( 'Edit Blog Tag' ),
                'update_item' => __( 'Update Blog Tag' ),
                'add_new_item' => __( 'Add New Blog Tag' ),
                'new_item_name' => __( 'New Blog Tag Name' ),
                'menu_name' => __( 'Blog Tags' ),
            ),
            'rewrite' => array(
                'slug' => 'liquidation-102-category',
                'hierarchical' => true
            ),
        ));
    }



}
 