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
//	wp_enqueue_style('kinectiv-start-vendor-style', get_stylesheet_directory_uri() . '/css/vendor.min.css', array(), '1.0.0');
    
    wp_deregister_script('wp-embed');
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.min.js', array(), null, false);
	wp_enqueue_script('kinectiv-start-script', get_template_directory_uri() . '/js/script.min.js', array('jquery'), '0.1.0', true);
}
add_action('wp_enqueue_scripts', 'kinectiv_start_scripts');

function ks_admin_scripts() {
	wp_enqueue_script('ks-admin-js', get_template_directory_uri() . '/js/wp-admin.js', array(), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'ks_admin_scripts');


/**
 * Add ACF options page
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page('Theme Options');
}


/**
 * Remove Site Icon control from Theme Customization
 */
function ks_customize_register($wp_customize) {
    $wp_customize->remove_control('site_icon');
}
add_action('customize_register', 'ks_customize_register', 20);  


/**
 * Add site icon
 */
function ks_favicon() {
  echo '<link rel="icon" type="image/x-icon" href="' . get_stylesheet_directory_uri() . '/images/favicon.ico" />';
}
add_action('wp_head', 'ks_favicon');
add_action('admin_head', 'ks_favicon');


/**
 * Gravity Forms hide "Add Form" WYSIWYG button
 */
add_filter('gform_display_add_form_button', '__return_false');


/**
 * Disable comments
 */
function ks_disable_comments_post_types_support() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if(post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}
add_action('admin_init', 'ks_disable_comments_post_types_support'); // disable support for comments and trackbacks for all post types

add_filter('comments_open', '__return_false', 20); // close comments
add_filter('pings_open', '__return_false', 20); // close pings

add_filter('comments_array', '__return_empty_array'); // return empty comments array

function ks_disable_comments_admin_menu() {
	remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
}
add_action('admin_menu', 'ks_disable_comments_admin_menu'); // remove comments and discussion settings from admin menu

function ks_disabled_comments_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}
add_action('admin_bar_menu', 'ks_disabled_comments_admin_bar', 999); // remove comments links from admin bar

function ks_disable_comments_admin_redirect() {
	global $pagenow;
	if ($pagenow == 'edit-comments.php' || $pagenow == 'options-discussion.php') {
		wp_redirect(admin_url());
        exit;
	}
}
add_action('admin_init', 'ks_disable_comments_admin_redirect'); // redirect any user trying to access comments page

function ks_disable_comments_dashboard() {
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('admin_init', 'ks_disable_comments_dashboard'); // remove comments metabox from dashboard


/**
 * Remove oEmbed discovery links and REST API endpoint
 */
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('rest_api_init', 'wp_oembed_register_route');


/**
 * Remove unnecessary header code
 */
remove_action('wp_head', 'rsd_link'); // remove RSD link used by blog clients
remove_action('wp_head', 'wlwmanifest_link'); // remove Windows Live Writer client link
remove_action('wp_head', 'wp_shortlink_wp_head'); // remove shortlink
remove_action('wp_head', 'wp_generator'); // remove generator meta tag


/**
 * Enable TinyMCE paste as text by default
 */
function ks_tinymce_paste_as_text($init) {
    $init['paste_as_text'] = true;
    
    return $init;
}
add_filter('tiny_mce_before_init', 'ks_tinymce_paste_as_text');


/**
 * Customize ACF WYSIWYG toolbars
 */
function ks_acf_toolbars( $toolbars ) {
    // Add Minimal toolbar
	$toolbars['Minimal'] = array();
	$toolbars['Minimal'][1] = array('bold' , 'italic', 'link');
    
	return $toolbars;
}
add_filter('acf/fields/wysiwyg/toolbars' , 'ks_acf_toolbars');


/**
 * Disable autoembed for ACF WYSIWYG fields (and add option to re-enable)
 */
function ks_acf_wysiwyg_disable_auto_embed($value, $post_id, $field) {
    if(!empty($GLOBALS['wp_embed']) && !$field['enable_autoembed']) {
	   remove_filter('acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8);
    }
	
	return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed', 10, 3); // disable autoembed

function ks_acf_wysiwyg_disable_auto_embed_after($value, $post_id, $field) {
    if(!empty($GLOBALS['wp_embed']) && !$field['enable_autoembed']) {
	   add_filter('acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8);
    }
	
	return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed_after', 20, 3); // re-enable autoembed after value is formatted

function ks_acf_wysiwyg_disable_auto_embed_setting($field) {
	acf_render_field_setting($field, array(
		'label'	=> 'Enable Autoembed',
		'name' => 'enable_autoembed',
		'type' => 'true_false',
        'ui' => 1
	));
}
add_action('acf/render_field_settings/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed_setting'); // add setting to enable/disable

function ks_acf_wysiwyg_disable_auto_embed_class($field) {
    if (!$field['enable_autoembed']) {
        $field['wrapper']['class'] = explode(' ', $field['wrapper']['class']);
        $field['wrapper']['class'][] = 'ks-disable-autoembed';
        $field['wrapper']['class'] = implode(' ', $field['wrapper']['class']);
    }

    return $field;
}
add_filter('acf/prepare_field/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed_class'); // add class to wrapper (so JS knows to disable the wpview TinyMCE plugin)