<?php

class WppcWebServiceSynchronization{

	public static function hooks(){
		add_filter('wppc_read_synchronization',array(__CLASS__,'read'),10,2);
	}
	
	public function read($service_answer,$query_vars){
		$service_answer = array();
		
		$service_answer = WppcComponents::get_components_synchro_data();
		
		return (object)$service_answer;
	}
	
}

WppcWebServiceSynchronization::hooks();