<?php
class WppcNavigationItemsStorage{
	
	const option_id = 'wppc-navigation-items';
	
	public static function get_navigation_items(){
		$navigation_items = self::get_navigation_items_raw();
		$navigation_items = self::order_items($navigation_items);
		return !empty($navigation_items) ? $navigation_items : array();
	}
	
	private static function get_navigation_items_raw(){
		$navigation_items = get_option( self::option_id );
		return !empty($navigation_items) ? $navigation_items : array();
	}
	
	public static function navigation_item_exists_by_component($component_id){
		$navigation_items = self::get_navigation_items_raw();
		if( !empty($navigation_items) ){
			foreach($navigation_items as $navigation_item_id => $item){
				if( $item->component_id == $component_id ){
					return $navigation_item_id;
				}
			}
		}
		return false;
	}
	
	public static function navigation_item_exists($navigation_item_id){
		$navigation_items = self::get_navigation_items_raw();
		return !empty($navigation_items) ? array_key_exists($navigation_item_id, $navigation_items) : false;
	}
	
	public static function add_or_update_navigation_item(WppcNavigationItem $navigation_item){
	
		if( WppcComponentsStorage::component_exists($navigation_item->component_id) ){
			$navigation_items = self::get_navigation_items_raw();
			if( !($navigation_item_id = self::navigation_item_exists_by_component($navigation_item->component_id)) ){
				$navigation_item_id = self::generate_navigation_item_id();
			}
			$navigation_items[$navigation_item_id] = $navigation_item;
			self::update_navigation_items($navigation_items);
		}
	
	}
	
	public static function delete_navigation_item($navigation_item_id){
		$deleted_ok = true;
		$navigation_items = self::get_navigation_items_raw();
		if( array_key_exists($navigation_item_id,$navigation_items) ){
			unset($navigation_items[$navigation_item_id]);
			self::update_navigation_items($navigation_items);
		}else{
			$deleted_ok = false;
		}
		return $deleted_ok;
	}
	
	public static function get_navigation_indexed_by_components_slugs($only_nav_items_options=false){
		$navigation_indexed_by_components = array();
		$navigation_items = self::get_navigation_items_raw();
		if( !empty($navigation_items) ){
			$navigation_items = self::order_items($navigation_items);
			foreach($navigation_items as $nav_item_id => $nav_item){
				if( WppcComponentsStorage::component_exists($nav_item->component_id) ){
					$component = WppcComponentsStorage::get_component($nav_item->component_id);
					$navigation_indexed_by_components[$component->slug] = $only_nav_items_options ? $nav_item->options : $nav_item;
				}
			}
		}
		return $navigation_indexed_by_components;
	}
	
	public static function get_navigation_components(){
		$components = array();
		$navigation_items = self::get_navigation_items_raw();
		if( !empty($navigation_items) ){
			$navigation_items = self::order_items($navigation_items);
			foreach($navigation_items as $nav_item_id => $nav_item){
				if( WppcComponentsStorage::component_exists($nav_item->component_id) ){
					$components[$nav_item->component_id] = WppcComponentsStorage::get_component($nav_item->component_id);
				}
			}
		}
		return $components;
	}
	
	public static function component_in_navigation($component_id){
		$navigation_items = self::get_navigation_items_raw();
		if( !empty($navigation_items) ){
			foreach($navigation_items as $nav_item_id => $nav_item){
				if( $nav_item->component_id == $component_id ){
					return $nav_item_id;
				}
			}
		}
		return false;
	}
	
	public static function get_navigation_item_id($navigation_item){
		$nav_item_id = self::navigation_item_exists_by_component($navigation_item->component_id);
		return $nav_item_id === false ? 0 : $nav_item_id;
	}
	
	public static function update_items_positions($items_positions){
		if( !empty($items_positions) ){
			$navigation_items = self::get_navigation_items_raw();
			foreach($items_positions as $nav_item_id => $nav_item_position){
				if( array_key_exists($nav_item_id,$navigation_items) ){
					$navigation_items[$nav_item_id]->set_position($nav_item_position);
				}
			}
			self::update_navigation_items($navigation_items);
		}
	}
	
	private static function update_navigation_items($navigation_items){
		$navigation_items = self::order_items($navigation_items);
		$current_navigation_items = get_option( self::option_id );
		if ( $current_navigation_items !== false ) {
			update_option( self::option_id, $navigation_items );
		} else {
			add_option( self::option_id, $navigation_items, '', 'no' );
		}
	}
	
	private static function order_items($navigation_items){
		$ordered = array();
		
		if( !empty($navigation_items) ){
			$to_order = array();
			foreach($navigation_items as $navigation_item_id => $item){
				$to_order[$navigation_item_id] = $item->position;
			}
			asort($to_order);
			$i=1;
			foreach(array_keys($to_order) as $navigation_item_id){
				$nav_item = $navigation_items[$navigation_item_id];
				$nav_item->set_position($i);
				$ordered[$navigation_item_id] = $nav_item;
				$i++;
			}
		}
		
		return $ordered;
	}
	
	private static function generate_navigation_item_id(){
		$nav_items = self::get_navigation_items_raw();
		$id = 1;
		if( !empty($nav_items) ){
			$id = max(array_keys($nav_items)) + 1;
		}
		return $id;
	}
	
}