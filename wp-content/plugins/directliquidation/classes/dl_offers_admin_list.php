<?php

class DL_Offers_Admin_List 
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
        add_action('admin_menu', array($this, 'addAdminMenu'));
    }

    public function addAdminMenu() {
        add_menu_page('DL Offers', 'Offers', 'level_10', 'dl-offers', array($this, 'show'), '', 57);
    }

    public function show() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        switch ($action) {
            case 'offers':
                $this->listOffersAction();
                break;
            case 'pickup':
                $this->pickupAction();
                break;
            default:
                $this->listAction();
        }
    }

    public function pickupAction() {
        $offerId = isset($_GET['offer_id']) ? $_GET['offer_id'] : 0;
        $offer = DL_Offer::load($offerId);
        if (!$offer || !$offer->isAccepted()) {
            wp_redirect('?page=dl-offers&action=list');
            return;
        }

        $dates = array();
        $times = array();
        $current = time() + 24*60*60;

        while(count($dates) < 3)
        {
            $day = date("w", $current);

            if($day != 0 && $day != 6)
                $dates[] = date("m/d/Y", $current);

            $current += 24*60*60;
        }

        for($hour = 9; $hour <= 15; $hour++)
        {
            for($minute = 0; $minute < ($hour < 15 ? 60: 15); $minute += 15)
            {
                $val = str_pad($hour, 2, '0', STR_PAD_LEFT).":".str_pad($minute, 2, '0', STR_PAD_LEFT)."  -  ".str_pad($hour+2, 2, '0', STR_PAD_LEFT).":".str_pad($minute, 2, '0', STR_PAD_LEFT);
                $times[]= $val;
            }
        }

        if (isset($_POST['dl_pickup_date'], $_POST['dl_pickup_time']) && in_array($_POST['dl_pickup_date'], $dates) && in_array($_POST['dl_pickup_time'], $times)) {
            $offer->setPickupDateTime($_POST['dl_pickup_date'], $_POST['dl_pickup_time']);
            add_settings_error('dl_offers', 'dl_offers', 'Pickup successfully scheduled between '.$_POST['dl_pickup_time'].' on '.$_POST['dl_pickup_date'].'. Email was sent to customer', 'updated');
        }

        echo '<div class="wrap dl-offersOffers">';
        printf('<h2>Schedule pickup for Lot #%d</h2>', $offer->getProduct()->getPost()->ID);
        settings_errors('dl_offers');
        $pickupDate = $offer->getPickupDate();
        $pickupTime = $offer->getPickupTime();
        if (!empty($pickupDate)) {
            printf("<div>Pickup date: %s</div>", $pickupDate);
            printf("<div>Pickup time: %s</div>", $pickupTime);
        } else {
            echo '<form method="post">';
            ?><table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_pickup_date">Pickup date</label>
                        </th>
                        <td>
                            <select id="dl_pickup_date" name="dl_pickup_date">
                                <?php foreach ($dates as $date):?>
                                    <option value="<?php echo esc_attr($date);?>"><?php echo esc_html($date); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="dl_pickup_time">Pickup time</label>
                        </th>
                        <td>
                            <select id="dl_pickup_time" name="dl_pickup_time">
                                <?php foreach ($times as $time):?>
                                    <option value="<?php echo esc_attr($time);?>"><?php echo esc_html($time); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
            </table>
            <p class="submit">
                <input id="submit" class="button button-primary" type="submit" value="Schedule">
            </p>
            <?php
            echo '</form>';
        }
        echo "</div>";
    }

    public function listOffersAction() {
        $page = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $product = DL_Product::load($_GET['product_id']);
        if (!$product || !$product->isVisible()) {
            wp_redirect('?page=dl-offers&action=list');
            return;
        }
        if (isset($_POST['offer_id'], $_POST['offer_action']) && $_POST['offer_action'] == 'accept') {
            $offer = DL_Offer::load($_POST['offer_id']);
            if ($offer && $product->getStatus() == 'active') {
                $product->setSoldOfferId($offer->getPost()->ID);
                do_action('dl_offers_accept', $offer->getPost()->ID);
                add_settings_error('dl_offers', 'dl_offers', 'Offer was successfully accepted', 'updated');
            }
        } elseif (isset($_POST['offer_id'], $_POST['offer_action'], $_POST['transactionId']) && $_POST['offer_action'] == 'transactionId') {
            $value = trim($_POST['transactionId']);
            $offer = DL_Offer::load($_POST['offer_id']);
            if ($value != '' && $offer && $product->getStatus() == DL_Product::STATUS_SOLD) {
                $offer->setTransactionId($value);
                add_settings_error('dl_offers', 'dl_offers', 'Payment information was successfully updated', 'updated');
            }
        }
        $args = array(
            'posts_per_page' => 20,
            'post_type' => 'dl_offer',
            'post_status' => 'publish',
            'post_parent' => $product->getPost()->ID,
            'paged' => $page,
            'orderby' => 'meta_value_num',
            'meta_key' => '_dl_amount',
            'order' => 'desc'
        );
        $offers = new WP_Query($args);
        echo '<div class="wrap dl-offersOffers">';
        printf('<h2>Offers for <a href="%s">%s</a></h2>', get_permalink($product->getPost()->ID), $product->getPost()->post_title);
        settings_errors('dl_offers');
        ?>
        <form class="tablenav top" method="get">
            <?php
            foreach ($_GET as $name => $value) {
                if ($name != 'paged') {
                    printf('<input type="hidden" name="%s" value="%s" />', htmlspecialchars($name), htmlspecialchars($value));
                }
            }
            $currentUrl = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            ?>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(_n('1 item', '%s items', $offers->found_posts), number_format_i18n($offers->found_posts)) ?>
                </span>
<span class='tablenav-pages<?php if ($offers->max_num_pages < 1): ?> no-pages<?php elseif ($offers->max_num_pages == 1): ?> one-page<?php endif; ?>'>
    <a class='first-page<?php if ($page == 1): ?> disabled<?php endif ?>'
       title='<?php echo esc_attr__('Go to the first page'); ?>'
       href='<?php echo esc_url(remove_query_arg('paged', $currentUrl)); ?>'>&laquo;</a>
<a class='prev-page<?php if ($page == 1): ?> disabled<?php endif ?>'
   title='<?php echo esc_attr__('Go to the previous page'); ?>'
   href='<?php echo esc_url(add_query_arg('paged', max(1, $page - 1), $currentUrl)); ?>'>&lsaquo;</a>
    <select name="paged" onchange="this.form.submit();">
        <?php for ($i = 1; $i <= $offers->max_num_pages; $i++): ?>
            <option value="<?php echo $i; ?>"<?php if ($i == $page): ?> selected="selected"<?php endif; ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
    <a class='next-page<?php if ($page == $offers->max_num_pages): ?> disabled<?php endif ?>'
       title='<?php echo esc_attr__('Go to the next page'); ?>'
       href='<?php echo esc_url(add_query_arg('paged', min($offers->max_num_pages, $page + 1), $currentUrl)); ?>'>&rsaquo;</a>
<a class='last-page<?php if ($page == $offers->max_num_pages): ?> disabled<?php endif ?>'
   title='<?php echo esc_attr__('Go to the last page'); ?>'
   href='<?php echo esc_url(add_query_arg('paged', $offers->max_num_pages, $currentUrl)); ?>'>&raquo;</a>
</span>
            </div>
        </form>
        <style type="text/css">
            tr.goodprice {
                background: #00ff00;
            }
            tr.goodprice.alternate {
                background: #00ee00;
            }
        </style>
        <table class="widefat" cellspacing="0">
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Username</th>
            <th scope="col">Amount</th>
            <th scope="col">Created</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Username</th>
            <th scope="col">Amount</th>
            <th scope="col">Created</th>
            <th scope="col">Actions</th>
        </tr>
        </tfoot>
        <tbody>
        <?php $i = 0; ?>
        <?php while ($offers->have_posts()): ?>
            <?php
            $offer = DL_Offer::load($offers->next_post());
            $classes = array();
            if (($i++) & 1) {
                $classes[] = 'alternate';
            }
            if (intval($product->getGreenPrice()) <= intval($offer->getAmount())) {
                $classes[] = 'goodprice';
            }
            ?>
            <tr<?php if (count($classes)) printf(' class="%s"', implode(' ', $classes)); ?>>
                <td>
                    <?php echo $offer->getPost()->ID; ?>
                </td>
                <td>
                    <a style="font-weight: bold; color: #000000;" href="user-edit.php?user_id=<?php echo $offer->getUser()->ID; ?>">
                        <?php echo htmlspecialchars($offer->getUser()->user_login); ?>
                    </a>
                    <?php
                    $user = $offer->getUser();
                    if ($user->get('_dl_tax_exempt_form')) {
                        $states = get_option("dl_states", array());
                        $stateId = $user->get('_dl_state_id');
                        $stateCode = '';
                        if (isset($states[$stateId])) {
                            $stateCode = $states[$stateId]['code'];
                        }
                        $name = 'TaxExemptForm-' . $stateCode . '.pdf';
                        printf(
                            '<br /><a href="%1$s?action=dl_user_form&id=%2$d&type=%3$s">%3$s</a>',
                            admin_url('admin-ajax.php'),
                            $user->ID,
                            urlencode($name)
                        );
                    }
                    if ($user->get('_dl_multistate_form')) {
                        $name = 'MultistateForm.pdf';
                        printf(
                            '<br /><a href="%1$s?action=dl_user_form&id=%2$d&type=%3$s">%3$s</a>',
                            admin_url('admin-ajax.php'),
                            $user->ID,
                            urlencode($name)
                        );
                    }
                    ?>
                </td>
                <td>
                    $<?php echo number_format($offer->getAmount(), 2); ?><br/>
                </td>
                <td>
                    <?php echo date('D, M jS Y, H:i', strtotime($offer->getPost()->post_date)); ?>
                </td>
                <td>
                    <?php if ($product->getStatus() != 'sold'): ?>
                        <form method="post" onsubmit="return confirm('Are you sure want to accept this offer? Email will be automatically sent to winning bidder.');">
                            <input type="hidden" name="offer_id" value="<?php echo $offer->getPost()->ID; ?>">
                            <input type="hidden" name="offer_action" value="accept">
                            <input type="submit" value="Accept"/>
                        </form>
                    <?php elseif ($offer->isAccepted()): ?>
                        <span style="color: green">ACCEPTED</span>
                        <?php $transactionId = $offer->getTransactionId(); ?>
                        <?php if (empty($transactionId)): ?>
                            <form method="post">
                                <input type="hidden" name="offer_id" value="<?php echo $offer->getPost()->ID; ?>">
                                <input type="hidden" name="offer_action" value="transactionId">
                                <label>
                                    Transaction#: <input type="text" name="transactionId" value="">
                                </label>
                                <input type="submit" value="Save"/>
                            </form>
                        <?php else: ?>
                            <br /> Transaction #<?php echo esc_html($transactionId); ?>
                            <br /> <a href="?page=dl-offers&action=pickup&offer_id=<?php echo $offer->getPost()->ID; ?>">Schedule pickup</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: red">REJECTED</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        </table>
<?php
        echo '</div>';
    }

    public function listAction() {
        $page = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $args = array(
            'posts_per_page' => 20,
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_dl_status',
                    'value' => 'cancelled',
                    'compare' => '!='
                ),
                array(
                    'key' => '_dl_max_offer_id',
                    'value' => '0',
                    'compare' => '>'
                )
            ),
            'paged' => $page
        );
        $productsWithOffers = new WP_Query($args);
        echo '<div class="wrap dl-offersOffers">';
        echo '<h2>Offers</h2>';
        settings_errors('dl_offers');
        ?>
        <form class="tablenav top" method="get">
            <?php
            foreach ($_GET as $name => $value) {
                if ($name != 'paged') {
                    printf('<input type="hidden" name="%s" value="%s" />', htmlspecialchars($name), htmlspecialchars($value));
                }
            }
            $currentUrl = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            ?>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(_n('1 item', '%s items', $productsWithOffers->found_posts), number_format_i18n($productsWithOffers->found_posts)) ?>
                </span>
<span class='tablenav-pages<?php if ($productsWithOffers->max_num_pages < 1): ?> no-pages<?php elseif ($productsWithOffers->max_num_pages == 1): ?> one-page<?php endif; ?>'>
    <a class='first-page<?php if ($page == 1): ?> disabled<?php endif ?>'
       title='<?php echo esc_attr__('Go to the first page'); ?>'
       href='<?php echo esc_url(remove_query_arg('paged', $currentUrl)); ?>'>&laquo;</a>
<a class='prev-page<?php if ($page == 1): ?> disabled<?php endif ?>'
   title='<?php echo esc_attr__('Go to the previous page'); ?>'
   href='<?php echo esc_url(add_query_arg('paged', max(1, $page - 1), $currentUrl)); ?>'>&lsaquo;</a>
    <select name="paged" onchange="this.form.submit();">
        <?php for ($i = 1; $i <= $productsWithOffers->max_num_pages; $i++): ?>
            <option value="<?php echo $i; ?>"<?php if ($i == $page): ?> selected="selected"<?php endif; ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
    <a class='next-page<?php if ($page == $productsWithOffers->max_num_pages): ?> disabled<?php endif ?>'
       title='<?php echo esc_attr__('Go to the next page'); ?>'
       href='<?php echo esc_url(add_query_arg('paged', min($productsWithOffers->max_num_pages, $page + 1), $currentUrl)); ?>'>&rsaquo;</a>
<a class='last-page<?php if ($page == $productsWithOffers->max_num_pages): ?> disabled<?php endif ?>'
   title='<?php echo esc_attr__('Go to the last page'); ?>'
   href='<?php echo esc_url(add_query_arg('paged', $productsWithOffers->max_num_pages, $currentUrl)); ?>'>&raquo;</a>
</span>
            </div>
        </form>
        <style type="text/css">
            tr.goodprice {
                background: #00ff00;
            }
            tr.goodprice.alternate {
                background: #00ee00;
            }
        </style>
        <table class="widefat" cellspacing="0">
        <thead>
        <tr>
            <th scope="col">Product ID</th>
            <th scope="col">Product Title</th>
            <th scope="col">Username</th>
            <th scope="col">Edge Amounts<br/>Offer Amount</th>
            <th scope="col">Last offer date</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th scope="col">Product ID</th>
            <th scope="col">Product Title</th>
            <th scope="col">Username</th>
            <th scope="col">Edge Amounts<br/>Offer Amount</th>
            <th scope="col">Last offer date</th>
            <th scope="col">Actions</th>
        </tr>
        </tfoot>
        <tbody>
        <?php $i = 0; ?>
        <?php while ($productsWithOffers->have_posts()): ?>
            <?php
            $product = DL_Product::load($productsWithOffers->next_post());
            $minOffer = $product->getMinOffer();
            $maxOffer = $product->getMaxOffer();
            $classes = array();
            if (($i++) & 1) {
                $classes[] = 'alternate';
            }
            if (intval($product->getGreenPrice()) <= intval($maxOffer->getAmount())) {
                $classes[] = 'goodprice';
            }
            ?>
            <tr<?php if (count($classes)) printf(' class="%s"', implode(' ', $classes)); ?>>
                <td>
                    <?php echo $product->getPost()->ID; ?>
                </td>
                <td>
                    <strong><a
                            href="<?php echo get_permalink($product->getPost()); ?>"><?php echo htmlspecialchars($product->getPost()->post_title); ?></a></strong>
                    <br/> Retail $<?php echo number_format($product->getMSRP(), 2); ?>
                    <?php if ($product->getStatus() == DL_Product::STATUS_SOLD): ?>
                        <br />
                        <strong><span style="color: red;">SOLD</span></strong>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="user-edit.php?user_id=<?php echo $minOffer->getUser()->ID; ?>">
                        <?php echo htmlspecialchars($minOffer->getUser()->user_login); ?>
                    </a>
                    <br/>
                    <a href="user-edit.php?user_id=<?php echo $maxOffer->getUser()->ID; ?>">
                        <?php echo htmlspecialchars($maxOffer->getUser()->user_login); ?>
                    </a>
                </td>
                <td>
                    $<?php echo number_format($minOffer->getAmount(), 2); ?><br/>
                    $<?php echo number_format($maxOffer->getAmount(), 2); ?><br/>
                    Total: <?php echo $product->getOffersCount(); ?> offers
                </td>
                <td>
                    <?php echo date('D, M jS Y, H:i', strtotime($product->getLastOffer()->getPost()->post_date)); ?>
                </td>
                <td>
                    <a href="?page=dl-offers&action=offers&product_id=<?php echo $product->getPost()->ID; ?>">Offers</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        </table><?php
        echo '</div>';
    }


} 