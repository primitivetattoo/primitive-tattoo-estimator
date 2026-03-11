<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PTB_Est_Elementor {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
    }

    public function register_widget( $widgets_manager ) {
        require_once PTB_EST_PATH . 'integrations/elementor/class-ptb-est-elementor-widget.php';
        $widgets_manager->register( new PTB_Est_Elementor_Widget() );
    }
}
