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
            "info" : "info"
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
        			var component_type = component.get('type');
	        		switch(component_type){
	        			case 'posts-list':
	        				var data = component.get('data');
	        				require(["core/models/items"],function(Items){
		        				var items = new Items.ItemsSlice();
		        				var global = App.globals[component.get('global')];
		        				_.each(data.ids,function(post_id, index){
		        					items.add(global.get(post_id));
		            	  		});
		        				require(["core/views/archive"],function(ArchiveView){
		        					RegionManager.leave();
		        					App.addToHistory('list',component_id,'',data);
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
			        				App.addToHistory('page',component_id,data.id,data);
			        				require(["core/views/single"],function(SingleView){
			        					RegionManager.leave();
			        					RegionManager.show(new SingleView({post:page}));
			        				});
	        					}
	        				}
	        				break;
	        			/*case 'navigation':
	        			    RegionManager.leave();
	        				App.addToHistory('navigation',component_id,'',data);
	        				RegionManager.show(RegionManager.getMenuView());
	        				break;*/
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
		        		RegionManager.leave(App.getCurrentPageData());
		        		App.addToHistory('single','',post_id,{post:post.toJSON()});
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
	        			//Check if we are still on the right post :
	        			var current_page = App.getCurrentPageData();
	        			if( current_page.page_type == 'single' && current_page.item_id == post_id ){
	        				RegionManager.leave(current_page);
	        				App.addToHistory('comments','',post_id);
		        			RegionManager.show(new CommentsView({comments:comments,post:post}));
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
        
        info: function(){
        	require(["core/app","core/views/info"],function(App,InfoView){
        		var current_info = App.getCurrentInfo();
        		if( current_info !== null ){
	        		var current_page = App.getCurrentPageData();
	        		RegionManager.leave(current_page);
	        		App.addToHistory('info','','',current_info);
	        		RegionManager.show(new InfoView({info:current_info}));
        		}
        	});
        }
        
    });
 
});