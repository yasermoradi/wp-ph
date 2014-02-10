define(function (require) {

	"use strict";

	var $                   = require('jquery'), 
		_                   = require('underscore'),
		Backbone            = require('backbone'),
		Utils               = require('core/app-utils');
      
	Backbone.View.prototype.close = function(){
		
		//We also have to remove Views Models and Collections events by hand + handle closing subviews :
		// > use onClose for this.
		if( this.onClose ){
			this.onClose();
		}
		
		this.unbind(); // this will unbind all listeners to events from this view. This is probably not necessary because this view will be garbage collected.
		this.remove(); // uses the default Backbone.View.remove() method which removes this.el from the DOM and removes DOM events.
	};
	
	var RegionManager = (function (Backbone, $, _) {
	    
		var headView = null;
		
		var layoutView = null;
		var elLayout = "#app-layout";
		
		var headerView = null;
		var elHeader = "#app-header";
		
		var currentView = null;
	    var el = "#app-container";
	    
	    var elMenu = "#app-menu";
	    var menuView= null; 
	    
	    var region = {};
	    
	    var vent = _.extend({}, Backbone.Events);
	    region.on = function(event,callback){
	    	vent.on(event,callback);
	    };
	    region.off = function(event,callback){
	    	vent.off(event,callback);
	    };
	 
	    region.buildHead = function(cb){
	    	if( headView === null ){
	    		require(['core/views/head'],function(HeadView){
	    			headView = new HeadView();
					headView.render();
					cb();
	    		});
	    	}else{
	    		cb();
	    	}
	    };
	    
	    region.buildLayout = function(cb){
	    	if( layoutView === null ){
	    		require(['core/views/layout'],function(LayoutView){
		    		layoutView = new LayoutView({el:elLayout});
		    		layoutView.render();
		    		cb();
	    		});
	    	}else{
	    		cb();
	    	}
	    };
	    
	    region.buildHeader = function(cb){
	    	if( headerView === null ){
	    		require(['core/views/header'],
	    				function(HeaderView){
				    		headerView = new HeaderView({
				    			el:elHeader,
				    			do_if_template_exists:function(view){
					    			view.render();
					    			cb();
					    		},
					    		do_if_no_template:function(){
					    			cb();
					    		}
					    	});
	    				}
	    		);
	    	}else{
	    		cb();
	    	}
	    };
	    
	    region.buildMenu = function(cb,force_reload){
	    	
	    	force_reload = (force_reload!=undefined && force_reload);
	    	
	    	if( menuView === null || force_reload ){
	    		require(['core/views/menu'],function(MenuView){
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
			    		cb();
		    		});
	    		});
	    	}else{
	    		cb();
	    	}
	    };
	    
	    var showMenu = function(force_reload){
	    	if( menuView ){
	    		if( $(elMenu).length 
	    			&& (!$(elMenu).html().length || (force_reload!=undefined && force_reload) ) ){
		    		menuView.render();
		    		$(elMenu).empty().append(menuView.el);
		    		Utils.log('Render navigation',force_reload,menuView);
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
	    
	    var renderSubRegions = function(){
	    	if( headerView.templateExists() ){
		    	headerView.render();
		    	Utils.log('Render header',headerView);
		    	if( headerView.containsMenu() ){
		    		showMenu(true);
		    	}
		    	require(['core/app'],function(App){
			    	vent.trigger('header:render',App.getCurrentPageData(),headerView);
			    });
	    	}
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
	    		if( view.isStatic != undefined && view.isStatic ){
	    			Utils.log('Open static view',view);
	    		}else{
	    			Utils.log('Open view',view);
	    		}
				view.render();
				$(el).empty().append(view.el);
	     	    if(view.onShow) {
	     	        view.onShow();
	     	   	}
	    	}else{
	    		Utils.log('Re-open existing static view',view);
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
		    
		    renderSubRegions();
		    
		    require(['core/app'],function(App){
		    	vent.trigger('page:showed',App.getCurrentPageData(),currentView);
		    });
	    };
	    
	    region.startWaiting = function(){
	    	$('#waiting').show();
	    };
	    
	    region.stopWaiting = function(){
	    	$('#waiting').hide();
	    };
	    
	    return region;
	    
	})(Backbone, $, _);
	
	return RegionManager;
});
