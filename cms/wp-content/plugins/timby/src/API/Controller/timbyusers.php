<?php
/*
Controller name: Timby Users
Controller description: Data manipulation methods for users
*/

require_once ABSPATH . 'wp-content/plugins/json-api/controllers/users.php';

class JSON_API_TimbyUsers_Controller extends JSON_API_Users_Controller
{
    public function update()
    {
        global $json_api;

        if (!$this->token_valid($_POST['user_id'], $_POST['token'])) {
            $json_api->error("Your token has expired, please try logging in again");
        }

        $userdata = array(
            'ID' => $_POST['user_id'],
            'name' => $_POST['username'],
            'user_pass' => $_POST['password']
        );

        if (!is_wp_error($ID = wp_update_user($userdata))) {
            return array(
                'id' => $ID
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
    private function token_valid($user_id, $token)
    {
        return (get_user_meta($user_id, 'api_token', true) === $token);
    }

    /**
     * Checks the token status
     * @return array
     */
    public function tokenstatus()
    {
        if ($user = get_user_by('id', $_POST['user_id'])) {
            if ($this->token_valid($user->data->ID, $_POST['token'])) {
                return array('token_status' => 'valid');
            } else {
                return array('token_status' => 'invalid');
            }
        }
    }
}
