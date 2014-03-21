define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeTplTags		= require('core/theme-tpl-tags'),
        Tpl                 = require('text!theme/archive.html');

    return Backbone.View.extend({
    	
    	className: "app-page",
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render','addPosts');
            
    		this.posts = args.posts;
    		
    		this.title = args.title;
    		this.total = args.total;
        },

        render : function() {
        	var renderedContent = this.template({ posts : this.posts.toJSON(), title: this.title, total:this.total, TemplateTags : ThemeTplTags });
            $(this.el).html(renderedContent); 
            return this;
        },
        
        addPosts : function(posts){
        	var _this = this;
        	_.each(posts,function(post){
        		_this.posts.add(post);
	  		});
        }
        
    });

});
