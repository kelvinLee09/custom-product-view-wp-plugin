<?php

/*

Plugin Name: Custom Product View For Woocommerce
Plugin URI: https://wordpress.org/
Description: Product View Customization For Woocommerce
Version: 1.0
Author: KelvinLee
License: GPLv2
Text Domain: custom-product-view
 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CPV_LIB_DIR' ) ) {
    define( 'CPV_LIB_DIR', plugin_dir_path( __FILE__ ) . '/lib' );
}
require_once CPV_LIB_DIR . '/header.php';
require_once CPV_LIB_DIR . '/product-view-modal.php';

add_action( 'plugins_loaded', 'cpv_init_class' );

/**
 * Init the class Custom_Product_View
 */
function cpv_init_class() {

    /**
     * Custom_Product_View class
     * 
     * @class Custom_Product_View
     */
    class Custom_Product_View {

        /**
         * Custom_Product_View Constructor
         * 
         * @access public
         */
        public function __construct() {
            $this->includes();

            /** 
             * * product item hover customization
            */
            // product item design hook at top page
            add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'add_product_hover_btns' ), 10, 3);
            // product item design hook at category page & shop page
            add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_hover_btns' ), 10);      

            /**
             * * woocommerce search customization ( add checkbox list when filtering by attributes, and more ... )
             */
            add_filter( 'woocommerce_layered_nav_term_html', array( $this, 'add_checkbox_to_filter_list' ), 10, 4);

            /**
             * * add "Compare" to the header
             * * this theme (cloudcms) specific
             */
            add_filter( 'Lightning_headerTop_menu', array( $this, 'add_extras_to_header' ) );
            add_filter( 'wp_nav_menu_items', array( $this, 'add_compare_to_mobile_menu' ), 20, 2 );
            /**
             * * add ajax wish & cart counters to the front page template
             * * this theme (cloudcms) specific
             */
            add_action( 'wp_body_open', array( $this, 'ajax_counter_bar' ) );
            add_shortcode( 'yith_wcwl_items_count', 'yith_wcwl_get_items_count' );

            /**
             * * ajax count wishlist & cart
             */
            add_filter('woocommerce_add_to_cart_fragments', 'wc_refresh_mini_cart_count');
            add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', array( $this, 'yith_wcwl_ajax_update_count' ) );
            add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', array( $this, 'yith_wcwl_ajax_update_count' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'yith_wcwl_enqueue_custom_script' ), 20 );

            /**
             * * add "product detail view modal to footer
             */
            add_action( 'wp_footer', 'cpv_quick_view_modal' );
            add_action( 'wc_ajax_product_quick_view', array( $this, 'product_quick_view' ) );
            add_action( 'martfury_before_single_product_summary', 'woocommerce_show_product_images', 20 );
            add_action( 'martfury_single_product_summary', 'get_product_quick_view_header', 5 );

            add_action( 'martfury_single_product_summary', 'woocommerce_template_single_price', 10 );
            add_action( 'martfury_single_product_summary',  'template_single_summary_header', 15 );
            add_action( 'martfury_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
            add_action( 'martfury_single_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
            add_action( 'martfury_single_product_summary', 'single_product_socials', 25 );

            add_action( 'martfury_single_product_header', 
			'product_loop_sold_by' );
            add_action( 'martfury_single_product_header', 'template_single_sold_by' );
            add_action( 'martfury_single_product_header', 
			'product_loop_sold_by2' );
        }

        /**
         * Include required core files
         * 
         * @access public
         */
        public function includes() {
            // css
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_cpv_enqueue_css' ) );
            // javascript
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_cpv_enqueue_scripts' ) );
        
        }

        /**
         * Add custom style to frontend pages
         */
        public function wp_cpv_enqueue_css() {
            wp_register_style( 'cpv_linearicons', plugin_dir_url(__FILE__) . 'assets/css/linear_icons.min.css' );
            wp_register_style( 'cpv_fontawesome', plugin_dir_url(__FILE__) . 'assets/css/font-awesome.min.css' );
            // * css for 'woocommerce search customization'
            wp_register_style( 'custom_wc_filter', plugin_dir_url(__FILE__) . 'assets/css/woocommerce_custom_filter.css' );
            // * css for 'add "Compare" to the header'
            wp_register_style( 'header_extra_btns', plugin_dir_url(__FILE__) . 'assets/css/extra_buttons_at_header.css' );
            // * css for product
            wp_register_style( 'cpv_view_modal', plugin_dir_url(__FILE__) . 'assets/css/product_view_modal.css' );

            wp_enqueue_style( 'cpv_css', plugin_dir_url(__FILE__) . 'assets/css/product_hover.css', array( 'cpv_linearicons', 'cpv_fontawesome', 'custom_wc_filter', 'header_extra_btns', 'cpv_view_modal' ) );
        }

        /**
         * Add custom js script to frontend pages
         */
        public function wp_cpv_enqueue_scripts() {
            $data = array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
                'asset_url' => plugin_dir_url(__FILE__) . 'assets/',
            );
            wp_enqueue_script( 'custom_script_for_cpv', plugin_dir_url(__FILE__) . 'assets/js/product_hover.js' );
            wp_localize_script( 'custom_script_for_cpv', 'settings', $data );

            wp_register_script( 'imagesLoaded', plugin_dir_url(__FILE__) . 'assets/js/plugins/imagesloaded.pkgd.min.js', array(), '5.0.0', true );
            wp_register_script( 'lazyload', plugin_dir_url(__FILE__) . 'assets/js/plugins/jquery.lazyload.min.js', array(), '1.9.7', true );
            wp_register_script( 'slick', plugin_dir_url(__FILE__) . 'assets/js/plugins/slick.min.js', array(), '1.6.0', true );

            $view_modal_params = array(
        		'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'wc_ajax_url'         => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
                'nonce'               => wp_create_nonce( '_cpv_nonce' ),
                'add_to_cart_ajax'    => 1,
            );

            wp_enqueue_script( 'view_modal_script_for_cpv', plugin_dir_url(__FILE__) . 'assets/js/product_view_modal.js', array('imagesLoaded', 'lazyload', 'slick') );
            wp_localize_script( 'view_modal_script_for_cpv', 'viewParams', $view_modal_params );
        }

        /**
         * WooCommerce Loop Product Item hover bar 1 ( to shop page & category pages )
         * * function for 'product item hover customization'
         * 
         * @return string
         */
        public function product_hover_btns() {
            global $product;
            $this->print_product_hover_btns($product);
        }

        /**
         * WooCommerce Loop Product Item hover bar 2 ( to homepage )
         * * function for 'product item hover customization'
         * 
         * @return string
         */
        public function add_product_hover_btns( $value, $data, $product_param ) {
            ob_start();
            $this->print_product_hover_btns($product_param);
            $result = ob_get_clean();
            return "<li class=\"wc-block-grid__product\">" .
                        $result
                        . "<a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
                            {$data->image}
                            {$data->title}
                        </a>
                        {$data->badge}
                        {$data->price}
                        {$data->rating}
                        {$data->button}
                    </li>";
        }

        /**
         * Echo Hover bar element for Product Item
         * * function for 'product item hover customization'
         */
        public function print_product_hover_btns( $product ) {
            if ( !$product ) {
                return 0;
            }
            $icons = array(
                'cart', 'qview', 'wishlist', 'compare'
            );

            echo '<div class="footer-btns">';
            foreach ( $icons as $icon ) {
                if ( 'cart' == $icon ) {
                    $this->print_add_to_cart($product);
                }

                if ( 'qview' == $icon ) {
                    echo '<div class="icon-container" data-tooltip="クイックビュー"><a href="' . $product->get_permalink() . '" data-id="' . esc_attr( $product->get_id() ) . '"  class="mf-product-quick-view"><i class="p-icon icon-eye"></i></a></div>';
                }

                if ( $icon === 'wishlist' ) {
					if ( shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) {
                        $product_id = $product->get_id();
                        $wishlist = do_shortcode( '[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]' );
                        $wishlist_new = '<div class="icon-container" data-tooltip="お気に入りに追加">' . $wishlist . '</div>';
                        echo $wishlist_new;
					}
				}

                if ( $icon === 'compare' ) {
                    $this->product_compare($product);
				}
            }
            echo '</div>';
        }

        /**
         * Echo add_to_cart. A complementary function for existing woocommerce_template_loop_add_to_cart function
         * when there is no global product variable
         * * function for 'product item hover customization'
         */
        public function print_add_to_cart( $product_param ) {
            global $product;

            $cur_product = $product_param;
            if ( $product ) {
                $cur_product = $product;
            }

            $defaults = array(
                'quantity'   => 1,
                'class'      => implode(
                    ' ',
                    array_filter(
                        array(
                            'button',
                            'product_type_' . $cur_product->get_type(),
                            $cur_product->is_purchasable() && $cur_product->is_in_stock() ? 'add_to_cart_button' : '',
                            $cur_product->supports( 'ajax_add_to_cart' ) && $cur_product->is_purchasable() && $cur_product->is_in_stock() ? 'ajax_add_to_cart' : '',
                        )
                    )
                ),
                'attributes' => array(
                    'data-product_id'  => $cur_product->get_id(),
                    'data-product_sku' => $cur_product->get_sku(),
                    'aria-label'       => $cur_product->add_to_cart_description(),
                    'rel'              => 'nofollow',
                ),
            );

            $args = wp_parse_args( array(), $defaults );
            echo sprintf( '<div class="icon-container" data-tooltip="%s"><a href="%s" data-quantity="%s" data-title="%s" class="%s" %s >
            <i class="p-icon icon-bag2"></i>
            <span class="add-to-cart-text">%s</span>
            </a></div>',
                    esc_html( $cur_product->add_to_cart_text() ),
                    esc_url( $cur_product->add_to_cart_url() ),
                    esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
                    esc_html($cur_product->get_title()),
                    esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
                    isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
                    esc_html( $cur_product->add_to_cart_text() )
                );
        }

        /**
         * WooCommerce product compare
         * * function for 'product item hover customization'
         */
        function product_compare( $product ) {
            if ( ! class_exists( 'YITH_Woocompare' ) ) {
                return;
            }

            $button_text = get_option( 'yith_woocompare_button_text', 'Compare' );
            $product_id  = $product->get_id();
            $url_args    = array(
                'action' => 'yith-woocompare-add-product',
                'id'     => $product_id,
            );
            $lang        = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : false;
            if ( $lang ) {
                $url_args['lang'] = $lang;
            }

            $css_class   = 'compare';
            $cookie_name = 'yith_woocompare_list';
            if ( function_exists( 'is_multisite' ) && is_multisite() ) {
                $cookie_name .= '_' . get_current_blog_id();
            }
            $the_list = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( $_COOKIE[ $cookie_name ] ) : array();
            if ( in_array( $product_id, $the_list ) ) {
                $css_class          .= ' added';
                $url_args['action'] = 'yith-woocompare-view-table';
                $button_text        = '';
            }

            $url = esc_url_raw( add_query_arg( $url_args, site_url() ) );

            printf('<div class="compare-button mf-compare-button icon-container" data-tooltip="%s"><a href="%s" class="%s" data-product_id="%d">%s</a></div>', esc_html( $button_text ), esc_url( $url ), esc_attr( $css_class ), $product_id, $button_text );
        }

        /**
         * Change woocommerce attribute list to checkbox list
         * * function for 'woocommerce search customization'
         * 
         * @return string
         */
        function add_checkbox_to_filter_list( $value, $term, $link, $count ) {
            // check whether need to render <a> or <span>
            $term_name = esc_html( $term->name );

            $term_html = "<input type='checkbox' class='wc-custom--filter custom-" . $term->slug . "' id='" . $term_name . "' name='" . $term_name . "' data-link='" . esc_url( $link ) . "' value='" . $term_name . "'>";
            $term_html .= "<label class='wc-custom--filter-label' for='" . $term_name . "'>" . $term_name . "</label>"; 
            // if ( $link === false ) {
            //     $term_html = '<span>' . $term_html . '</span>';
            // } else {
            //     $term_html = '<a rel="nofollow" href="' . esc_url( $link ) . '">' . $term_name . '</a>';
            // }

			$term_html .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );
            return $term_html;
        }

        /**
         * Add extra icons (including Compare) to the header
         * * add "Compare" to the header
         * 
         * @return string
         */
        function add_extras_to_header( $value ) {
            if ( ! function_exists( 'cpv_extra_compare' ) ) {
                return $value;
            }

            $value = substr( $value, 0, strlen($value) - 11 );
        
            ob_start();
            cpv_extra_compare();
            cpv_extra_wishlist();
            cpv_extra_cart();
            $header_buttons = ob_get_clean();

            return $value . $header_buttons . '</ul></nav>';
        }

        /**
         * Add compare menu to the mobile menu
         * * add "Compare" to the header
         * * and more
         * 
         * @return string
         */
        function add_compare_to_mobile_menu( $items, $args ) {
            if ( ! function_exists( 'cpv_extra_compare_mobile' ) ) {
                return $items;
            }

            ob_start();
            cpv_extra_compare_text();
            $compare_item = ob_get_clean();

            ob_start();
            cpv_extra_cart_text();
            $cart_item = ob_get_clean();

            ob_start();
            cpv_extra_wishlist_text();
            $wish_item = ob_get_clean();

            ob_start();
            cpv_add_my_account();
            $myaccount_item = ob_get_clean();

            preg_match_all( '/<li(.*?)<\/li>/s', $items, $regex_list );
            $items_list = $regex_list[0];

            if ( $args->theme_location == 'vk-mobile-nav' ) {
            } else if ( $args->theme_location == 'header-top' ) {
                $items_count = count( $items_list );
                if ( $items_count > 3 ) {
                    return $items_list[0] . $items_list[1] . $compare_item . $wish_item . $cart_item;
                } else if ( $items_count > 0 ) {
                    return $items_list[0] . $items_list[1] . $myaccount_item . $compare_item . $wish_item . $cart_item;
                } else {
                    return $items;
                }
            } else {
                return $items;
            }

            ob_start();
            cpv_extra_compare_mobile();
            $compare_menu = ob_get_clean();

            if ( $items_list ) {
                if ( count( $items_list ) > 4 ) {
                    array_splice( $items_list, 4, 0, $compare_menu );
                } else {
                    return $items . $compare_menu;
                }

                return implode( '', $items_list );
            } else {
                return $items;
            }
        }

        /**
         * add 
         */
        function ajax_counter_bar() {
            $wishlist_counter = $this->yith_wcwl_get_items_count();
            // $wishlist_counter = do_shortcode("[yith_wcwl_items_count]");

            ?>
                <div class="ajax-counter-list">
                    <span class="mini-cart-counter" id="mini-item-counter">counter</span>
                    <?php echo $wishlist_counter; ?>
                </div>
            <?php
        }        

        function ajax_counter_bar_filter($content) {
            $wishlist_counter = $this->yith_wcwl_get_items_count();
            ob_start(); 
            ?>
                <div class="ajax-counter-list">
                    <span class="mini-cart-counter" id="mini-item-counter">counter</span>
                    <?php echo $wishlist_counter; ?>
                </div>
            <?php
            $output = ob_get_clean();
            return $output . $content;
        }

        /**
         * Product quick view ajax modal
         * ajax function
         */
        function product_quick_view() {
            if ( apply_filters( 'cpv_ajax_referer', true ) ) {
                check_ajax_referer( '_cpv_nonce', 'nonce' );
            }
            ob_start(); 
            if ( isset( $_POST['product_id'] ) && !empty( $_POST['product_id'] ) ) {
                $product_id      = $_POST['product_id'];
                $original_post   = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : 0;
                $GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
                setup_postdata( $GLOBALS['post'] );
                wc_get_template_part( 'content', 'product-quick-view' );
                $GLOBALS['post'] = $original_post; // WPCS: override ok.
            }
            $output = ob_get_clean();
            wp_send_json_success( $output );
            die();
        }

        /**
         * ajax wishlist
         */
        function yith_wcwl_get_items_count() {
            ob_start();
            ?>
                <a href="<?php echo esc_url( YITH_WCWL()->get_wishlist_url() ); ?>">
                <span class="yith-wcwl-items-count">
                    <i class="yith-wcwl-icon fa fa-heart-o"><?php echo esc_html( yith_wcwl_count_all_products() ); ?></i>
                </span>
                </a>
            <?php
            return ob_get_clean();
        }

        function yith_wcwl_ajax_update_count() {
            wp_send_json( array(
            'count' => yith_wcwl_count_all_products()
            ) );
        }

        function yith_wcwl_enqueue_custom_script() {
            wp_add_inline_script(
            'jquery-yith-wcwl',
            "
                jQuery( function( $ ) {
                $( document ).on( 'added_to_wishlist removed_from_wishlist', function() {
                    $.get( yith_wcwl_l10n.ajax_url, {
                    action: 'yith_wcwl_update_wishlist_count'
                    }, function( data ) {
                    $('.yith-wcwl-items-count').children('i').html( data.count );
                    } );
                } );
                } );
            "
            );
        }

    }
    
    new Custom_Product_View();
}


/**
 * template part file load helper function
 * 
 */
function bb_load_wc_template_file( $template_name ) {
    // Now check plugin folder - e.g. wp-content/plugins/myplugin/woocommerce.
    $file = plugin_dir_path( __FILE__ ) . '/woocommerce/' . $template_name;
    if ( @file_exists( $file ) ) {
        return $file;
    }
    // Check theme folder first - e.g. wp-content/themes/---theme/woocommerce.
    $file = get_stylesheet_directory() . '/woocommerce/' . $template_name;
    if ( @file_exists( $file ) ) {
        return $file;
    }
}
add_filter( 'wc_get_template_part', function( $template, $slug, $name ){
    $file = bb_load_wc_template_file( "{$slug}-{$name}.php" );
    return $file ? $file : $template;
}, 10, 3 );
add_filter( 'woocommerce_locate_template', function( $template, $template_name ){
    $file = bb_load_wc_template_file( $template_name );
    return $file ? $file : $template;
}, 10, 3 );
add_filter( 'wc_get_template', function( $template, $template_name ){
    $file = bb_load_wc_template_file( $template_name );
    return $file ? $file : $template;
}, 10, 2 );

/**
 * * error log wrapper
 */
if ( !function_exists( 'write_log' ) ) {

    function write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}