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
	
	App.setAutoBodyClass(true); //Adds class on <body> according to the current page
	
	App.on('refresh:start',function(){
		$('#refresh-button').addClass('refreshing');
	});
	
	App.on('refresh:end',function(){
		scrollTop();
		$('#refresh-button').removeClass('refreshing');
	});
	
	App.on('error',function(error){
		$('#feedback').addClass('error').html(error.message).slideDown();
	});
	
	$('body').click(function(e){
		$('#feedback').slideUp();
	});
	
	//Allow to click anywhere on li to go to post detail :
	$('#container').on('click','li.media',function(e){
		var navigate_to = $('a',this).attr('href');
		App.navigate(navigate_to);
	});
	
	RegionManager.on('page:showed',function(current_page,view){
		scrollTop();
		//current_page.page_type can be 'list','single','page','comments'
	});
	
	function updateHeaderEvents(){
		
		$('#refresh-button').unbind().bind('click', function(e){
			e.preventDefault();
			closeMenu();
			App.refresh(function(){
				$('#feedback').removeClass('error').html('Content updated successfully :)').slideDown();
			});
		});
	
		if( App.isRefreshing() ){
			$('#refresh-button').addClass('refreshing');
		}
	}

	RegionManager.on('header:render',function(current_page,headerView){
		updateHeaderEvents();
	});
	
});