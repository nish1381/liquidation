<?php

class DL_Users
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
        add_action('dl_login', array($this, 'doLogin'));
        add_action('dl_reset_password', array($this, 'doResetPassword'));
        add_action('dl_register', array($this, 'doRegister'));
        add_action('dl_update_profile', array($this, 'doUpdateProfile'));
        add_action('dl_activate', array($this, 'doActivate'));
        add_action('dl_reset_password_complete', array($this, 'doResetPasswordComplete'));
        add_action('dl_after_register', array($this, 'doAfterRegister'));
        add_action('dl_after_activate', array($this, 'doAfterActivate'));
        add_action('dl_seller_request', array($this, 'doSellerRequest'));
        add_action('dl_action_form', array($this, 'doForm'));
        add_action('dl_action_offer_form', array($this, 'doOfferForm'));
        add_action('edit_user_profile', array($this, 'editUserProfile'));
        add_action('edit_user_profile_update', array($this, 'editUserProfileUpdate'));
        add_filter('wp_authenticate_user', array($this, 'authenticateUser'));
        add_filter('allow_password_reset', array($this, 'isAllowedPasswordReset'));
        add_filter('dl_after_reset_password_key', array($this, 'doAfterResetPasswordKey'));
        add_action('wp_ajax_dl_user_form', array($this, 'userForm'));
    }

    public function authenticateUser($user) {
        if (is_a($user, 'WP_User')) {
            /** @var WP_User $user */
            if ($user->has_cap('dl_customer')) {
                if ($user->get('_dl_require_verify') == 'yes') {
                    $error = new WP_Error();
                    $error->add(0, "Your account has not been verified. Please verify your account or <a href=\'/contacts\' class=\'lnk-blue\'>contact us</a>.");
                    return $error;
                }
                if ($user->get('_dl_disabled') == 'yes') {
                    $error = new WP_Error();
                    $error->add(0, "Your user account has been suspended by administrator");
                    return $error;
                }
            }
        }
        return $user;
    }

    public function doLogin(&$params) {
        $username = sanitize_user($params['username']);
        $password = $params['password'];
        $user = get_user_by('login', $username);
        if (!$user) {
            $params['error'] = 'Invalid username or password, try again';
            do_action('wp_login_failed', $username);
            return;
        }
        if ( !wp_check_password($password, $user->user_pass, $user->ID) ) {
            $params['error'] = 'Invalid username or password, try again';
            do_action('wp_login_failed', $username);
            return;
        }
        $error = apply_filters('wp_authenticate_user', $user, $password);
        if (is_wp_error($error)) {
            $params['error'] = $error->get_error_message();
            return;
        }
        wp_set_auth_cookie($user->ID, false);
        wp_set_current_user($user->ID);
    }

    public function doResetPassword(&$params) {
        $username = sanitize_user($params['username']);
        $user = get_user_by('login', $username);
        if (!$user) {
            $user = get_user_by('email', $username);
        }
        if (!$user || !$user->has_cap('dl_customer')) {
            $params['error'] = 'Invalid username or email, try again';
            return;
        }
        if ($user->get('_dl_require_verify') == 'yes') {
            $params['error'] = 'Your account has not been verified. Please verify your account or <a href=\'/contacts\' class=\'lnk-blue\'>contact us</a>.';
            return;
        }
        if ($user->get('_dl_disabled') == 'yes') {
            $params['error'] = 'Your user account has been suspended by administrator';
            return;
        }
        $key = strtolower(wp_generate_password(24, false));
        update_user_meta($user->ID, '_dl_reset_password_key', $key);
        do_action('dl_after_reset_password_key', $user->ID);
    }

    public function doActivate(&$params) {
        $users = get_users(array('meta_key' => '_dl_verify_key', 'meta_value' => $params['key']));
        if (count($users) == 0) {
            $params['error'] = 'Not found';
            return;
        }
        /** @var WP_User $user */
        $user = $users[0];
        if ($user->get('_dl_require_verify') != 'yes') {
            $params['error'] = 'Already verified';
            return;
        }
        update_user_meta($user->ID, '_dl_require_verify', 'no');
        do_action('dl_after_activate', $user->ID);
    }

    public function doResetPasswordComplete(&$params) {
        $users = get_users(array('meta_key' => '_dl_reset_password_key', 'meta_value' => $params['key']));
        if (count($users) == 0) {
            $params['error'] = 'Not found';
            return;
        }
        /** @var WP_User $user */
        $user = $users[0];
        update_user_meta($user->ID, '_dl_reset_password_key', '');
        $newPassword = wp_generate_password();
        wp_set_password($newPassword, $user->ID);
        $params['password'] = $newPassword;
    }

    /**
     * //TODO: rewrite
     * Imported from CakePHP site
     */
    private function isValidUSPhoneFormat($phone_no, $country_id) {
        if((int)($country_id) != 1)
        {
            if(!preg_match('/^\+?[\d -\(\)]+$/', $phone_no)) {
                return "Please enter valid Phone Number";
            }
            else
                return true;
        }
        $errors = array();
        if(empty($phone_no)) {
            $errors [] = "Please enter Phone Number";
        }
        else if (!preg_match('/^[(]{0,1}[0-9]{3}[)]{0,1}[-\s.]{0,1}[0-9]{3}[-\s.]{0,1}[0-9]{4}$/', $phone_no)) {
            $errors [] = "Please enter valid Phone Number";
        }
        if (!empty($errors))
            return implode("\n", $errors);
        return true;
    }
    //////////////////////////

    /**
     * //TODO: rewrite
     * Imported from CakePHP site
     */
    function isValidState($state_id, $country_id){
        if((int)($country_id) != 1)
            return true;
        elseif($state_id <= 0 ||$state_id == '')
            return false;
        else
            return true;
    }
    //////////////////////////


    public function doRegister(&$params) {
        try {
            $errors = array();
            $username = sanitize_user($params['username']);
            if ($username != $params['username']) {
                $errors['username'] = 'Invalid username';
            } elseif (!preg_match('/^[a-z0-9]+$/i', $params['username'])) {
                $errors['username'] = 'Alphabets and numbers only';
            } elseif (strlen($username) < 5 || strlen($username) > 15) {
                $errors['username'] = 'Between 5 to 15 characters';
            }
            if (strlen($params['password']) < 5) {
                $errors['password'] = 'Mimimum 5 characters long';
            }
            if (($check = $this->isValidUSPhoneFormat($params['phone'], $params['county_id'])) !== true) {
                $errors['phone'] = $check;
            }
            if (($check = $this->isValidState($params['state_id'], $params['county_id'])) !== true) {
                $errors['state_id'] = $check;
            }
            if (empty($params['contact_name'])) {
                $errors['contact_name'] = 'Not empty';
            }
            $email = is_email($params['email']);
            if (empty($email)) {
                $errors['email'] = 'Incorrect';
            }
            if (count($errors)) {
                $params['errors'] = $errors;
                throw new Exception("Data contain errors");
            }
            try {
                if (username_exists($username)) {
                    $params['errorAlreadyExists'] = 'username';
                    throw new Exception("Entered username is already taken");
                }
                if (email_exists($email)) {
                    $params['errorAlreadyExists'] = 'email';
                    throw new Exception("This email is already used. Please enter another email address");
                }
                $userdata = array(
                    'user_login'    =>  $username,
                    'user_pass'    =>  $params['password'],
                    'user_email' => $email,
                    'role' => 'dl_customer'
                );

                $userId = wp_insert_user( $userdata ) ;

                if( is_wp_error($userId) ) {
                    throw new Exception("Creating user error");
                }

                update_user_meta($userId, '_dl_phone', $params['phone']);
                update_user_meta($userId, '_dl_contact_name', $params['contact_name']);
                update_user_meta($userId, '_dl_business_name', $params['business_name']);
                update_user_meta($userId, '_dl_country_id', $params['country_id']);
                update_user_meta($userId, '_dl_state_id', $params['state_id']);
                do_action('dl_after_register', $userId);
                $params['registeredUserId'] = $userId;
            } catch (Exception $e) {
                $params['error'] = $e->getMessage();
            }
        } catch (Exception $e) {
            $params['error'] = 'The registration could not be saved. Please, try again.';
        }
    }

    private function handleFormUpload($name, $prefix) {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            return;
        }
        $uploadPath = WP_CONTENT_DIR . '/uploads/dl/forms';
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0 && $_FILES[$name]['size'] > 0) {
            $fileName = $user->get($name);
            if ($fileName == '') {
                $fileName = uniqid("{$user->ID}-{$prefix}-", true).'.pdf';
                update_user_meta($user->ID, $name, $fileName);
            }
            move_uploaded_file($_FILES[$name]['tmp_name'], $uploadPath .'/'.$fileName);
        }
    }

    public function doUpdateProfile(&$params) {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            return;
        }
        try {
            $errors = array();
            if ($params['password'] != '' && strlen($params['password']) < 5) {
                $errors['password'] = 'Mimimum 5 characters long';
            }
            if (($check = $this->isValidUSPhoneFormat($params['phone'], $params['county_id'])) !== true) {
                $errors['phone'] = $check;
            }
            if (($check = $this->isValidState($params['state_id'], $params['county_id'])) !== true) {
                $errors['state_id'] = $check;
            }
            if (empty($params['contact_name'])) {
                $errors['contact_name'] = 'Not empty';
            }
            $email = is_email($params['email']);
            if (empty($email)) {
                $errors['email'] = 'Incorrect';
            }
            if (count($errors)) {
                $params['errors'] = $errors;
                throw new Exception("Data contain errors");
            }
            try {
                if (email_exists($email) && $email != $user->user_email) {
                    $params['errorAlreadyExists'] = 'email';
                    throw new Exception("This email is already used. Please enter another email address");
                }
                $userdata = array(
                    'ID' => $user->ID,
                    'user_email' => $email,
                );
                if ($params['password'] != '') {
                    $userdata['user_pass'] = $params['password'];
                }

                $userId = wp_update_user( $userdata ) ;

                if( is_wp_error($userId) ) {
                    throw new Exception("The registration could not be saved. Please, try again.");
                }

                update_user_meta($userId, '_dl_phone', $params['phone']);
                update_user_meta($userId, '_dl_contact_name', $params['contact_name']);
                update_user_meta($userId, '_dl_business_name', $params['business_name']);
                update_user_meta($userId, '_dl_country_id', $params['country_id']);
                update_user_meta($userId, '_dl_state_id', $params['state_id']);
                do_action('dl_after_user_update', $userId);
                $params['updatedUserId'] = $userId;
                $this->handleFormUpload('_dl_tax_exempt_form', 't');
                $this->handleFormUpload('_dl_credit_card_form', 'c');
                $this->handleFormUpload('_dl_multistate_form', 'm');
            } catch (Exception $e) {
                $params['error'] = $e->getMessage();
            }
        } catch (Exception $e) {
            $params['error'] = 'Changes could not be saved. Please, try again.';
        }
    }

    public function doAfterRegister($userId) {
        /** @var WP_User $user */
        $user = get_userdata($userId);
        update_user_meta($userId, '_dl_require_verify', 'yes');
        update_user_meta($userId, '_dl_verify_key', $key = md5(time().$userId));
        $activateUrl = get_site_url(null, '/activate/?key='.$key);
        wp_mail(
            $user->user_email,
            'Complete your DirectLiquidation.com Registration',
            'Dear '.$user->get('_dl_contact_name').',<br /><br />'.
            'Thank you for registering with DirectLiquidation.com<br />'.
            'You’re almost done! To complete your registration please click the ACTIVATE NOW link below.<br /><br />'.
            '<a href="'.$activateUrl.'">'.$activateUrl.'</a><br /><br />'.
            'If the above link does not work for you, simply select the link manually and copy and paste it into your Web browser address bar.<br />'.
            'If you need further assistance please <a href="'.get_site_url(null, '/contacts').'">Contact Us</a>.<br /><br />'.
            'Thank you!<br />'.
            'DirectLiquidation.com'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );
    }

    public function doAfterActivate($userId) {
        /** @var WP_User $user */
        $user = get_userdata($userId);
        wp_mail(
            $user->user_email,
            'Congratulations! Your DirectLiquidation.com Registration is complete.',
            'Dear '.$user->get('_dl_contact_name').',<br /><br />'.
            'Thank you for registering with DirectLiquidation.com<br />'.
            'You can now make offer for listings on the <a href="/availability-list">Availability List</a><br />'.
            'If you need further assistance please <a href="'.get_site_url(null, '/contacts').'">Contact Us</a><br /><br />'.
            'Thank you!<br />'.
            'DirectLiquidation.com'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );
    }

    public function isAllowedPasswordReset($userId) {
        /** @var WP_User $user */
        $user = get_userdata($userId);
        return $user && !$user->has_cap('dl_customer');
    }

    public function doAfterResetPasswordKey($userId) {
        /** @var WP_User $user */
        $user = get_userdata($userId);
        $resetUrl = get_site_url(null, '/reset-password-complete/?key='.$user->get('_dl_reset_password_key'));
        wp_mail(
            $user->user_email,
            'DirectLiquidation password reset request',
            'Hello '.$user->get('_dl_contact_name').', <br /><br />You recently requested to reset your password.<br />'.
            'Please <a href="'.$resetUrl.'">Click Here</a> to proceed. This link will expire in 3 days.<br />'.
            'If you need further assistance please <a href="'.get_site_url(null, '/contacts').'">Contact Us</a>.<br /><br />'.
            'Thank you!<br />'.
            'DirectLiquidation.com'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );

    }

    /**
     * @param WP_User $user
     */
    public function editUserProfile($user) {
        if (!$user->has_cap('dl_customer')) {
            return;
        }
        echo "<h3>DL Customer Info</h3>";
        $fields = array(
            array(
                'name' => '_dl_phone',
                'id' => 'UserPhone',
                'li' => 'half ar',
                'label' => 'Phone',
                'type' => 'text',
                'required' => true
            ),
            array(
                'name' => '_dl_contact_name',
                'id' => 'UserContactName',
                'li' => 'half al',
                'label' => 'Contact Name',
                'type' => 'text',
                'required' => true
            ),
            array(
                'name' => '_dl_business_name',
                'id' => 'UserBusinessName',
                'li' => 'half ar',
                'label' => 'Business Name',
                'type' => 'text',
                'required' => false
            )
        );
		echo '<table class="form-table">';
	    foreach ($fields as $field) {
            printf('<tr>
					<th><label for="%1$s">%2$s</label></th>
			        <td><input type="%3$s" name="%4$s" id="%1$s" value="%5$s" class="regular-text" /></td>
					</tr>', $field['id'], htmlspecialchars($field['label']), $field['type'], $field['name'], htmlspecialchars($user->get($field['name'])));
        }
        ?>
        <tr>
        <th><label for="UserCountryId">Country</label></th>
        <td>
        <select name="_dl_country_id" class="input-sel" id="UserCountryId">
            <?php $countries = get_option("dl_countries", array()); ?>
            <?php foreach ($countries as $country): ?>
                <option value="<?php echo $country['id'] ?>"<?php if($country['id'] == $user->get('_dl_country_id')): ?> selected="selected"<?php endif; ?>><?php echo htmlspecialchars($country['name']); ?></option>
            <?php endforeach;?>
        </select>
        </td></tr>
        <th><label for="UserStateId">State</label></th>
        <td>
        <select name="_dl_state_id" class="input-sel" id="UserStateId">
            <option value="">-- Choose State--</option>
            <?php $states = get_option("dl_states", array()); ?>
            <?php foreach ($states as $state): ?>
                <option value="<?php echo $state['id'] ?>"<?php if($state['id'] == $user->get('_dl_state_id')): ?> selected="selected"<?php endif; ?>><?php echo htmlspecialchars($state['name']); ?></option>
            <?php endforeach;?>
        </select>
        </td></tr>
        <?php
        printf('<tr>
					<th><label for="UserDisabled">Suspended</label></th>
			        <td><input type="checkbox" name="_dl_disabled" id="UserDisabled" value="yes" class="regular-text" %s/></td>
					</tr>', $user->get('_dl_disabled') == 'yes' ? 'checked="checked"': '');
        printf('<tr>
					<th><label for="UserActive">Not verified</label></th>
			        <td><input type="checkbox" name="_dl_require_verify" id="UserActive" value="yes" class="regular-text" %s/></td>
					</tr>', $user->get('_dl_require_verify') == 'yes' ? 'checked="checked"': '');
        printf('<tr>
					<th><label for="UserSeller">Seller</label></th>
			        <td><input type="checkbox" name="_dl_seller" id="UserSeller" value="yes" class="regular-text" %s/></td>
					</tr>', $user->get('_dl_seller') == 'yes' ? 'checked="checked"': '');
        if ($user->get('_dl_tax_exempt_form')) {
            $states = get_option("dl_states", array());
            $stateId = $user->get('_dl_state_id');
            $stateCode = '';
            if (isset($states[$stateId])) {
                $stateCode = $states[$stateId]['code'];
            }
            $name = 'TaxExemptForm-' . $stateCode . '.pdf';
            printf(
                '<tr>
            <th>Tax Exempt Form</th>
            <td><a href="%1$s?action=dl_user_form&id=%2$d&type=%3$s">%3$s</a></td>
            </tr>',
                admin_url('admin-ajax.php'),
                $user->ID,
                urlencode($name)
            );
        }
        if ($user->get('_dl_multistate_form')) {
            $name = 'MultistateForm.pdf';
            printf(
                '<tr>
            <th>Multistate Form</th>
            <td><a href="%1$s?action=dl_user_form&id=%2$d&type=%3$s">%3$s</a></td>
            </tr>',
                admin_url('admin-ajax.php'),
                $user->ID,
                urlencode($name)
            );
        }
        if ($user->get('_dl_credit_card_form')) {
            $name = 'CreditCardAuthorizationForm.pdf';
            printf(
                '<tr>
            <th>Credit Card Authorization Form</th>
            <td><a href="%1$s?action=dl_user_form&id=%2$d&type=%3$s">%3$s</a></td>
            </tr>',
                admin_url('admin-ajax.php'),
                $user->ID,
                urlencode($name)
            );
        }
        echo "</table>";
    }


    public function editUserProfileUpdate($userId) {
        /** @var WP_User $user */
        $user = get_userdata($userId);
        if (!$user->has_cap('dl_customer')) {
            return;
        }
        $fields = array('_dl_phone', '_dl_contact_name', '_dl_business_name', '_dl_country_id', '_dl_state_id');
        foreach ($fields as $field) {
            update_user_meta($user->ID, $field, $_POST[$field]);
        }
        if (isset($_POST['_dl_disabled']) && $_POST['_dl_disabled'] == 'yes') {
            update_user_meta($user->ID, '_dl_disabled', 'yes');
        } else {
            update_user_meta($user->ID, '_dl_disabled', 'no');
        }
        if (isset($_POST['_dl_require_verify']) && $_POST['_dl_require_verify'] == 'yes') {
            update_user_meta($user->ID, '_dl_require_verify', 'yes');
        } else {
            update_user_meta($user->ID, '_dl_require_verify', 'no');
        }
        if (isset($_POST['_dl_seller']) && $_POST['_dl_seller'] == 'yes') {
            update_user_meta($user->ID, '_dl_seller', 'yes');
        } else {
            update_user_meta($user->ID, '_dl_seller', 'no');
        }
    }

    private function renderUserForm($user, $type) {
        $name = '';
        if (preg_match('/^taxexemptform-[a-z]{2}\.pdf$/', $type)) {
            $type = '_dl_tax_exempt_form';
            $stateCode = '';
            $states = get_option("dl_states", array());
            $stateId = $user->get('_dl_state_id');
            if (isset($states[$stateId])) {
                $stateCode = $states[$stateId]['code'];
            }
            $name = 'TaxExemptForm-'.$stateCode.'.pdf';
        } elseif ($type == 'multistateform.pdf') {
            $type = '_dl_multistate_form';
            $name = 'MultistateForm.pdf';
        } elseif ($type == 'creditcardauthorizationform.pdf') {
            $type = '_dl_credit_card_form';
            $name = 'CreditCardAuthorizationForm.pdf';
        } else {
            return false;
        }
        $fileName = $user->get($type);
        if (empty($fileName) || !file_exists(WP_CONTENT_DIR . '/uploads/dl/forms/'.$fileName)) {
            return false;
        }
        $path = WP_CONTENT_DIR . '/uploads/dl/forms/'.$fileName;
        header("Content-Type: application/pdf");
        header("Content-Length: " . filesize($path));
        header('Content-Disposition: attachment; filename="'.$name.'"');
        readfile($path);
        return true;
    }

    public function doForm($params)
    {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            wp_redirect('/');
            return;
        }
        $type = strtolower($params['type']);
        if (!$this->renderUserForm($user, $type)) {
            wp_redirect('/');
            return;
        }
    }

    public function doOfferForm($params)
    {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            wp_redirect('/');
            return;
        }
        if ($user->get('_dl_seller') != 'yes') {
            wp_redirect('/');
            die();
        }
        $offer = DL_Offer::load($params['offerId']);
        if (empty($offer)) {
            wp_redirect('/login');
            die();
        }

        $product = $offer->getProduct();

        if ($product == null || $product->getPost()->post_author != $user->ID) {
            wp_redirect('/');
            die();
        }

        $type = strtolower($params['type']);
        if (!$this->renderUserForm($offer->getUser(), $type)) {
            wp_redirect('/');
            return;
        }
    }

    public function userForm() {
        if (!isset($_GET['id'], $_GET['type'])) {
            die();
        }
        $user = get_userdata($_GET['id']);
        if (!$user) {
            die();
        }
        $this->renderUserForm($user, strtolower($_GET['type']));
        die();
    }

    public function doSellerRequest($data) {
        $message = $data['request'];
        $user = wp_get_current_user();
        $admin_link = get_admin_url(null, 'user-edit.php?user_id='.$user->ID);
        $html = 'User request access to seller tools<br />
        <a href="'.$admin_link.'">'.$admin_link.'</a><br />
        Username: '.esc_html($user->user_login).'<br />
        Email: '.esc_html($user->user_email).'<br />
        Contact name: '.esc_html($user->get('_dl_contact_name')).'<br />
        Business Name: '.esc_html($user->get('_dl_business_name')).'<br />
        Message: '.esc_html($message);
         wp_mail(
             get_option('admin_email'),
             'DirectLiquidation seller request',
             $html,
             array('Content-type: text/html')
         );


    }

}