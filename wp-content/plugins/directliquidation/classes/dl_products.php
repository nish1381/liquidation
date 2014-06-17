<?php

class DL_Products
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
        add_filter('post_type_link', array($this, 'filterPrivateLink'), 10, 2);
        add_filter('posts_join', array($this, 'filterNotPrivateJoin'), 10, 2);
        add_filter('posts_where', array($this, 'filterNotPrivateWhere'), 10, 2);
    }

    /**
     * @param string $where
     * @param WP_Query $query
     * @return string
     */
    function filterNotPrivateWhere($where, $query) {
        /** @var wpdb $wpdb */
        if (!$query->get('_dl_not_private')) {
            return $where;
        }
        if ($where != '') {
            $where .= ' AND ';
        }
        $where .= ' (_dl_npj.meta_value IS NULL OR _dl_npj.meta_value != 1)';
        return $where;
    }

    /**
     * @param string $join
     * @param WP_Query $query
     * @return string
     */
    function filterNotPrivateJoin($join, $query) {
        if (!$query->get('_dl_not_private')) {
            return $join;
        }
        /** @var wpdb $wpdb */
        global $wpdb;
        if ($join != '') {
            $join .= ' ';
        }
        $join .= " LEFT JOIN $wpdb->postmeta _dl_npj ON $wpdb->posts.ID = _dl_npj.post_id AND _dl_npj.meta_key = '_dl_private'";
        return $join;
    }

    public function filterPrivateLink($link, $post)
    {
        $product = DL_Product::load($post);
        if ($product && $product->isPrivate()) {
            if (strpos($link, '?') === false) {
                $link .= '?';
            } else {
                $link .= '&';
            }
            $link .= 'private='.urlencode($product->getPrivateHash());
        }
        return $link;
    }


}
 