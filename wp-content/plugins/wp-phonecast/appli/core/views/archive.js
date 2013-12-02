define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeFunctions		= require('core/theme-functions'),
        Tpl                 = require('text!theme/archive.html');

    return Backbone.View.extend({
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render');
            
    		this.posts = args.posts;
    		this.posts.on('add', this.render);
    		this.posts.on('reset', this.render);
    		
    		this.title = args.title;
    		this.total = args.total;
        },

        render : function() {
        	var renderedContent = this.template({ posts : this.posts.toJSON(), title: this.title, total:this.total, Functions : ThemeFunctions });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
