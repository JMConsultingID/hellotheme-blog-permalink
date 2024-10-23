<?php
/**
 * Plugin functions and definitions for Functions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package hellotheme-blog-permalink
 */
// Custom permalink structure for posts in the /blog/ format
function hellotheme_blog_permalink_rewrite_rules() {
    $enable_permalink = get_option( 'hellotheme_blog_permalink_enable_permalink' );
    if ($enable_permalink!== '1') {
        return;
    }
    add_rewrite_rule(
        '^blog/([^/]+)/([^/]+)/?$',
        'index.php?category_name=$matches[1]&name=$matches[2]',
        'top'
    );
}

// Ensure the rules are added
add_action('init', 'hellotheme_blog_permalink_rewrite_rules');

// Custom function for generating permalinks
function hellotheme_blog_permalink_blog_post_permalink($permalink, $post) {
    $enable_permalink = get_option( 'hellotheme_blog_permalink_enable_permalink' );
    if ($enable_permalink!== '1') {
        return;
    }

    if ($post->post_type !== 'post') {
        return $permalink;
    }

    // Get primary category if set (Yoast or fallback to first category)
    $primary_category = '';
    if (class_exists('WPSEO_Primary_Term')) {
        $primary_term = new WPSEO_Primary_Term('category', $post->ID);
        $primary_category_id = $primary_term->get_primary_term();
        $primary_category = get_category($primary_category_id);
    }

    // Fallback to first category if no primary category is found
    if (!$primary_category || is_wp_error($primary_category)) {
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $primary_category = $categories[0];
        }
    }

    // If category is found, modify permalink
    if ($primary_category) {
        $category_slug = $primary_category->slug;
        $permalink = home_url('/blog/' . $category_slug . '/' . $post->post_name . '/');
    }

    return $permalink;
}

add_filter('post_link', 'hellotheme_blog_permalink_blog_post_permalink', 10, 2);

function hellotheme_blog_permalink_redirect_old_post_links() {
    $enable_permalink = get_option( 'hellotheme_blog_permalink_enable_permalink' );
    if ($enable_permalink!== '1') {
        return;
    }
    
    if (is_singular('post')) {
        global $post;

        // Get current post slug
        $post_slug = $post->post_name;

        // Get primary category or first category for the post
        $primary_category = '';
        if (class_exists('WPSEO_Primary_Term')) {
            $primary_term = new WPSEO_Primary_Term('category', $post->ID);
            $primary_category_id = $primary_term->get_primary_term();
            $primary_category = get_category($primary_category_id);
        }

        // Fallback to first category if no primary category found
        if (!$primary_category || is_wp_error($primary_category)) {
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $primary_category = $categories[0];
            }
        }

        // Construct the new permalink with /blog/ prefix
        if ($primary_category) {
            $category_slug = $primary_category->slug;
            $new_permalink = home_url('/blog/' . $category_slug . '/' . $post_slug . '/');

            // If the current URL doesn't match the new permalink, redirect
            if (!strstr($_SERVER['REQUEST_URI'], '/blog/' . $category_slug . '/')) {
                wp_redirect($new_permalink, 301);
                exit();
            }
        }
    }
}

// Hook into template_redirect to perform the redirect
add_action('template_redirect', 'hellotheme_blog_permalink_redirect_old_post_links');


// Flush rewrite rules after theme or plugin updates
function hellotheme_blog_permalink_flush_custom_rewrite_rules() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'hellotheme_blog_permalink_flush_custom_rewrite_rules');
add_action('init', 'hellotheme_blog_permalink_flush_custom_rewrite_rules');