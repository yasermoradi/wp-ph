require.config({

    baseUrl: 'vendor',

    waitSeconds: 10,
    
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
		  
		  RegionManager.startWaiting();
		  
		  RegionManager.buildHead();
		  
		  App.initialize();
		  
		  App.sync(function(){
			  
			  RegionManager.buildLayout();
			  
			  RegionManager.buildMenu();
			  
			  App.router = new Router();
			  App.resetDefaultRoute();
			  
			  require(['theme/js/functions'],
					  function(){ 
				  		//Refresh at app launch : as the theme is now loaded, use theme-app :
				  		require(['core/theme-app'],function(ThemeApp){
				  			ThemeApp.refresh();
				  		});
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
		  false //true to force refresh local storage at each app launch.
		  );
	  });
	    
	});
	
});



