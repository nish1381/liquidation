<?php
 /*
    Plugin Name: Register form extra field
    Plugin URI: http://www.orangecreative.net
    Description: Addition more field on register form
    Author: Thomas trinh
    Version: 1.0
    Author URI: http://www.orangecreative.net
    */

   //1. Add a new form element...
    add_action('register_form','frontend_register_form');
    function frontend_register_form (){
        $contact_name = ( isset( $_POST['contact_name'] ) ) ? $_POST['contact_name']: '';
        $bussiness_name = ( isset( $_POST['bussiness_name'] ) ) ? $_POST['bussiness_name']: '';
        $country = ( isset( $_POST['country'] ) ) ? $_POST['country']: '';
        $state = ( isset( $_POST['state'] ) ) ? $_POST['state']: '';

        ?>
        <div class="row">
            <div class="col">
                <label for="contact_name"><?php _e('Contact Name') ?>:</label>
                <input type="text" name="contact_name" id="contact_name" class="input" value="<?php echo esc_attr(stripslashes($contact_name)); ?>" size="25" />
            </div>
            <div class="col">
                <label for="bussiness_name"><?php _e('Business Name') ?>:</label>
                <input type="text" name="bussiness_name" id="bussiness_name" class="input" value="<?php echo esc_attr(stripslashes($bussiness_name)); ?>" size="25" />
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="country">Country:</label>
                <select data-pair="#state" id="country" name="country">
                    <option selected>USA</option>
                    <option data-choice="null">Germany</option>
                    <option data-choice="null">Spain</option>
                </select>
            </div>
            <div class="col">
                <label for="state">State:</label>
                <select id="state" name="state">
                    <option selected>New York</option>
                </select>
            </div>
        </div>
        <?php
    }

    //2. Add validation. In this case, we make sure first_name is required.
    add_filter('registration_errors', 'frontend_registration_errors', 10, 3);
    function frontend_registration_errors ($errors, $sanitized_user_login, $user_email) {

        if ( empty( $_POST['phone_number'] ) )
            $errors->add( 'phone_number_error', __('<strong>ERROR</strong>: You must include a phone number.','mydomain') );
        if ( empty( $_POST['contact_name'] ) )
            $errors->add( 'contact_name_error', __('<strong>ERROR</strong>: You must include a Contact name.','mydomain') );
        if ( empty( $_POST['bussiness_name'] ) )
            $errors->add( 'bussiness_name_error', __('<strong>ERROR</strong>: You must include a bussiness name.','mydomain') );
        if ( empty( $_POST['country'] ) )
            $errors->add( 'country_error', __('<strong>ERROR</strong>: You must include a country.','mydomain') );
        if ( empty( $_POST['state'] ) )
            $errors->add( 'state_error', __('<strong>ERROR</strong>: You must include a state.','mydomain') );
        if ( empty( $_POST['user_pass'] ) )
            $errors->add( 'user_pass_error', __('<strong>ERROR</strong>: You must include a password.','mydomain') );
        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    add_action('user_register', 'frontend_user_register');
    function frontend_user_register ($user_id) {
        // $contact_name = ( isset( $_POST['contact_name'] ) ) ? $_POST['contact_name']: '';
        // $bussiness_name = ( isset( $_POST['bussiness_name'] ) ) ? $_POST['bussiness_name']: '';
        // $country = ( isset( $_POST['country'] ) ) ? $_POST['country']: '';
        // $state = ( isset( $_POST['state'] ) ) ? $_POST['state']: '';
        if ( isset( $_POST['phone_number'] ) )
            update_user_meta($user_id, 'phone_number', $_POST['phone_number']);
        if ( isset( $_POST['contact_name'] ) )
            update_user_meta($user_id, 'contact_name', $_POST['contact_name']);
        if ( isset( $_POST['bussiness_name'] ) )
            update_user_meta($user_id, 'bussiness_name', $_POST['bussiness_name']);
        if ( isset( $_POST['country'] ) )
            update_user_meta($user_id, 'country', $_POST['country']);
        if ( isset( $_POST['state'] ) )
            update_user_meta($user_id, 'state', $_POST['state']);
    }


    add_action( 'register_form', 'frontend_user_register_password' );
    function frontend_user_register_password(){

    }

 ?>