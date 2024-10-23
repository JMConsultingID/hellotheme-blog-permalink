<?php
/**
 * Plugin functions and definitions for Admin.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package hellotheme-blog-permalink
 */
function hellotheme_blog_permalink_table_add_menu() {
    // Add as a submenu under Settings
    add_submenu_page(
        'options-general.php', // Parent menu slug for Settings
        'Hello Blog Permalink', // Page title
        'Hello Blog Permalink', // Menu title
        'manage_options', // Capability
        'hellotheme_blog_permalink_settings', // Menu slug
        'hellotheme_blog_permalink_settings_page' // Function to display the page content
    );
}

add_action( 'admin_menu', 'hellotheme_blog_permalink_table_add_menu');

function hellotheme_blog_permalink_settings_page() {
    ?>
    <div class="wrap">
        <h1>Hello Blog Permalink</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'hellotheme_blog_permalink_group' );
            do_settings_sections( 'hellotheme-blog-permalink-settings' );
            submit_button( 'Submit' );
            ?>
        </form>
    </div>
    <?php
}

// Hook for adding admin settings
add_action( 'admin_init', 'hellotheme_blog_permalink_register_permalink_setting_fields' );

function hellotheme_blog_permalink_register_permalink_setting_fields() {
    register_setting( 'hellotheme_blog_permalink_group', 'hellotheme_blog_permalink_enable_permalink' );

    add_settings_section(
        'hellotheme_blog_permalink_section',
        'Hello Blog Permalink Settings',
        'hellotheme_blog_permalink_section_callback',
        'hellotheme-blog-permalink-settings'
    );

    add_settings_field(
        'hellotheme_blog_permalink_enable_permalink',
        'Enable Blog Permalink',
        'hellotheme_blog_permalink_enable_permalink_callback',
        'hellotheme-blog-permalink-settings',
        'hellotheme_blog_permalink_section'
    );

}

function hellotheme_blog_permalink_section_callback() {
    // Get the base URL of the site
    $permalink_url = home_url('/wp-admin/options-permalink.php');
    
    echo '<p>Please enable the configuration below.</p>';
    echo '<p><strong>Important:</strong> After enabling this setting, you need to flush the permalink settings.</p>';
    echo '<p>To do this, go to the <a href="' . esc_url($permalink_url) . '">Permalink Settings page</a> and click "Save Changes".</p>';
    echo '<p>Once the permalink is enabled, the URL structure for your blog posts will be:</p>';
    echo '<p><code>' . esc_url(home_url()) . '/blog/%category%/%postname%/</code></p>';
}


function hellotheme_blog_permalink_enable_permalink_callback() {
    $options = get_option( 'hellotheme_blog_permalink_enable_permalink' );
    ?>
    <input type="checkbox" name="hellotheme_blog_permalink_enable_permalink" value="1" <?php checked( 1, $options, true ); ?> />
    <?php
}