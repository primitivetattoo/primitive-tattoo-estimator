<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PTB_Est_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ptb_estimator';
    }

    public function get_title() {
        return __( 'Tattoo Price Estimator', 'primitive-tattoo-estimator' );
    }

    public function get_icon() {
        return 'eicon-price-list';
    }

    public function get_categories() {
        return array( 'general' );
    }

    public function get_keywords() {
        return array( 'tattoo', 'price', 'estimator', 'calculator', 'primitive' );
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array(
                'label' => __( 'Tattoo Estimator', 'primitive-tattoo-estimator' ),
            )
        );

        $this->add_control(
            'info_notice',
            array(
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => sprintf(
                    /* translators: %s: settings page URL */
                    __( 'Pricing, promo, and typography settings are managed in %s.', 'primitive-tattoo-estimator' ),
                    '<a href="' . esc_url( admin_url( 'options-general.php?page=ptb-estimator' ) ) . '" target="_blank">' . __( 'Settings &rarr; Tattoo Estimator', 'primitive-tattoo-estimator' ) . '</a>'
                ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            )
        );

        $this->add_control(
            'font_heading_override',
            array(
                'label'       => __( 'Heading Font Override', 'primitive-tattoo-estimator' ),
                'type'        => \Elementor\Controls_Manager::FONT,
                'default'     => '',
                'description' => __( 'Override heading font for this widget instance. Leave empty to use plugin settings or theme default.', 'primitive-tattoo-estimator' ),
            )
        );

        $this->add_control(
            'font_body_override',
            array(
                'label'       => __( 'Body Font Override', 'primitive-tattoo-estimator' ),
                'type'        => \Elementor\Controls_Manager::FONT,
                'default'     => '',
                'description' => __( 'Override body font for this widget instance. Leave empty to use plugin settings or theme default.', 'primitive-tattoo-estimator' ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // In Elementor editor, show a preview placeholder
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div style="background:linear-gradient(135deg,#1e1608,#2a1f0a);border:1px solid #b8986a;border-radius:6px;padding:40px 24px;text-align:center;color:#f5efe6;">'
                . '<p style="font-size:11px;letter-spacing:0.3em;color:#b8986a;text-transform:uppercase;margin-bottom:8px;">&#10022; Primitive Tattoo Bali</p>'
                . '<h3 style="font-size:24px;font-weight:400;margin:0 0 8px;">Tattoo Price Estimator</h3>'
                . '<p style="font-size:13px;opacity:0.5;margin:0;">Live estimator will render on the frontend</p>'
                . '</div>';
            return;
        }

        // Build per-instance font overrides as CSS custom properties
        $font_heading = '';
        $font_body    = '';

        if ( ! empty( $settings['font_heading_override'] ) ) {
            $font_heading = $settings['font_heading_override'];
        }
        if ( ! empty( $settings['font_body_override'] ) ) {
            $font_body = $settings['font_body_override'];
        }

        if ( $font_heading || $font_body ) {
            $parts = array();
            if ( $font_heading ) {
                $parts[] = '--ptb-font-heading: ' . esc_attr( $font_heading );
            }
            if ( $font_body ) {
                $parts[] = '--ptb-font-body: ' . esc_attr( $font_body );
            }
            echo '<div id="ptb-estimator-root" class="ptb-estimator-wrap" style="' . esc_attr( implode( '; ', $parts ) ) . '"></div>';
        } else {
            echo '<div id="ptb-estimator-root" class="ptb-estimator-wrap"></div>';
        }
    }
}
