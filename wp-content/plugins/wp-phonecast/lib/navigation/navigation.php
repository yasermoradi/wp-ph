<?php
require_once(dirname(__FILE__) .'/navigation-items-storage.php');
require_once(dirname(__FILE__) .'/navigation-bo-settings.php');

class WppcNavigationItem{

	protected $component_slug = '';
	protected $position = 0;
	protected $options = array();

	public function __construct($component_slug,$position,$options=array()){
		$this->component_slug = $component_slug;
		$this->position = $position;
		$this->options = $options;
	}

	public function __get($attribute){
		return property_exists(__CLASS__, $attribute) ? $this->$attribute : null;
	}

	public function __isset($attribute){
		return isset($this->$attribute);
	}
	
	public function set_position($position){
		$this->position = $position;
	}

}