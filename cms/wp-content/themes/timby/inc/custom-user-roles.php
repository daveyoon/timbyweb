<?php 
/*------------------------------------------------------------------
[Custom User Roles]

* Timby Mobile App User
-------------------------------------------------------------------*/

add_action( 'init', 'register_custom_roles' );
function register_custom_roles(){

  add_role( 'timbymobileapp', __('Timby Mobile App', 'timbyweb'), array(
    'read' => true,
    'publish_posts' => false,
    'delete_posts' => false
  ));
 }

/*
  Show the Access keys field for 
  users with the timbymobileapp role
 */
add_action( 'show_user_profile', 'add_custom_profile_fields' );
add_action( 'edit_user_profile', 'add_custom_profile_fields' );
function add_custom_profile_fields( $user ) {
  if( in_array('timbymobileapp', $user->roles ) ) {
?>
    <h3>Access Keys</h3>
    <table class="form-table">
        <tr>
            <th><label for="api_key">API KEY</label></th>
            <td>
                <input id="api_key" name="api_key" type="text" value="6b239b3568b209" hidden />
                <strong><?php echo get_the_author_meta( 'api_key',  $user->ID) ?></strong>
            </td>
        </tr>
    </table>
<?php
  }
}

add_action( 'personal_options_update', 'save_module_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_module_user_profile_fields' );

function save_module_user_profile_fields( $user_id ) 
{
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }else{
        if(isset($_POST['api_key'])){
            update_usermeta( $user_id, 'api_key', $_POST['api_key'] );
        }else{
            delete_usermeta($user_id, 'api_key');
        }
    }
}

