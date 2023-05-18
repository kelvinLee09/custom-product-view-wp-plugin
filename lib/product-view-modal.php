<?php
/**
 * Custom functions for product detail view modal
 * 
 * @package custom-product-view
 * * inspired by Martfury
 */

/**
 * 
* @return 
*/
if ( ! function_exists(('cpv_quick_view_modal')) ):
    function cpv_quick_view_modal() {
        if ( is_page_template('template-coming-soon-page.php') || is_404()) {
            return;
        }

        if ( function_exists( 'wcmp_vendor_dashboard_page_id' ) ) {
            if ( is_page( wcmp_vendor_dashboard_page_id() ) ){
                return;
            }
        }

        if ( martfury_cartflows_template() ) {
            return;
        }
        ?>

        <div id="mf-quick-view-modal" class="mf-quick-view-modal martfury-modal woocommerce" tabindex="-1">
            <div class="mf-modal-overlay"></div>
            <div class="modal-content">
                <a href="#" class="close-modal">
                    <i class="icon-cross"></i>
                </a>
                <div class="product-modal-content loading"></div>
            </div>
            <div class="mf-loading"></div>
        </div>

        <?php
    }

endif;

if ( ! function_exists( 'martfury_cartflows_template' ) ) {
    function martfury_cartflows_template() {
        if ( ! class_exists( 'Cartflows_Loader' ) || ! function_exists('_get_wcf_step_id') ) {
            return false;
        }

		$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

        if( !$page_template || $page_template == 'default' ) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'single_product_brand' ) ) {
    function single_product_brand() {
        global $product;
        $terms = get_the_terms( $product->get_id(), 'product_brand' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ): ?>
            <li class="meta-brand">
                <?php echo apply_filters( 'martfury_product_brand_text', esc_html__( 'Brand:', 'martfury' ) ); ?>
                <a href="<?php echo esc_url( get_term_link( $terms[0] ), 'product_brand' ); ?>"
                class="meta-value"><?php echo esc_html( $terms[0]->name ); ?></a>
            </li>
        <?php endif;
    }
}

if ( ! function_exists( 'single_product_sku' ) ) {
    function single_product_sku() {
        global $product;
        if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
            <li class="meta-sku">
                <?php esc_html_e( 'SKU:', 'martfury' ); ?>
                <span class="meta-value">
                    <?php
                    if ( $sku = $product->get_sku() ) {
                        echo wp_kses_post( $sku );
                    } else {
                        esc_html_e( 'N/A', 'martfury' );
                    }
                    ?>
                </span>
            </li>
        <?php endif;
    }
}


if ( ! function_exists( 'get_product_quick_view_header' ) ) {
    function get_product_quick_view_header() {
            global $product;

            ?>

            <div class="mf-entry-product-header">
                <div class="entry-left">
                    <?php
                    echo sprintf( '<h2 class="product_title"><a href="%s">%s</a></h2>', esc_url( $product->get_permalink() ), $product->get_title() );
                    ?>

                    <ul class="entry-meta">
                        <?php

                        single_product_brand();

                        if ( function_exists( 'woocommerce_template_single_rating' ) && $product->get_rating_count() ) {
                            echo '<li class="meta-review">';
                            woocommerce_template_single_rating();
                            echo '</li>';
                        }
                        single_product_sku();
                        ?>

                    </ul>
                </div>
            </div>
            <?php
    }
}

if ( ! function_exists( 'template_single_summary_header' ) ) {
    function template_single_summary_header() {
		global $product;
		$output = array();

		if ( class_exists( 'WCV_Vendor_Shop' ) && method_exists( 'WCV_Vendor_Shop', 'template_loop_sold_by' ) ) {
			if ( get_option( 'wcvendors_display_label_sold_by_enable' ) == 'yes' ) {
				ob_start();
				echo '<div class="mf-summary-meta">';
				WCV_Vendor_Shop::template_loop_sold_by( $product->get_id() );
				echo '</div>';
				$output[] = ob_get_clean();
			}
		}
		ob_start();
		do_action( 'martfury_single_product_header' );
		$output[] = ob_get_clean();

		if ( in_array( $product->get_type(), array( 'simple', 'variable' ) ) ) {
			$output[] = sprintf( '<div class="mf-summary-meta">%s</div>', wc_get_stock_html( $product ) );
		}

		echo sprintf( '<div class="mf-summary-header">%s</div>', implode( ' ', $output ) );
    }
}

if ( ! function_exists( 'single_product_socials' ) ) {
    function single_product_socials() {
		if ( ! function_exists( 'martfury_addons_share_link_socials' ) ) {
			return;
		}

		$image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		martfury_addons_share_link_socials( get_the_title(), get_the_permalink(), $image );
    }
}

if ( ! function_exists( 'product_loop_sold_by' ) ) {
    function product_loop_sold_by() {
		if ( ! function_exists( 'get_wcmp_vendor_settings' ) ) {
			return;
		}

		if ( ! function_exists( 'get_wcmp_product_vendors' ) ) {
			return;
		}

		if ( 'Enable' !== get_wcmp_vendor_settings( 'sold_by_catalog', 'general' ) ) {
			return;
		}

		global $post;
		$vendor = get_wcmp_product_vendors( $post->ID );

		if ( empty( $vendor ) ) {
			return;
		}

		$sold_by_text = apply_filters( 'wcmp_sold_by_text', esc_html__( 'Sold By:', 'martfury' ) );
		echo '<div class="sold-by-meta">';
		echo '<span class="sold-by-label">' . $sold_by_text . ' ' . '</span>';

		echo sprintf(
			'<a href="%s">%s</a>',
			esc_url( $vendor->permalink ),
			$vendor->page_title
		);
		echo '</div>';

    }
}

if ( ! function_exists( 'template_single_sold_by' ) ) {
    function template_single_sold_by() {
		echo '<div class="mf-summary-meta">';
		get_template_part( 'template-parts/vendor/loop', 'sold-by' );
		echo '</div>';
    }
}

if ( ! function_exists( 'product_loop_sold_by2' ) ) {
    function product_loop_sold_by2() {
		if ( ! class_exists( 'WCFM' ) ) {
			return;
		}

		global $WCFM, $post, $WCFMmp;

		if( ! $post ) {
			return;
        }

		$vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $post->ID );

		if ( ! $vendor_id ) {
			return;
		}

		$sold_by_text = apply_filters( 'wcfmmp_sold_by_label', esc_html__( 'Sold By:', 'martfury' ) );
		if ( $WCFMmp ) {
			$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint( $vendor_id ) );
		}
		$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint( $vendor_id ) );

		echo '<div class="sold-by-meta">';
		echo '<span class="sold-by-label">' . $sold_by_text . ': ' . '</span>';
		echo wp_kses_post( $store_name );
		echo '</div>';        
    }
}