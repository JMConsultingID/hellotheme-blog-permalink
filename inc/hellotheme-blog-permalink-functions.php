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
        '^en/blog/([^/]+)/([^/]+)/?$',
        'index.php?category_name=$matches[1]&name=$matches[2]&lang=en',
        'top'
    );

    // Add more rewrite rules for other languages if needed
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

    // Fallback to first category if no primary category found
    if (!$primary_category || is_wp_error($primary_category)) {
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $primary_category = $categories[0];
        }
    }

    // If category is found, modify permalink
    if ($primary_category) {
        $category_slug = $primary_category->slug;

        // Detect current language (using Polylang)
        if (function_exists('pll_get_post_language')) {
            $language = pll_get_post_language($post->ID, 'slug');
            $language_prefix = ($language !== pll_default_language()) ? '/' . $language : '';
        } else {
            $language_prefix = '';
        }

        // Construct the permalink with language prefix
        $permalink = home_url($language_prefix . '/blog/' . $category_slug . '/' . $post->post_name . '/');
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

function hellotheme_blog_permalink_category_rewrite_rules() {
    // Add rewrite rule for category archives with /blog/ prefix
    $enable_category_permalink = get_option( 'hellotheme_blog_permalink_enable_category_permalink' );
    if ($enable_category_permalink!== '1') {
        return;
    }

    add_rewrite_rule(
        '^blog/([^/]+)/?$',
        'index.php?category_name=$matches[1]',
        'top'
    );

    // Add rewrite rule for language-specific category archives with /blog/ prefix
    if (function_exists('pll_current_language')) {
        $languages = pll_languages_list(); // Get all languages

        foreach ($languages as $language) {
            if ($language !== pll_default_language()) {
                add_rewrite_rule(
                    '^' . $language . '/blog/([^/]+)/?$',
                    'index.php?category_name=$matches[1]&lang=' . $language,
                    'top'
                );
            }
        }
    }
}
add_action('init', 'hellotheme_blog_permalink_category_rewrite_rules');


function hellotheme_blog_permalink_category_archive_permalink($termlink, $term) {
    $enable_category_permalink = get_option( 'hellotheme_blog_permalink_enable_category_permalink' );

    if ($enable_category_permalink!== '1') {
        return;
    }

    // Ensure we are modifying category links only
    if ($term->taxonomy !== 'category') {
        return $termlink;
    }

    // Get the category slug
    $category_slug = $term->slug;

    // Detect current language (using Polylang)
    if (function_exists('pll_current_language')) {
        $language = pll_current_language('slug');
        $language_prefix = ($language !== pll_default_language()) ? '/' . $language : '';
    } else {
        $language_prefix = '';
    }

    // Construct the new permalink with /blog/ prefix
    $new_permalink = home_url($language_prefix . '/blog/' . $category_slug . '/');

    return $new_permalink;
}

// Hook into the category link filter
add_filter('category_link', 'hellotheme_blog_permalink_category_archive_permalink', 10, 2);


// Flush rewrite rules after theme or plugin updates
function hellotheme_blog_permalink_flush_custom_rewrite_rules() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'hellotheme_blog_permalink_flush_custom_rewrite_rules');
add_action('init', 'hellotheme_blog_permalink_flush_custom_rewrite_rules');