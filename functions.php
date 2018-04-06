<?php
/**
 * Set up theme defaults and register support for various WordPress features
 */
if (!function_exists('kinectiv_start_setup')) {
	function kinectiv_start_setup() {
		// Let WordPress manage the document title
		add_theme_support('title-tag');

		// Register nav menus
		register_nav_menus(array(
			'menu-1' => 'Primary Menu'
		));

		// Switch default core markup for search form, comment form, and comments to output valid HTML5
		add_theme_support('html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		));
	}
}
add_action('after_setup_theme', 'kinectiv_start_setup');

/**
 * Set the content width in pixels
 */
function kinectiv_start_content_width() {
	$GLOBALS['content_width'] = apply_filters('kinectiv_start_content_width', 640);
}
add_action('after_setup_theme', 'kinectiv_start_content_width', 0);

/**
 * Enqueue scripts and styles
 */
function kinectiv_start_scripts() {
	wp_enqueue_style('kinectiv-start-style', get_stylesheet_directory_uri() . '/style.min.css', array(), '0.1.0');
    
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.min.js', array(), '1.12.4', false);
	wp_enqueue_script('kinectiv-start-script', get_template_directory_uri() . '/js/script.min.js', array('jquery'), '0.1.0', true);
}
add_action('wp_enqueue_scripts', 'kinectiv_start_scripts');

/**
 * Add ACF options page
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page('Theme Options');
}