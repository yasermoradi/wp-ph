define(['jquery','core/region-manager','core/theme-app','theme/js/snap'],function($,RegionManager,App){
	
	var snapper = new Snap({
	  element: document.getElementById('content'),
	  disable: 'right,left',
	  tapToClose : false,
	  touchToDrag : false
	});
	
	$('#slide-menu-button').bind('click', function(e){
		e.preventDefault();
	    if( snapper.state().state=="left" ){
	        snapper.close();
	    } else {
	        snapper.open('left');
	    }
	});
	
	$('#refresh-button').bind('click', function(e){
		e.preventDefault();
		App.refresh();
	});
	
	App.on('refresh:start',function(){
		$('#refresh-button span').addClass('refreshing');
	});
	
	App.on('refresh:end',function(){
		$('#refresh-button span').removeClass('refreshing');
	});
	
	RegionManager.on('page:showed',function(current_page,view){
		if( current_page.page_type == 'single' ){
			snapper.close();
		}
		else if( current_page.page_type == 'page' ){
			snapper.close();
		}
		else if( current_page.page_type == 'archive' ){
			
		}
	});
	
	return true;
});