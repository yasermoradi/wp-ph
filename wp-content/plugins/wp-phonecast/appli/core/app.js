define(function (require) {

      "use strict";

      var $                   = require('jquery'),
      	  _                   = require('underscore'),
          Backbone            = require('backbone'),
          Components          = require('core/models/components'),
          Globals             = require('core/models/globals'),
          Navigation          = require('core/models/navigation'),
          Items               = require('core/models/items'),
          Comments            = require('core/models/comments'),
          Config              = require('root/config'),
          Utils               = require('core/app-utils'),
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
	  
	  //Error handling
	  app.trigger_error = function(error_data,error_callback){
	  	  if( error_callback != undefined ){
	  		error_callback(error_data);
	  	  }
		  vent.trigger('error',error_data);
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
		  
		  _.each(initializers, function(initializer,index){
			  if( initializer.wait ){
				  wait_events.push(index);
			  }
		  });
		  
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
		  
	  };
	  
	  app.router = null;
	  
	  //Router must be set before calling this resetDefaultRoute :
	  app.resetDefaultRoute = function(){
		  var first_nav_component_id = app.navigation.first().get('component_id');
		  app.router.setDefaultRoute('#component-'+ first_nav_component_id);
	  };
	  
	  //--------------------------------------------------------------------------
	  //History :
	  var history_stack = [];
	  
	  var history_push = function (page_type,component_id,item_id,fragment,data){
		  history_stack.push({	page_type:page_type,
				component_id:component_id,
				item_id:item_id,
				fragment:fragment,
				data:(data != undefined) ? data : {}
			 });
	  };
		
	  app.addToHistory = function(page_type,component_id,item_id,data,force_flush){
		  
		  var force_flush_history = force_flush != undefined && force_flush == true;
		  
		  var current_page = app.getCurrentPageData();
		  var previous_page = app.getPreviousPageData();
		  var current_fragment = Backbone.history.fragment;
		  
		  if( current_page.page_type != page_type || current_page.component_id != component_id 
			  || current_page.item_id != item_id || current_page.fragment != current_fragment ){
			  
			  if( force_flush_history ){
				  history_stack = [];
			  }
			  
			  if( page_type == 'list' ){
				  history_stack = [];
				  history_push(page_type,component_id,item_id,current_fragment,data);
			  }else if( page_type == 'single' ){
				  if( current_page.page_type == 'list' ){
					  history_push(page_type,component_id,item_id,current_fragment,data);
				  }else if( current_page.page_type == 'comments' ){
					  if( previous_page.page_type == 'single' && previous_page.item_id == item_id ){
						  history_stack.pop();
					  }else{
						  history_stack = [];
						  history_push(page_type,component_id,item_id,current_fragment,data);
					  }
				  }else{
					  history_stack = [];
					  history_push(page_type,component_id,item_id,current_fragment,data);
				  }
			  }else if( page_type == 'page' ){
				  history_stack = [];
				  history_push(page_type,component_id,item_id,current_fragment,data);
			  }else if( page_type == 'comments' ){
				  //if( current_page.page_type == 'single' && current_page.item_id == item_id ){
					  history_push(page_type,component_id,item_id,current_fragment,data);
				  //}
			  }
			  
		  }
		  
	  };
	  
	  /**
	   * Returns infos about the currently displayed page.
	   * @returns {page_type:string, component_id:string, item_id:integer, fragment:string}
	   * Core page_types are "list", "single", "page" "comments". 
	   */
	  app.getCurrentPageData = function(){
		  var current_page = {};
		  if( history_stack.length ){
			  current_page = history_stack[history_stack.length-1];
		  }
		  return current_page;
	  };
	  
	  /**
	   * Returns infos about the page displayed previously.
	   * @returns {page_type:string, component_id:string, item_id:integer, fragment:string} or {} if no previous page 
	   */
	  app.getPreviousPageData = function(){
		  var previous_page = {};
		  if( history_stack.length > 1 ){
			  previous_page = history_stack[history_stack.length-2];
		  }
		  return previous_page;
	  };
	  
	  app.getPreviousPageLink = function(){
		  var previous_page_link = '';
		  var previous_page = app.getPreviousPageData();
		  if( !_.isEmpty(previous_page) ){
			  previous_page_link = '#'+ previous_page.fragment;
		  }
		  return previous_page_link;
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
    	  
		  $.ajax({
				url : Config.wp_ws_url + ws_url, 
				timeout : 40000,
				success : function(data) {
				  
					  app.components.resetAll();
					  _.each(data.components,function(value, key, list){
						  app.components.add({id:key,label:value.label,type:value.type,data:value.data,global:value.global});
					  });
					  app.components.saveAll();
					  
					  app.navigation.resetAll();
					  _.each(data.navigation,function(value, key, list){
						  app.navigation.add({id:key,component_id:key,data:{}});
					  });
					  app.navigation.saveAll();
					  
					  globals_keys.resetAll();
					  _.each(data.globals,function(global, key, list){
						  var items = new Items.Items({global:key});
						  items.resetAll();
						  _.each(global,function(item, id){
							  items.add(_.extend({id:id},item));
						  });
						  items.saveAll();
						  app.globals[key] = items;
						  globals_keys.add({id:key});
					  });
					  globals_keys.saveAll();
					  
					  Utils.log('Components, navigation and globals retrieved from online.',app.components,app.navigation,app.globals);

					  cb_ok();
				},
			  	error : function(jqXHR, textStatus, errorThrown){
			  		app.trigger_error(
			  			{type:'ajax',where:'app::syncWebService',data:{url: Config.wp_ws_url + ws_url, jqXHR:jqXHR, textStatus:textStatus, errorThrown:errorThrown}},
    		  		    cb_error
			  		);
			  	}
		  });
	  };
	  
	  app.getPostComments = function(post_id,cb_ok,cb_error){
    	  var token = ''; //getToken();
    	  var ws_url = token +'/comments-post/'+ post_id;
    	  
    	  var comments = new Comments.Comments;
    	  
    	  var post = app.globals['posts'].get(post_id);
    	  
    	  if( post != undefined ){
	    	  $.ajax({
	    		  type: 'GET',
	    		  url: Config.wp_ws_url + ws_url,
	    		  success: function(data) {
		    		  	_.each(data.items,function(value, key, list){
		    		  		comments.add(value);
		    	  		});
		    		  	cb_ok(comments,post);
		    	  },
		    	  error: function(jqXHR, textStatus, errorThrown){
		    		  app.trigger_error(
		    			  {type:'ajax',where:'app::getPostComments',data:{url: Config.wp_ws_url + ws_url, jqXHR:jqXHR, textStatus:textStatus, errorThrown:errorThrown}},
	    		  		  cb_error
	        		  );
		    	  }
	    	  });
    	  }else{
    		  app.trigger_error(
    			  {type:'not-found',where:'app::getPostComments',data:{message:'Post '+ post_id +' not found.'}},
		  		  cb_error
    		  );
    	  }
      };
      
      app.getMoreOfComponent = function(component_id,cb_ok,cb_error){
    	  var component = app.components.get(component_id);
    	  if( component ){
	    	  var token = ''; //getToken();
	    	  var ws_url = token +'/component/'+ component_id;
	    	  
	    	  var last_item_id = component.getLastItemId();
	    	  ws_url += '?before_item='+ last_item_id;
	    	  
	    	  $.ajax({
	    		  type: 'GET',
	    		  url: Config.wp_ws_url + ws_url,
	    		  success: function(answer) {
		    		  if( answer.result && answer.result.status == 1 ){
		    			  if( answer.component.slug == component_id ){
		    				  var global = answer.component.global;
		    				  if( app.globals.hasOwnProperty(global) ){
		    					  
		    					  var component_data = component.get('data');
		    					  
		    					  var new_ids = _.difference(answer.component.data.ids,component_data.ids);
		    					  
		    					  component_data.ids = _.union(component_data.ids,answer.component.data.ids); //merge ids
		    					  component.set('data',component_data);
		    					  
			    				  var current_items = app.globals[global];
								  _.each(answer.globals[global],function(item, id){
									  current_items.add(_.extend({id:id},item)); //auto merges if "id" already in items
								  });
								  
		    					  var new_items = [];
								  _.each(new_ids,function(item_id){
									  new_items.push(current_items.get(item_id));
			          	  		  });
								  
								  var nb_left = component_data.total - component_data.ids.length;
								  var is_last = !_.isEmpty(answer.component.data.query.is_last_page) ? true : nb_left <= 0;  
								  
								  Utils.log('More content retrieved for component',component_id,new_ids,new_items,component);
								  
								  cb_ok(new_items,is_last,{nb_left:nb_left,new_ids:new_ids,global:global,component:component});
								  
		    				  }else{
			    				  app.trigger_error(
			    					  {type:'not found',where:'app::getMoreOfComponent',data:{message:'Global not found : '+ global}},
							  		  cb_error
					    		  );
			    			  }
		    			  }else{
						  	  app.trigger_error(
						  		  {type:'not found',where:'app::getMoreOfComponent',data:{message:'Wrong component id : '+ component_id}},
						  		  cb_error
				    		  );
		    			  }
		    		  }else{
					  	  app.trigger_error(
					  		  {type:'web-service',where:'app::getMoreOfComponent',data:{message:answer.message}},
					  		  cb_error
			    		  );
		    		  }
		    	  },
		    	  error: function(jqXHR, textStatus, errorThrown){
		    		  app.trigger_error(
		    			  {type:'ajax',where:'app::getMoreOfComponent',data:{url: Config.wp_ws_url + ws_url, jqXHR:jqXHR, textStatus:textStatus, errorThrown:errorThrown}},
		    			  cb_error
		    		  );
		    	  }
	    	  });
    	  }
      };
	  
	  app.sync = function(cb_ok,cb_error,force_reload){
		  
		  var force = force_reload != undefined && force_reload;
		  
		  app.components.fetch({'success': function(components, response, options){
	    		 if( components.length == 0 || force ){
	    			 syncWebService(cb_ok,cb_error);
	    		 }else{
	    			 Utils.log('Components retrieved from local storage.',components);
	    			 app.navigation.fetch({'success': function(navigation, response_nav, options_nav){
	    	    		 if( navigation.length == 0 ){
	    	    			 syncWebService(cb_ok,cb_error);
	    	    		 }else{
	    	    			 Utils.log('Navigation retrieved from local storage.',navigation);
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
		    	    	    				 Utils.log('Global items retrieved from local storage.',app.globals);
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
