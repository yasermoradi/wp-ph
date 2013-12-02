define(function (require) {

    "use strict";

    var Backbone                 = require('backbone');
    require('localstorage');

    var Component = Backbone.Model.extend({
    	defaults : {
    		id : "",
    		label : "",
            type : "",
            data : "",
            global : ""
        }
    });

    var Components = Backbone.Collection.extend({
    	localStorage: new Backbone.LocalStorage("Components"),
    	model : Component,
    	saveAll : function(){
       	 	this.map(function(component){component.save()});
        }
    });
    
    return Components;

});