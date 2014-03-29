define(function (require) {
 
    "use strict";
 
    var Backbone       = require('backbone'),
    	Utils          = require('core/app-utils'),
        RegionManager  = require("core/region-manager");
    
    var default_route = '';
    
    return Backbone.Router.extend({
 
        routes: {
            "": "default_route",
            "posts-list-:id" : "archive",
            "single-:id" : "single",
            "page-:id" : "page",
            "comments-:post_id" : "comments",
            "component-:id" : "component",
            "custom-page" : "custom_page"
        },
 
        setDefaultRoute : function(_default_route){
    		default_route = _default_route;
    	},
    	
        default_route: function(){
        	this.navigate(default_route, {trigger: true});
        },
        
        component: function (component_id) {
        	require(["core/app"],function(App){
        		
        		var component = App.getComponentData(component_id);
        		if( component ){
        			switch( component.type ){
        				case 'posts-list':
        					require(["core/views/archive"],function(ArchiveView){
	        					RegionManager.show(new ArchiveView(component.view_data),'list',component_id,'',component.data);
	        				});
        					break;
        				case 'page':
        					require(["core/views/single"],function(SingleView){
	        					RegionManager.show(new SingleView(component.view_data),'page',component_id,component.data.id,component.data);
	        				});
        					break;
        			}
        		}else{
        			App.router.default_route();
        		}
        		
        	});
        },
        
        /**
         * The post must be in the "posts" global to be accessed via this "single" route.
         */
        single: function (post_id) {
        	require(["core/app","core/views/single"],function(App,SingleView){
	        	var global = App.globals['posts'];
	        	if( global ){
		        	var post = global.get(post_id);
		        	if( post ){
		        		RegionManager.show(new SingleView({post:post}),'single','',post_id,{post:post.toJSON()});
		        	}else{
	        			App.router.default_route();
	        		}
	        	}else{
        			App.router.default_route();
        		}
        	});
        },
        
        comments: function (post_id) {
        	RegionManager.startWaiting();
        	require(["core/app","core/views/comments"],function(App,CommentsView){
	        	App.getPostComments(
	        		post_id,
	        		function(comments,post){
	        			//Check if we are still on the right post :
	        			var current_page = App.getCurrentPageData();
	        			if( current_page.page_type == 'single' && current_page.item_id == post_id ){
		        			RegionManager.show(new CommentsView({comments:comments,post:post}),'comments','',post_id);
	        			}
	        			RegionManager.stopWaiting();
		        	},
		        	function(error){
		        		Utils.log('router.js error : App.getPostComments failed',error);
		        		RegionManager.stopWaiting();
		        	}
		        );
        	});
        },
        
        custom_page: function(){
        	require(["core/app"],function(App){
        		var current_custom_page_view = App.getCurrentCustomPageView();
        		if( current_custom_page_view !== null ){
	        		RegionManager.show(current_custom_page_view,'custom-page','','',current_custom_page_view);
        		}
        	});
        }
        
    });
 
});