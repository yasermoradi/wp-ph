define(function (require) {

      "use strict";

      var filter = {};
      
      var filters = {};
	  
      filter.applyFilter = function(filter,value,params,context){
    	  if( filters.hasOwnProperty(filter) ){
    		  params.unshift(value);
    		  value = filters[filter].apply(context,params);
    	  }
    	  return value;
	  };
	  
	  filter.addFilter = function(filter,callback){
		  filters[filter] = callback;
	  };
	  
	  filter.removeFilter = function(filter,callback){
		  if( filters.hasOwnProperty(filter) ){
			  delete filters[filter];
		  }
	  };
      
      return filter;
});