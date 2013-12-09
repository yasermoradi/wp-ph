define(['jquery','core/region-manager','core/theme-app','theme/js/bootstrap.min'],function($,RegionManager,App){
	
	function closeMenu(){
		var navbar_toggle_button = $(".navbar-toggle").eq(0);
		if( !navbar_toggle_button.hasClass('collapsed') ){
			navbar_toggle_button.click(); 
		}
	}
	
	function scrollTop(){
		window.scrollTo(0,0);
	}
	
	$('#refresh-button').bind('click', function(e){
		e.preventDefault();
		closeMenu();
		App.refresh(function(){
			$('#feedback').removeClass('error').html('Content updated successfully :)').slideDown();
		});
	});
	
	App.on('refresh:start',function(){
		$('#refresh-button span').addClass('refreshing');
	});
	
	App.on('refresh:end',function(){
		scrollTop();
		$('#refresh-button span').removeClass('refreshing');
	});
	
	App.on('error',function(error){
		$('#feedback').addClass('error').html(error.message).slideDown();
	});
	
	$('body').click(function(e){
		$('#feedback').slideUp();
	});
	
	//The menu can be dynamically refreshed, so we use "on" on parent div (which is always here):
	$('#navbar-collapse').on('click','a',function(e){
		//Close menu when we click a link inside it
		closeMenu();
	});
	
	$('#container').on('click','li.media',function(e){
		var navigate_to = $('a',this).attr('href');
		App.navigate(navigate_to);
	});
	
	RegionManager.on('page:showed',function(current_page,view){
		scrollTop();
		if( current_page.page_type == 'single' ){
		}
		else if( current_page.page_type == 'page' ){
		}
		else if( current_page.page_type == 'archive' ){
		}
		
	});
	
});