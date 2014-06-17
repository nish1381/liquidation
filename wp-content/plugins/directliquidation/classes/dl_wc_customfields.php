<?php


class DL_WC_CustomFields
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
        add_action('woocommerce_product_options_general_product_data', array($this, 'addFields'));
        add_action('woocommerce_process_product_meta', array($this, 'saveFields'));
        add_action('add_meta_boxes', array($this, 'apiPostMetabox'), 10, 2);
    }

    /**
     * @param $pt
     * @param WP_Post $post
     */
    public function apiPostMetabox($pt, $post)
    {
        if ($pt != 'product' || empty($post)) {
            return;
        }
        $product = DL_Product::load($post);
        if (empty($product) || !$product->isAPI()) {
            return;
        }
        add_meta_box(
            'api-prodocut-meta-box',
            'API Product',
            array($this, 'renderAPIPostMetabox'),
            'product',
            'normal',
            'high'
        );
    }

    public function renderAPIPostMetabox()
    {
        echo '<strong id="dl_api_product">Product added by API. You can\'t edit it.</strong>'.
        '<script type="text/javascript">
        jQuery(function($) {$("#dl_api_product").closest("form").submit(function(e){e.preventDefault(); alert("Product added by API. You can\'t edit it.");});});</script>';
    }

    private function getLocations()
    {
        return array(
            "Arkansas",
            "Kentucky",
            "New Jersey",
            "Oklahoma",
        );
    }
    
    private function getConditions() {
        return array(
            'Brand New',
            'Refurbished GRADE-A',
            'Refurbished GRADE-B',
            'Refurbished GRADE-C',
            'Refurbished GRADE-A&B',
            'Refurbished GRADE-A&B&C',
            'Untested Customer Returns',
            'Tested Not Working',
            'Refurbished Incomplete',
            'Salvage Cracked Display'
        );        
    }

    public function addFields()
    {
        echo '<div class="options_group">';
        $locations = $this->getLocations();
        woocommerce_wp_select(
            array(
                'id' => '_dl_location',
                'label' => "Location",
                'options' => array_combine($locations, $locations)
            )
        );
        $conditions = $this->getConditions();
        woocommerce_wp_select(
            array(
                'id' => '_dl_condition',
                'label' => "Grade",
                'options' => array_combine($conditions, $conditions),
                'class' => 'select'
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_dl_quantity',
                'label' => "Quantity",
                'type' => 'number',
                'custom_attributes' => array(
                    'step' => '1',
                    'min' => '0'
                )
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_dl_msrp',
                'label' => "MSRP",
                'type' => 'number',
                'custom_attributes' => array(
                    'step' => 'Any',
                    'min' => '0'
                )
            )
        );
        woocommerce_wp_checkbox(
            array(
                'id' => '_dl_private',
                'wrapper_class' => 'show_if_simple',
                'label' => "Private",
                'cbvalue' => '1'
            )
        );
        global $post;
        $product = DL_Product::load($post);
        if ($product->isPrivate()) {
            printf('<p class="form-field show_if_simple" style="display: block;">
            <label>Private url</label><a href="%1$s">%1$s</a></p>', get_permalink($post));
        }
        woocommerce_wp_select(
            array(
                'id' => '_dl_status',
                'label' => "Status",
                'options' => array(
                    "active" => "Active",
                    "sold" => "Sold",
                    "cancelled" => "Cancelled"
                ),
                'class' => 'select'
            )
        );
        echo '</div>';
        $allOptions = get_option("dl_product_options", array());
        if (count($allOptions)) {
            echo '<div class="options_group">';
            foreach ($allOptions as $option) {
                $type = isset($option['type']) ? $option['type'] : 'string';
                switch ($type) {
                    case 'bool':
                        woocommerce_wp_checkbox(
                            array(
                                'id' => '_dl_option_'.$option['id'],
                                'wrapper_class' => 'show_if_simple',
                                'label' => $option['name'],
                                'cbvalue' => '1'
                            )
                        );
                        break;
                    case 'enum':
                        woocommerce_wp_select(
                            array(
                                'id' => '_dl_option_'.$option['id'],
                                'label' => $option['name'],
                                'options' => array_combine($option['values'], $option['values']),
                                'class' => 'select'
                            )
                        );
                        break;
                    default:
                        woocommerce_wp_text_input(
                            array(
                                'id' => '_dl_option_'.$option['id'],
                                'label' => $option['name'],
                                'class' => ''
                            )
                        );
                }
            }
            echo '</div>';
        }
    }

    public function saveFields($post_id) {
        if (isset($_POST['_dl_status'])) {
            if (in_array($_POST['_dl_status'], array('active', 'sold', 'cancelled'))) {
                update_post_meta($post_id, '_dl_status', $_POST['_dl_status']);
            }
        }
        if (isset($_POST['_dl_location'])) {
            if (in_array($_POST['_dl_location'], $this->getLocations())) {
                update_post_meta($post_id, '_dl_location', $_POST['_dl_location']);
            }
        }
        if (isset($_POST['_dl_condition'])) {
            if (in_array($_POST['_dl_condition'], $this->getConditions())) {
                update_post_meta($post_id, '_dl_condition', $_POST['_dl_condition']);
            }
        }
        if (isset($_POST['_dl_quantity'])) {
            update_post_meta($post_id, '_dl_quantity', $_POST['_dl_quantity']);
        }
        if (isset($_POST['_dl_msrp'])) {
            update_post_meta($post_id, '_dl_msrp', $_POST['_dl_msrp']);
        }
        update_post_meta($post_id, '_dl_private', isset($_POST['_dl_private']) ? '1' : '0');
        $allOptions = get_option("dl_product_options", array());
        foreach ($allOptions as $option) {
            $name = '_dl_option_'.$option['id'];
            $type = isset($option['type']) ? $option['type'] : 'string';
            switch ($type) {
                case 'bool':
                    $value = isset($_POST[$name]) && $_POST[$name] == '1' ? '1' : '0';
                    update_post_meta($post_id, $name, $value);
                    break;
                case 'int':
                    if (isset($_POST[$name])) {
                        update_post_meta($post_id, $name, intval($_POST[$name]));
                    }
                    break;
                case 'double':
                    if (isset($_POST[$name])) {
                        update_post_meta($post_id, $name, floatval($_POST[$name]));
                    }
                    break;
                default:
                    if (isset($_POST[$name])) {
                        update_post_meta($post_id, $name, $_POST[$name]);
                    }
            }
        }
    }

} 