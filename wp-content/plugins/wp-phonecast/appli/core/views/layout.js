define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        Tpl                 = require('text!theme/layout.html');

    return Backbone.View.extend({
    	
    	initialize : function(args) {
    		this.template = _.template(Tpl);
        },

        render : function() {
        	var renderedContent = this.template({ menu : '<div id="app-menu"></div>', content : '<div id="app-container"></div>' });
            $(this.el).html(renderedContent); 
            return this;
        }
        
    });

});
