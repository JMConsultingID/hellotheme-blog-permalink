<?php
/**
 * @link              https://finpr.com
 * @since             1.2.1
 * @package           hellotheme-blog-permalink
 * GitHub Plugin URI: https://github.com/JMConsultingID/hellotheme-blog-permalink
 * GitHub Branch: develop
 * @wordpress-plugin
 * Plugin Name:       Hello Blog Permalink
 * Plugin URI:        https://finpr.com
 * Description:       This Plugin to Change Permalink
 * Version:           1.2.1.0
 * Author:            FinPR Team
 * Author URI:        https://finpr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hellotheme-blog-permalink
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'HELLO_BLOG_PERMALINK', '1.2.1.0' );

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

require plugin_dir_path( __FILE__ ) . 'inc/hellotheme-blog-permalink-functions.php';