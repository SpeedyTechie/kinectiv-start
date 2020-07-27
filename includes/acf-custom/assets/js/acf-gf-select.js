(function($, undefined){
	
	var Field = acf.models.SelectField.extend({
		type: 'gf_select',	
	});
	
	acf.registerFieldType( Field );
	
})(jQuery);