define(function (require) {

    "use strict";

    var $                   = require('jquery'),
        _                   = require('underscore'),
        Backbone            = require('backbone'),
        ThemeTplTags		= require('core/theme-tpl-tags'),
        Utils               = require('core/app-utils');

    return Backbone.View.extend({
    	
    	className: "app-page",
    	
    	custom_page_data : null,
    	
    	initialize : function(custom_page) {
    		
    		this.custom_page = custom_page;
    		
            _.bindAll(this,'render','checkTemplate');
        },
        
        checkTemplate : function(cb_ok,cb_error){
        	var _this = this;
        	require(['text!theme/'+ this.custom_page.get('template') +'.html'],
  					function(tpl){
  						_this.template = _.template(tpl);
  						_this.custom_page_data = _this.custom_page.get('data');
  						cb_ok();
  	      		  	},
  	      		  	function(error){
  	      		  		Utils.log('Error : custom page template "'+ _this.custom_page.get('template') +'.html" not found in theme');
  	      		  		cb_error();
  	      		  	}
  			);
        },

        render : function() {
        	if( this.custom_page_data !== null ){
        		var renderedContent = this.template({ data : this.custom_page_data, TemplateTags : ThemeTplTags });
        		$(this.el).html(renderedContent);
        	}
            return this;
        }
        
    });

});
