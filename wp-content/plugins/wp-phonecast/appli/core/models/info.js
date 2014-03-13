define(function (require) {

    "use strict";

    var Backbone    = require('backbone');

    var Info = Backbone.Model.extend({
    	defaults : {
    		id: '',
    		type: '',
    		title: '',
    		content: '',
    		data: {}
        }
    });

    return Info;
});