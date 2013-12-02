define(function (require) {

      "use strict";

      var _                   = require('underscore'),
      	  Backbone            = require('backbone'),
      	  Tpl                 = require('text!theme/menu.html'),
      	  MenuItems           = require('core/models/menu-items');
      	  
      return Backbone.View.extend({
  		
  		initialize : function(options) {
  			
  	        this.template = _.template(Tpl);
  	        
  	        _.bindAll(this,'render');
  			
  			this.menu = new MenuItems.MenuItems();
  			//this.menu.on('add', this.render);
  			
  	    },

  	    events : {
  	    	'click .app-link' : 'navigate'
  	    },

  	    addItem : function(id,type,label){
  	    	this.menu.add({id:id,label:label,type:type,link: '#component-'+id});
  	    },
  	    
  	    render : function( ) {
  	    	var renderedContent = this.template({'menu_items':this.menu.toJSON()});
  	        $(this.el).html(renderedContent);
  	        return this;
  	    },
  	    
  	    navigate : function(e){
  	    	require(['core/app'],function(App){
  	    		e.preventDefault();
      	    	App.router.navigate($(e.currentTarget).data('href'), {trigger: true});
  	    	});
  	    }
  	    
  	});
});