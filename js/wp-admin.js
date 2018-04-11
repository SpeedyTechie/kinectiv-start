/* Disable the wpview TinyMCE plugin for ACF WYSIWYG fields with the class ks-no-embeds */

acf.add_filter('wysiwyg_tinymce_settings', function( mceInit, id, $field ){
    if ($field.hasClass('ks-no-embeds')) {
        let plugins = mceInit['plugins'].split(',');
        let wpviewIndex = plugins.indexOf('wpview');
        
        if (wpviewIndex > -1) {
            plugins.splice(wpviewIndex, 1);
        }
        mceInit['plugins'] = plugins.join(',');
    }
    
	return mceInit;		
});