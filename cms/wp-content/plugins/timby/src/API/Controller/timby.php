<?php

require_once ABSPATH . 'wp-content/plugins/json-api/controllers/core.php';
require_once ABSPATH . 'wp-content/plugins/json-api/models/post.php';

class JSON_API_Timby_Controller extends JSON_API_Core_Controller
{
    public function get_all_terms_for_taxonomy()
    {
        global $json_api;

        $args = array();

        if (!$this->iskeyvalid($json_api->query->key, $json_api->query->user_id)) {
            $json_api->error("Invalid key, please try logging in again");
        }

        if (!$this->istokenvalid($json_api->query->token, $json_api->query->user_id)) {
            $json_api->error("Your token has expired, please try logging in again");
        }

        if (!empty($json_api->query->parent)) {
            $args['parent'] = $json_api->query->parent;
        }
        // default get terms for 'category' taxonomy
        if (empty($json_api->query->taxonomy)) {
            $json_api->query->taxonomy = 'category';
        }

        $args['hide_empty'] = 0; //do not hide empty args
        @$json_api->introspector = new JSON_API_Timby_Introspector();
        $terms = $json_api->introspector->get_terms_for($json_api->query->taxonomy, $args);

        return array(
            'count' => count($terms),
            'terms' => $terms
        );
    }

    public function get_page_index()
    {
        global $json_api;
        $pages = array();
        $post_type = $json_api->query->post_type ? $json_api->query->post_type : 'page';

        // Thanks to blinder for the fix!
        $numberposts = empty($json_api->query->count) ? -1 : $json_api->query->count;
        $wp_posts = get_posts(array(
            'post_type' => $post_type,
            'post_parent' => 0,
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'numberposts' => $numberposts
        ));
        foreach ($wp_posts as $wp_post) {
            $pages[] = new JSON_API_Timby_Post($wp_post);
        }
        foreach ($pages as $page) {
            $json_api->introspector->attach_child_posts($page);
        }
        return array(
            'pages' => $pages
        );
    }

    public function get_post()
    {
        global $json_api, $post;
        $post = $json_api->introspector->get_current_post();
        if ($post) {
            $previous = get_adjacent_post(false, '', true);
            $next = get_adjacent_post(false, '', false);
            $response = array(
                'post' => new JSON_API_Timby_Post($post)
            );
            if ($previous) {
                $response['previous_url'] = get_permalink($previous->ID);
            }
            if ($next) {
                $response['next_url'] = get_permalink($next->ID);
            }
            return $response;
        } else {
            $json_api->error("Not found.");
        }
    }

    private function iskeyvalid($key, $user_id)
    {
        if ($user = get_user_by('id', $user_id)) {
            return (get_user_meta($user->ID, 'api_key', true) == $key);
        }
        return false;
    }

    private function istokenvalid($token, $user_id)
    {
        if ($user = get_user_by('id', $user_id)) {
            return (get_user_meta($user->ID, 'api_token', true) == $token);
        }
        return false;
    }
}
