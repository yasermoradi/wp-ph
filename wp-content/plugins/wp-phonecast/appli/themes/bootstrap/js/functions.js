define(['jquery','core/region-manager','core/theme-app','core/lib/storage.js','theme/js/bootstrap.min'],function($,RegionManager,App,Storage){
	
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
	
	//Automatically shows and hide Back button according to current page
	App.setAutoBackButton($('#go-back'),function(back_button_showed){
		if(back_button_showed){
			$('#refresh-button').hide();
		}else{
			$('#refresh-button').show();
		}
	}); 
	
	App.on('refresh:start',function(){
		$('#refresh-button').addClass('refreshing');
	});
	
	App.on('refresh:end',function(){
		scrollTop();
		Storage.clear('scroll-pos');
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
	
	//The menu can be dynamically refreshed, so we use "on" on parent div (which is always here):
	$('#navbar-collapse').on('click','a',function(e){
		//Close menu when we click a link inside it
		closeMenu();
	});
	
	RegionManager.on('page:leave',function(current_page,view){
		//current_page.page_type can be 'list','single','page','comments'
		if( current_page.page_type == 'list' ){
			Storage.set('scroll-pos',current_page.fragment,$('body').scrollTop());
		}
	});
	
	RegionManager.on('page:showed',function(current_page,view){
		//current_page.page_type can be 'list','single','page','comments'
		if( current_page.page_type == 'list' ){
			var pos = Storage.get('scroll-pos',current_page.fragment);
			if( pos !== null ){
				$('body').scrollTop(pos);
			}else{
				scrollTop();
			}
		}else{
			scrollTop();
		}
	});
	
});