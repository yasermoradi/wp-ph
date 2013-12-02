define(function (require) {

    "use strict";

    var Backbone                 = require('backbone');

    var Item = Backbone.Model.extend({
    	defaults : {
    		id : ""
        }
    });

    var Items = Backbone.Collection.extend({
    	model : Item,
    	localStorage: null,
    	initialize : function(args){
    		this.localStorage = new Backbone.LocalStorage("Items-"+args.global);
    	},
    	saveAll : function(){
       	 	this.map(function(item){item.save()});
        }
    });
    
    var ItemsSlice = Backbone.Collection.extend({
    	model : Item,
    });
    
    return {Item:Item,Items:Items,ItemsSlice:ItemsSlice};

});