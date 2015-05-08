<?php
/*
 * Plugin Name: Easy Social Share Buttons
 * Version: 1.0
 * Plugin URI: http://www.idiom.co/
 * Description: Easily add social sharing buttons to your posts and images without slowing down your site with unnecessary javascript and image files.
 * Author: Idiom Interactive
 * Author URI: http://www.idiom.co/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: easy-social-share-buttons
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Idiom Interactive
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-easy-social-share-buttons.php' );
require_once( 'includes/class-easy-social-share-buttons-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-easy-social-share-buttons-admin-api.php' );

/**
 * Returns the main instance of Easy_Social_Share_Buttons to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Easy_Social_Share_Buttons
 */
function Easy_Social_Share_Buttons () {
	$instance = Easy_Social_Share_Buttons::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Easy_Social_Share_Buttons_Settings::instance( $instance );
	}

	return $instance;
}

$easy_social_share = Easy_Social_Share_Buttons();
