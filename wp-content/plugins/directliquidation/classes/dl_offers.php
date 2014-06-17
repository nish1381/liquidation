<?php

class DL_Offers
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
        add_action('dl_make_offer', array($this, 'doMakeOffer'));
        add_action('dl_new_max_offer', array($this, 'doNotifyNewMaxOffer'));
        add_action('dl_offers_accept', array($this, 'doNotifyAccept'));
        add_action('dl_after_set_transaction_id', array($this, 'doAfterTransaction'));
        add_action('dl_after_set_pickup_date_time', array($this, 'doAfterSetPickupDateTime'));
    }

    public function doInit()
    {
        register_post_type("dl_offer",
            array(
                'public' => false,
            )
        );
    }

    public function doMakeOffer(&$params)
    {
        $amount = $params['amount'];
        $product = DL_Product::load($params['productId']);
        $user = get_userdata($params['userId']);
        $newOfferId = wp_insert_post(array(
            'post_status' => 'publish',
            'post_type' => 'dl_offer',
            'post_parent' => $product->getPost()->ID,
            'post_author' => $user->ID
        ));
        update_post_meta($newOfferId, '_dl_amount', $amount);
        $oldOfferId = $product->getMaxOfferId();
        $maxOffer = false;
        $maxPrice = floatval($product->getMaxOfferPrice());
        if ((floatval($amount) > $maxPrice)) {
            update_post_meta($product->getPost()->ID, '_dl_max_offer_price', $amount);
            update_post_meta($product->getPost()->ID, '_dl_max_offer_id', $newOfferId);
            do_action('dl_new_max_offer', array(
                'oldOfferId' => $oldOfferId,
                'newOfferId' => $newOfferId
            ));
            $maxOffer = true;
        }
        $maxPrice = max($maxPrice, floatval($product->getStartPrice()));
        if (floatval($amount) < $maxPrice) {
            $maxOffer = false;
        }
        $message = '';
        if (!$maxOffer) {
            $message .= 'Warning!<br />Thank you for making an offer. Unfortunately it’s below our current highest offer of $' . number_format($maxPrice, 2);
            if ($user->get('_dl_require_verify') == 'yes') {
                $message .= 'Your new account successfully created.<br /><br />'.'A confirmation email was sent to your account. '.
                    'To complete the registration follow the instruction in the confirmation email.<br /><br /><strong>Your account will not be accessible until your email address is verified.</strong><br /><br />If you do not receive this email, please <a href="/contacts">Contact Us</a>';
            }
        } else {
            if ($user->get('_dl_require_verify') == 'yes') {
                $message .= 'Thank you! Your offer was successfully submitted and new account successfully created.<br /><br />A confirmation email was sent to your account. To complete the registration follow the instruction in the confirmation email.<br /><br /><strong>Your account will not be accessible until your email address is verified.</strong><br /><br />If you do not receive this email, please <a href="/contacts">Contact Us</a>';
            } else {
                $message .= 'Your offer has been successfully submitted.  If your offer has been accepted a DirectLiquidation.com account manager will contact you.';
            }
        }
        $params['message'] = $message;
    }

    public function doNotifyNewMaxOffer($params) {
        $oldOfferId = $params['oldOfferId'];
        $newOfferId = $params['newOfferId'];
        if (is_null($oldOfferId) || !($oldOffer = get_post($oldOfferId))) {
            return;
        }
        if (is_null($newOfferId) || !($newOffer = get_post($newOfferId))) {
            return;
        }
        $product = DL_Product::load($newOffer->post_parent);
        if (is_null($product) || floatval($product->getStartPrice()) > floatval(get_post_meta($oldOffer->ID, '_dl_amount', true))) {
            return;
        }
        if ($oldOffer->post_author == $newOffer->post_author) {
            return;
        }
        $user = get_userdata($oldOffer->post_author);
        wp_mail(
            $user->user_email,
            'LOT #'.$product->getPost()->ID.' - '.$product->getPost()->post_title.' offer',
            'The offer you placed is no longer the highest offer.  You can increase your offer by <a href="'.get_permalink($product->getPost()->ID).'">clicking here</a></br /><br />'.

            'Thank you,<br />'.
            'Sales Department.<br />'.
            'Tel: (855) TRG-TRG1 – (855) 874-8741<br />'.
            '<a href="mailto:sales@therecongroup.com">sales@therecongroup.com</a><br />'.
            '<a href="'.get_site_url().'">'.'www.DirectLiquidation.com'.'</a>'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );

    }

    public function doNotifyAccept($offerId) {
        $offer = DL_Offer::load($offerId);
        $product = $offer->getProduct();
        $userEmail = $offer->getUser()->user_email;
        $allOffers = $product->getAllOffers();
        $otherUsers = array();
        while ($allOffers->have_posts()) {
            $otherOffer = DL_Offer::load($allOffers->next_post());
            $otherEmail = $otherOffer->getUser()->user_email;
            if ($otherEmail != $userEmail) {
                $otherUsers[$otherEmail] = $otherOffer->getUser();
            }
        }

        $subject = 'LOT #'.$product->getPost()->ID.' - '.$product->getPost()->post_title.' offer';

        $formRequest = "";

        $taxExemptForm = $offer->getUser()->get('_dl_tax_exempt_form');
        if (in_array($offer->getUser()->get('_dl_state_id'), array(4, 10, 19, 32, 38)) && empty($taxExemptForm)) {
            $formRequest = '<font color="#FF7900">In order to complete this transaction you must fill out and upload the reseller certificate form located in the MY ACCOUNT section.</font><br />';
        }

        wp_mail(
            $offer->getUser()->user_email,
            $subject,
            'Congratulations, you are the winner of LOT #'.$product->getPost()->ID.', your offer was $' . number_format($offer->getAmount(), 2) . ', your invoice is attached.<br />'.
            $formRequest.
            'Payment Type: wire, information attached<br />'.
            'Once payment is confirmed your order will be released and you will receive pickup instructions by email.<br /><br />'.

            'Thank you,<br />'.
            'Sales Department.<br />'.
            'Tel: (855) TRG-TRG1 – (855) 874-8741<br />'.
            '<a href="mailto:sales@therecongroup.com">sales@therecongroup.com</a><br />'.
            '<a href="http://www.directliquidation.com/">www.DirectLiquidation.com</a>'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );

        foreach ($otherUsers as $user) {
            wp_mail(
                $user->user_email,
                $subject,
                'Thank you for making an offer on lot '.$product->getPost()->ID.' it has been sold to another buyer for $' . number_format($offer->getAmount(), 2) . ' and is no longer available for sale.<br /><br />To view other lots that may be of interest to you please go to the <a href="/availability-list">availability list</a><br /><br />'.
                'Thank you,<br />'.
                'Sales Department.<br />'.
                'Tel: (855) TRG-TRG1 – (855) 874-8741<br />'.
                '<a href="mailto:sales@therecongroup.com">sales@therecongroup.com</a><br />'.
                '<a href="'.get_site_url().'">'.'www.DirectLiquidation.com'.'</a>'.
                '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
                array('Content-type: text/html')
            );

        }
    }

    public function doAfterTransaction($offerId) {
        $offer = DL_Offer::load($offerId);
        $product = $offer->getProduct();

        $subject = 'LOT #'.$product->getPost()->ID.' - '.$product->getPost()->post_title.' payment received, order released';
        $bolLink = get_site_url(null, '/bol/?id='.$offer->getPost()->ID);
        wp_mail(
            $offer->getUser()->user_email,
            $subject,
            'Thank you for your payment. Please complete a <a href="'.$bolLink.'">bill of lading</a> so we can prepare your order for shipping.<br />'.
            'Within one business day you will receive an email with available pickup hours.<br /><br />'.

            'Thank you,<br />'.
            'Sales Department.<br />'.
            'Tel: (855) TRG-TRG1 – (855) 874-8741<br />'.
            '<a href="mailto:sales@therecongroup.com">sales@therecongroup.com</a><br />'.
            '<a href="http://www.DirectLiquidation.com">www.DirectLiquidation.com</a>'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );
    }

    public function doAfterSetPickupDateTime($offerId) {
        $offer = DL_Offer::load($offerId);
        $product = $offer->getProduct();

        $subject = 'LOT #'.$product->getPost()->ID.' - '.$product->getPost()->post_title.' - pickup scheduled';
        wp_mail(
            $offer->getUser()->user_email,
            $subject,
            'Your order is scheduled for pickup between '.$offer->getPickupTime().' on '.$offer->getPickupDate().'<br />'.

            'Thank you,<br />'.
            'Sales Department.<br />'.
            'Tel: (855) TRG-TRG1 – (855) 874-8741<br />'.
            '<a href="mailto:sales@therecongroup.com">sales@therecongroup.com</a><br />'.
            '<a href="http://www.DirectLiquidation.com">www.DirectLiquidation.com</a>'.
            '<br /><br />Privileged/Confidential Information may be contained in this message.  If you are not the addressee indicated in this message (or responsible for delivery of the message to such person), you may not copy or deliver this message to anyone. In such case, you should destroy this message and kindly notify the sender by reply email. Please advise immediately if you or your employer does not consent to email or messages of this kind. Opinions, conclusions and other information in this message that do not relate to the official business of THE RECON GROUP INC shall be understood as neither given nor endorsed by it.',
            array('Content-type: text/html')
        );
    }

}
 