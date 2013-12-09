define(function (require) {

	"use strict";

	var $                   = require('jquery'), 
		_                   = require('underscore'),
		Backbone            = require('backbone'),
		HeadView            = require('core/views/head'),
		MenuView            = require('core/views/menu'),
		LayoutView          = require('core/views/layout');
      
	Backbone.View.prototype.close = function(){
		
		//We also have to remove Views Models and Collections events by hand + handle closing subviews :
		// > use onClose for this.
		if( this.onClose ){
			this.onClose();
		}
		
		console.log('view.prototype.close()',this);
		
		this.unbind(); // this will unbind all listeners to events from this view. This is probably not necessary because this view will be garbage collected.
		this.remove(); // uses the default Backbone.View.remove() method which removes this.el from the DOM and removes DOM events.
	};
	
	var RegionManager = (function (Backbone, $, _, LayoutView) {
	    
		var headView = null;
		
		var layoutView = null;
		var elLayout = "#app-layout";
		
		var currentView = null;
	    var el = "#app-container";
	    
	    var elMenu = "#app-menu";
	    var menuView= null; 
	    
	    var region = {};
	    
	    var vent = _.extend({}, Backbone.Events);
	    region.on = function(event,callback){
	    	vent.on(event,callback);
	    };
	 
	    region.buildHead = function(){
	    	if( headView === null ){
		    	headView = new HeadView();
				headView.render();
	    	}
	    };
	    
	    region.buildLayout = function(){
	    	if( layoutView === null ){
	    		layoutView = new LayoutView({el:elLayout});
	    		layoutView.render();
	    	}
	    };
	    
	    region.buildMenu = function(force_reload){
	    	
	    	force_reload = (force_reload!=undefined && force_reload);
	    	
	    	if( menuView === null || force_reload ){
	    		menuView = new MenuView();
	    		require(['core/app'],function(App){
	    			menuView.resetAll();
		    		App.navigation.each(function(element, index){
		    			var component = App.components.get(element.get('component_id'));
		    			if( component ){
		    				menuView.addItem(component.get('id'),component.get('type'),component.get('label'));
		    			}
		   		  	});
		    		showMenu(force_reload);
	    		});
	   		  	
	    	}
	    };
	    
	    var showMenu = function(force_reload){
	    	if( menuView ){
	    		if( $(elMenu).length 
	    			&& (!$(elMenu).html().length || (force_reload!=undefined && force_reload) ) ){
		    		menuView.render();
		    		$(elMenu).empty().append(menuView.el);
	    		}
	    	}else{
	    		if( $(elMenu).html().length ){
	    			$(elMenu).empty();
	    		}
	    	}
	    };
	    
	    region.getMenuView = function(){
	    	return menuView;
	    };
	    
	    var closeView = function (view) {
	        if( view ){
	        	if( view.isStatic ){
	        		var static_pages_wrapper = $('#static-pages');
	        		if( !static_pages_wrapper.find('[data-viewid='+ view.cid +']').length ){
	        			$(view.el).attr('data-viewid',currentView.cid);
	        			static_pages_wrapper.append(view.el);
	        		}
	        	}else{
		        	if( view.close ){
		        		view.close();
		        	}
	        	}
	        }
	    };
	 
	    var openView = function (view) {
	    	
	    	if( !view.isStatic || !$(view.el).html().length ){
	    		console.log('Open view, static = ', view.isStatic);
				view.render();
				$(el).empty().append(view.el);
	     	    if(view.onShow) {
	     	        view.onShow();
	     	   	}
	    	}else{
	    		console.log('Open static view');
	    		$(el).empty().append(view.el);
	    	}
	    	
	    };
	    
	    region.show = function(view,force_no_waiting) {
	    		
	    	var no_waiting = force_no_waiting != undefined && force_no_waiting == true;

	    	if( !view.isStatic || !$(view.el).html().length ){

	    		if( !no_waiting ){
	    			region.startWaiting();
	    		}
		    	
		    	if( view.loadViewData ){
			    	view.loadViewData(function(){
			    		region.showSimple(view);
			    		if( !no_waiting ){
			    			region.stopWaiting();
			    		}
			    	});
		    	}else{
		    		region.showSimple(view);
		    		if( !no_waiting ){
		    			region.stopWaiting();
		    		}
		    	}
		    	
	    	}else{
	    		if( !no_waiting ){
	    			region.showSimple(view);
	    		}
	    	}
		    	
	    };
	    
	    region.showSimple = function(view) {
			
	    	if( currentView ){
	    		closeView(currentView);
	    	}
	    	currentView = view;
		    openView(currentView);
		    
		    console.log('View opened : ', currentView, currentView.cid, currentView.isStatic );
		    
		    require(['core/app'],function(App){
		    	vent.trigger('page:showed',App.getCurrentPage(),currentView);
		    });
	    };
	    
	    region.startWaiting = function(){
	    	$('#waiting').show();
	    };
	    
	    region.stopWaiting = function(){
	    	$('#waiting').hide();
	    };
	    
	    return region;
	    
	})(Backbone, $, _, LayoutView);
	
	return RegionManager;
});
