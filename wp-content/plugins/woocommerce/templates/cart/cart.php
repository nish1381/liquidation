<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>

<form action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">

<?php do_action( 'woocommerce_before_cart_table' ); ?>
<ul class="titles l-hidden m-hidden">
	<li class="">&nbsp;</li>
	<li class="first-title">&nbsp;</li>
	<li class="second-title"><?php _e( 'Product', 'woocommerce' ); ?></li>
	<li class="third-title"><?php _e( 'Price', 'woocommerce' ); ?></li>
	<li class="fourth-title"><?php _e( 'Quantity', 'woocommerce' ); ?></li>
	<li class="fifth-title"><?php _e( 'Total', 'woocommerce' ); ?></li>
</ul>
<ul class="table-products" cellspacing="0">
	<!-- <thead>
		<tr>
			<th class="product-remove">&nbsp;</th>
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
		</tr>
	</thead> -->
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<li class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<ul class="products">
						<li class="">
							<?php
								echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
							?>
						</li>
						<li>
							<ul>
								
								<li class="product-thumbnail image-col">
									<?php
										$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

										if ( ! $_product->is_visible() )
											echo $thumbnail;
										else
											printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
									?>
								</li>

								<li class="product-name secondcol">
									<?php
										if ( ! $_product->is_visible() )
											echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key );
										else
											echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', $_product->get_permalink(), $_product->get_title() ), $cart_item, $cart_item_key );

										// Meta data
										echo WC()->cart->get_item_data( $cart_item );

			               				// Backorder notification
			               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
			               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
									?>
								</li>
							</ul>

						</li>
						<li class="slide js-slide-hidden">
							<ul>
								<li class="product-price first-column">
									<dl class="first-line">
										<dt itemprop="itemCondition">
											<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?> 
										</dt>
									</dl>
									
								</li>

								<li class="product-quantity second-column m-hidden">
									<dl class="first-line">
										<dt>
											<?php
												if ( $_product->is_sold_individually() ) {
													$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
												} else {
													$product_quantity = woocommerce_quantity_input( array(
														'input_name'  => "cart[{$cart_item_key}][qty]",
														'input_value' => $cart_item['quantity'],
														'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
														'min_value'   => '0'
													), $_product, false );
												}

												echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
											?>
										</dt>
									</dl>
								</li>

								<li class="product-subtotal third-column s-hidden">
									<dl class="first-line">
										<dt>
											<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?> 
										</dt>
									</dl>
								</li>
							</ul>

						</li>
						

					</ul>
					

				</li>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
		<li>
			<ul>
				<li colspan="6" class="actions">

					<?php if ( WC()->cart->coupons_enabled() ) { ?>
						<div class="coupon">

							<label for="coupon_code"><?php _e( 'Coupon', 'woocommerce' ); ?>:</label> 
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>" /> 
							<input type="submit" class="btn green" name="apply_coupon" style="color:#FFF; height:auto;font-size: 13px; font-weight: normal; border: none;" value="<?php _e( 'Apply Coupon', 'woocommerce' ); ?>" />
							<?php do_action('woocommerce_cart_coupon'); ?>

						</div>
					<?php } ?>

					<input type="submit" class="btn green" style="color:#FFF; height:auto;font-size: 13px; font-weight: normal; border: none;" name="update_cart" value="<?php _e( 'Update Cart', 'woocommerce' ); ?>" />
					<input type="submit" class="checkout-button btn green alt wc-forward" style="color:#FFF; height:auto;font-size: 13px; font-weight: normal; border: none;" name="proceed" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>" />

					<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart' ); ?>
				</li>
			</ul>
		</li>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
</ul>

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php woocommerce_cart_totals(); ?>

	<?php woocommerce_shipping_calculator(); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
