<?php


class DL_User
{

    /** @var  WP_User */
    private $user;

    function __construct($user)
    {
        $this->user = $user;
    }

    public static function load($data)
    {
        /** @var WP_User $post */
        $user = null;
        if (is_a($data, 'WP_User')) {
            $user = $data;
        } else {
            $user = get_userdata($data);
            if (empty($user)) {
                $user = null;
            }
        }
        if (is_null($user)) {
            return null;
        }
        return new DL_User($user);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isSeller()
    {
        return $this->user->get('_dl_seller') == 'yes';
    }

    public function getApiKey()
    {
        $key = $this->user->get('_dl_api_key');
        if (empty($key)) {
            $key = $this->user->ID . '-' . md5(uniqid("", true));
            update_user_meta($this->user->ID, '_dl_api_key', $key);
        }
        return $key;
    }

}