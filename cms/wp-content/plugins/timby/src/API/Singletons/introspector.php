<?php

require_once ABSPATH . 'wp-content/plugins/json-api/singletons/introspector.php';
require_once __DIR__ . '/../Model/term.php';

class JSON_API_Timby_Introspector extends JSON_API_Introspector
{
    /**
     * Get terms for a given taxonomy
     *
     * @param  string $taxonomy
     * @param  string|array $args
     * @return array  An array of terms, see http://codex.wordpress.org/Function_Reference/get_terms
     */
    public function get_terms_for($taxonomy, $args = '')
    {
        $wp_terms = get_terms($taxonomy, $args);

        $terms = array();
        foreach ($wp_terms as $term) {
            if ($term->term_id == 1 && $term->slug == 'uncategorized') {
                continue;
            }
            $terms[] = $this->get_term_object($term);
        }
        return $terms;
    }

    protected function get_term_object($term)
    {
        if (!$term) {
            return null;
        }
        return new JSON_API_Timby_Term($term);
    }
}
