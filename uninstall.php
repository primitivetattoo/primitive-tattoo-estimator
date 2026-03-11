<?php
/**
 * Uninstall handler for Primitive Tattoo Price Estimator.
 * Removes all plugin data from the database.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'ptb_est_settings' );
