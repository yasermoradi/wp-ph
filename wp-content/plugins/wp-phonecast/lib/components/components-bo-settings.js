var Wppc = (function ($){
		
		var wppc = {};
		
		var get_parent_form_id = function(element){
			return $(element).closest('form').attr('id');
		};
		
		wppc.ajax_update_component_options = function(element,component_type,action,params){
			var form_id = get_parent_form_id(element);
			//TODO : add nonce (via wp_localize_script...)
			var data = {
				action: 'wppc_update_component_options',
				component_type: component_type,
				wppc_action: action,
				params: params
			};
			jQuery.post(ajaxurl, data, function(response) {
				$('.ajax-target',$('#'+form_id)).html(response);
			});
		};
		
		wppc.ajax_update_component_type = function(element,component_type){
			var form_id = get_parent_form_id(element);
			//TODO : add nonce (via wp_localize_script...)
			var data = {
				action: 'wppc_update_component_type',
				component_type: component_type
			};
			jQuery.post(ajaxurl, data, function(response) {
				$('.component-options-target',$('#'+form_id)).html(response);
			});
		};
		
		return wppc;
		
})(jQuery);

jQuery().ready(function(){
	var $ = jQuery;
	
	$('a.editinline').click(function(e){
		e.preventDefault();
		var id = $(this).data('edit-id');
		$('#edit-component-wrapper-'+id).show();
		$(this).parents('tr').eq(0).hide();
	});
	
	$('tr.edit-component-wrapper a.cancel').click(function(e){
		e.preventDefault();
		var form_tr = $(this).parents('tr').eq(0);
		form_tr.hide();
		form_tr.prev('tr').show();
	});
	
	$('#add-new-component').click(function(e){
		e.preventDefault();
		$('#new-component-form').slideToggle();
	});
	
	$('#cancel-new-component').click(function(e){
		e.preventDefault();
		$('#new-component-form').slideUp();
	});
	
	$('.component-type').change(function(){
		var type = $(this).find(":selected").val();
		Wppc.ajax_update_component_type(this,type);
	});
	
});