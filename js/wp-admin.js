/* Disable the wpview TinyMCE plugin for ACF WYSIWYG fields (unless the enabled in settings) */

acf.add_filter('wysiwyg_tinymce_settings', function(mceInit, id, $field){
    if ($field.hasClass('ks-disable-autoembed')) {
        var plugins = mceInit['plugins'].split(',');
        var wpviewIndex = plugins.indexOf('wpview');
        
        if (wpviewIndex > -1) {
            plugins.splice(wpviewIndex, 1);
        }
        mceInit['plugins'] = plugins.join(',');
    }
    
	return mceInit;		
});