<?php 
/*
Controller name: Users
Controller description: Data manipulation methods for users
*/
class JSON_API_Users_Controller {

  /**
   * Requires username and password
   * @return [type] [description]
   */
  public function authenticate(){
    if( $user = $this->user_exists($_POST['username'], $_POST['password']) ) {
      $token = $this->create_token();
      update_user_meta( $user->ID, 'api_token', $token);

      return array(
        'user_id'     => $user->ID,
        'api_key'     => get_user_meta($user->ID, 'api_key', true),
        'api_token'   => get_user_meta( $user->ID, 'api_token', true)
      );

    }
  }

  public function update(){
    global $json_api;

    if (!$this->token_valid($_POST['user_id'], $_POST['token'])) {
      $json_api->error("Your token has expired, please try logging in again");
    }

    $userdata = array(
      'ID'        => $_POST['user_id'],
      'name'      => $_POST['username'],
      'user_pass' => $_POST['password']
    );

    if( !is_wp_error( $ID = wp_update_user( $userdata ) ) ) {
      return array(
        'id'     => $ID
      );
    }
  }

  /**
   * Check the token status against the user id
   * 
   * @param  integer $user_id
   * @param  string $token
   * @return [type] [description]
   */
  private function token_valid( $user_id, $token){
    return (get_user_meta( $user_id, 'api_token', true) === $token);
  }

  /**
   * Checks the token status
   * @return array
   */
  public function tokenstatus(){
    if( $user = get_user_by('id', $_POST['user_id']) ) {
      if( $this->token_valid( $user->data->ID, $_POST['token'] ) ) {
        return array('token_status' => 'valid');
      } else {
        return array('token_status' => 'invalid');
      }
    }
  }

  /**
   * Log the user out
   * doesn't do a delete of the key but sets it to null
   * @return bool
   */
  public function logout(){
    if( $user = $this->user_exists($_POST['username'], $_POST['password']) ) {
      return update_user_meta( $user->ID, 'api_token', ''); //blank api_token
    } else {
      return false;
    }
  }

  /**
   * Check if the user exists
   * 
   * @param  string $username
   * @param  string $password
   * @return mixed $user object if user exists, false if the user was not found
   */
  private function user_exists($username, $password) {
    $user = get_user_by('login', $username );
    return ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID) ) ? $user : false;
  }



  private function create_token() {
    $token = md5(time());
    $token_len = strlen($token);
    $token_half = ceil($token_len / 2);
    $token = substr($token, $token_half, $token_half - 2);
    return $token;
  }

}