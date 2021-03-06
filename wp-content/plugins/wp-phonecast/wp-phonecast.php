<?php
/*
Plugin Name: WP PhoneCast
Description: Wordpress mobile app
Version: 0.1
*/

if( !class_exists('WpPhoneCast') ){
	
require_once(dirname(__FILE__) .'/lib/web-services/web-services.php');
require_once(dirname(__FILE__) .'/lib/apps/apps.php');
require_once(dirname(__FILE__) .'/lib/apps/build.php');
require_once(dirname(__FILE__) .'/lib/themes/themes.php');
require_once(dirname(__FILE__) .'/lib/components/components.php');
require_once(dirname(__FILE__) .'/lib/navigation/navigation.php');
require_once(dirname(__FILE__) .'/lib/simulator/simulator.php');

class WpPhoneCast{
	
	const resources_version = '0.1';
	
	public static function hooks(){
		register_activation_hook( __FILE__, array(__CLASS__,'on_activation') );
		register_deactivation_hook( __FILE__, array(__CLASS__,'on_deactivation') );
		add_action('init',array(__CLASS__,'init'));
		add_action('template_redirect',array(__CLASS__,'template_redirect'),5);
	}
	
	public static function on_activation(){
		WppcWebServices::add_rewrite_tags_and_rules();
		WppcConfigFile::rewrite_rules();
		flush_rewrite_rules();
	}
	
	public static function on_deactivation(){
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