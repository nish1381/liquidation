<?php

class DL_Plugin
{

    private static $pluginDir;
    private static $pluginFile;


    public static function init($pluginFile)
    {
        DL_Plugin::$pluginFile = $pluginFile;
        DL_Plugin::$pluginDir = dirname($pluginFile);
        register_activation_hook($pluginFile, 'DL_Plugin::activate');
        add_action('init', 'DL_Plugin::blockCustomersAdmin');
        add_action('after_setup_theme', 'DL_Plugin::removeCustomersAdminBar');
        DL_WC_CustomFields::getInstance()->init();
        DL_Users::getInstance()->init();
        DL_Auctions::getInstance()->init();
        DL_Offers::getInstance()->init();
        DL_Testimonials::getInstance()->init();
        DL_Brands::getInstance()->init();
        DL_Blog::getInstance()->init();
        DL_Wishes::getInstance()->init();
        DL_Products::getInstance()->init();
        DL_Offers_Admin_List::getInstance()->init();
        DL_Extra_Fields::getInstance()->init();
        add_filter('rewrite_rules_array', 'DL_Plugin::flushRewriteRules');
        add_action('wp_loaded', 'DL_Plugin::insertRewriteRules');
        add_action('parse_request', 'DL_Plugin::parseRequest');
    }

    /**
     * @param WP $wp
     */
    public static function parseRequest($wp)
    {
        if (!empty($wp->matched_query)) {
            parse_str($wp->matched_query, $params);
            if (array_key_exists('dl-action', $params)) {
                do_action('dl_action_'.$params['dl-action'], $params);
                die();
            }
        }
    }

    public static function insertRewriteRules()
    {
        $rules = get_option('rewrite_rules');
        if (!isset(
            $rules['news/page/(\d+)/?$'],
            $rules['news/?$'],
            $rules['forms/uploaded/((MultistateForm|CreditCardAuthorizationForm|(TaxExemptForm-[A-Z]{2}))\.pdf)$'],
            $rules['forms/offers/(\d+)/((MultistateForm|CreditCardAuthorizationForm|(TaxExemptForm-[A-Z]{2}))\.pdf)$'],
            $rules['dl/wishes/(add|remove)$']
        )) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    public static function flushRewriteRules($rules)
    {
        $newrules = array();
        $newrules['dl/wishes/(add|remove)'] = 'index.php?dl-action=$matches[1]_wish';
        $newrules['news/?$'] = 'index.php?category_name=news&paged=1';
        $newrules['news/page/(\d+)/?$'] = 'index.php?category_name=news&paged=$matches[1]';
        $newrules['forms/uploaded/((MultistateForm|CreditCardAuthorizationForm|(TaxExemptForm-[A-Z]{2}))\.pdf)$'] = 'index.php?dl-action=form&type=$matches[1]';
        $newrules['forms/offers/(\d+)/((MultistateForm|CreditCardAuthorizationForm|(TaxExemptForm-[A-Z]{2}))\.pdf)$'] = 'index.php?dl-action=offer_form&offerId=$matches[1]&type=$matches[2]';
        return $newrules + $rules;
    }

    public static function activate()
    {
        add_role('dl_customer', "DL Customer", array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false
        ));
    }

    public static function blockCustomersAdmin()
    {
        if (is_admin() && current_user_can('dl_customer')) {
            wp_redirect(home_url());
            exit;
        }
    }

    public static function removeCustomersAdminBar()
    {
        if (current_user_can('dl_customer')) {
            show_admin_bar(false);
        }
    }

    public static function getTmpDir()
    {
        $name = DL_Plugin::$pluginDir . '/tmp';
        if (!file_exists($name)) {
            mkdir($name);
        }
        return $name;
    }

} 