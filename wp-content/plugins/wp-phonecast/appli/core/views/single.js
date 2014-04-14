define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        TemplateView        = require('core/views/backbone-template-view'),
        ThemeTplTags		= require('core/theme-tpl-tags');

    return TemplateView.extend({
    	
    	className: "app-page",
    	
    	initialize : function(args) {
            
    		this.setTemplate('single');
           
            _.bindAll(this,'render');
            
    		this.post = args.post;
    		
    		this.post.on('change', this.render);
        },

        render : function() {
        	var renderedContent = this.template({ post : this.post.toJSON(), TemplateTags : ThemeTplTags });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
