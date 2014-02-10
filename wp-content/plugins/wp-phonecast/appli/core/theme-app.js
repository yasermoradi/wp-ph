/**
 * Defines functions that can be called from theme functions.js. 
 * (Those functions can't be directly called form theme templates).
 */
define(function (require) {

      "use strict";

      var _                   = require('underscore'),
          Backbone            = require('backbone'),
          RegionManager       = require('core/region-manager'),
          Utils               = require('core/app-utils'),
          Config              = require('root/config'),
          App                 = require('core/app');
          
      var themeApp = {};
      
      
      /************************************************
	   * Events management
	   */
      
      //Event aggregator
	  var vent = _.extend({}, Backbone.Events);
	  themeApp.on = function(event,callback){
		  vent.on(event,callback);
	  };
	  
	  //Proxy App events
	  App.on('all',function(event,data){
		  
		  var theme_event_data = format_theme_event_data(event,data);
		  
		  if( theme_event_data.type == 'error' ){
			  vent.trigger('error',theme_event_data);
		  }
		  
	  });
	  
	  //Format events feedbacks
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
	  
	  
	  /************************************************
	   * App contents refresh
	   */

	  var refreshing = 0;
	  
      themeApp.refresh = function(cb_ok,cb_error){
    	  
    	  refreshing++;
    	  vent.trigger('refresh:start');
    	  
    	  App.sync(function(){
    		  	RegionManager.buildMenu(
    		  		function(){
	    		  		App.resetDefaultRoute();
	        		  	App.router.default_route();
	        		  	Backbone.history.stop(); 
	        		  	Backbone.history.start();
	        		  	
	        		  	refreshing--;
	    				vent.trigger('refresh:end');
	    				
	    				if( cb_ok ){
	    					cb_ok();
	    				}
	    		  	},
	    		  	true
    		  	);
			},function(error){
				refreshing--;
				if( cb_error ){
					cb_error(format_theme_event_data('error',error));
				}
				vent.trigger('refresh:end');
			},
			true
		);
      };
      
      themeApp.isRefreshing = function(){
    	  return refreshing > 0;
      };
      
      
      /************************************************
	   * App navigation
	   */
      
      themeApp.navigate = function(navigate_to_fragment){
    	  App.router.navigate(navigate_to_fragment,{trigger: true});
      };
      
      
      /************************************************
	   * Back button
	   */
      
	  /**
	   * Automatically shows and hide Back button according to current page (list, single, comments, etc...)
	   * Use only if back button is not refreshed at each page load! (otherwhise $go_back_btn will not be set correctly).
	   * @param $go_back_btn Back button jQuery DOM element 
	   */
	  themeApp.setAutoBackButton = function($go_back_btn,do_before_auto_action){
		  RegionManager.on('page:showed',function(current_page,view){
			  var display = themeApp.getBackButtonDisplay();
			  if( display == 'show' ){
				  if( do_before_auto_action != undefined ){
					  do_before_auto_action(true);
				  }
				  $go_back_btn.show();
				  themeApp.updateBackButtonEvents($go_back_btn);
			  }else if( display == 'hide' ){
				  if( do_before_auto_action != undefined ){
					  do_before_auto_action(false);
				  }
				  themeApp.updateBackButtonEvents($go_back_btn);
				  $go_back_btn.hide();
			  }
		  });
	  };
	  
	  themeApp.getBackButtonDisplay = function(current_page){
		  var display = 'default';
		  
		  var current_page = App.getCurrentPageData();
		  
		  if( current_page.page_type == 'single' || current_page.page_type == 'comments' ){
			  var previous_page = App.getPreviousPageData();
			  if( !_.isEmpty(previous_page) ){
				  display = 'show';
			  }
		  }
		  else if( current_page.page_type == 'page' ){
			  display = 'hide';
		  }
		  else if( current_page.page_type == 'list' ){
			  display = 'hide';
		  }
		  return display;
	  };
	  
	  themeApp.updateBackButtonEvents = function($go_back_btn){
		  if( $go_back_btn.length ){
			  var display = themeApp.getBackButtonDisplay();
			  if( display == 'show' ){
				  $go_back_btn.unbind('click').click(function(e){
					  e.preventDefault();
					  var prev_page_link = App.getPreviousPageLink();
					  themeApp.navigate(prev_page_link);
				  });
			  }else if( display == 'hide' ){
				  $go_back_btn.unbind('click');
			  }
		  }
	  };
	  
	  
	  /************************************************
	   * Body class
	   */
	  
	  /**
	   * Sets class to the body element according to the given current page 
	   */
	  var setBodyClass = function(current_page){
		  if( !_.isEmpty(current_page) ){
			  var $body = $('body');
			  $body.removeClass(function(index, css){
				  return (css.match (/\app-\S+/g) || []).join(' ');
			  });
			  $body.addClass('app-'+ current_page.page_type);
			  $body.addClass('app-'+ current_page.fragment);
		  }
	  };
	  
	  /**
	   * Adds class on DOM body element according to the current page
	   * @param activate Set to true to activate
	   */
	  themeApp.setAutoBodyClass = function(activate){
		  if( activate ){
			  RegionManager.on('page:showed',setBodyClass);
			  setBodyClass(App.getCurrentPageData());
		  }
		  //TODO : handle deactivation!
	  };
      
	  return themeApp;
});