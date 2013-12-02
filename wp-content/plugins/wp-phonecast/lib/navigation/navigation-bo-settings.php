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
			wp_enqueue_script('wppc_navigation_bo_settings_js',plugins_url('lib/navigation/navigation-bo-settings.js', dirname(dirname(__FILE__))),array('jquery'),WpPhoneCast::resources_version);
		}
	}
	
	public static function settings_panel(){
		self::handle_navigation_actions();
		self::handle_url_messages();
		$nav_items = WppcNavigationItemsStorage::get_navigation_items();
		?>
			<div class="wrap">
				<?php screen_icon('generic') ?>
				<h2><?php _e('Navigation') ?> <a href="#" class="add-new-h2" id="add-new-item"><?php _e('Add New') ?></a></h2>
				
				<?php settings_errors('wppc_navigation_settings') ?>
				
				<div id="new-item-form" style="display:none">
					<h4><?php _e('New navigation item') ?></h4>
					<?php self::echo_item_form() ?>
				</div>
				
				<table class="wp-list-table widefat fixed" style="margin-top:15px">
					<thead>
						<tr>
							<th><?php _e('Component') ?></th>
							<th><?php _e('Position') ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if( !empty($nav_items) ): ?>
						<?php foreach($nav_items as $component_slug => $nav_item): ?>
							<?php $alternate_class = $i++%2 ? '' : 'class="alternate"' ?>
							<?php $component = WppcComponentsStorage::get_component($component_slug) ?>
							<?php if( !empty($component) ): ?>
								<tr <?php echo $alternate_class ?>>
									<td>
										<?php echo $component->label ?> (<?php echo $component->slug ?>)
										<div class="row-actions">
											<span class="inline hide-if-no-js"><a class="editinline" href="#" data-edit-id="<?php echo $component_slug ?>"><?php _e('Edit') ?></a> | </span>
											<span class="trash"><a class="submitdelete" href="<?php echo add_query_arg(array('wppc_action'=>'delete','navigation_item_id'=>$component_slug)) ?>" class="delete_item"><?php _e('Delete')?></a></span>
										</div>
									</td>
									<td><?php echo $nav_item->position ?></td>
								</tr>
								<tr class="edit-item-wrapper" id="edit-item-wrapper-<?php echo $component_slug ?>" style="display:none" <?php echo $alternate_class ?>>
									<td colspan="2">
										<?php self::echo_item_form($nav_item) ?>
									</td>
								</tr>
							<?php endif ?>
						<?php endforeach ?>
					<?php else: ?>
						<tr><td colspan="2"><?php _e('No navigation item yet!') ?></td></tr>
					<?php endif ?>
					</tbody>
				</table>
			
			</div>
		<?php
	}
	
	private static function echo_item_form($navigation_item=null){
		
		$edit = !empty($navigation_item);
	
		$components = WppcComponentsStorage::get_components();
		
		$no_components = empty($components);
		
		$navigation_items = WppcNavigationItemsStorage::get_navigation_items();
		$navigation_items_slugs = array_keys($navigation_items);
		foreach($components as $k => $component){
			if( in_array($component->slug,$navigation_items_slugs) ){
				unset($components[$k]);
			}
		}
		
		if( !$edit && !empty($components) ){
			$first_component = reset($components);
			$navigation_item = new WppcNavigationItem('',0);
		}
		
		$navigation_item_id = $navigation_item->component_slug; 
		
		?>
		<?php if( !$no_components ): ?>
			<form method="post" action="<?php echo remove_query_arg('wppc_feedback',add_query_arg(array())) ?>" id="item-form-<?php echo $navigation_item_id ?>">
				<table class="form-table">
					<?php if( !$edit ): ?>
						<tr valign="top">
							<th scope="row"><?php _e('Component') ?></th>
					        <td>
					        	<select name="component_slug">
					        		<?php foreach($components as $component): ?>
					        			<?php $selected = $component->slug == $navigation_item_id ? 'selected="selected"' : '' ?>
					        			<option value="<?php echo $component->slug ?>" <?php echo $selected ?>><?php echo $component->label ?></option>
					        		<?php endforeach ?>
					        	</select>
					        </td>
					    </tr>
					<?php else: ?>
						<input type="hidden" name="component_slug" value="<?php echo $navigation_item_id ?>" />
					<?php endif ?>
				    <tr valign="top">
						<th scope="row"><?php _e('Position') ?></th>
				        <td><input type="text" name="position" value="<?php echo $navigation_item->position ?>" /></td>
				    </tr>
				</table>
				<input type="hidden" name="<?php echo $edit ? 'edit' : 'new' ?>_item_submitted" value="<?php echo $navigation_item_id ?>"/>
				<p class="submit">
					<a class="button-secondary alignleft cancel" title="<?php _e('Cancel') ?>" href="#" <?php echo !$edit ? 'id="cancel-new-item"' : '' ?>><?php _e('Cancel') ?></a>&nbsp;
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $edit ? __('Save Changes') : __('Save new item') ?>">
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
	
		if( isset($_POST['new_item_submitted']) || !empty($_POST['edit_item_submitted']) ){
	
			$edit = !empty($_POST['edit_item_submitted']);
			$edit_id = $edit ? $_POST['edit_item_submitted'] : 0;
	
			$nav_item_component_slug = $_POST['component_slug'];
			$nav_item_position = !empty($_POST['position']) && is_numeric($_POST['position']) ? $_POST['position'] : 0;
				
			if( empty($nav_item_component_slug) ){
				add_settings_error('wppc_navigation_settings','no-component-slug',__('You must choose a component!'),'error');
				return;
			}
				
			if( !WppcComponentsStorage::component_exists($nav_item_component_slug) ){
				add_settings_error('wppc_navigation_settings','component-doesnt-exist',__("This component doesn't exist!"),'error');
				return;
			}
			
			if( !$edit ){
				if( WppcNavigationItemsStorage::navigation_item_exists($nav_item_component_slug) ){
					add_settings_error('wppc_navigation_settings','already-exists',__('This component is already in navigation!'),'error');
					return;
				}
			}
				
			$navigation_item = new WppcNavigationItem($nav_item_component_slug, $nav_item_position);
			WppcNavigationItemsStorage::add_or_update_navigation_item($navigation_item);
				
			if( $edit ){
				add_settings_error('wppc_navigation_settings','edited',__('Navigation updated successfuly'),'updated');
			}else{
				add_settings_error('wppc_navigation_settings','created',__('New navigation item created successfuly'),'updated');
			}
				
	
		}elseif( !empty($_GET['wppc_action']) ){
			$action = $_GET['wppc_action'];
			switch($action){
				case 'delete':
					$id = $_GET['navigation_item_id'];
					if( $component = WppcNavigationItemsStorage::navigation_item_exists($id) ){
						if( !WppcNavigationItemsStorage::delete_navigation_item($id) ){
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