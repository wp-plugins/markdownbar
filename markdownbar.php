<?php
/*
 * Plugin Name: MarkdownBar
 * Version: 0.1
 * Plugin URI: http://www.jonheller.com/
 * Description: Adds a toolbar to the post editor with Markdown buttons
 * Author: Jon Heller
 * Author URI: http://www.jonheller.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: markdownbar
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Jon Heller
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-markdownbar.php' );
// require_once( 'includes/class-markdownbar-settings.php' );

// Load plugin libraries
// require_once( 'includes/lib/class-markdownbar-admin-api.php' );

/**
 * Returns the main instance of MarkdownBar to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object MarkdownBar
 */
function MarkdownBar () {
	$instance = MarkdownBar::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		//$instance->settings = MarkdownBar_Settings::instance( $instance );
	}

	return $instance;
}

MarkdownBar();
