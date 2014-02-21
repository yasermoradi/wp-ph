<?php

class WppcWebServiceSynchronization{

	public static function hooks(){
		add_filter('wppc_read_synchronization',array(__CLASS__,'read'),10,3);
	}
	
	public function read($service_answer,$query_vars,$app_id){
		$service_answer = array();
		
		$app_id = WppcApps::get_app_id($app_id);
		
		$service_answer = WppcComponents::get_components_synchro_data($app_id);
		
		return (object)$service_answer;
	}
	
}

WppcWebServiceSynchronization::hooks();