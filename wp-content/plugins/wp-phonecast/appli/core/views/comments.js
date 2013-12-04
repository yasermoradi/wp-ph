define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeFunctions		= require('core/theme-functions'),
        Tpl                 = require('text!theme/comments.html');

    return Backbone.View.extend({
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render');
            
    		this.comments = args.comments;
    		
    		this.post = args.post;
        },

        render : function() {
        	var renderedContent = this.template({ comments : this.comments.toJSON(), post : this.post.toJSON(), Functions : ThemeFunctions });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
