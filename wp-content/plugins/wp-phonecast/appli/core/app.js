define(function (require) {

      "use strict";

      var $                   = require('jquery'),
      	  _                   = require('underscore'),
          Backbone            = require('backbone'),
          RegionManager       = require("core/region-manager"),
          Components          = require('core/models/components'),
          Globals             = require('core/models/globals'),
          Navigation          = require('core/models/navigation'),
          Items               = require('core/models/items'),
          Comments            = require('core/models/comments'),
          Config              = require('root/config'),
          sha256              = require('core/lib/sha256');
      
	  var app = {};
	  var initializers = [];
	  
	  //Logic to do treatments after initializers are done.
	  var after_initializers = [];
	  var wait_events = [];
	 
	  //Event aggregator
	  var vent = _.extend({}, Backbone.Events);
	  app.on = function(event,callback){
		  vent.on(event,callback);
	  };
	  
	  //App initializer
	  app.addInitializer = function(callback,wait){
	    var initializer = {
	      obj: this,
	      callback: callback,
	      wait: (wait !== undefined) && wait
	    }
	    initializers.push(initializer);
	  };
	  
	  app.addAfterInitializers = function(callback){
		    var after_initializers_callback = {
		      obj: this,
		      callback: callback,
		    }
		    after_initializers.push(after_initializers_callback);
	  };
	 
	  app.initialize = function(){
		  
		  RegionManager.startWaiting();
		  
		  _.each(initializers, function(initializer,index){
			  if( initializer.wait ){
				  wait_events.push(index);
			  }
		  });
		  
		  //console.log('Total : wait_events.length',wait_events.length);
		  
		  _.each(initializers, function(initializer,index){
			  if( initializer.wait ){
				  initializer.callback.call(initializer.obj,function(){
					  var wait_events_index = wait_events.indexOf(index);
					  if( wait_events_index > -1 ){
						  wait_events.splice(wait_events_index, 1);
					  }
					  if( wait_events.length <= 0 ){
						  _.each(after_initializers, function(after_initializers_callback){
							  after_initializers_callback.callback.call(after_initializers_callback.obj);
						  });
					  }
				  });
			  }else{
				  initializer.callback.call(initializer.obj);
			  }
		  });
		  
		  RegionManager.stopWaiting();
		  
	  };
	  
	  app.router = null;
	  
	  var currentPage = {page_type:'',component_id:'',item_id:''};
	  app.setCurrentPage = function(page_type,component_id,item_id){
		  currentPage.page_type = page_type;
		  currentPage.component_id = component_id;
		  currentPage.item_id = item_id;
	  };
	  app.getCurrentPage = function(){
		  return currentPage;
	  };
	  
	  //--------------------------------------------------------------------------
	  //App items data :
	  app.components = new Components;
	  app.navigation = new Navigation;
	  
	  //For globals, separate keys from values because localstorage on 
	  //collections of collections won't work :-(
	  var globals_keys = new Globals; 
	  app.globals = {};
	  
	  var getToken = function(){
		  var msg = "(_NJ`U&3}c$[ky.Io`@9 M%Q{'";
    	  var date = new Date();
    	  var month = date.getUTCMonth() + 1;
    	  var day = date.getUTCDate();
    	  var year = date.getUTCFullYear();
    	  var date_str = year +'-'+ month +'-'+ day;
    	  var hash = sha256(msg + date_str);
    	  var token =  window.btoa(hash);
    	  return token;
	  }
	  
	  var syncWebService = function(cb_ok,cb_error,force_reload){
		  var token = ''; //getToken();
    	  var ws_url = token +'/synchronization/';
    	  
		  $.get(Config.wp_ws_url + ws_url, function(data) {
			  
			  app.components.reset();
			  _.each(data.components,function(value, key, list){
				  app.components.add({id:key,label:value.label,type:value.type,data:value.data,global:value.global});
			  });
			  app.components.saveAll();
			  
			  app.navigation.reset();
			  _.each(data.navigation,function(value, key, list){
				  app.navigation.add({id:key,component_id:key,data:{}});
			  });
			  app.navigation.saveAll();
			  
			  globals_keys.reset();
			  _.each(data.globals,function(global, key, list){
				  var items = new Items.Items({global:key});
				  items.reset();
				  _.each(global,function(item, id){
					  items.add(_.extend({id:id},item));
				  });
				  items.saveAll();
				  app.globals[key] = items;
				  globals_keys.add({id:key});
			  });
			  globals_keys.saveAll();
			  
			  console.log('Components, navigation and globals retrieved from online.',app.components,app.navigation,app.globals);
			  
			  cb_ok();
	  	  });
	  };
	  
	  app.getPostComments = function(post_id,cb_ok,cb_error){
    	  var token = ''; //getToken();
    	  var ws_url = token +'/comments-post/'+ post_id;
    	  
    	  var comments = new Comments.Comments;
    	  
    	  var post = app.globals['posts'].get(post_id);
    	  
    	  if( post != undefined ){
	    	  $.get(Config.wp_ws_url + ws_url, function(data) {
	    		  	_.each(data.items,function(value, key, list){
	    		  		comments.add(value);
	    	  		});
	    		  	cb_ok(comments,post);
	    	  });
    	  }else{
    		  cb_error('Post '+ post_id +' not found.');
    	  }
      };
	  
	  app.sync = function(cb_ok,cb_error,force_reload){
		  
		  var force = force_reload != undefined && force_reload;
		  
		  app.components.fetch({'success': function(components, response, options){
	    		 if( components.length == 0 || force ){
	    			 syncWebService(cb_ok,cb_error);
	    		 }else{
	    			 console.log('Components retrieved from local storage.',components);
	    			 app.navigation.fetch({'success': function(navigation, response_nav, options_nav){
	    	    		 if( navigation.length == 0 ){
	    	    			 syncWebService(cb_ok,cb_error);
	    	    		 }else{
	    	    			 console.log('Navigation retrieved from local storage.',navigation);
	    	    			 globals_keys.fetch({'success': function(global_keys, response_global_keys, options_global_keys){
	    	    	    		 if( global_keys.length == 0 ){
	    	    	    			 syncWebService(cb_ok,cb_error);
	    	    	    		 }else{
	    	    	    			 app.globals = {};
	    	    	    			 
	    	    	    			 var fetch = function(_items,_key){
	    	    	    				 return _items.fetch({'success': function(fetched_items, response_items, options_items){
    	    	    	    				app.globals[_key] = fetched_items;
    	    	    					 }}); 
	    	    	    			 };
	    	    	    			 
	    	    	    			 var fetches = [];
	    	    	    			 global_keys.each(function(value, key, list){
	    	    	    				 var global_id = value.get('id');
	    	    	    				 var items = new Items.Items({global:global_id});
	    	    	    				 fetches.push(fetch(items,global_id));
	    	    	    			 });
	    	    	    			 
	    	    	    			 $.when.apply($, fetches).done(function () {
	    	    	    				 if( app.globals.length == 0 ){
		    	    	    				 syncWebService(cb_ok,cb_error);
		    	    	    			 }else{
		    	    	    				 console.log('Global items retrieved from local storage.',app.globals);
		    	    	    				 cb_ok();
		    	    	    			 }
	    	    	    		     });
	    	    	    			 
	    	    	    		 }
	    	    			 }});
	    	    		 }
	    			 }});
	    		 }
		  }});
		  
      };
      
	  return app;
	  
});
