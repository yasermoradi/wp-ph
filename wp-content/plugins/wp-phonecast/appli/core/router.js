define(function (require) {
 
    "use strict";
 
    var $              = require('jquery'),
        Backbone       = require('backbone'),
        RegionManager  = require("core/region-manager");
    
    var default_route = '';
    
    return Backbone.Router.extend({
 
        routes: {
            "": "default_route",
            "posts-list-:id" : "archive",
            "single-:id" : "single",
            "page-:id" : "page",
            "comments-:post_id" : "comments",
            "component-:id" : "component"
        },
 
        setDefaultRoute : function(_default_route){
    		default_route = _default_route;
    	},
    	
        default_route: function(){
        	this.navigate(default_route, {trigger: true});
        },
        
        component: function (component_id) {
        	require(["core/app"],function(App){
        		var component = App.components.get(component_id);
        		if( component ){
	        		switch(component.get('type')){
	        			case 'posts-list':
	        				var data = component.get('data');
	        				require(["core/models/items"],function(Items){
		        				var items = new Items.ItemsSlice();
		        				var global = App.globals[component.get('global')];
		        				_.each(data.ids,function(post_id, index){
		        					items.add(global.get(post_id));
		            	  		});
		        				require(["core/views/archive"],function(ArchiveView){
		        					App.setCurrentPage('archive',component_id);
		        					RegionManager.show(new ArchiveView({posts:items,title: component.get('label'), total: data.total}));
		        				});
	        				});
	        				break;
	        			case 'page':
	        				var data = component.get('data');
	        				var global = App.globals[component.get('global')];
	        				if( global ){
	        					var page = global.get(data.id);
	        					if( page ){
			        				App.setCurrentPage('page',component_id,data.id);
			        				require(["core/views/single"],function(SingleView){
			        					RegionManager.show(new SingleView({post:page}));
			        				});
	        					}
	        				}
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
		        		App.setCurrentPage('single','',post_id);
		        		RegionManager.show(new SingleView({post:post}));
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
	        			App.setCurrentPage('comments','',post_id);
	        			RegionManager.show(new CommentsView({comments:comments,post:post}));
	        			RegionManager.stopWaiting();
		        	},
		        	function(error){
		        		console.log('App.getPostComments Error',error);
		        		RegionManager.stopWaiting();
		        	}
		        );
        	});
        }
        
    });
 
});