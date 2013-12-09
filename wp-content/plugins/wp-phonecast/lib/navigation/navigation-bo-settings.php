<?php

class wppc_navigation{
	
	const menu_item = 'wppc_navigation_bo_settings';
	
	public static function hooks(){
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			add_action('admin_enqueue_scripts', array(__CLASS__,'admin_enqueue_scripts'));
		}
	}
	
	public static function add_settings_panels(){
		add_submenu_page(WppcComponentsBoSettings::menu_item,__('Navigation'), __('Navigation'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}
	
	public static function admin_enqueue_scripts(){
		global $pagenow, $typenow, $plugin_page;
		if( $pagenow == 'admin.php' && $plugin_page == self::menu_item ){
			wp_enqueue_script('wppc_navigation_bo_settings_js',plugins_url('lib/navigation/navigation-bo-settings.js', dirname(dirname(__FILE__))),array('jquery','jquery-ui-sortable'),WpPhoneCast::resources_version);
		}
	}
	
	public static function settings_panel(){
		self::handle_navigation_actions();
		self::handle_url_messages();
		$nav_items = WppcNavigationItemsStorage::get_navigation_items();
		
		$nav_items_positions = array();
		?>
			<div class="wrap">
				<?php screen_icon('generic') ?>
				<h2><?php _e('Navigation') ?> <a href="#" class="add-new-h2" id="add-new-item"><?php _e('Add new component to navigation') ?></a></h2>
				
				<?php settings_errors('wppc_navigation_settings') ?>
				
				<div id="new-item-form" style="display:none">
					<h4><?php _e('New navigation item') ?></h4>
					<?php self::echo_item_form() ?>
				</div>
				
				<table class="wp-list-table widefat fixed" id="navigation-items-table">
					<thead>
						<tr>
							<th><?php _e('Navigation components') ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if( !empty($nav_items) ): ?>
						<?php $i = 0 ?>
						<?php foreach($nav_items as $nav_item_id => $nav_item): ?>
							<?php $alternate_class = $i++%2 ? '' : 'alternate' ?>
							<?php $component = WppcComponentsStorage::get_component($nav_item->component_id) ?>
							<?php if( !empty($component) ): ?>
								<?php $nav_items_positions[$nav_item_id] = $nav_item->position ?>
								<tr class="ui-state-default <?php echo $alternate_class ?>" data-id="<?php echo $nav_item_id ?>">
									<td>
										<?php echo $component->label ?> (<?php echo $component->slug ?>)
										<div class="row-actions">
											<span class="trash"><a class="submitdelete" href="<?php echo add_query_arg(array('wppc_action'=>'delete','navigation_item_id'=>$nav_item_id)) ?>" class="delete_item"><?php _e('Delete')?></a></span>
										</div>
									</td>
								</tr>
							<?php endif ?>
						<?php endforeach ?>
					<?php else: ?>
						<tr><td><?php _e('No navigation item yet!') ?></td></tr>
					<?php endif ?>
					</tbody>
				</table>
				
				<?php if( !empty($nav_items) && !empty($nav_items_positions) ): ?>
					<form  method="post" action="<?php echo remove_query_arg('wppc_feedback',add_query_arg(array())) ?>" >
						<input type="hidden" name="order-items" value="1" />
						<?php foreach($nav_items_positions as $nav_item_id => $nav_item_position): ?>
							<input type="hidden" id="position-<?php echo $nav_item_id ?>" name="positions[<?php echo $nav_item_id ?>]" value="<?php echo $nav_item_position ?>" />
						<?php endforeach ?>
						<input type="submit" name="submit" id="submit-navigation-orger" class="button button-primary" value="<?php _e('Save navigation order') ?>">
					</form>
				<?php endif ?>
				
				<style>
					#navigation-items-table{ margin:15px 0 }
					.ui-sortable-placeholder{ height: 60px; border-bottom:1px solid #dfdfdf; }
					.ui-sortable-helper{ width:100%; background:#fff; }
					.ui-state-default{ cursor:move }
				</style>
			</div>
		<?php
	}
	
	private static function echo_item_form(){
		
		$components = WppcComponentsStorage::get_components();
		
		$no_components = empty($components);
		
		foreach(array_keys($components) as $component_id){
			if( WppcNavigationItemsStorage::component_in_navigation($component_id) ){
				unset($components[$component_id]);
			}
		}
		
		$navigation_item_id = 0;
		if( !$no_components ){
			$navigation_item = new WppcNavigationItem(0,0);
		}
		
		?>
		<?php if( !$no_components ): ?>
			<form method="post" action="<?php echo remove_query_arg('wppc_feedback',add_query_arg(array())) ?>" id="item-form-<?php echo $navigation_item_id ?>">
				<table class="form-table">
					<tr valign="top">
						<?php if( !empty($components) ): ?>
							<th scope="row"><?php _e('Component') ?></th>
					        <td>
					        	<select name="component_id">
					        		<?php foreach($components as $component_id => $component): ?>
					        			<option value="<?php echo $component_id ?>"><?php echo $component->label ?></option>
					        		<?php endforeach ?>
					        	</select>
					        </td>
					     <?php else: ?>
					     	<td>
				        		<?php _e('All components are already in navigation!') ?>
				        	</td>
				        <?php endif ?>
				    </tr>
				</table>
				<input type="hidden" name="position" value="0" />
				<input type="hidden" name="new_item_submitted" value="1"/>
				<p class="submit">
					<a class="button-secondary alignleft cancel" title="<?php _e('Cancel') ?>" href="#" id="cancel-new-item"><?php _e('Cancel') ?></a>&nbsp;
					<?php if( !empty($components) ): ?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Add component to navigation') ?>">
					<?php endif ?>
				</p>
			</form>
		<?php else: ?>
			<div>
				<?php _e('No component found!') ?> : <a href="<?php echo admin_url('admin.php?page=wppc_components_bo_settings') ?>"><?php _e('Please create a component!') ?></a>
			</div>
		<?php endif ?>
		<?php 
	}
	
	private static function handle_navigation_actions(){
		//TODO : add nonce!
	
		if( isset($_POST['new_item_submitted']) && $_POST['new_item_submitted'] == 1 ){
	
			$nav_item_component_id = $_POST['component_id'];
			$nav_item_position = !empty($_POST['position']) && is_numeric($_POST['position']) ? $_POST['position'] : 0;
				
			if( empty($nav_item_component_id) ){
				add_settings_error('wppc_navigation_settings','no-component-slug',__('You must choose a component!'),'error');
				return;
			}
				
			if( !WppcComponentsStorage::component_exists($nav_item_component_id) ){
				add_settings_error('wppc_navigation_settings','component-doesnt-exist',__("This component doesn't exist!"),'error');
				return;
			}
			
			if( WppcNavigationItemsStorage::navigation_item_exists_by_component($nav_item_component_id) ){
				add_settings_error('wppc_navigation_settings','already-exists',__('This component is already in navigation!'),'error');
				return;
			}
				
			$navigation_item = new WppcNavigationItem($nav_item_component_id, $nav_item_position);
			WppcNavigationItemsStorage::add_or_update_navigation_item($navigation_item);
				
			add_settings_error('wppc_navigation_settings','created',__('New navigation item created successfuly'),'updated');
	
		}elseif( isset($_POST['order-items']) && $_POST['order-items'] == 1 ){

			if( !empty($_POST['positions']) && is_array($_POST['positions']) ){
				WppcNavigationItemsStorage::update_items_positions($_POST['positions']);
				add_settings_error('wppc_navigation_settings','order-updated',__('Navigation order updated successfuly'),'updated');
			}
		
		}elseif( !empty($_GET['wppc_action']) ){
			$action = $_GET['wppc_action'];
			switch($action){
				case 'delete':
					$nav_item_id = $_GET['navigation_item_id'];
					if( WppcNavigationItemsStorage::navigation_item_exists($nav_item_id) ){
						if( !WppcNavigationItemsStorage::delete_navigation_item($nav_item_id) ){
							$message = 1;
						}else{
							$message = 3;
						}
					}else{
						$message = 2;
					}
					wp_redirect(remove_query_arg(array('wppc_action','navigation_item_id'),add_query_arg(array('wppc_feedback'=>$message))));
					break;
			}
		}
	}
	
	private static function handle_url_messages(){
		if( !empty($_GET['wppc_feedback']) && is_numeric($_GET['wppc_feedback']) ){
			$messages = self::get_feedback_messages();
			$msg_id = $_GET['wppc_feedback'];
			if( array_key_exists($msg_id,$messages) ){
				add_settings_error('wppc_navigation_settings','url-message-'. $msg_id,$messages[$msg_id]['message'],$messages[$msg_id]['type']);
			}
		}
	}
	
	private static function get_feedback_messages(){
		$messages = array(
				1 => array('message'=>__('Could not delete navigation item'), 'type' => 'error'),
				2 => array('message'=>__('Navigation item to delete not found'), 'type' => 'error'),
				3 => array('message'=>__('Navigation item deleted successfuly'), 'type' => 'updated'),
		);
		return $messages;
	}
	
}

wppc_navigation::hooks();