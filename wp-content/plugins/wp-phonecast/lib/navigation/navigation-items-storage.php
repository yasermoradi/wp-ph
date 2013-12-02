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
	
	public static function navigation_item_exists($navigation_item_id){
		$navigation_items = self::get_navigation_items_raw();
		if( !empty($navigation_items) ){
			foreach($navigation_items as $_navigation_item_id => $item){
				if( $_navigation_item_id == $navigation_item_id ){
					return true;
				}
			}
		}
		return false;
	}
	
	public static function add_or_update_navigation_item(WppcNavigationItem $navigation_item){
	
		if( WppcComponentsStorage::component_exists($navigation_item->component_slug) ){
			$navigation_items = self::get_navigation_items_raw();
			$navigation_items[$navigation_item->component_slug] = $navigation_item;
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
			foreach($navigation_items as $component_slug => $item){
				$to_order[$component_slug] = $item->position;
			}
			asort($to_order);
			$i=1;
			foreach(array_keys($to_order) as $component_slug){
				$nav_item = $navigation_items[$component_slug];
				$nav_item->set_position($i);
				$ordered[$component_slug] = $nav_item;
				$i++;
			}
		}
		
		return $ordered;
	}
	
}