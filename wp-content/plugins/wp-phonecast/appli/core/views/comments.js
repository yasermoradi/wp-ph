define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeTplTags		= require('core/theme-tpl-tags'),
        Tpl                 = require('text!theme/comments.html');

    return Backbone.View.extend({
    	
    	className: "app-page",
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render');
            
    		this.comments = args.comments;
    		
    		this.post = args.post;
        },

        render : function() {
        	var renderedContent = this.template({ comments : this.comments.toJSON(), post : this.post.toJSON(), TemplateTags : ThemeTplTags });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
