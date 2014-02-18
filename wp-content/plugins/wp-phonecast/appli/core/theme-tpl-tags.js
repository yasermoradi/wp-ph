/**
 * Defines "template tags like" functions that can be called from theme templates 
 * and theme functions.js. 
 */
define(function (require) {

      "use strict";

      var _                   = require('underscore'),
          Config              = require('root/config'),
          App                 = require('core/app'),
      	  ThemeApp            = require('core/theme-app');
          
      var themeTplTags = {};
      
      /**
       * Retrieves current page infos :
       * @return JSON object containing :
       * - page_type : list, single, comments, page
       * - fragment : unique page url id (what's after # in url)
       * - component_id : component slug id, if displaying a component page (list, page)
       * - item_id : current page id, if displaying single content (post,page)
       * - data : contains more specific data depending on which page type is displayed
       * 	> total : total number of posts for lists
       * 	> query : query vars used to retrieve contents (taxonomy, terms...)
       * 	> ids : id of posts displayed in lists
       * 	> any other specific data depending on currently displayed component
       */
      themeTplTags.getCurrentPage = function(){
    	  return App.getCurrentPageData();
      };
      
      themeTplTags.getPreviousPageLink = function(){
    	  return App.getPreviousPageLink();
	  };
	  
      themeTplTags.get_post_link = function(post_id){
    	  //TODO Check if the post exists in the posts global 
    	  return '#single-'+ post_id;
      };
      
      themeTplTags.get_comments_link = function(post_id){
    	  //TODO Check if the post exists in the posts global
    	  return '#comments-'+ post_id;
      };
      
      themeTplTags.displayBackButton = function(){
    	  var display = ThemeApp.getBackButtonDisplay();
    	  return display == 'show';
	  };
      
      themeTplTags.is_single = function(post_id){
    	  var page_data = App.getCurrentPageData();
    	  var is_single = page_data.page_type == 'single';
    	  if( post_id != undefined ){
    		  is_single &= parseInt(post_id) == page_data.item_id;
    	  }
    	  return is_single;
      };
      
      themeTplTags.is_page = function(page_id){
    	  var page_data = App.getCurrentPageData();
    	  var is_page = page_data.page_type == 'page';
    	  if( page_id != undefined ){
    		  is_page &= parseInt(page_id) == page_data.item_id;
    	  }
    	  return is_page;
      };
      
      themeTplTags.is_post_type = function(post_type,post_id){
    	  //TODO!
      };
      
      themeTplTags.is_taxonomy = function(taxonomy,terms){
    	  var is_taxonomy = false;
    	  
    	  var page_data = App.getCurrentPageData();
    	  
    	  if( !_.isEmpty(page_data.data) && !_.isEmpty(page_data.data.query) ){
	    	  var page_query = page_data.data.query;
	    	  is_taxonomy = page_data.page_type == 'list' && !_.isEmpty(page_query.type) && page_query.type == 'taxonomy';
	    	  if( is_taxonomy && !_.isEmpty(taxonomy) ){
	    		  is_taxonomy &= !_.isEmpty(page_query.taxonomy) && page_query.taxonomy == taxonomy;
		    	  if( is_taxonomy && terms != undefined ){
		    		  if( typeof terms === 'string' ){
		    			  terms = [terms];
		    		  }
		    		  is_taxonomy &= !_.isEmpty(_.intersection(terms,page_query.terms));
		    	  }
	    	  }
    	  }	  
    	  
    	  return is_taxonomy;
      };
      
      themeTplTags.is_category = function(categories){
    	  return themeTplTags.is_taxonomy('category',categories);
      };
      
      themeTplTags.is_tag = function(tags){
    	  return themeTplTags.is_taxonomy('tag',tags);
      };
      
      themeTplTags.is_screen = function(screen_fragment){
    	  var page_data = App.getCurrentPageData();
    	  return page_data.fragment == screen_fragment;
      };
      
	  return themeTplTags;
});