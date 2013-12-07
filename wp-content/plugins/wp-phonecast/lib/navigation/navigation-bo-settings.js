jQuery().ready(function(){
	var $ = jQuery;
	
	$('table#navigation-items-table tbody').sortable({
		  axis: "y",
		  stop: function( event, ui ) {
			  $('table#navigation-items-table tbody tr').each(function(index){
				  $('#position-'+ $(this).data('id')).attr('value',index+1);
				  console.log($(this).data('id'));
				  console.log($('#position-'+ $(this).data('id')));
			  });
		  }
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