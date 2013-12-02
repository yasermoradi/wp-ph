define(['jquery','core/region-manager','core/app','template/js/snap'],function($,RegionManager,App){
	
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
		var span = $('span',this);
		span.addClass('refreshing');
		App.sync(function(){
				App.router.navigate('#archive-1', {trigger: true});
				span.removeClass('refreshing');
			},function(){
			
			},
			true
		);
	});
	
	RegionManager.on('page-showed',function(router_data,view){
		if( router_data.route == 'single' ){
			snapper.close();
		}else if( router_data.route == 'archive' ){
			
		}
	});
	
	return true;
});