<?php

require_once(dirname(__FILE__) .'/components-utils.php');
require_once(dirname(__FILE__) .'/components-bo-settings.php');
require_once(dirname(__FILE__) .'/components-storage.php');
require_once(dirname(__FILE__) .'/components-types.php');

class WppcComponents{
	
	public static function get_app_components($app_post_id){
		return WppcComponentsStorage::get_components($app_post_id);
	}
	
	public static function get_components_synchro_data($app_id){
		
		$components = array();
		$components_data = array();
		
		$components_raw = WppcComponentsStorage::get_components($app_id);
		
		if( !empty($components_raw) ){
			$globals = array();
			foreach($components_raw as $component){
				$component_data = WppcComponentsTypes::get_component_data($component,$globals);
				$globals = $component_data['globals'];
				$components[$component->slug] = $component_data['specific'];
			}
			
			$navigation_items = WppcNavigationItemsStorage::get_navigation_indexed_by_components_slugs($app_id,true);
			
			$components_data['navigation'] = $navigation_items;
			$components_data['components'] = $components;
			$components_data['globals'] = $globals;
		}
		
		return $components_data;
	}
	
	public static function get_component_data($app_id,$component_slug,$args){
		$component_data = array();
		
		if( WppcComponentsStorage::component_exists($app_id, $component_slug) ){
			$component = WppcComponentsStorage::get_component($app_id, $component_slug);
			$component_data_raw = WppcComponentsTypes::get_component_data($component,array(),$args);
			$component_data['component'] = $component_data_raw['specific'];
			$component_data['globals'] = $component_data_raw['globals'];
		}
		
		return $component_data;
	}
	
}

class WppcComponent{

	protected $slug = '';
	protected $label = '';
	protected $type = '';
	protected $options = array();

	public function __construct($slug,$label,$type,$options=array()){
		$this->slug = $slug;
		$this->label = $label;
		$this->type = $type;
		$this->options = $options;
	}

	public function __get($attribute){
		return property_exists(__CLASS__, $attribute) ? $this->$attribute : null;
	}
	
	public function __isset($attribute){
		return isset($this->$attribute);
	}

}
