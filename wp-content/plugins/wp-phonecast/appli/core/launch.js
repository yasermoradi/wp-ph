require.config({

	urlArgs : "bust="+new Date().getTime(),
	 
    baseUrl: 'vendor',

    paths: {
        core: '../core',
        root: '..'
    },

    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        }
    }
});

require(['root/config'],function(Config){
	
	require.config({
	    paths: {
	    	theme: '../themes/'+ Config.theme
	    }
	});
	
	require(['jquery', 'core/app', 'core/router', 'core/region-manager'], 
			function ($, App, Router, RegionManager) {
	    
	  //Backbone.emulateHTTP = true;
	  //Backbone.emulateJSON = true;

	  $(document).ready(function() { 
		  
		  RegionManager.buildHead();
		  
		  RegionManager.startWaiting();
		  
		  App.initialize();
		  
		  App.sync(function(){
			  
			  RegionManager.buildLayout();
			  
			  RegionManager.buildMenu();
			  
			  App.router = new Router();
			  var first_nav_component_id = App.navigation.first().get('component_id');
			  App.router.setDefaultRoute('#component-'+ first_nav_component_id);
			  
			  require(['theme/js/functions'],
					  function(){ 
				  		Backbone.history.start();
				  		RegionManager.stopWaiting();
			  		  },
			  		  function(error){ 
			  			  console.log('Require theme/js/functions.js error', error); 
			  		  }
			  );
			  
		  },
		  function(){
			  console.log("Sync error");
		  },
		  false
		  );
	  });
	    
	});
	
});



