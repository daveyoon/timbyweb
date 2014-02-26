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

  /**
   * Checks the token status
   * @return array
   */
  public function tokenstatus(){
    if( $user = get_user_by('id', $_POST['user_id']) ) {
      if(get_user_meta( $user->ID, 'api_token', true) == $_POST['token']) {
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