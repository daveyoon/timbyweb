<?php
/*
Controller name: Timby Posts
Controller description: Data manipulation methods for posts
*/

require_once ABSPATH . 'wp-content/plugins/json-api/controllers/posts.php';

class JSON_API_TimbyPosts_Controller extends JSON_API_Posts_Controller
{
    public function create_post()
    {
        global $json_api;

        // does the author of this post have a valid token
        if (!$this->author_has_valid_token($_POST['author'], $_POST['token'])) {
            $json_api->error("Your token has expired, please try logging in again");
        }

        nocache_headers();
        $post = new JSON_API_Timby_Post();
        $id = $post->create($_REQUEST);
        if (empty($id)) {
            $json_api->error("Could not create post.");
        }
        return array(
            'post' => $post
        );
    }

    public function update_post()
    {
        global $json_api;

        if (!$this->user_is_valid($_POST['user_id'], $_POST['key'], $_POST['token'])) {
            $json_api->error("Invalid user");
        }

        $post = $json_api->introspector->get_current_post();
        if (empty($post)) {
            $json_api->error("Post not found.");
        }

        nocache_headers();
        $post = new JSON_API_Timby_Post($post);
        $post->update($_REQUEST);
        return array(
            'post' => $post
        );
    }

    public function create_attachment()
    {
        global $json_api;

        if (!$this->author_has_valid_token($_POST['author'], $_POST['token'])) {
            $json_api->error("Your token has expired, please try logging in again");
        }

        $post_data = array(
            'post_parent' => $_POST['id'],
            'post_title' => $_POST['title'],
            'post_content' => $_POST['content']
        );

        if (!empty($_FILES['attachment'])) {

            include_once ABSPATH . '/wp-admin/includes/file.php';
            include_once ABSPATH . '/wp-admin/includes/media.php';
            include_once ABSPATH . '/wp-admin/includes/image.php';
            $attachment_id = media_handle_upload('attachment', $_POST['id'], $post_data);
            unset($_FILES['attachment']);

            // set the media type as a meta
            update_post_meta($attachment_id, '_media_type', $_POST['media_type']);

            return array(
                'id' => $attachment_id
            );

        } else {
            $json_api->error("Please attach a file to upload.");
        }

    }

    private function user_is_valid($userid, $apikey, $token)
    {
        $user = get_user_by('id', $userid);
        if ($user &&
            get_user_meta($user->ID, 'api_key', true) == $apikey &&
            get_user_meta($user->ID, 'api_token', true) == $token
        ) {
            return true;
        }
        return false;
    }

    private function author_has_valid_token($author, $token)
    {
        $user = get_user_by('id', $author);
        if ($user && get_user_meta($user->ID, 'api_token', true) == $token) {
            return true;
        }
        return false;
    }
}
