define(function (require) {

      "use strict";

      var _                   = require('underscore'),
          Backbone            = require('backbone'),
          RegionManager       = require("core/region-manager"),
          Utils               = require('core/app-utils'),
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
      
      themeApp.navigate = function(navigate_to_fragment){
    	  App.router.navigate(navigate_to_fragment,{trigger: true});
      };
      
      themeApp.getCurrentPage = function(){
    	  return App.getCurrentPageData();
      };
      
      themeApp.getPreviousPageLink = function(){
		  var previous_page_link = '';
		  var previous_page = App.getPreviousPageData();
		  if( !_.isEmpty(previous_page) ){
			  previous_page_link = '#'+ previous_page.fragment;
		  }
		  return previous_page_link;
	  };
	  
	  /**
	   * Automatically shows and hide Back button according to current page (list, single, comments, etc...)
	   * @param $go_back_btn Back button jQuery DOM element 
	   */
	  themeApp.setAutoBackButton = function($go_back_btn){
		  RegionManager.on('page:showed',function(current_page,view){
				if( current_page.page_type == 'single' || current_page.page_type == 'comments' ){
					var prev_page_link = themeApp.getPreviousPageLink();
					if( prev_page_link.length ){
						$go_back_btn.unbind('click').click(function(){
							themeApp.navigate(prev_page_link);
						});
						$go_back_btn.show();
					}
				}
				else if( current_page.page_type == 'page' ){
					$go_back_btn.unbind('click');
					$go_back_btn.hide();
				}
				else if( current_page.page_type == 'list' ){
					$go_back_btn.unbind('click');
					$go_back_btn.hide();
				}
		  });
	  };
	  
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