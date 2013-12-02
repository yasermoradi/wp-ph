<?php

//TODO : require this dynamically
require_once(dirname(__FILE__) .'/components-types/posts-list.php');
require_once(dirname(__FILE__) .'/components-types/page.php');

abstract class WppcComponentType{

	private $data = array('specific'=>array(),'globals'=>array());
	
	abstract protected function compute_data($options);
	abstract public function get_options_to_display($component);
	abstract public function get_ajax_action_html_answer($action,$params);
	abstract public function echo_form_fields($component);
	abstract public function echo_form_javascript();
	abstract public function get_options_from_posted_form();
	
	public function get_data(WppcComponent $component,$globals){
		$this->data['globals'] = $globals;
		$this->data['specific']['label'] = $component->label;
		$this->data['specific']['type'] = $component->type;
		$this->compute_data($component->options);
		return $this->data;
	}
	
	protected function set_specific($specific_key,$values){
		@$this->data['specific']['data'][$specific_key] = $values;
	}
	
	/**
	 * Only one global will be taken into account. If more than one are set, only the last one will count.
	 */
	protected function set_globals($globals_key,$values){
		@$this->data['specific']['global'] = $globals_key;
		foreach($values as $k=>$v){
			@$this->data['globals'][$globals_key][$k] = $v;
		}
	}
}

class WppcComponentsTypes{
	
	public static function get_available_components_types(){
	
		$available_components_types = array(
				'posts-list' => array(
						'label' => __('Posts list'),
				),
				'page' => array(
						'label' => __('Wordpress page'),
				),
				/*'terms-list' => array(
						'label' => __('Terms list'),
				),*/
		);
	
		return $available_components_types;
	}
	
	public static function get_component_data(WppcComponent $component,$globals){
		$data = null;
		if( self::component_type_exists($component->type) ){
			$data = self::factory($component->type)->get_data($component,$globals);
		}
		return $data;
	}
	
	public static function get_options_to_display($component){
		$options = array();
		if( self::component_type_exists($component->type) ){
			$options = self::factory($component->type)->get_options_to_display($component);
		}
		return $options;
	}
	
	public static function get_ajax_action_html_answer($component_type,$action,$params){
		$result = '';
		if( self::component_type_exists($component_type) ){
			$result = self::factory($component_type)->get_ajax_action_html_answer($action,$params);
		}
		return $result;
	}
	
	public static function echo_form_fields($component_type,WppcComponent $component = null){
		if( self::component_type_exists($component_type) ){
			self::factory($component_type)->echo_form_fields($component);
		}
	}
	
	public static function echo_components_javascript(){
		foreach(array_keys(self::get_available_components_types()) as $component_type){
			$result = self::factory($component_type)->echo_form_javascript();
		}
	}
	
	public static function get_component_type_options_from_posted_form($component_type){
		if( self::component_type_exists($component_type) ){
			return self::factory($component_type)->get_options_from_posted_form();
		}
	}
	
	public static function component_type_exists($component_type){
		return array_key_exists($component_type,self::get_available_components_types());
	}
	
	public static function get_component_type($component_type_slug){
		$available_components_types = self::get_available_components_types();
		return self::component_type_exists($component_type_slug) ? $available_components_types[$component_type_slug] : null;
	}
	
	public static function get_label($component_type_slug){
		$ct = self::get_component_type($component_type_slug);
		return !empty($ct) ? $ct['label'] : '';
	}
	
	private static function factory($component_type){
		$words = explode('-',$component_type);
		$class = 'WppcComponentType';
		foreach($words as $word){
			$class .= ucfirst($word);
		}
		return class_exists($class) ? new $class : null;
	}
}