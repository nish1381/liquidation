<?php

class DL_Wish
{

    /** @var  WP_Post */
    private $post;

    function __construct($post)
    {
        $this->post = $post;
    }

    public static function load($offer)
    {
        /** @var WP_Post $post */
        $post = null;
        if (is_a($offer, 'WP_Post')) {
            $post = $offer;
        } else {
            $post = get_post($offer);
        }
        if (is_null($post) || $post->post_type != 'dl_wish') {
            return null;
        }
        return new DL_Wish($post);
    }

    public function getProduct()
    {
        return DL_Product::load($this->post->post_parent);
    }

    /**
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    public function getUser()
    {
        return get_userdata($this->post->post_author);
    }

    public function remove()
    {
        wp_delete_post($this->post->ID, true);
    }

} 