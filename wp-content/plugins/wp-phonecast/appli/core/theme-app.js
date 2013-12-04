define(function (require) {

      "use strict";

      var _                   = require('underscore'),
          Backbone            = require('backbone'),
          RegionManager       = require("core/region-manager"),
          Config              = require('root/config'),
          App                 = require('core/app');
          
      var themeApp = {};
      
      //Event aggregator
	  var vent = _.extend({}, Backbone.Events);
	  themeApp.on = function(event,callback){
		  vent.on(event,callback);
	  };
      
	  var format_theme_event_data = function(event,data){
		  
		  var theme_event_data = {type:'', message:'', data:{}, original_data:data, original_event:event};
		  
		  if( event == 'error' ){
			  
			  theme_event_data.type = 'error';
			  
			  if( data.type == 'ajax' ){
				  theme_event_data.message = 'Remote connexion to website failed'; // + ' ('+ data.data.url +')'; 
				  // + ' ('+ data.data.textStatus + ', '+ data.data.errorThrown +')';
			  }
			  else if( data.type == 'not-found' ){
				  theme_event_data.message = data.message;
			  }
			  else{
				  theme_event_data.message = 'Error' + ' ('+ data.where +')';
			  }
			  
		  }
		  
		  return theme_event_data;
	  };
	  
	  //Proxy App events
	  App.on('all',function(event,data){
		  
		  var theme_event_data = format_theme_event_data(event,data);
		  
		  if( theme_event_data.type == 'error' ){
			  vent.trigger('error',theme_event_data);
		  }
		  
	  });
	  
      themeApp.refresh = function(cb_ok,cb_error){
    	  vent.trigger('refresh:start');
    	  App.sync(function(){
    		  	RegionManager.buildMenu(true);
    		  	App.resetDefaultRoute();
    		  	App.router.default_route();
    		  	Backbone.history.stop(); 
    		  	Backbone.history.start();
				vent.trigger('refresh:end');
				if( cb_ok ){
					cb_ok();
				}
			},function(error){
				if( cb_error ){
					cb_error(format_theme_event_data('error',error));
				}
				vent.trigger('refresh:end');
			},
			true
		);
      };
      
      themeApp.getCurrentPage = function(){
    	  return App.getCurrentPage();
      };
      
	  return themeApp;
});