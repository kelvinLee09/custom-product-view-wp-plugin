<?php
/**
 * Custom functions for header.
 * 
 * @package custom-product-view
 * * inspired by Martfury
 */

 /**
 * Get Menu extra cart
 *
 * @return string
 */
if ( ! function_exists( 'cpv_extra_cart' ) ) :
    function cpv_extra_cart() {
        if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
            return '';
        }
        global $woocommerce;
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();

		$mini_content = sprintf( '	<div class="widget_shopping_cart_content">%s</div>', $mini_cart );

		printf(
			'<li class="extra-menu-item-touch menu-item-cart mini-cart woocommerce">
				<a class="cart-contents" id="icon-cart-contents" href="%s">
					<i class="icon-bag2 extra-icon"></i>
					<span class="mini-item-counter">
						%s
					</span>
				</a>
				<div class="mini-cart-content">
				<span class="tl-arrow-menu"></span>
				%s
				</div>
			</li>',
			esc_url( wc_get_cart_url() ),
			intval( $woocommerce->cart->cart_contents_count ),
			$mini_content
		);
    }
endif;

/**
 * Get Menu extra wishlist
 *
 * @return string
 */
if ( ! function_exists( 'cpv_extra_wishlist' ) ) :
    function cpv_extra_wishlist() {
		if ( ! function_exists( 'YITH_WCWL' ) ) {
			return '';
		}

		$count = YITH_WCWL()->count_products();

		printf(
			'<li class="extra-menu-item-touch menu-item-wishlist menu-item-yith">
			<a class="yith-contents" id="icon-wishlist-contents" href="%s">
				<i class="icon-heart extra-icon" rel="tooltip"></i>
				<span class="mini-item-counter">
					%s
				</span>
			</a>
		</li>',
			esc_url( get_permalink( get_option( 'yith_wcwl_wishlist_page_id' ) ) ),
			intval( $count )
		);
    }
endif;

/**
 * Get Menu extra compare
 * 
 * @return string
 */
if ( ! function_exists( 'cpv_extra_compare' ) ) :
    function cpv_extra_compare() {
		if ( ! class_exists( 'YITH_Woocompare' ) ) {
			return '';
		}

		global $yith_woocompare;

		$count = $yith_woocompare->obj->products_list;

		printf(
			'<li class="extra-menu-item-touch menu-item-compare menu-item-yith">
				<a class="yith-contents yith-woocompare-open" href="#">
					<i class="icon-chart-bars extra-icon"></i>
					<span class="mini-item-counter" id="mini-compare-counter">
						%s
					</span>
				</a>
			</li>', sizeof( $count )
		);
    }
endif;

/**
 * cpv_extra_compare
 */

/**
 * Get Mobile Menu extra compare
 * 
 * @return string
 */
if ( ! function_exists( 'cpv_extra_compare_mobile' ) ) :
    function cpv_extra_compare_mobile() {
        if ( ! class_exists( 'YITH_Woocompare' ) ) {
            return '';
        }

        global $yith_woocompare;

        printf(
			'<li class="extra-menu-item menu-item-compare menu-item-yith">
				<a class="yith-contents yith-woocompare-open" href="#" target="_self">
                    比較する
				</a>
			</li>'
        );
    }
endif;

/**
 * Get Menu extra compare text
 * 
 * @return string
 */
if ( ! function_exists( 'cpv_extra_compare_text' ) ) :
    function cpv_extra_compare_text() {
		if ( ! class_exists( 'YITH_Woocompare' ) ) {
			return '';
		}

		global $yith_woocompare;

		$count = $yith_woocompare->obj->products_list;

		printf(
			'<li class="extra-menu-item menu-item-compare menu-item-yith">
				<a class="yith-contents yith-woocompare-open" href="#">
					比較する
					<span class="mini-item-counter" id="mini-compare-counter">
						%s
					</span>
				</a>
				<i class="icon-chart-bars extra-icon"></i>
			</li>', sizeof( $count )
		);
    }
endif;

 /**
 * Get Menu extra cart text
 *
 * @return string
 */
if ( ! function_exists( 'cpv_extra_cart_text' ) ) :
    function cpv_extra_cart_text() {
        if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
            return '';
        }
        global $woocommerce;
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();

		$mini_content = sprintf( '	<div class="widget_shopping_cart_content">%s</div>', $mini_cart );
		// <i class="icon-bag2 extra-icon"></i>

		printf(
			'<li class="extra-menu-item menu-item-cart mini-cart woocommerce">
				<a class="cart-contents" id="icon-cart-contents" href="%s">
					カート
					<span class="mini-item-counter">
						%s
					</span>
				</a>
				<i class="fas fa-cart-plus"></i>
				<div class="mini-cart-content">
				<span class="tl-arrow-menu"></span>
				%s
				</div>
			</li>',
			esc_url( wc_get_cart_url() ),
			intval( $woocommerce->cart->cart_contents_count ),
			$mini_content
		);
    }
endif;

/**
 * Get Menu extra wishlist text
 *
 * @return string
 */
if ( ! function_exists( 'cpv_extra_wishlist_text' ) ) :
    function cpv_extra_wishlist_text() {
		if ( ! function_exists( 'YITH_WCWL' ) ) {
			return '';
		}

		$count = YITH_WCWL()->count_products();

		printf(
		'<li class="extra-menu-item menu-item-wishlist menu-item-yith">
			<a class="yith-contents" id="icon-wishlist-contents" href="%s">
				お気に入り
				<span class="mini-item-counter">
					%s
				</span>
			</a>
			<i class="icon-heart extra-icon" rel="tooltip"></i>
		</li>',
			esc_url( get_permalink( get_option( 'yith_wcwl_wishlist_page_id' ) ) ),
			intval( $count )
		);
    }
endif;

/**
 * Add MyAccount Menu Item
 */
if ( ! function_exists( 'cpv_add_my_account' ) ) :
	function cpv_add_my_account() {
		$url_myaccount = get_permalink( get_option('woocommerce_myaccount_page_id') );

		printf(
			'<li class="extra-menu-item menu-item-myaccount">
				<a href="%s">
					マイアカウント
					<i class="fas fa-user-circle"></i>
				</a>
			</li>',
			$url_myaccount
		);
	}
endif;

/**
 * cart item counter
 */
function wc_refresh_mini_cart_count($fragments) {
	ob_start();
		?>
			<span id="mini-item-counter">
				<?php echo WC()->cart->get_cart_contents_count(); ?>
			</span>
		<?php
	$fragments['#mini-item-counter'] = ob_get_clean();
	ob_get_clean();
	return $fragments;
}
