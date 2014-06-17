<?php


class DL_Offer
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
        if (is_null($post) || $post->post_type != 'dl_offer') {
            return null;
        }
        return new DL_Offer($post);
    }

    public function getAmount()
    {
        return get_post_meta($this->post->ID, '_dl_amount', true);
    }

    public function getProduct()
    {
        return DL_Product::load($this->post->post_parent);
    }

    public function getSingleMeta($name) {
        return get_post_meta($this->post->ID, $name, true);
    }

    public function getTransactionId() {
        return $this->getSingleMeta('_dl_transaction_id');
    }

    public function getPickupDate() {
        return $this->getSingleMeta('_dl_pickup_date');
    }

    public function getPickupTime() {
        return $this->getSingleMeta('_dl_pickup_time');
    }

    public function setPickupDateTime($date, $time) {
        update_post_meta($this->post->ID, '_dl_pickup_date', $date);
        update_post_meta($this->post->ID, '_dl_pickup_time', $time);
        do_action('dl_after_set_pickup_date_time', $this->post->ID);
    }

    public function setTransactionId($value) {
        update_post_meta($this->post->ID, '_dl_transaction_id', $value);
        do_action('dl_after_set_transaction_id', $this->post->ID);
    }

    public function isAccepted() {
        return $this->getProduct()->getSoldOfferId() == $this->getPost()->ID;
    }

    public function hasTransaction() {
        $transactionId = $this->getTransactionId();
        return !empty($transactionId);
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

}