<?php

class DL_Brand
{

    /** @var  WP_Post */
    private $post;

    function __construct($post)
    {
        $this->post = $post;
    }

    public static function load($brand)
    {
        /** @var WP_Post $post */
        $post = null;
        if (is_a($brand, 'WP_Post')) {
            $post = $brand;
        } else {
            $post = get_post($brand);
        }
        if (is_null($post) || $post->post_type != 'dl_brand') {
            return null;
        }
        return new DL_Brand($post);
    }

    /**
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    public function isPublished() {
        return $this->getPost()->post_status == 'publish';
    }

} 