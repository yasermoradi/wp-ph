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
      
      themeApp.refresh = function(){
    	  vent.trigger('refresh:start');
    	  App.sync(function(){
    		  	Backbone.history.stop(); 
    		  	Backbone.history.start();
				vent.trigger('refresh:end');
			},function(){
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