define(function (require) {

      "use strict";

      var _                   = require('underscore'),
      	  Backbone            = require('backbone'),
      	  Config              = require('root/config'),
      	  ThemeFunctions	  = require('core/theme-functions'),
      	  Utils               = require('core/app-utils');
      
      var tpl = null;
      var contains_menu = false;
      	  
      return Backbone.View.extend({
  		
  		initialize : function(args) {
  			var _this = this;
  			require(['text!theme/header.html'],
  					function(_tpl){
  						tpl = _tpl;
  						contains_menu = tpl.match(/<%=\s*menu\s*%>/) !== null;
  						_this.template = _.template(tpl);
  						args.do_if_template_exists(_this);
  	      		  	},
  	      		  	function(error){
  	      		  		Utils.log('Info : no theme/header.html found in theme', error); 
  	      		  		args.do_if_no_template();
  	      		  	}
  			);
  	    },

  	    render : function(){
  	    	if( tpl !== null ){
	  	    	var renderedContent = this.template({title : Config.app_title, menu : '<div id="app-menu"></div>', Functions : ThemeFunctions});
	  	        $(this.el).html(renderedContent);
  	    	}
  	        return this;
  	    },
  	    
  	    templateExists : function(){
  	    	return tpl !== null;
  	    },
  	    
  	    containsMenu : function(){
  	    	return contains_menu;
  	    }
  	    
  	});
});