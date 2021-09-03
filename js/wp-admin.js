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
