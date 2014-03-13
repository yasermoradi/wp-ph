define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeTplTags		= require('core/theme-tpl-tags'),
        Tpl                 = require('text!theme/info.html');

    return Backbone.View.extend({
    	
    	initialize : function(args) {
            
    		this.template = _.template(Tpl);
           
            _.bindAll(this,'render');
            
    		this.info = args.info;
        },

        render : function() {
        	var renderedContent = this.template({ info : this.info.toJSON(), TemplateTags : ThemeTplTags });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});