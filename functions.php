<?php
/**
 * Set up theme defaults and register support for various WordPress features
 */
if (!function_exists('ks_setup')) {
	function ks_setup() {
		// Let WordPress manage the document title
		add_theme_support('title-tag');

		// Switch default core markup for search form, comment form, and comments to output valid HTML5
		add_theme_support('html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
            'style',
            'script'
		));
	}
}
add_action('after_setup_theme', 'ks_setup');


/**
 * Set the content width in pixels
 */
function ks_content_width() {
	$GLOBALS['content_width'] = apply_filters('ks_content_width', 640);
}
add_action('after_setup_theme', 'ks_content_width', 0);


/**
 * Enqueue scripts and styles
 */
function kinectiv_start_scripts() {
	wp_enqueue_style('kinectiv-start-style', get_stylesheet_directory_uri() . '/style.min.css', array(), '0.1.0');
//	wp_enqueue_style('kinectiv-start-vendor-style', get_stylesheet_directory_uri() . '/css/vendor.min.css', array(), '1.0.0');
    
    wp_deregister_script('wp-embed');
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-3.6.0.min.js', array(), null, true);
	wp_enqueue_script('kinectiv-start-script', get_template_directory_uri() . '/js/script.min.js', array('jquery'), '0.1.0', true);
}
add_action('wp_enqueue_scripts', 'kinectiv_start_scripts');

function ks_admin_scripts() {
    wp_enqueue_style('ks-admin-css', get_stylesheet_directory_uri() . '/css/wp-admin.css', array(), '1.0.0');
    
	wp_enqueue_script('ks-admin-js', get_template_directory_uri() . '/js/wp-admin.js', array(), '1.0.0', true);

    wp_localize_script('ks-admin-js', 'wpVars', array(
        'wysiwygConfigs' => ks_wysiwyg_configs()
    ));
}
add_action('admin_enqueue_scripts', 'ks_admin_scripts');


/**
 * Add ACF options page
 */
if (function_exists('acf_add_options_page')) {
    $options_page = acf_add_options_page(array(
		'page_title' 	=> 'Site Options',
		'capability'	=> 'edit_theme_options'
	));
    
    acf_add_options_sub_page(array(
        'page_title' => 'General Info',
        'parent_slug' 	=> $options_page['menu_slug']
    ));
    acf_add_options_sub_page(array(
        'page_title' => 'Header & Footer',
        'parent_slug' 	=> $options_page['menu_slug']
    ));
    acf_add_options_sub_page(array(
        'page_title' => '404 Page',
        'parent_slug' 	=> $options_page['menu_slug']
    ));
    acf_add_options_sub_page(array(
        'page_title' => 'Site Configuration',
        'parent_slug' 	=> $options_page['menu_slug']
    ));
}


/**
 * Purge Kinsta Cache when ACF options page is updated
 */
function ks_save_options_page($post_id) {
    // check if this is an options page
    if ($post_id == 'options') {
        // check if this site is hosted on a Kinsta production environment with caching
        if (wp_get_environment_type() == 'production' && class_exists('Kinsta\Cache')) {
            wp_remote_get('https://localhost/kinsta-clear-cache-all', [
               'sslverify' => false, 
               'timeout'   => 5
            ]); // purge the cache
        }
    }
}
add_action('acf/save_post', 'ks_save_options_page');


/**
 * Remove unnecessary panels/controls from WP Customizer
 */
function ks_customize_register($wp_customize) {
    $wp_customize->remove_control('site_icon'); // remove site icon control
    $wp_customize->remove_panel('nav_menus'); // remove menus panel
}
add_action('customize_register', 'ks_customize_register', 20);


/**
 * Disable WP Theme Editor
 */	
define('DISALLOW_FILE_EDIT', true);


/**
 * Add site icon
 */
function ks_favicon() {
  echo '<link rel="icon" type="image/x-icon" href="' . get_stylesheet_directory_uri() . '/images/favicon.ico" />';
}
add_action('wp_head', 'ks_favicon');
add_action('admin_head', 'ks_favicon');


/**
 * Gravity Forms - hide "Add Form" WYSIWYG button
 */
add_filter('gform_display_add_form_button', '__return_false');


/**
 * Gravity Forms - customize back-end confirmation WYSIWYG editor
 */
function ks_gform_confirmation_wp_editor_settings($settings, $editor_id) {
    $gf_subview = GFSettings::get_subview();
    
    if ($editor_id == '_gform_setting_message' && $gf_subview == 'confirmation') {
        $settings['quicktags'] = false; // disable "text" tab
    }
    
    return $settings;
}
add_filter('wp_editor_settings', 'ks_gform_confirmation_wp_editor_settings', 10, 2); // customize wp_editor settings

function ks_gform_confirmation_tiny_mce_before_init($mce_init, $editor_id) {
    $gf_subview = GFSettings::get_subview();

    if ($editor_id == '_gform_setting_message' && $gf_subview == 'confirmation') {
        $mce_init = ks_configure_tinymce($mce_init, 'Standard', false);
    }

    return $mce_init;
}
add_filter('tiny_mce_before_init', 'ks_gform_confirmation_tiny_mce_before_init', 10, 2); // customize toolbar


/**
 * Gravity Forms - customize confirmation
 */
function ks_gform_pre_confirmation_save($confirmation, $form) {
    $confirmation['disableAutoformat'] = true; // disable nl2br
    $confirmation['message'] = wpautop($confirmation['message']); // auto add <p> tags
    
    return $confirmation;
}
add_filter('gform_pre_confirmation_save', 'ks_gform_pre_confirmation_save', 10, 2);


/**
 * Gravity Forms - disable theme CSS
 */
add_filter('gform_disable_form_theme_css', '__return_true');


/**
 * Disable comments
 */
function ks_disable_comments_post_types_support() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if (post_type_supports($post_type, 'comments')) {
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

function ks_disable_comments_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}
add_action('admin_bar_menu', 'ks_disable_comments_admin_bar', 999); // remove comments links from admin bar

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
 * Disable search
 */
function ks_disable_search($query, $error = true) {
    if (is_search() && !is_admin()) {
        $query->is_search = false;
        $query->query_vars['s'] = false;
        $query->query['s'] = false;

        if ($error == true) {
            $query->is_404 = true;
        }
    }
}

add_action('parse_query', 'ks_disable_search');
add_filter('get_search_form', '__return_null');


/**
 * Customize order of admin menu items
 */
function ks_admin_menu_order($menu_order) {
    // list of items keyed by the item they should be located after
    $relocate_after = array(
        'separator1' => array('edit.php?post_type=page'),
        'separator2' => array('acf-options-general-info', 'separator-last')
    );
    
    // create a list of all menu items that will be relocated
    $to_relocate = array();
    foreach ($relocate_after as $set) {
        $to_relocate = array_merge($to_relocate, $set);
    }
    
    // build new array and with items relocated
    $custom_order = array();
    foreach ($menu_order as $item) {
        // only process this item if it will not be relocated
        if (!in_array($item, $to_relocate)) {
            $custom_order[] = $item; // add this item to the array
            
            // if there are items to be located after this item, add them to the array also
            if (array_key_exists($item, $relocate_after)) {
                $custom_order = array_merge($custom_order, $relocate_after[$item]);
            }
        }
    }
    
    return $custom_order;
}
add_filter('custom_menu_order', '__return_true'); // enable menu_order filter
add_filter('menu_order', 'ks_admin_menu_order'); // filter menu order


/**
 * Hide Posts from admin
 */
function ks_disable_posts_admin_menu() {
    remove_menu_page('edit.php');
}
add_action('admin_menu', 'ks_disable_posts_admin_menu'); // remove posts link from admin menu

function ks_disable_posts_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('new-post');
}
add_action('wp_before_admin_bar_render', 'ks_disable_posts_admin_bar'); // remove new post link from admin bar


/**
 * Disable archive pages for Posts
 */
function ks_disable_post_archives($query){
    if ((!is_front_page() && is_home()) || is_category() || is_tag() || is_author() || is_date()) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}
add_action('parse_query', 'ks_disable_post_archives');


/**
 * Unregister default taxonomies for Posts
 */
function ks_unregister_default_taxonomies() {
    unregister_taxonomy_for_object_type('category', 'post'); // unregister categories for posts
    unregister_taxonomy_for_object_type('post_tag', 'post'); // unregister tags for posts
}
add_action('init', 'ks_unregister_default_taxonomies');


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
remove_action('wp_head', 'rest_output_link_wp_head'); // remove REST links


/**
 * Dequeue WP Block Editor styles
 */
function ks_dequeue_block_styles(){
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'ks_dequeue_block_styles', 100);


/**
 * Disable WordPress emojis
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

add_filter('emoji_svg_url', '__return_false', 10, 2);

function ks_tinymce_disable_emojis($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}
add_filter('tiny_mce_plugins', 'ks_tinymce_disable_emojis'); // disable wpemoji TinyMCE plugin


/**
 * Custom WYSIWYG configurations
 */
function ks_wysiwyg_configs() {
    $configs = array();

    // add config: Full
    $configs['Full'] = array(
        'toolbars' => array(
            1 => array('formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv'),
			2 => array('strikethrough', 'hr', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help')
        )
    );

    // add config: Standard
    $configs['Standard'] = array(
        'toolbars' => array(
            1 => array('formatselect', 'bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'bullist', 'numlist', 'link', 'hr', 'undo', 'redo', 'removeformat', 'fullscreen')
        ),
        'formats' => array(
            'p' => 'Text',
            'h2' => 'Heading 2',
            'h3' => 'Heading 3',
            'h4' => 'Heading 4',
            'h5' => 'Heading 5'
        ),
        'elements' => array(
            'p' => array(),
            'h2' => array(),
            'h3' => array(),
            'h4' => array(),
            'h5' => array(),
            'strong' => array(
                'synonyms' => array('b')
            ),
            'em' => array(
                'synonyms' => array('i')
            ),
            'span' => array(
                'styles' => array('text-decoration')
            ),
            'del' => array(
                'synonyms' => array('s')
            ),
            'blockquote' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'a' => array(
                'attributes' => array('href', 'target', 'rel')
            ),
            'hr' => array(),
            'br' => array()
        ),
        'media_elements' => array(
            'img' => array(
                'attributes' => array('src', 'alt', 'width', 'height', 'class', 'title', 'data-mce-src')
            ),
            'div' => array(
                'attributes' => array('!class<mceTemp')
            ),
            'dl' => array(
                'attributes' => array('id', '!class', 'data-mce-style'),
                'styles' => array('width')
            ),
            'dt' => array(
                'attributes' => array('!class<wp-caption-dt')
            ),
            'dd' => array(
                'attributes' => array('!class<wp-caption-dd')
            )
        )
    );

    // add config: Standard (No Headings)
    $configs['Standard (No Headings)'] = array(
        'toolbars' => array(
            1 => array('bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'bullist', 'numlist', 'link', 'hr', 'undo', 'redo', 'removeformat', 'fullscreen')
        ),
        'elements' => array(
            'p' => array(),
            'strong' => array(
                'synonyms' => array('b')
            ),
            'em' => array(
                'synonyms' => array('i')
            ),
            'span' => array(
                'styles' => array('text-decoration')
            ),
            'del' => array(
                'synonyms' => array('s')
            ),
            'blockquote' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'a' => array(
                'attributes' => array('href', 'target', 'rel')
            ),
            'hr' => array(),
            'br' => array()
        ),
        'media_elements' => array(
            'img' => array(
                'attributes' => array('src', 'alt', 'width', 'height', 'class', 'title', 'data-mce-src')
            ),
            'div' => array(
                'attributes' => array('!class<mceTemp')
            ),
            'dl' => array(
                'attributes' => array('id', '!class', 'data-mce-style'),
                'styles' => array('width')
            ),
            'dt' => array(
                'attributes' => array('!class<wp-caption-dt')
            ),
            'dd' => array(
                'attributes' => array('!class<wp-caption-dd')
            )
        )
    );

    // add config: Minimal
    $configs['Minimal'] = array(
        'toolbars' => array(
            1 => array('bold' , 'italic', 'link')
        ),
        'elements' => array(
            'p' => array(),
            'strong' => array(
                'synonyms' => array('b')
            ),
            'em' => array(
                'synonyms' => array('i')
            ),
            'a' => array(
                'attributes' => array('href', 'target', 'rel')
            ),
            'br' => array()
        )
    );
    
    // add config: Minimal (No Links)
    $configs['Minimal (No Links)'] = array(
        'toolbars' => array(
            1 => array('bold' , 'italic')
        ),
        'elements' => array(
            'p' => array(),
            'strong' => array(
                'synonyms' => array('b')
            ),
            'em' => array(
                'synonyms' => array('i')
            ),
            'br' => array()
        )
    );


    // process configs
    $processed_configs = array();
    foreach ($configs as $config_name => $config_data) {
        $processed_data = array();

        // add (unsanitzed) name to data
        $processed_data['label'] = $config_name;

        // add toolbars to data
        $processed_data['toolbars'] = $config_data['toolbars'];

        // generate block_formats string
        if (isset($config_data['formats'])) {
            $formats_list = array();
            foreach ($config_data['formats'] as $format_tag => $format_label) {
                $formats_list[] = $format_label . '=' . $format_tag;
            }

            $processed_data['formats'] = implode(';', $formats_list);
        }

        // generate valid_styles array
        if (isset($config_data['elements']) || isset($config_data['global_styles'])) {
            $styles_list = array();
            
            if (isset($config_data['global_styles'])) {
                $styles_list['*'] = implode(',', $config_data['global_styles']);
            }
            if (isset($config_data['elements'])) {
                foreach ($config_data['elements'] as $element_tag => $element_options) {
                    if (isset($element_options['styles'])) {
                        $styles_list[$element_tag] = implode(',', $element_options['styles']);
                    }
                }
            }

            if ($styles_list) {
                $processed_data['styles'] = $styles_list;
            }
        }

        // generate valid_styles array for when media is allowed
        if (isset($config_data['media_elements'])) {
            $styles_list = array();

            if (isset($config_data['elements'])) {
                $config_data['media_elements'] = array_merge($config_data['elements'], $config_data['media_elements']);
            }

            if (isset($config_data['global_styles'])) {
                $styles_list['*'] = implode(',', $config_data['global_styles']);
            }
            foreach ($config_data['media_elements'] as $element_tag => $element_options) {
                if (isset($element_options['styles'])) {
                    $styles_list[$element_tag] = implode(',', $element_options['styles']);
                }
            }

            if ($styles_list) {
                $processed_data['styles_with_media'] = $styles_list;
            }
        }

        // generate valid_elements string
        if (isset($config_data['elements'])) {
            $elements_list = array();

            if (isset($processed_data['styles'])) {
                $elements_list[] = '@[style]'; // ensure that the style attribute is allowed if there are valid styles specified (this has to be first in the list)
            }
            foreach ($config_data['elements'] as $element_tag => $element_options) {
                $element_attribute_string = '';
                if (isset($element_options['attributes'])) {
                    $element_attribute_string = '[' . implode('|', $element_options['attributes']) . ']';
                }

                if (isset($element_options['synonyms'])) {
                    foreach ($element_options['synonyms'] as $synonym) {
                        $elements_list[] = $element_tag . '/' . $synonym . $element_attribute_string;
                    }
                } else {
                    $elements_list[] = $element_tag . $element_attribute_string;
                }
            }

            $processed_data['elements'] = implode(',', $elements_list);
        }

        // generate valid_elements string for when media is allowed
        if (isset($config_data['media_elements'])) {
            $elements_list = array();

            if (isset($config_data['elements'])) {
                $config_data['media_elements'] = array_merge($config_data['elements'], $config_data['media_elements']);
            }

            if (isset($processed_data['styles_with_media']) || isset($processed_data['styles'])) {
                $elements_list[] = '@[style]'; // ensure that the style attribute is allowed if there are valid styles specified (this has to be first in the list)
            }
            foreach ($config_data['media_elements'] as $element_tag => $element_options) {
                $element_attribute_string = '';
                if (isset($element_options['attributes'])) {
                    $element_attribute_string = '[' . implode('|', $element_options['attributes']) . ']';
                }

                if (isset($element_options['synonyms'])) {
                    foreach ($element_options['synonyms'] as $synonym) {
                        $elements_list[] = $element_tag . '/' . $synonym . $element_attribute_string;
                    }
                } else {
                    $elements_list[] = $element_tag . $element_attribute_string;
                }
            }

            $processed_data['elements_with_media'] = implode(',', $elements_list);
        }

        // add processed data to array
        $processed_configs[str_replace( '-', '_', sanitize_title($config_name))] = $processed_data;
    }

    return $processed_configs;
}

function ks_configure_tinymce($mce_init, $config_name, $media_allowed) {
    $wysiwyg_configs = ks_wysiwyg_configs();

    $config_name = str_replace( '-', '_', sanitize_title($config_name));

    if (isset($wysiwyg_configs[$config_name])) {
        $config = $wysiwyg_configs[$config_name];

        // update toolbars
        $mce_init['toolbar1'] = isset($config['toolbars'][1]) ? implode(',', $config['toolbars'][1]) : '';
        $mce_init['toolbar2'] = isset($config['toolbars'][2]) ? implode(',', $config['toolbars'][2]) : '';
        $mce_init['toolbar3'] = isset($config['toolbars'][3]) ? implode(',', $config['toolbars'][3]) : '';
        $mce_init['toolbar4'] = isset($config['toolbars'][4]) ? implode(',', $config['toolbars'][4]) : '';

        // update block_formats setting
        if (isset($config['formats'])) {
            $mce_init['block_formats'] = $config['formats'];
        }

        // update valid_elements setting
        if ($media_allowed && isset($config['elements_with_media'])) {
            $mce_init['valid_elements'] = $config['elements_with_media'];
        } elseif (isset($config['elements'])) {
            $mce_init['valid_elements'] = $config['elements'];
        }

        // update valid_styles setting
        if ($media_allowed && isset($config['styles_with_media'])) {
            $mce_init['valid_styles'] = $config['styles_with_media'];
        } elseif (isset($config['styles'])) {
            $mce_init['valid_styles'] = $config['styles'];
        }
    }

    return $mce_init;
}


/**
 * Add custom WYSIWYG toolbars to ACF
 */
function ks_acf_toolbars($toolbars) {
    $wysiwyg_configs = ks_wysiwyg_configs();

    if ($wysiwyg_configs) {
        $toolbars = array();
        foreach ($wysiwyg_configs as $config) {
            $toolbars[$config['label']] = $config['toolbars'];
        }
    }
    
	return $toolbars;
}
add_filter('acf/fields/wysiwyg/toolbars' , 'ks_acf_toolbars'); // add toolbars


/**
 * Disable autoembed for ACF WYSIWYG fields (and add option to re-enable)
 */
function ks_acf_wysiwyg_disable_auto_embed($value, $post_id, $field) {
    if (!empty($GLOBALS['wp_embed']) && !$field['enable_autoembed']) {
	   remove_filter('acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8);
    }
	
	return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed', 10, 3); // disable autoembed

function ks_acf_wysiwyg_disable_auto_embed_after($value, $post_id, $field) {
    if (!empty($GLOBALS['wp_embed']) && !$field['enable_autoembed']) {
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
add_action('acf/render_field_general_settings/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed_setting'); // add setting to enable/disable

function ks_acf_wysiwyg_disable_auto_embed_class($field) {
    if (!$field['enable_autoembed']) {
        $field['wrapper']['class'] = explode(' ', $field['wrapper']['class']);
        $field['wrapper']['class'][] = 'ks-disable-autoembed';
        $field['wrapper']['class'] = implode(' ', $field['wrapper']['class']);
    }

    return $field;
}
add_filter('acf/prepare_field/type=wysiwyg', 'ks_acf_wysiwyg_disable_auto_embed_class'); // add class to wrapper (so JS knows to disable the wpview TinyMCE plugin)


/**
 * Add option to post object, page link, and relationship fields to allow filtering by page template
 */
function ks_acf_template_filter_setting($field) {
    acf_render_field_setting($field, array(
        'label'	=> 'Filter by Page Template',
        'name' => 'filter_template',
        'type' => 'select',
        'choices' => array_flip(get_page_templates()),
        'multiple' => 1,
        'ui' => 1,
        'allow_null' => 1,
        'placeholder' => 'All page templates'
    ));
}
add_action('acf/render_field_general_settings/type=post_object', 'ks_acf_template_filter_setting'); // add setting to post object fields
add_action('acf/render_field_general_settings/type=page_link', 'ks_acf_template_filter_setting'); // add setting to page_link fields
add_action('acf/render_field_general_settings/type=relationship', 'ks_acf_template_filter_setting'); // add setting to relationship fields

function ks_acf_template_filter_query($args, $field, $post_id) {
    if ($field['filter_template']) {
        $args['meta_query'] = array(
            array(
                'key' => '_wp_page_template',
                'value' => $field['filter_template'],
                'compare' => 'IN'
            )
        );
    }
	
    return $args;
}
add_filter('acf/fields/post_object/query', 'ks_acf_template_filter_query', 10, 3); // update query for post object fields to include template filter
add_filter('acf/fields/page_link/query', 'ks_acf_template_filter_query', 10, 3); // update query for page link fields to include template filter
add_filter('acf/fields/relationship/query', 'ks_acf_template_filter_query', 10, 3); // update query for relationship fields to include template filter


/**
 * Add maximum/minimum selection options to field types with multi-select functionality
 */
function ks_acf_multi_min_max_settings($field) {
    if ($field['type'] == 'checkbox') {
        // render settings for checkbox fields (always show settings)
        acf_render_field_setting($field, array(
            'label'	=> 'Minimum Selection',
            'name' => 'multi_min',
            'type' => 'number'
        ));
        acf_render_field_setting($field, array(
            'label'	=> 'Maximum Selection',
            'name' => 'multi_max',
            'type' => 'number'
        ));
    } elseif ($field['type'] == 'taxonomy') {
        // render settings for taxonomy fields (hide/show settings based on whether selected appearance allows multiple values)
        acf_render_field_setting($field, array(
            'label'	=> 'Minimum Selection',
            'name' => 'multi_min',
            'type' => 'number',
            'conditions' => array(
                array(
                    array(
                        'field' => 'field_type',
                        'operator' => '==',
                        'value' => 'checkbox'
                    )
                ),
                array(
                    array(
                        'field' => 'field_type',
                        'operator' => '==',
                        'value' => 'multi_select'
                    )
                ),
            )
        ));
        acf_render_field_setting($field, array(
            'label'	=> 'Maximum Selection',
            'name' => 'multi_max',
            'type' => 'number',
            'conditions' => array(
                array(
                    array(
                        'field' => 'field_type',
                        'operator' => '==',
                        'value' => 'checkbox'
                    )
                ),
                array(
                    array(
                        'field' => 'field_type',
                        'operator' => '==',
                        'value' => 'multi_select'
                    )
                ),
            )
        ));
    } else {
        // render settings for other field types (hide/show settings based on whether multi-select is enabled)
        acf_render_field_setting($field, array(
            'label'	=> 'Minimum Selection',
            'name' => 'multi_min',
            'type' => 'number',
            'conditions' => array(
                'field' => 'multiple',
                'operator' => '==',
                'value' => 1
            )
        ));
        acf_render_field_setting($field, array(
            'label'	=> 'Maximum Selection',
            'name' => 'multi_max',
            'type' => 'number',
            'conditions' => array(
                'field' => 'multiple',
                'operator' => '==',
                'value' => 1
            )
        ));
    }
}
add_action('acf/render_field_general_settings/type=checkbox', 'ks_acf_multi_min_max_settings'); // add min/max settings to checkbox fields
add_action('acf/render_field_general_settings/type=select', 'ks_acf_multi_min_max_settings'); // add min/max settings to select fields
add_action('acf/render_field_general_settings/type=post_object', 'ks_acf_multi_min_max_settings'); // add min/max settings to post object fields
add_action('acf/render_field_general_settings/type=page_link', 'ks_acf_multi_min_max_settings'); // add min/max settings to page link fields
add_action('acf/render_field_general_settings/type=taxonomy', 'ks_acf_multi_min_max_settings'); // add min/max settings to taxonomy fields
add_action('acf/render_field_general_settings/type=user', 'ks_acf_multi_min_max_settings'); // add min/max settings to user fields
add_action('acf/render_field_general_settings/type=gf_select', 'ks_acf_multi_min_max_settings'); // add min/max settings to Gravity Form fields

function ks_acf_multi_min_max_validation($valid, $value, $field, $input) {
    if ($valid) {
        if ($field['multi_min']) {
            if (!$value) $value = array(); // if value is empty, set it to an empty array so count() returns 0
            
            // if value doesn't meet minimum, return validation error message
            if (count($value) < $field['multi_min']) {
                $valid = 'Please select a minimum of ' . $field['multi_min'];
                if ($field['multi_min'] == 1) {
                    $valid .= ' value.';
                } else {
                    $valid .= ' values.';
                }
            }
        }
        if ($field['multi_max']) {
            if (!$value) $value = array(); // if value is empty, set it to an empty array so count() returns 0
            
            // if value exceeds maximum, return validation error message
            if (count($value) > $field['multi_max']) {
                $valid = 'Please select a maximum of ' . $field['multi_max'];
                if ($field['multi_max'] == 1) {
                    $valid .= ' value.';
                } else {
                    $valid .= ' values.';
                }
            }
        }
    }
    
    return $valid;
}
add_action('acf/validate_value/type=checkbox', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for checkbox fields
add_action('acf/validate_value/type=select', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for select fields
add_action('acf/validate_value/type=post_object', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for post object fields
add_action('acf/validate_value/type=page_link', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for page link fields
add_action('acf/validate_value/type=taxonomy', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for taxonomy fields
add_action('acf/validate_value/type=user', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for user fields
add_action('acf/validate_value/type=gf_select', 'ks_acf_multi_min_max_validation', 10, 4); // validate min/max settings for Gravity Form fields


/**
 * Add ACF WYSIWYG height setting
 */
function ks_acf_wysiwyg_field_height_setting($field) {
	acf_render_field_setting($field, array(
		'label'	=> 'Height',
		'name' => 'editor_height',
		'type' => 'number',
        'placeholder' => '300',
        'append' => 'px'
	));
}
add_action('acf/render_field_presentation_settings/type=wysiwyg', 'ks_acf_wysiwyg_field_height_setting'); // add setting to adjust field height

function ks_acf_wysiwyg_field_height_script($field) {
    if ($field['editor_height']) { ?>
        <style type="text/css">
            textarea[name="<?php echo $field['name']; ?>"] {
                height: <?php echo $field['editor_height']; ?>px !important;
            }
        </style>
        <script type="text/javascript">
            jQuery(function() {
                jQuery('textarea[name="<?php echo $field['name']; ?>"]').css('height', '<?php echo $field['editor_height']; ?>px');
                jQuery('iframe#' + jQuery('textarea[name="<?php echo $field['name']; ?>"]').attr('id') + '_ifr').css('height', '<?php echo $field['editor_height']; ?>px');
            });
        </script>
    <?php }

    return $field;
}
add_filter('acf/prepare_field/type=wysiwyg', 'ks_acf_wysiwyg_field_height_script'); // add js to adjust field height


/**
 * Add character counter for ACF text and textarea field types
 */
function ks_acf_character_limit_markup($field) {
    $class_list = explode(' ', $field['wrapper']['class']);
    
    if (!in_array('no-char-count', $class_list) && $field['maxlength']) { ?>
        <p class="ks-char-count"><span class="ks-char-count__current"><?php echo strlen(iconv('utf-8', 'utf-16le', str_replace(PHP_EOL, ' ', $field['value']))) / 2; ?></span>/<?php echo $field['maxlength']; ?> characters</p>
    <?php }
}
add_action('acf/render_field/type=text', 'ks_acf_character_limit_markup'); // add counter to text fields
add_action('acf/render_field/type=textarea', 'ks_acf_character_limit_markup'); // add counter to textarea fields


/**
 * Add custom ACF field types
 */
function ks_include_custom_acf_field_types() {
    include_once(get_template_directory() . '/includes/acf-custom/fields/acf-gf-select.php'); // add Gravity Form field type
}
add_action('acf/include_field_types', 'ks_include_custom_acf_field_types');
