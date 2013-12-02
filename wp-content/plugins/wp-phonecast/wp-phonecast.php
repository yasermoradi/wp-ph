<?php
/*
Plugin Name: WP PhoneCast
Description: Wordpress mobile app
Version: 0.1
*/

if( !class_exists('WpPhoneCast') ){
	
require_once(dirname(__FILE__) .'/lib/web-services/web-services.php');
require_once(dirname(__FILE__) .'/lib/components/components.php');
require_once(dirname(__FILE__) .'/lib/navigation/navigation.php');
require_once(dirname(__FILE__) .'/lib/simulator/simulator.php');

class WpPhoneCast{
	
	const resources_version = '1.0';
	
	public static function hooks(){
		register_activation_hook( __FILE__, array(__CLASS__,'on_activation') );
		add_action('init',array(__CLASS__,'init'));
		add_action('template_redirect',array(__CLASS__,'template_redirect'),5);
	}
	
	public static function on_activation(){
		flush_rewrite_rules();
	}
	
	public static function init(){
		WppcWebServices::add_rewrite_tags_and_rules();
		add_image_size('mobile-featured-thumb', 327, 218);
	}
	
	public static function template_redirect(){
		WppcWebServices::template_redirect();
	}
	
}

WpPhoneCast::hooks();

}