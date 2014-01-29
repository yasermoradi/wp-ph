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
	
	require(['jquery', 'core/app', 'core/router', 'core/region-manager', 'core/phonegap-utils'], 
			function ($, App, Router, RegionManager, PhoneGap) {
	    
			var launch = function() { 
		  
				RegionManager.startWaiting();
				  
				RegionManager.buildHead();
		  
				App.initialize();
		  
				RegionManager.buildLayout();

				RegionManager.buildMenu();
		  
				App.router = new Router();		 
		  
				require(['theme/js/functions'],
						function(){ 
							App.sync(
								function(){
		  
									App.resetDefaultRoute();
				  
									Backbone.history.start();
				  
									//Refresh at app launch : as the theme is now loaded, use theme-app :
									require(['core/theme-app'],function(ThemeApp){
										ThemeApp.refresh();
									});
								  
									RegionManager.stopWaiting();
									PhoneGap.hideSplashScreen();
								  
								},
								function(){
									Backbone.history.start();
									console.log("Sync error");
									RegionManager.stopWaiting();
									PhoneGap.hideSplashScreen();
								},
								false //true to force refresh local storage at each app launch.
							);
					
						},
						function(error){ 
							console.log('Require theme/js/functions.js error', error); 
						}
				);  
		  
			};
	  
			if( PhoneGap.isLoaded() ){
				// Listen for the deviceready event
				document.addEventListener('deviceready', launch, false);
			}else{
				$(document).ready(launch);
			}
	    
	});
	
});