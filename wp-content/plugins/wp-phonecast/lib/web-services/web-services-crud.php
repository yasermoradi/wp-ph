<?php

require_once(dirname(__FILE__) .'/core-web-services/synchronization.php');
require_once(dirname(__FILE__) .'/core-web-services/comments.php');

class MlrwsWebServiceCrud{

	public function create($service_slug,$data){
		
		$service_answer = array();
		$service_answer = apply_filters('wppc_create_'. $service_slug,$service_answer,$data);
		$service_answer = apply_filters('wppc_create',$service_answer,$service_slug,$data);
		
		return $service_answer;
	}
	
	public function read($service_slug,$query_vars){
		
		$service_answer = array();
		$service_answer = apply_filters('wppc_read_'. $service_slug,$service_answer,$query_vars);
		$service_answer = apply_filters('wppc_read',$service_answer,$service_slug,$query_vars);
		
		return $service_answer;
	}
	
	public function read_one($service_slug,$id){

		$service_answer = array();
		$service_answer = apply_filters('wppc_read_one_'. $service_slug,$service_answer,$id);
		$service_answer = apply_filters('wppc_read_one',$service_answer,$service_slug,$id);
		
		return $service_answer;
	}
	
	public function update($service_slug,$data){
		
		$service_answer = array();
		$service_answer = apply_filters('wppc_update_'. $service_slug,$service_answer,$id);
		$service_answer = apply_filters('wppc_update',$service_answer,$service_slug,$id);
		
		return $service_answer;
	}
	
	public function delete($service_slug,$id){

		$service_answer = array();
		$service_answer = apply_filters('wppc_delete_'. $service_slug,$service_answer,$id);
		$service_answer = apply_filters('wppc_delete',$service_answer,$service_slug,$id);
		
		return $service_answer;
	}
	
}
