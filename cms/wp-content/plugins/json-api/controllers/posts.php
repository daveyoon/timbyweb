<?php
/*
Controller name: Posts
Controller description: Data manipulation methods for posts
*/

class JSON_API_Posts_Controller {

  public function create_post() {
    global $json_api;

    // does the author of this post have a valid token
    if (!$this->author_has_valid_token($_POST['author'], $_POST['token'])) {
      $json_api->error("Your token has expired, please try logging in again");
    }

    nocache_headers();
    $post = new JSON_API_Post();
    $id = $post->create($_REQUEST);
    if (empty($id)) {
      $json_api->error("Could not create post.");
    }
    return array(
      'post' => $post
    );
  }
  
  public function update_post() {
    global $json_api;

    if( !$this->user_is_valid($_POST['user_id'],$_POST['key'], $_POST['token']) ) {
      $json_api->error("Invalid user");
    }

    $post = $json_api->introspector->get_current_post();
    if (empty($post)) {
      $json_api->error("Post not found.");
    }

    nocache_headers();
    $post = new JSON_API_Post($post);
    $post->update($_REQUEST);
    return array(
      'post' => $post
    );
  }
  
  public function delete_post() {
    global $json_api;

    if( !$this->user_is_valid($_POST['user_id'],$_POST['key'], $_POST['token']) ) {
      $json_api->error("Invalid user");
    }

    $post = $json_api->introspector->get_current_post();
    if (empty($post)) {
      $json_api->error("Post not found.");
    }

    nocache_headers();
    return wp_trash_post($post->ID);
  }

  private function user_is_valid($userid, $apikey, $token){
    $user = get_user_by('id', $userid );
    if ( $user && 
        get_user_meta($user->ID,'api_key', true) == $apikey && 
        get_user_meta($user->ID,'api_token', true) == $token ) {
      return true;
    }
    return false;
  }

  private function author_has_valid_token($author, $token){
    $user = get_user_by('id', $author );
    if ( $user && get_user_meta($user->ID,'api_token', true) == $token ) {
      return true;
    }
    return false;
  }
}

?>
