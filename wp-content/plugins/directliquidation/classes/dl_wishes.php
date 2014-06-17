<?php

class DL_Wishes 
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
        add_action('dl_action_add_wish', array($this, 'doAddWish'));
        add_action('dl_action_remove_wish', array($this, 'doRemoveWish'));
    }

    public function doInit()
    {
        register_post_type("dl_wish",
            array(
                'public' => false,
            )
        );
    }

    public function doAddWish() {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            echo json_encode(array(
                'success' => false,
                'error' => 'Login first'
            ));
            die();
        }
        if (!isset($_POST['id'])) {
            return;
        }
        $product = DL_Product::load(intval($_POST['id']));
        if (empty($product)) {
            wp_redirect('/');
            die();
        }
        if ($product->isWishlisted($user)) {
            echo json_encode(array(
                'success' => false,
                'error' => 'Product was already added to your watch list.'
            ));
        } else {
            $newWishId = wp_insert_post(array(
                'post_status' => 'publish',
                'post_type' => 'dl_wish',
                'post_parent' => $product->getPost()->ID,
                'post_author' => $user->ID
            ));
            echo json_encode(array(
                'success' => true
            ));
        }
        die();
    }

    public function doRemoveWish() {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            echo json_encode(array(
                'success' => false,
                'error' => 'Login first'
            ));
            die();
        }
        if (!isset($_POST['id'])) {
            return;
        }
        $product = DL_Product::load(intval($_POST['id']));
        if (empty($product)) {
            wp_redirect('/');
            die();
        }
        $wish = $product->getWish($user);
        if ($wish) {
            $wish->remove();
            echo json_encode(array(
                'success' => true,
                'message' => 'Product was removed from your watch list.'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Product is not in your watch list.'
            ));
        }
        die();
    }

} 