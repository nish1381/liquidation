<?php

class DL_Auctions
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
        add_action('pre_get_posts', array($this, 'filterQuery'));
        add_action('dl_create_product', array($this, 'doCreateProduct'));
    }

    /**
     * @param WP_Query $query
     * @return mixed
     */
    public function filterQuery($query) {
        if (!is_admin() && $query->get('dl_show_cancelled') != 'yes' && $query->get('post_type') == 'product') {
            $mq = $query->get('meta_query');
            if ($mq == '') {
                $mq = array();
            }
            $mq[] = array(
                'key' => '_dl_status',
                'value' => 'cancelled',
                'compare' => '!='
            );
            $query->set('meta_query', $mq);
        }
        return $query;

    }

    public function doCreateProduct(&$params)
    {
        $errors = array();
        $user = wp_get_current_user();
        $post_data = array(
            'post_content' => isset($_POST['_dl_description']) ? trim($_POST['_dl_description']) : '',
            'post_title' => isset($_POST['_dl_title']) ? trim($_POST['_dl_title']) : '',
            'post_status' => 'publish',
            'post_type' => 'product',
            'post_excerpt' => isset($_POST['_dl_excerpt']) ? trim($_POST['_dl_excerpt']) : '',
            'post_author' => $user->ID
        );
        $post_meta = array(
            '_dl_status' => 'active',
            '_dl_quantity' => isset($_POST['_dl_quantity']) ? intval($_POST['_dl_quantity']) : '',
            '_dl_condition' => isset($_POST['_dl_condition']) ? trim($_POST['_dl_condition']) : '',
            '_dl_msrp' => isset($_POST['_dl_msrp']) ? floatval($_POST['_dl_msrp']) : '',
            '_dl_price' => isset($_POST['_dl_price']) ? floatval($_POST['_dl_price']) : '',
            '_price' => isset($_POST['_dl_price']) ? floatval($_POST['_dl_price']) : '',
            '_sale_price' => isset($_POST['_dl_price']) ? floatval($_POST['_dl_price']) : '',
            '_private' => '0',
            '_active' => '1',
        );
        $productOptions = get_option("dl_product_options", array());
        foreach ($productOptions as $option) {
            $name = '_dl_option_'.$option['id'];
            $type = isset($option['type']) ? $option['type'] : 'string';
            if (!isset($_POST[$name])) {
                continue;
            }
            $value = '';
            switch ($type) {
                case 'int':
                    if (empty($_POST[$name])) {
                        continue;
                    }
                    $value = intval(trim($_POST[$name]));
                    break;
                case 'double':
                    if (empty($_POST[$name])) {
                        continue;
                    }
                    $value = floatval(trim($_POST[$name]));
                    break;
                case 'bool':
                    $value = trim($_POST[$name]) == '1' ? '1' : '0';
                    if ($value == '0') {
                        continue;
                    }
                    break;
                case 'enum':
                    $value = $_POST[$name];
                    if (!in_array($value, $option['values'])) {
                        continue;
                    }
                    break;
                case 'datetime':
                    $time = strtotime($_POST[$name]);
                    if (!$time || $time <= 0) {
                        continue;
                    }
                    $value = date('Y-m-d H:i:s', strtotime($_POST[$name]));
                    break;
                default:
                    $value = trim($_POST[$name]);
                    if ($value == '') {
                        continue;
                    }
            }
            $post_meta[$name] = $value;
        }
        $term = get_term($_POST['_dl_category'], 'product_cat');
        if (empty($term)) {
            throw new Exception("Unknown category", DLSoapResponseMessageCode::CATEGORY_NOT_FOUND);
        }
        $post_data['tax_input'] = array('product_cat' => array($term->term_id));
        if (empty($post_data['post_title'])) {
            $errors['_dl_title'] = 'Title is required.';
        }
        if ($post_meta['_dl_quantity'] <= 0) {
            $errors['_dl_quantity'] = 'Quantity is required.';
        }
        if ($post_meta['_dl_price'] <= 0) {
            $errors['_dl_price'] = 'Start price is required.';
        }
        if ($post_meta['_dl_msrp'] <= 0) {
            $errors['_dl_msrp'] = 'MSRP is required.';
        }
        if (empty($post_data['post_excerpt'])) {
            $errors['_dl_excerpt'] = 'Short summary is required.';
        }
        if (empty($post_data['post_content'])) {
            $errors['_dl_description'] = 'Description is required.';
        }
        if (count($errors)) {
            $params['errors'] = $errors;
            $params['error'] = reset($errors);
            return;
        }
        $postId = wp_insert_post($post_data);
        foreach ($post_meta as $key => $value) {
            update_post_meta($postId, $key, $value);
        }
        if (isset($_FILES['_dl_manifest']) && $_FILES['_dl_manifest']['error'] == 0 && $_FILES['_dl_manifest']['size'] > 0 && preg_match('/\.(xlsx?)$/', $_FILES['_dl_manifest']['name'], $m)) {
            $uploadPath = WP_CONTENT_DIR . '/uploads/manifests/'.$postId;
            mkdir($uploadPath, 0777, true);
            $fileName = 'manifest.'.$m[1];
            move_uploaded_file($_FILES['_dl_manifest']['tmp_name'], $uploadPath .'/'.$fileName);
            $attachment = array(
                'guid' => get_site_url(null, '/wp-content/uploads/manifests/'.$postId.'/'.$fileName),
                'post_mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'post_title' => $fileName,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, null, $postId);
            update_post_meta($attach_id, '_internal_path', $uploadPath .'/'.$fileName);
            update_post_meta($postId, '_manifest', $attach_id);
        }
        $images = array();
        if (isset($_FILES['_dl_image'])) {
            $i = 0;
            foreach ($_FILES['_dl_image']['error'] as $k => $error) {
                if ($error != 0) {
                    continue;
                }
                if ($_FILES['_dl_image']['size'][$k] > 0 && preg_match('/\.(jpg?)$/', $_FILES['_dl_image']['name'][$k])) {
                    $i++;
                    $uploadPath = WP_CONTENT_DIR . '/uploads/product_images/'.$postId;
                    mkdir($uploadPath, 0777, true);
                    $fileName = "{$i}.jpg";
                    move_uploaded_file($_FILES['_dl_image']['tmp_name'][$k], $uploadPath .'/'.$fileName);
                    $attachment = array(
                        'guid' => get_site_url(null, '/wp-content/uploads/product_images/'.$postId.'/'.$fileName),
                        'post_mime_type' => 'image/jpeg',
                        'post_title' => $fileName,
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment($attachment, $uploadPath .'/'.$fileName, $postId);
                    update_post_meta($attach_id, '_internal_path', $uploadPath .'/'.$fileName);
                    $images[] = $attach_id;
                }
            }
        }
        if (count($images)) {
            update_post_meta($postId, '_product_image_gallery', implode(",", $images));
            update_post_meta($postId, '_thumbnail_id', $images[0]);
        }
        $params['success'] = true;
        $params['post_id'] = $postId;
        $params['url'] = get_permalink($postId);
    }

}