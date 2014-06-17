<?php

class DL_Testimonials
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
        add_action('edit_post', array($this, 'updateTitle'));
        add_action('save_post', array($this, 'updateTitle'));
    }

    public function doInit()
    {
        register_post_type("dl_testimonial",
            array(
                'labels' => array(
                    'name' => __( 'Testimonials' ),
                    'singular_name' => __( 'Testimonial' ),
                    'add_new_item' => __('New testimonial'),
                    'edit_item' => __('Edit testimonial'),
                    'new_item' => __('New testimonial'),
                    'view_item' => __('View testimonial'),
                    'search_items' => __('Search testimonials'),
                    'not_found' => __('No testimonials found'),
                    'not_found_in_trash' => __('No testimonials found in trash'),
                ),
                'public' => true,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'supports' => array('editor', 'custom-fields')
            )
        );
    }

    public function updateTitle($id) {
        $post = get_post($id);
        if ($post->post_type != 'dl_testimonial') {
            return;
        }
        $title = 'Testimonial';
        $author = get_post_meta($id, 'Author', true);
        if (!empty($author)) {
            $title .= ' by '.$author;
        }
        if ($title != $post->post_title) {
            wp_update_post(array(
                'ID' => $id,
                'post_title' => $title
            ));
        }
    }

}
 