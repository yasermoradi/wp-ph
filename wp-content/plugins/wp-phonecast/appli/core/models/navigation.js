define(function (require) {

    "use strict";

    var Backbone                 = require('backbone');
    require('localstorage');

    var NavigationItem = Backbone.Model.extend({
    	defaults : {
    		id : "",
    		component_id : "",
            data : ""
        }
    });

    var NavigationItems = Backbone.Collection.extend({
    	localStorage: new Backbone.LocalStorage("Navigation"),
    	model : NavigationItem,
    	saveAll : function(){
       	 	this.map(function(item){item.save()});
        }
    });
    
    return NavigationItems;

});