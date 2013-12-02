<?php
class WppcComponentsBoSettings{

	const menu_item = 'wppc_components_bo_settings';

	public static function hooks(){
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			add_action('admin_enqueue_scripts', array(__CLASS__,'admin_enqueue_scripts'));
			add_action('wp_ajax_wppc_update_component_type', array(__CLASS__,'ajax_update_component_type'));
			add_action('wp_ajax_wppc_update_component_options', array(__CLASS__,'ajax_update_component_options'));
		}
	}

	public static function add_settings_panels(){
		add_menu_page(__('WP PhoneCast'), __('WP PhoneCast'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
		add_submenu_page(self::menu_item,__('Components'), __('Components'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}
	
	public static function admin_enqueue_scripts(){
		global $pagenow, $typenow, $plugin_page;
		if( $pagenow == 'admin.php' && $plugin_page == self::menu_item ){
			wp_enqueue_script('wppc_components_bo_settings_js',plugins_url('lib/components/components-bo-settings.js', dirname(dirname(__FILE__))),array('jquery'),WpPhoneCast::resources_version);
		}
	}
	
	public static function settings_panel(){
		self::handle_component_actions();
		self::handle_url_messages();
		$components = WppcComponentsStorage::get_components();
		?>
		<div class="wrap">
			<?php screen_icon('generic') ?>
			<h2><?php _e('Components') ?> <a href="#" class="add-new-h2" id="add-new-component">Add New</a></h2>
			
			<?php settings_errors('wppc_components_settings') ?>
			
			<div id="new-component-form" style="display:none">
				<h4><?php _e('New Component') ?></h4>
				<?php self::echo_component_form() ?>
			</div>
			
			<table class="wp-list-table widefat fixed" style="margin-top:15px">
				<thead>
					<tr>
						<th><?php _e('Name') ?></th>
						<th><?php _e('Slug') ?></th>
						<th><?php _e('Type') ?></th>
						<th><?php _e('Options') ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if( !empty($components) ): ?>
					<?php $i = 0 ?>
					<?php foreach($components as $id => $component): ?>
						<?php $alternate_class = $i++%2 ? '' : 'class="alternate"' ?>
						<tr <?php echo $alternate_class ?>>
							<td>
								<?php echo $component->label ?>
								<div class="row-actions">
									<span class="inline hide-if-no-js"><a class="editinline" href="#" data-edit-id="<?php echo $id ?>"><?php _e('Edit') ?></a> | </span>
									<span class="trash"><a class="submitdelete" href="<?php echo add_query_arg(array('wppc_action'=>'delete','component_id'=>$id)) ?>" class="delete_component"><?php _e('Delete')?></a></span>
								</div>
							</td>
							<td><?php echo $component->slug ?></td>
							<td><?php echo WppcComponentsTypes::get_label($component->type) ?></td>
							<td>
								<?php $options = WppcComponentsTypes::get_options_to_display($component) ?>
								<?php foreach($options as $option): ?>
									<?php echo $option['label'] ?> : <?php echo $option['value'] ?><br/>
								<?php endforeach ?>
							</td>
						</tr>
						<tr class="edit-component-wrapper" id="edit-component-wrapper-<?php echo $id ?>" style="display:none" <?php echo $alternate_class ?>>
							<td colspan="4">
								<?php self::echo_component_form($component) ?>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr><td colspan="4"><?php _e('No Component yet!') ?></td></tr>
				<?php endif ?>
				</tbody>
			</table>
			
			<?php WppcComponentsTypes::echo_components_javascript() ?>
			
		</div>
		<?php
	}
	
	private static function echo_component_form($component=null){
		
		$edit = !empty($component);
	
		if( !$edit ){
			$component = new WppcComponent('','','posts-list');
		}
		
		$component_id = $edit ? WppcComponentsStorage::get_component_id($component) : '0';
	
		$components_types = WppcComponentsTypes::get_available_components_types();
	
		?>
		<form method="post" action="<?php echo remove_query_arg('wppc_feedback',add_query_arg(array())) ?>" id="component-form-<?php echo $component_id ?>">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Component label') ?></th>
			        <td><input type="text" name="component_label" value="<?php echo $component->label ?>" /></td>
			    </tr>
			    <tr valign="top">
					<th scope="row"><?php _e('Component slug') ?></th>
			        <td><input type="text" name="component_slug" value="<?php echo $component->slug ?>" /></td>
			    </tr>
		        <tr valign="top">
		        	<th scope="row"><?php _e('Component type') ?></th>
		        	<td>
		        		<select type="text" name="component_type" class="component-type">
		        			<?php foreach($components_types as $type => $data): ?>
		        				<?php $selected = $type == $component->type ? 'selected="selected"' : '' ?>
		        				<option value="<?php echo $type ?>" <?php echo $selected ?> ><?php echo $data['label'] ?></option>
		        			<?php endforeach ?>
		        		</select>
		        	</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row"><?php _e('Component options') ?></th>
		        	<td class="component-options-target">
		        		<?php WppcComponentsTypes::echo_form_fields($component->type,$edit ? $component : null) ?>
		        	</td>
		        </tr>
			</table>
			<input type="hidden" name="<?php echo $edit ? 'edit' : 'new' ?>_component_submitted" value="<?php echo $component_id ?>"/>
			<p class="submit">
				<a class="button-secondary alignleft cancel" title="<?php _e('Cancel') ?>" href="#" <?php echo !$edit ? 'id="cancel-new-component"' : '' ?>><?php _e('Cancel') ?></a>&nbsp;
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $edit ? __('Save Changes') : 'Save new component'?>">
			</p>
		</form>
		<?php 
	}
	
	private static function handle_component_actions(){
		//TODO : add nonce! 
		
		if( isset($_POST['new_component_submitted']) || !empty($_POST['edit_component_submitted']) ){
				
			$edit = !empty($_POST['edit_component_submitted']);
			$edit_id = $edit ? $_POST['edit_component_submitted'] : 0;
		
			$component_label = trim($_POST['component_label']);
			$component_slug = trim($_POST['component_slug']);
			$component_type = $_POST['component_type'];
			
			if( empty($component_label) ){
				add_settings_error('wppc_components_settings','no-component-name',__('You must provide a name for the component!'),'error');
				return;
			}
			
			if( is_numeric($component_slug) ){
				add_settings_error('wppc_components_settings','slug-numeric',__("The component slug can't be numeric."),'error');
				return;
			}
		
			if( !$edit ){
				if( WppcComponentsStorage::component_exists($component_slug) ){
					add_settings_error('wppc_components_settings','already-exists',sprintf(__('A component with the slug "%s" already exists!'),$component_slug),'error');
					return;
				}
			}
			
			$component_options = WppcComponentsTypes::get_component_type_options_from_posted_form($component_type);
				
			$component = new WppcComponent($component_slug, $component_label, $component_type, $component_options);
			WppcComponentsStorage::add_or_update_component($component,$edit_id);
			
			if( $edit ){
				add_settings_error('wppc_components_settings','edited',sprintf(__('Component "%s" updated successfuly'),$component_label),'updated');
			}else{
				add_settings_error('wppc_components_settings','created',sprintf(__('Component "%s" created successfuly'),$component_label),'updated');
			}
			
				
		}elseif( !empty($_GET['wppc_action']) ){
			$action = $_GET['wppc_action'];
			switch($action){
				case 'delete':
					$id = $_GET['component_id'];
					if(  is_numeric($id) ){
						if( $component = WppcComponentsStorage::component_exists($id) ){
							if( !WppcComponentsStorage::delete_component($id) ){
								$message = 1;
							}else{
								$message = 3;
							}
						}else{
							$message = 2;
						}
						wp_redirect(remove_query_arg(array('wppc_action','component_id'),add_query_arg(array('wppc_feedback'=>$message))));
					}
					break;
			}
		}
	}
	
	public static function ajax_update_component_options(){
		
		//TODO : nonce!
		$component_type = $_POST['component_type'];
		$action = $_POST['wppc_action'];
		$params = $_POST['params'];
		
		echo WppcComponentsTypes::get_ajax_action_html_answer($component_type, $action, $params);
		exit();
	}
	
	public static function ajax_update_component_type(){
	
		//TODO : nonce!
		$component_type = $_POST['component_type'];
		
		WppcComponentsTypes::echo_form_fields($component_type);
		exit();
	}
	
	private static function handle_url_messages(){
		if( !empty($_GET['wppc_feedback']) && is_numeric($_GET['wppc_feedback']) ){
			$messages = self::get_feedback_messages();
			$msg_id = $_GET['wppc_feedback'];
			if( array_key_exists($msg_id,$messages) ){
				add_settings_error('wppc_components_settings','url-message-'. $msg_id,$messages[$msg_id]['message'],$messages[$msg_id]['type']);
			}
		} 
	}
	
	private static function get_feedback_messages(){
		$messages = array(
			1 => array('message'=>__('Could not delete component'), 'type' => 'error'),
			2 => array('message'=>__('Component to delete not found'), 'type' => 'error'),
			3 => array('message'=>__('Component deleted successfuly'), 'type' => 'updated'),
		);
		return $messages;
	}
}

WppcComponentsBoSettings::hooks();