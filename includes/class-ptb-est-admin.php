<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PTB_Est_Admin {

    private $option_key = 'ptb_est_settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
    }

    /**
     * Customize admin footer text on plugin page.
     *
     * @param string $text Default footer text.
     * @return string
     */
    public function admin_footer_text( $text ) {
        $screen = get_current_screen();

        if ( $screen && 'settings_page_ptb-estimator' === $screen->id ) {
            return 'Built by <a href="https://primitivetattoo.com" target="_blank">Primitive Tattoo Bali</a> | Thank you for creating with WordPress.';
        }

        return $text;
    }

    /**
     * Get default settings matching current hardcoded values.
     */
    public static function get_defaults() {
        return array(
            // General
            'wa_number'       => '',

            // Promo
            'promo_active'    => 0,
            'promo_title'     => 'Special Offer',
            'promo_sub'       => 'Limited time · While promo lasts',
            'promo_price'     => '$100',
            'promo_url'       => '',
            'promo_wa_msg'    => "Hi! I'd like to book the special promo offer. Can I get more details?",

            // Styles
            'styles' => array(
                array( 'name' => 'Fine Line',   'icon' => '✦', 'desc' => 'Delicate, precise linework',    'mult' => 1.2 ),
                array( 'name' => 'Blackwork',   'icon' => '◼', 'desc' => 'Bold black ink designs',         'mult' => 1.0 ),
                array( 'name' => 'Mandala',     'icon' => '❋', 'desc' => 'Geometric spiritual patterns',   'mult' => 1.3 ),
                array( 'name' => 'Traditional', 'icon' => '⚓', 'desc' => 'Classic bold outlines & fills', 'mult' => 1.0 ),
                array( 'name' => 'Realism',     'icon' => '◎', 'desc' => 'Photo-realistic detail work',    'mult' => 1.5 ),
                array( 'name' => 'Watercolor',  'icon' => '◈', 'desc' => 'Fluid painterly effects',        'mult' => 1.2 ),
            ),

            // Sizes
            'sizes' => array(
                array( 'name' => 'Small',       'sub' => 'Coin-sized · up to 5cm',  'base_min' => 50,   'base_max' => 100 ),
                array( 'name' => 'Medium',      'sub' => 'Palm-sized · 5–10cm',     'base_min' => 100,  'base_max' => 250 ),
                array( 'name' => 'Large',       'sub' => 'Hand-sized · 10–20cm',    'base_min' => 250,  'base_max' => 500 ),
                array( 'name' => 'Extra Large', 'sub' => 'Full panel · 20cm+',      'base_min' => 500,  'base_max' => 1000 ),
            ),

            // Placements
            'placements' => array(
                array( 'name' => 'Wrist',     'mult' => 1.0 ),
                array( 'name' => 'Forearm',   'mult' => 1.0 ),
                array( 'name' => 'Upper Arm', 'mult' => 1.0 ),
                array( 'name' => 'Chest',     'mult' => 1.1 ),
                array( 'name' => 'Back',      'mult' => 1.1 ),
                array( 'name' => 'Ribcage',   'mult' => 1.2 ),
                array( 'name' => 'Calf',      'mult' => 1.0 ),
                array( 'name' => 'Thigh',     'mult' => 1.05 ),
                array( 'name' => 'Neck',      'mult' => 1.2 ),
                array( 'name' => 'Finger',    'mult' => 0.9 ),
            ),

            // Complexity
            'complexity' => array(
                array( 'label' => 'Simple',   'sub' => 'Clean lines, minimal detail', 'mult' => 1.0 ),
                array( 'label' => 'Moderate', 'sub' => 'Some shading or fills',       'mult' => 1.3 ),
                array( 'label' => 'Detailed', 'sub' => 'Heavy shading, fine detail',  'mult' => 1.65 ),
            ),

            // Typography
            'font_heading' => '',
            'font_body'    => '',
        );
    }

    /**
     * Get merged settings (saved values + defaults fallback).
     */
    public static function get_settings() {
        $saved    = get_option( 'ptb_est_settings', array() );
        $defaults = self::get_defaults();
        return wp_parse_args( $saved, $defaults );
    }

    public function add_menu() {
        add_options_page(
            __( 'Tattoo Estimator', 'primitive-tattoo-estimator' ),
            __( 'Tattoo Estimator', 'primitive-tattoo-estimator' ),
            'manage_options',
            'ptb-estimator',
            array( $this, 'render_page' )
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_ptb-estimator' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'ptb-est-admin',
            PTB_EST_URL . 'admin/admin.css',
            array(),
            PTB_EST_VERSION
        );
    }

    public function register_settings() {
        register_setting( 'ptb_est_settings_group', $this->option_key, array( $this, 'sanitize_settings' ) );
    }

    /**
     * Sanitize all settings on save.
     */
    public function sanitize_settings( $input ) {
        $clean = array();

        // General
        $clean['wa_number'] = sanitize_text_field( wp_unslash( $input['wa_number'] ?? '' ) );

        // Promo
        $clean['promo_active'] = ! empty( $input['promo_active'] ) ? 1 : 0;
        $clean['promo_title']  = sanitize_text_field( wp_unslash( $input['promo_title'] ?? '' ) );
        $clean['promo_sub']    = sanitize_text_field( wp_unslash( $input['promo_sub'] ?? '' ) );
        $clean['promo_price']  = sanitize_text_field( wp_unslash( $input['promo_price'] ?? '' ) );
        $clean['promo_url']    = esc_url_raw( wp_unslash( $input['promo_url'] ?? '' ) );
        $clean['promo_wa_msg'] = sanitize_textarea_field( wp_unslash( $input['promo_wa_msg'] ?? '' ) );

        // Styles
        $clean['styles'] = array();
        if ( ! empty( $input['styles'] ) && is_array( $input['styles'] ) ) {
            foreach ( $input['styles'] as $style ) {
                $clean['styles'][] = array(
                    'name' => sanitize_text_field( wp_unslash( $style['name'] ?? '' ) ),
                    'icon' => sanitize_text_field( wp_unslash( $style['icon'] ?? '' ) ),
                    'desc' => sanitize_text_field( wp_unslash( $style['desc'] ?? '' ) ),
                    'mult' => floatval( $style['mult'] ?? 1.0 ),
                );
            }
        }

        // Sizes
        $clean['sizes'] = array();
        if ( ! empty( $input['sizes'] ) && is_array( $input['sizes'] ) ) {
            foreach ( $input['sizes'] as $size ) {
                $clean['sizes'][] = array(
                    'name'     => sanitize_text_field( wp_unslash( $size['name'] ?? '' ) ),
                    'sub'      => sanitize_text_field( wp_unslash( $size['sub'] ?? '' ) ),
                    'base_min' => intval( $size['base_min'] ?? 0 ),
                    'base_max' => intval( $size['base_max'] ?? 0 ),
                );
            }
        }

        // Placements
        $clean['placements'] = array();
        if ( ! empty( $input['placements'] ) && is_array( $input['placements'] ) ) {
            foreach ( $input['placements'] as $placement ) {
                $clean['placements'][] = array(
                    'name' => sanitize_text_field( wp_unslash( $placement['name'] ?? '' ) ),
                    'mult' => floatval( $placement['mult'] ?? 1.0 ),
                );
            }
        }

        // Complexity
        $clean['complexity'] = array();
        if ( ! empty( $input['complexity'] ) && is_array( $input['complexity'] ) ) {
            foreach ( $input['complexity'] as $comp ) {
                $clean['complexity'][] = array(
                    'label' => sanitize_text_field( wp_unslash( $comp['label'] ?? '' ) ),
                    'sub'   => sanitize_text_field( wp_unslash( $comp['sub'] ?? '' ) ),
                    'mult'  => floatval( $comp['mult'] ?? 1.0 ),
                );
            }
        }

        // Typography
        $clean['font_heading'] = sanitize_text_field( wp_unslash( $input['font_heading'] ?? '' ) );
        $clean['font_body']    = sanitize_text_field( wp_unslash( $input['font_body'] ?? '' ) );

        return $clean;
    }

    /**
     * Render the settings page.
     */
    public function render_page() {
        $s = self::get_settings();
        ?>
        <div class="wrap ptb-admin-wrap">
            <h1><?php esc_html_e( 'Tattoo Price Estimator Settings', 'primitive-tattoo-estimator' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Configure the pricing, promo banner, and options for the [ptb_estimator] shortcode.', 'primitive-tattoo-estimator' ); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields( 'ptb_est_settings_group' ); ?>

                <!-- ======== GENERAL ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'General', 'primitive-tattoo-estimator' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="ptb_wa_number"><?php esc_html_e( 'WhatsApp Number', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td>
                                <input type="text" id="ptb_wa_number" name="ptb_est_settings[wa_number]" value="<?php echo esc_attr( $s['wa_number'] ); ?>" class="regular-text" placeholder="6281945737100">
                                <p class="description"><?php esc_html_e( 'Full international format without +, e.g. 6281945737100', 'primitive-tattoo-estimator' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- ======== TYPOGRAPHY ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Typography', 'primitive-tattoo-estimator' ); ?></h2>
                    <p class="description" style="margin-bottom: 8px;"><?php esc_html_e( 'Leave empty to inherit from your theme. Enter any Google Font or system font name.', 'primitive-tattoo-estimator' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th><label for="ptb_font_heading"><?php esc_html_e( 'Heading Font', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td>
                                <input type="text" id="ptb_font_heading" name="ptb_est_settings[font_heading]" value="<?php echo esc_attr( $s['font_heading'] ); ?>" class="regular-text" placeholder="e.g. Playfair Display">
                                <p class="description"><?php esc_html_e( 'Used for the main title, step questions, and price display.', 'primitive-tattoo-estimator' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="ptb_font_body"><?php esc_html_e( 'Body Font', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td>
                                <input type="text" id="ptb_font_body" name="ptb_est_settings[font_body]" value="<?php echo esc_attr( $s['font_body'] ); ?>" class="regular-text" placeholder="e.g. Inter">
                                <p class="description"><?php esc_html_e( 'Used for descriptions, labels, buttons, and all other text.', 'primitive-tattoo-estimator' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- ======== PROMO BANNER ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Promo Banner', 'primitive-tattoo-estimator' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Enable Promo', 'primitive-tattoo-estimator' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ptb_est_settings[promo_active]" value="1" <?php checked( $s['promo_active'], 1 ); ?>>
                                    <?php esc_html_e( 'Show promo banner on estimator', 'primitive-tattoo-estimator' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="ptb_promo_title"><?php esc_html_e( 'Title', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td><input type="text" id="ptb_promo_title" name="ptb_est_settings[promo_title]" value="<?php echo esc_attr( $s['promo_title'] ); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="ptb_promo_sub"><?php esc_html_e( 'Subtitle', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td><input type="text" id="ptb_promo_sub" name="ptb_est_settings[promo_sub]" value="<?php echo esc_attr( $s['promo_sub'] ); ?>" class="large-text"></td>
                        </tr>
                        <tr>
                            <th><label for="ptb_promo_price"><?php esc_html_e( 'Price Display', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td><input type="text" id="ptb_promo_price" name="ptb_est_settings[promo_price]" value="<?php echo esc_attr( $s['promo_price'] ); ?>" class="regular-text" placeholder="IDR 1,000,000"></td>
                        </tr>
                        <tr>
                            <th><label for="ptb_promo_url"><?php esc_html_e( 'Button URL', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td>
                                <input type="url" id="ptb_promo_url" name="ptb_est_settings[promo_url]" value="<?php echo esc_attr( $s['promo_url'] ); ?>" class="large-text" placeholder="https://example.com/booking">
                                <p class="description"><?php esc_html_e( 'Leave empty to use WhatsApp link. Set a URL to link the promo button to a booking page instead.', 'primitive-tattoo-estimator' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="ptb_promo_wa_msg"><?php esc_html_e( 'WhatsApp Message', 'primitive-tattoo-estimator' ); ?></label></th>
                            <td>
                                <textarea id="ptb_promo_wa_msg" name="ptb_est_settings[promo_wa_msg]" rows="3" class="large-text"><?php echo esc_textarea( $s['promo_wa_msg'] ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Pre-filled WhatsApp message when Button URL is empty.', 'primitive-tattoo-estimator' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- ======== SIZES (Base Price Ranges) ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Size Pricing Ranges (IDR)', 'primitive-tattoo-estimator' ); ?></h2>
                    <p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Base price ranges before style, placement and complexity multipliers are applied.', 'primitive-tattoo-estimator' ); ?></p>
                    <table class="widefat ptb-pricing-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Size Name', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Min Price (IDR)', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Max Price (IDR)', 'primitive-tattoo-estimator' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $s['sizes'] as $i => $size ) : ?>
                            <tr>
                                <td><input type="text" name="ptb_est_settings[sizes][<?php echo intval( $i ); ?>][name]" value="<?php echo esc_attr( $size['name'] ); ?>" class="ptb-input-name"></td>
                                <td><input type="text" name="ptb_est_settings[sizes][<?php echo intval( $i ); ?>][sub]" value="<?php echo esc_attr( $size['sub'] ); ?>" class="ptb-input-desc"></td>
                                <td><input type="number" name="ptb_est_settings[sizes][<?php echo intval( $i ); ?>][base_min]" value="<?php echo intval( $size['base_min'] ); ?>" step="50000" min="0" class="ptb-input-price"></td>
                                <td><input type="number" name="ptb_est_settings[sizes][<?php echo intval( $i ); ?>][base_max]" value="<?php echo intval( $size['base_max'] ); ?>" step="50000" min="0" class="ptb-input-price"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ======== STYLES (Multipliers) ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Style Multipliers', 'primitive-tattoo-estimator' ); ?></h2>
                    <p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Multipliers applied to the base size price. 1.0 = no change, 1.2 = 20% more, 0.9 = 10% less.', 'primitive-tattoo-estimator' ); ?></p>
                    <table class="widefat ptb-pricing-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Icon', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Style Name', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Multiplier', 'primitive-tattoo-estimator' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $s['styles'] as $i => $style ) : ?>
                            <tr>
                                <td><input type="text" name="ptb_est_settings[styles][<?php echo intval( $i ); ?>][icon]" value="<?php echo esc_attr( $style['icon'] ); ?>" class="ptb-input-icon"></td>
                                <td><input type="text" name="ptb_est_settings[styles][<?php echo intval( $i ); ?>][name]" value="<?php echo esc_attr( $style['name'] ); ?>" class="ptb-input-name"></td>
                                <td><input type="text" name="ptb_est_settings[styles][<?php echo intval( $i ); ?>][desc]" value="<?php echo esc_attr( $style['desc'] ); ?>" class="ptb-input-desc"></td>
                                <td><input type="number" name="ptb_est_settings[styles][<?php echo intval( $i ); ?>][mult]" value="<?php echo esc_attr( $style['mult'] ); ?>" step="0.05" min="0" class="ptb-input-mult"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ======== PLACEMENTS (Multipliers) ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Placement Multipliers', 'primitive-tattoo-estimator' ); ?></h2>
                    <p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Body area difficulty multipliers. Ribcage/Neck cost more due to sensitivity.', 'primitive-tattoo-estimator' ); ?></p>
                    <table class="widefat ptb-pricing-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Placement', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Multiplier', 'primitive-tattoo-estimator' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $s['placements'] as $i => $placement ) : ?>
                            <tr>
                                <td><input type="text" name="ptb_est_settings[placements][<?php echo intval( $i ); ?>][name]" value="<?php echo esc_attr( $placement['name'] ); ?>" class="ptb-input-name"></td>
                                <td><input type="number" name="ptb_est_settings[placements][<?php echo intval( $i ); ?>][mult]" value="<?php echo esc_attr( $placement['mult'] ); ?>" step="0.05" min="0" class="ptb-input-mult"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ======== COMPLEXITY (Multipliers) ======== -->
                <div class="ptb-admin-card">
                    <h2><?php esc_html_e( 'Complexity Multipliers', 'primitive-tattoo-estimator' ); ?></h2>
                    <table class="widefat ptb-pricing-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Level', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'primitive-tattoo-estimator' ); ?></th>
                                <th><?php esc_html_e( 'Multiplier', 'primitive-tattoo-estimator' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $s['complexity'] as $i => $comp ) : ?>
                            <tr>
                                <td><input type="text" name="ptb_est_settings[complexity][<?php echo intval( $i ); ?>][label]" value="<?php echo esc_attr( $comp['label'] ); ?>" class="ptb-input-name"></td>
                                <td><input type="text" name="ptb_est_settings[complexity][<?php echo intval( $i ); ?>][sub]" value="<?php echo esc_attr( $comp['sub'] ); ?>" class="ptb-input-desc"></td>
                                <td><input type="number" name="ptb_est_settings[complexity][<?php echo intval( $i ); ?>][mult]" value="<?php echo esc_attr( $comp['mult'] ); ?>" step="0.05" min="0" class="ptb-input-mult"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php submit_button( __( 'Save Settings', 'primitive-tattoo-estimator' ) ); ?>
            </form>
        </div>
        <?php
    }
}
