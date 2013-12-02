<?php
class WppcComponentsStorage{
	
	const option_id = 'wppc-components';
	
	public static function get_components(){
		$components = get_option( self::option_id );
		return !empty($components) ? $components : array();
	}
	
	public static function get_component($component_slug_or_id){
		$components = self::get_components();
		
		$is_slug = !is_numeric($component_slug_or_id);
		
		foreach($components as $id => $component){
			if( $is_slug && $component_slug_or_id == $component->slug || $component_slug_or_id == $id){
				return $component;
			}
		}
		
		return null;
	}
	
	public static function component_exists($component_slug_or_id){
		$components = self::get_components();
		
		$is_slug = !is_numeric($component_slug_or_id);
		
		foreach($components as $id => $component){
			if( $is_slug && $component_slug_or_id == $component->slug || $component_slug_or_id == $id){
				return $id;
			}
		}
		return false;
	}
	
	public static function add_or_update_component(WppcComponent $component,$component_id=0){
		
		if( empty($component_id) ){
			$component_id = self::generate_component_id();
		}else{
			$component_id = self::component_exists($component_id);
		}
		
		if( !empty($component_id) ){
			$components = self::get_components();
			$components[$component_id] = $component;
			self::update_components($components);
		}
	}
	
	public static function delete_component($component_id){
		$deleted_ok = true;
		$components = self::get_components();
		if( array_key_exists($component_id,$components) ){
			unset($components[$component_id]);
			self::update_components($components);
		}else{
			$deleted_ok = false;
		}
		return $deleted_ok;
	}
	
	public static function get_component_id(WppcComponent $component){
		return self::component_exists($component->slug);
	}
	
	private static function update_components($components){
		$current_components = get_option( self::option_id );
		if ( $current_components !== false ) {
			update_option( self::option_id, $components );
		} else {
			add_option( self::option_id, $components, '', 'no' );
		}
	}
	
	private static function generate_component_id(){
		$components = self::get_components();
		$id = 1;
		if( !empty($components) ){
			$id = max(array_keys($components)) + 1;
		}
		return $id;
	}
}