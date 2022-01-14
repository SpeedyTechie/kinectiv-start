(function($) {

    /* Disable the wpview TinyMCE plugin for ACF WYSIWYG fields (unless enabled in settings) */

    acf.addFilter('wysiwyg_tinymce_settings', function(mceInit, id, field){
        if (field.$el.hasClass('ks-disable-autoembed')) {
            var plugins = mceInit['plugins'].split(',');
            var wpviewIndex = plugins.indexOf('wpview');

            if (wpviewIndex > -1) {
                plugins.splice(wpviewIndex, 1);
            }
            mceInit['plugins'] = plugins.join(',');
        }

        return mceInit;		
    });



    /* Update TinyMCE settings for ACF WYSIWYG fields based on selected toolbar */

    acf.addFilter('wysiwyg_tinymce_settings', function(mceInit, id, field){
        if (field.data.toolbar in wpVars.wysiwygConfigs) {
            var config = wpVars.wysiwygConfigs[field.data.toolbar];
            var mediaAllowed = field.$el.find('.wp-media-buttons').length > 0;

            // update block_formats setting
            if ('formats' in config) {
                mceInit['block_formats'] = config['formats'];
            }

            // update valid_elements setting
            if (mediaAllowed && 'elements_with_media' in config) {
                mceInit['valid_elements'] = config['elements_with_media'];
            } else if ('elements' in config) {
                mceInit['valid_elements'] = config['elements'];
            }

            // update valid_styles setting
            if (mediaAllowed && 'styles_with_media' in config) {
                mceInit['valid_styles'] = config['styles_with_media'];
            } else if ('styles' in config) {
                mceInit['valid_styles'] = config['styles'];
            }
        }

        return mceInit;		
    });
    
    
    
    /* Update character count for text and textarea fields with a character limit */
    
    acf.field.extend({
		type: 'text',
		events: {
			'input input': 'onChangeValue',
			'change input': 'onChangeValue'
		},
		onChangeValue: function(e){
            var countContainer = e.$el.closest('.acf-input').find('.ks-char-count');
            
            if (countContainer.length != 0 && e.$el[0].hasAttribute('maxlength')) {
                var max = e.$el.attr('maxlength');
                var cur = e.$el.val().length;
                
                countContainer.find('.ks-char-count__current').text(cur);
            }
		}
	});
    
    acf.field.extend({
		type: 'textarea',
		events: {
			'input textarea': 'onChangeValue',
			'change textarea': 'onChangeValue'
		},
		onChangeValue: function(e){
            var countContainer = e.$el.closest('.acf-input').find('.ks-char-count');
            
            if (countContainer.length != 0 && e.$el[0].hasAttribute('maxlength')) {
                var max = e.$el.attr('maxlength');
                var cur = e.$el.val().length;
                
                countContainer.find('.ks-char-count__current').text(cur);
            }
		}
	});
    
})(jQuery);
