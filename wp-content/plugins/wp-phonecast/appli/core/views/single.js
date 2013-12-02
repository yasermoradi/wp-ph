define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeFunctions		= require('core/theme-functions'),
        Tpl                 = require('text!theme/single.html');

    return Backbone.View.extend({
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render');
            
    		this.post = args.post;
    		
    		this.post.on('change', this.render);
        },

        render : function() {
        	var renderedContent = this.template({ post : this.post.toJSON(), Functions : ThemeFunctions });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
