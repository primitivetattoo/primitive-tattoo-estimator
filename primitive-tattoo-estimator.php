<?php
/**
 * Plugin Name: Primitive Tattoo Price Estimator
 * Description: Interactive tattoo price estimator with promo support and Elementor widget. Use shortcode [ptb_estimator] or the Elementor widget.
 * Version: 1.2.6
 * Author: Primitive Tattoo Bali
 * Author URI: https://primitivetattoo.com
 * Plugin URI: https://github.com/primitivetattoo/primitive-tattoo-estimator
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: primitive-tattoo-estimator
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PTB_EST_VERSION', '1.2.6' );
define( 'PTB_EST_URL', plugin_dir_url( __FILE__ ) );
define( 'PTB_EST_PATH', plugin_dir_path( __FILE__ ) );

// Admin settings page
if ( is_admin() ) {
    require_once PTB_EST_PATH . 'includes/class-ptb-est-admin.php';
    new PTB_Est_Admin();
}

// Elementor widget integration
add_action( 'plugins_loaded', function() {
    if ( did_action( 'elementor/loaded' ) ) {
        require_once PTB_EST_PATH . 'integrations/elementor/class-ptb-est-elementor.php';
        PTB_Est_Elementor::instance();
    }
} );

/**
 * Build the config array to pass to frontend JS.
 */
function ptb_est_get_frontend_config() {
    require_once PTB_EST_PATH . 'includes/class-ptb-est-admin.php';
    $s = PTB_Est_Admin::get_settings();

    // Convert sizes to JS format with base array
    $sizes = array();
    foreach ( $s['sizes'] as $size ) {
        $sizes[] = array(
            'name' => $size['name'],
            'sub'  => $size['sub'],
            'base' => array( intval( $size['base_min'] ), intval( $size['base_max'] ) ),
        );
    }

    return array(
        'waNumber'   => $s['wa_number'],
        'promo'      => array(
            'active' => (bool) $s['promo_active'],
            'title'  => $s['promo_title'],
            'sub'    => $s['promo_sub'],
            'price'  => $s['promo_price'],
            'url'    => $s['promo_url'],
            'waMsg'  => $s['promo_wa_msg'],
        ),
        'styles'     => $s['styles'],
        'sizes'      => $sizes,
        'placements' => $s['placements'],
        'complexity' => $s['complexity'],
    );
}

/**
 * Generate inline CSS for custom font overrides.
 */
function ptb_est_get_font_css() {
    require_once PTB_EST_PATH . 'includes/class-ptb-est-admin.php';
    $s = PTB_Est_Admin::get_settings();

    $css = '';
    $font_heading = $s['font_heading'];
    $font_body    = $s['font_body'];

    if ( ! $font_heading && ! $font_body ) {
        return '';
    }

    // CSS custom properties on the wrapper — can be overridden per-instance by Elementor widget
    $css .= '.ptb-estimator-wrap {';
    if ( $font_heading ) {
        $css .= '--ptb-font-heading: ' . esc_attr( $font_heading ) . ';';
    }
    if ( $font_body ) {
        $css .= '--ptb-font-body: ' . esc_attr( $font_body ) . ';';
    }
    $css .= '}';

    return $css;
}

/**
 * Enqueue Google Fonts if custom fonts are set.
 */
function ptb_est_enqueue_google_fonts() {
    require_once PTB_EST_PATH . 'includes/class-ptb-est-admin.php';
    $s = PTB_Est_Admin::get_settings();

    $fonts = array();
    if ( ! empty( $s['font_heading'] ) ) {
        $fonts[] = $s['font_heading'];
    }
    if ( ! empty( $s['font_body'] ) && $s['font_body'] !== $s['font_heading'] ) {
        $fonts[] = $s['font_body'];
    }

    if ( empty( $fonts ) ) {
        return;
    }

    // System fonts that don't need Google Fonts loading
    $system_fonts = array(
        'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Verdana',
        'Courier New', 'Trebuchet MS', 'Impact', 'Comic Sans MS',
        'Tahoma', 'Palatino', 'Garamond', 'Lucida Console',
        'sans-serif', 'serif', 'monospace', 'cursive', 'fantasy',
    );

    $google_fonts = array();
    foreach ( $fonts as $font ) {
        $is_system = false;
        foreach ( $system_fonts as $sf ) {
            if ( strcasecmp( $font, $sf ) === 0 ) {
                $is_system = true;
                break;
            }
        }
        if ( ! $is_system ) {
            $google_fonts[] = str_replace( ' ', '+', $font ) . ':wght@300;400;500;600;700';
        }
    }

    if ( ! empty( $google_fonts ) ) {
        $url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $google_fonts ) . '&display=swap';
        wp_enqueue_style( 'ptb-est-google-fonts', $url, array(), PTB_EST_VERSION );
    }
}

/**
 * Enqueue assets — supports both shortcode and Elementor widget.
 */
function ptb_est_enqueue_assets() {
    global $post;

    $has_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ptb_estimator' );
    $has_elementor = is_a( $post, 'WP_Post' ) && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( $post->ID ) && strpos( get_post_meta( $post->ID, '_elementor_data', true ) ?: '', '"widgetType":"ptb_estimator"' ) !== false;

    if ( ! $has_shortcode && ! $has_elementor ) {
        return;
    }

    wp_enqueue_style(
        'ptb-estimator',
        PTB_EST_URL . 'assets/estimator.css',
        array(),
        PTB_EST_VERSION
    );

    // Inline font CSS (from plugin settings — Elementor per-instance overrides via style attr)
    $font_css = ptb_est_get_font_css();
    if ( $font_css ) {
        wp_add_inline_style( 'ptb-estimator', $font_css );
    }

    wp_enqueue_script(
        'ptb-estimator',
        PTB_EST_URL . 'assets/estimator.js',
        array(),
        PTB_EST_VERSION,
        true
    );
    wp_localize_script( 'ptb-estimator', 'ptbEstConfig', ptb_est_get_frontend_config() );

    ptb_est_enqueue_google_fonts();
}
add_action( 'wp_enqueue_scripts', 'ptb_est_enqueue_assets' );

/**
 * Also enqueue in Elementor preview/editor.
 */
add_action( 'elementor/preview/enqueue_scripts', function() {
    wp_enqueue_style(
        'ptb-estimator',
        PTB_EST_URL . 'assets/estimator.css',
        array(),
        PTB_EST_VERSION
    );
    wp_enqueue_script(
        'ptb-estimator',
        PTB_EST_URL . 'assets/estimator.js',
        array(),
        PTB_EST_VERSION,
        true
    );
    wp_localize_script( 'ptb-estimator', 'ptbEstConfig', ptb_est_get_frontend_config() );

    $font_css = ptb_est_get_font_css();
    if ( $font_css ) {
        wp_add_inline_style( 'ptb-estimator', $font_css );
    }

    ptb_est_enqueue_google_fonts();
} );

/**
 * Shortcode handler.
 */
function ptb_est_render_shortcode( $atts ) {
    return '<div id="ptb-estimator-root" class="ptb-estimator-wrap"></div>';
}
add_shortcode( 'ptb_estimator', 'ptb_est_render_shortcode' );
