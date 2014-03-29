define(function (require) {

    "use strict";

    var Backbone    = require('backbone');

    var CustomPage = Backbone.Model.extend({
    	defaults : {
    		id: '',
    		template: 'custom',
    		data: {}
        }
    });

    return CustomPage;
});