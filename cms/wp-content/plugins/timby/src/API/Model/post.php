<?php

require_once ABSPATH . 'wp-content/plugins/json-api/models/post.php';

class JSON_API_Timby_Post extends JSON_API_Post
{
    function save($values = null)
    {
        global $json_api, $user_ID;

        $wp_values = array();

        if (!empty($values['id'])) {
            $wp_values['ID'] = $values['id'];
        }

        if (!empty($values['type'])) {
            $wp_values['post_type'] = $values['type'];
        }

        if (!empty($values['status'])) {
            $wp_values['post_status'] = $values['status'];
        }

        if (!empty($values['title'])) {
            $wp_values['post_title'] = $values['title'];
        }

        if (!empty($values['content'])) {
            $wp_values['post_content'] = $values['content'];
        }

        if (!empty($values['author'])) {
            $author = $json_api->introspector->get_author_by_login($values['author']);
            $wp_values['post_author'] = $author->id;
        }

        if (isset($values['categories'])) {
            $categories = explode(',', $values['categories']);
            foreach ($categories as $category_slug) {
                $category_slug = trim($category_slug);
                $category = $json_api->introspector->get_category_by_slug($category_slug);
                if (empty($wp_values['post_category'])) {
                    $wp_values['post_category'] = array($category->id);
                } else {
                    array_push($wp_values['post_category'], $category->id);
                }
            }
        }

        if (isset($values['tags'])) {
            $tags = explode(',', $values['tags']);
            foreach ($tags as $tag_slug) {
                $tag_slug = trim($tag_slug);
                if (empty($wp_values['tags_input'])) {
                    $wp_values['tags_input'] = array($tag_slug);
                } else {
                    array_push($wp_values['tags_input'], $tag_slug);
                }
            }
        }

        if (isset($wp_values['ID'])) {
            $this->id = wp_update_post($wp_values);
        } else {
            $this->id = wp_insert_post($wp_values);
        }
        // save custom fields
        if (array_key_exists('custom_fields', $values)) {
            $this->save_custom_fields($values['custom_fields']);
        }

        //save taxonomy fields
        if (!empty($values['terms'])) {
            foreach ($values['terms'] as $the_term) {
                // cast the term id as an int to avoid it being passed
                // as a string and hence intepreted as a slug
                if (!is_array($the_term['term']))
                    $the_term['term'] = (int)$the_term['term'];

                wp_set_object_terms($this->id, $the_term['term'], $the_term['taxonomy']);
            }
        }

        // upload an attachment if it exists
        if (!empty($_FILES['attachment'])) {
            include_once ABSPATH . '/wp-admin/includes/file.php';
            include_once ABSPATH . '/wp-admin/includes/media.php';
            include_once ABSPATH . '/wp-admin/includes/image.php';
            $attachment_id = media_handle_upload('attachment', $this->id);
            $this->attachments[] = new JSON_API_Attachment($attachment_id);
            unset($_FILES['attachment']);
        }

        $wp_post = get_post($this->id);
        $this->import_wp_object($wp_post);

        return $this->id;
    }

    function save_custom_fields($fields = array())
    {
        foreach ($fields as $meta_key => $meta_value) {
            update_post_meta($this->id, $meta_key, $meta_value);
        }
    }
}
