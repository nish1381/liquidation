<?php


class DL_Product
{

    const STATUS_ACTIVE = "active";
    const STATUS_SOLD = "sold";
    const STATUS_CANCELLED = "cancelled";

    /** @var  WP_Post */
    private $post;

    function __construct($post) {
        $this->post = $post;
    }

    public static function load($product) {
        /** @var WP_Post $post */
        $post = null;
        if (is_a($product, 'WP_Post')) {
            $post = $product;
        } else {
            $post = get_post($product);
        }
        if (is_null($post) || $post->post_type != 'product') {
            return null;
        }
        return new DL_Product($post);
    }

    public function isVisible() {
        return $this->getStatus() != 'cancelled' && $this->post->post_status == 'publish';
    }

    public function isPrivate() {
        return $this->getSingleMeta('_dl_private') == '1';
    }

    public function isAPI() {
        return $this->getSingleMeta('_dl_api') == 'yes';
    }

    public function getPrivateHash() {
        $hash = $this->getSingleMeta('_dl_private_hash');
        if (empty($hash)) {
            $hash = uniqid();
            update_post_meta($this->post->ID, '_dl_private_hash', $hash);
        }
        return $hash;
    }

    public function getStatus() {
        return get_post_meta($this->post->ID, '_dl_status', true);
    }

    public function setStatus($status) {
        update_post_meta($this->post->ID, '_dl_status', $status);
        if ($status == DL_Product::STATUS_CANCELLED) {
            update_post_meta($this->post->ID, '_dl_cancelled_time', date('Y-m-d H:i:s'));
        }
    }

    public function getCondition() {
        return get_post_meta($this->post->ID, '_dl_condition', true);
    }

    public function getMSRP() {
        return get_post_meta($this->post->ID, '_dl_msrp', true);
    }

    public function getSoldOfferId() {
        return get_post_meta($this->post->ID, '_dl_sold_offer_id', true);
    }

    public function getSoldTime() {
        return get_post_meta($this->post->ID, '_dl_sold_offer_time', true);
    }

    public function getCancelledTime() {
        $time = get_post_meta($this->post->ID, '_dl_cancelled_time', true);
        if (empty($time)) {
            $time = $this->post->post_modified;
        }
        return $time;
    }

    public function getQuantity() {
        return get_post_meta($this->post->ID, '_dl_quantity', true);
    }

    public function getLocation() {
        return get_post_meta($this->post->ID, '_dl_location', true);
    }

    public function setSoldOfferId($offerId) {
        update_post_meta($this->post->ID, '_dl_sold_offer_id', $offerId);
        update_post_meta($this->post->ID, '_dl_sold_offer_time', date('Y-m-d H:i:s'));
        $this->setStatus(DL_Product::STATUS_SOLD);
    }

    /**
     * @return WP_Post[]
     */
    public function getImages() {
        $args = array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => $this->post->ID,
            'include' => get_post_meta($this->post->ID, '_product_image_gallery', true)
        );
        $posts = get_posts($args);
        return $posts;
    }

    private function makeManifestXML() {
        $manifest = $this->getManifest();
        if (empty($manifest)) {
            return null;
        }
        $response = wp_remote_get( $manifest->guid);

        if ( is_wp_error( $response ) ) {
            return null;
        } else {
            $data = $response['body'];
        }
        if (empty($data)) {
            return null;
        }
        if (!preg_match('/\.(xlsx?)$/', $manifest->guid, $m)) {
            return null;
        }
        $tmpFile = DL_Plugin::getTmpDir() . '/manifest-' . $manifest->ID . '.' . $m[1];
        file_put_contents($tmpFile, $data);
        $xml = new SimpleXMLElement("<manifest/>");
        $objPHPExcel = PHPExcel_IOFactory::load($tmpFile);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        $availableHeaders = array(
            array('title' => 'Manufacturer', 'headers' => array('manufacturer')),
            array('title' => 'Item Description', 'headers' => array('item description', 'itemdescription', 'item_description')),
            array('title' => 'Model', 'headers' => array('model')),
            array('title' => 'UPC', 'headers' => array('upc', 'upc template', 'upc_template')),
            array('title' => 'Condition', 'headers' => array('grade', 'physical condition', 'condition')),
            array('title' => 'RetailPrice', 'headers' => array('retail', 'retail_price', 'retail price'))
        );

        $columns = array();
        $tempColumns = array();
        $tempHeaders = array();
        $headers = array();
        $headersRow = array();

        $modelFound = false;
        $manufacturerFound = false;

        foreach($sheetData[1] as $column => $headerRowValue)
        {
            for($i = 0; $i < count($availableHeaders); $i++)
            {
                if(in_array(strtolower($headerRowValue), $availableHeaders[$i]['headers']))
                {
                    $tempColumns[$i] = $column;
                    $tempHeaders[$i] = $availableHeaders[$i]['title'];
                    $headersRow[$tempHeaders[$i]] = $tempHeaders[$i];

                    if($availableHeaders[$i]['title'] == 'Model')
                        $modelFound = true;
                    elseif($availableHeaders[$i]['title'] == 'Manufacturer')
                        $manufacturerFound = true;

                }
            }
        }

        for($i = 0; $i < count($availableHeaders); $i++) {
            if(isset($tempColumns[$i]))
            {
                // hardcode - we don't show item description if manufacturer and model were found
                if($modelFound && $manufacturerFound && $tempHeaders[$i] == 'Item Description')
                    continue;

                $columns []= $tempColumns[$i];
                $headers []= $tempHeaders[$i];
            }
        }

        for($i = 2; $i <= count($sheetData); $i++)
        {
            $item = $xml->addChild('item');
            $row = $sheetData[$i];
            foreach($columns as $index=>$column)
            {
                $item->{$headers[$index]} = (string)$row[$column];
            }
        }
        unlink($tmpFile);
        $xmlStr = $xml->asXML();
        update_post_meta($this->post->ID, '_manifest_xml', $xmlStr);
        return $xmlStr;
    }

    public function getManifestData()
    {
        $xml = $this->getSingleMeta("_manifest_xml");
        if (empty($xml)) {
            $xml = $this->makeManifestXML();
        }
        if (empty($xml)) {
            return null;
        }
        $xml = new SimpleXMLElement($xml);
        $data = array();
        foreach ($xml->item as $item) {
            $data[] = array(
                'manufacturer' => (string)$item->Manufacturer,
                'model' => (string)$item->Model,
                'upc' => (string)$item->UPC,
                'condition' => (string)$item->Condition,
                'retailPrice' => (string)$item->RetailPrice,
            );
        }
        return $data;
    }

    /**
     * @return WP_Post
     */
    public function getManifest() {
        $manifestId = get_post_meta($this->post->ID, '_manifest', true);
        if (empty($manifestId)) {
            return null;
        }
        $args = array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => $this->post->ID,
            'include' => $manifestId
        );
        $posts = get_posts($args);
        return count($posts) ? $posts[0] : null;
    }

    /**
     * @return WP_Post
     */
    public function getThumbnail() {
        $id = get_post_thumbnail_id($this->post->ID);
        return get_post($id);
    }

    /**
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    public function getSingleMeta($name) {
        return get_post_meta($this->post->ID, $name, true);
    }

    /**
     * @return DL_Product_Option[]
     */
    public function getOptions() {
        $options = get_option("dl_product_options", array());
        $result = array();
        foreach ($options as $option) {
            $id = $option['id'];
            $name = $option['name'];
            $order = $option['order'];
            $value = $this->getSingleMeta('_dl_option_'.$id);
            if (empty($value)) {
                continue;
            }
            $result[] = new DL_Product_Option($id, $name, $order, $value, $option);
        }
        return $result;
    }

    public function getStartPrice() {
        return $this->getSingleMeta('_price');
    }

    public function getMaxOfferPrice() {
        $result = $this->getSingleMeta('_dl_max_offer_price');
        return empty($result) ? 0 : $result;
    }

    public function getReservedPrice() {
        $result = $this->getSingleMeta('_dl_reserved_price');
        return empty($result) ? 0 : $result;
    }

    public function getGreenPrice() {
        return max($this->getStartPrice(), $this->getReservedPrice());
    }

    public function getMaxOfferId() {
        return $this->getSingleMeta('_dl_max_offer_id');
    }

    public function getMaxOffer() {
        $maxOfferId = $this->getMaxOfferId();
        return empty($maxOfferId) ? null : DL_Offer::load($maxOfferId);
    }

    public function getMSRPPercent() {
        $price = floatval($this->getMaxOfferPrice());
        if ($price == 0) {
            $price = floatval($this->getStartPrice());
        }
        return 100*$price/ floatval($this->getMSRP());
    }

    public function getOffersCount() {
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'dl_offer',
            'post_status' => 'publish',
            'post_parent' => $this->post->ID
        );
        $q = new WP_Query($args);
        return $q->found_posts;
    }

    public function getAllOffers() {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'dl_offer',
            'post_status' => 'publish',
            'post_parent' => $this->post->ID
        );
        $q = new WP_Query($args);
        return $q;
    }

    public function isWishlisted($user) {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'dl_wish',
            'author' => $user->ID,
            'post_status' => 'publish',
            'post_parent' => $this->post->ID
        );
        $q = new WP_Query($args);
        return $q->found_posts > 0;
    }

    public function getWish($user) {
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'dl_wish',
            'author' => $user->ID,
            'post_status' => 'publish',
            'post_parent' => $this->post->ID
        );
        $q = new WP_Query($args);
        if ($q->found_posts > 0) {
            return DL_Wish::load($q->next_post());
        } else {
            return null;
        }
    }

    public function getLastOffer() {
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'dl_offer',
            'post_status' => 'publish',
            'post_parent' => $this->post->ID,
            'orderby' => 'date',
            'order' => 'desc'
        );
        $q = new WP_Query($args);
        if ($q->have_posts()) {
            return DL_Offer::load($q->next_post());
        }
        return null;
    }

    public function getMinOffer() {
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'dl_offer',
            'post_status' => 'publish',
            'post_parent' => $this->post->ID,
            'orderby' => 'meta_value_num',
            'meta_key' => '_dl_amount',
            'order' => 'asc'
        );
        $q = new WP_Query($args);
        if ($q->have_posts()) {
            return DL_Offer::load($q->next_post());
        }
        return null;
    }

    /**
     * @return DL_Brand[]
     */
    public function getBrands()
    {
        $result = array();
        $postTerms = wp_get_object_terms($this->getPost()->ID, 'dl_brands');
        foreach ($postTerms as $term) {
            $brand = DL_Brand::load($term->name);
            if ($brand && $brand->isPublished()) {
                $result[] = $brand;
            }
        }
        return $result;
    }

    /**
     * @param DL_Brand[] $brands
     */
    public function setBrands($brands) {
        $brandIds = array();
        foreach ($brands as $brand) {
            $name = (string)$brand->getPost()->ID;
            if (!term_exists($name, 'dl_brands')) {
                wp_insert_term($name, 'dl_brands');
            }
            $brandIds[] = $name;
        }
        wp_set_object_terms($this->getPost()->ID, $brandIds, 'dl_brands');
    }

    public function setTRGOrderId($value)
    {
        update_post_meta($this->post->ID, '_dl_trg_order_id', $value);
    }

}