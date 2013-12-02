define(function (require) {

      "use strict";

      var _                   = require('underscore'),
          Backbone            = require('backbone'),
          Config              = require('root/config'),
          App                 = require('core/app');
          
      var themeFunctions = {};
      
      /**
       * For posts present in the "posts" global
       */
      themeFunctions.get_post_link = function(id){
    	  //TODO Check if the post exists in the posts global 
    	  return '#single-'+ id;
      };
      
      themeFunctions.get_comments_link = function(post_id){
    	  //TODO Check if the post exists in the posts global
    	  return '#comments-'+ post_id;
      };
      
	  return themeFunctions;
});