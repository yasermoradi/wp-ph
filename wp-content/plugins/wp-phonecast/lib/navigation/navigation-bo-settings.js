jQuery().ready(function(){
	var $ = jQuery;
	
	$('a.editinline').click(function(e){
		e.preventDefault();
		var id = $(this).data('edit-id');
		$('#edit-item-wrapper-'+id).show();
		$(this).parents('tr').eq(0).hide();
	});
	
	$('tr.edit-item-wrapper a.cancel').click(function(e){
		e.preventDefault();
		var form_tr = $(this).parents('tr').eq(0);
		form_tr.hide();
		form_tr.prev('tr').show();
	});
	
	$('#add-new-item').click(function(e){
		e.preventDefault();
		$('#new-item-form').slideToggle();
	});
	
	$('#cancel-new-item').click(function(e){
		e.preventDefault();
		$('#new-item-form').slideUp();
	});
	
});