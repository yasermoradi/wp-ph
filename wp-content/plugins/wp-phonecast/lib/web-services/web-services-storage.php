<?php
class WppcWebServicesStorage{
	
	const option_id = 'mlrws_web_services_list';
	
	public static function get_web_services(){
		
		$web_services = array(
			'synchronization' => array('hook'=>'wppc_synchronisation_web_service')
		);
		
		$web_services = apply_filters('wppc_web_services',$web_services);
		
		return $web_services;
	}
	
	public static function get_web_service($ws_slug){
		$web_services = self::get_web_services();
		$web_service = array();
		if( array_key_exists($ws_slug,$web_services) ){
			$web_service = array('slug' => $ws_slug) + $web_services[$ws_slug];
		}
		return $web_service;
	}
	
}