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
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.5.1.min.js', array(), null, false);
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
        $query->query_vars[s] = false;
        $query->query[s] = false;

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
    if((!is_front_page() && is_home()) || is_category() || is_tag() || is_author() || is_date()) {
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


/**
 * Dequeue WP Block Editor styles
 */
function ks_dequeue_block_styles(){
    wp_dequeue_style('wp-block-library');
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
function ks_acf_toolbars($toolbars) {
    // Add Minimal toolbar
	$toolbars['Minimal'] = array();
	$toolbars['Minimal'][1] = array('bold' , 'italic', 'link');
    
	return $toolbars;
}
add_filter('acf/fields/wysiwyg/toolbars' , 'ks_acf_toolbars'); // add toolbars

function ks_acf_wysiwyg_strip_tags($value, $post_id, $field) {
    if ($field['enable_strip_tags']) {
        if ($field['toolbar'] == 'basic') {
            $value = strip_tags($value, '<p><strong><em><span><a><br><blockquote><del><ul><ol><li>');
        } elseif ($field['toolbar'] == 'minimal') {
            $value = strip_tags($value, '<p><strong><em><a><br>');
        }
    }
    
    return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'ks_acf_wysiwyg_strip_tags', 10, 3); // strip tags from WYSIWYG content based on toolbar

function ks_acf_wysiwyg_strip_tags_setting($field) {
	acf_render_field_setting($field, array(
		'label'	=> 'Strip Tags Based on Toolbar',
        'instructions' => 'HTML tags not supported by the selected toolbar will be stripped',
		'name' => 'enable_strip_tags',
		'type' => 'true_false',
        'ui' => 1
	));
}
add_action('acf/render_field_settings/type=wysiwyg', 'ks_acf_wysiwyg_strip_tags_setting'); // add setting to enable/disable


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
add_action('acf/render_field_settings/type=post_object', 'ks_acf_template_filter_setting'); // add setting to post object fields
add_action('acf/render_field_settings/type=page_link', 'ks_acf_template_filter_setting'); // add setting to page_link fields
add_action('acf/render_field_settings/type=relationship', 'ks_acf_template_filter_setting'); // add setting to relationship fields

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
add_action('acf/render_field_settings/type=checkbox', 'ks_acf_multi_min_max_settings'); // add min/max settings to checkbox fields
add_action('acf/render_field_settings/type=select', 'ks_acf_multi_min_max_settings'); // add min/max settings to select fields
add_action('acf/render_field_settings/type=post_object', 'ks_acf_multi_min_max_settings'); // add min/max settings to post object fields
add_action('acf/render_field_settings/type=page_link', 'ks_acf_multi_min_max_settings'); // add min/max settings to page link fields
add_action('acf/render_field_settings/type=taxonomy', 'ks_acf_multi_min_max_settings'); // add min/max settings to taxonomy fields
add_action('acf/render_field_settings/type=user', 'ks_acf_multi_min_max_settings'); // add min/max settings to user fields
add_action('acf/render_field_settings/type=gf_select', 'ks_acf_multi_min_max_settings'); // add min/max settings to Gravity Form fields

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
add_action('acf/render_field_settings/type=wysiwyg', 'ks_acf_wysiwyg_field_height_setting'); // add setting to adjust field height

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
 * Add custom ACF field types
 */
function ks_include_custom_acf_field_types() {
    include_once(get_template_directory() . '/includes/acf-custom/fields/acf-gf-select.php'); // add Gravity Form field type
}
add_action('acf/include_field_types', 'ks_include_custom_acf_field_types');
