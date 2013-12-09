<?php
/**
 * NOT USED FOR NOW
 * TODO : see if we need this and if we do it this way... 
 */
class WppcComponentTypeNavigation extends WppcComponentType{
	
	protected function compute_data($options){
	} 
	
	public function get_options_to_display($component){
		$options = array();
		return $options;
	}
	
	public function echo_form_fields($component){
		
	}
	
	public function echo_form_javascript(){
	}
	
	public function get_ajax_action_html_answer($action,$params){
	}
	
	public function get_options_from_posted_form(){
		$options = array();
		return $options;
	}
	
}