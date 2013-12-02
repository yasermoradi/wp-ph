<?php

require_once(dirname(__FILE__) .'/components-utils.php');
require_once(dirname(__FILE__) .'/components-bo-settings.php');
require_once(dirname(__FILE__) .'/components-storage.php');
require_once(dirname(__FILE__) .'/components-types.php');

class WppcComponents{
	
	public static function get_components_synchro_data(){
		$components_data = array();
		
		$components_data['navigation'] = WppcNavigationItemsStorage::get_navigation_items();

		$components = WppcComponentsStorage::get_components();
		$globals = array();
		$components_data['components'] = array();
		$components_data['globals'] = array();
		foreach($components as $component){
			$component_data = WppcComponentsTypes::get_component_data($component,$globals);
			$globals = $component_data['globals'];
			$components_data['components'][$component->slug] = $component_data['specific'];
			 
		}
		
		$components_data['globals'] = $globals;
				
		return $components_data;
	}
	
	public static function create_default_components(){
		/*
		$categories = get_terms('category',array('hierarchical'=>false));
		if( !is_wp_error($categories) ){
			$i = 1;
			foreach($categories as $category){
				$components[$i] = new WppcComponent($i,'categorie-'. $category->slug,$category->name,'posts-list',array('taxonomy'=>'category','slug'=>$category->slug));
				$i++;
			}
		}
		*/
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
