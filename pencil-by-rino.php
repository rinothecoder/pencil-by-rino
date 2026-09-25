<?php
/**
 * Plugin Name: Pencil by Rino
 * Description: Frontend content editing for AI-built WordPress themes. The theme owns the design, your client edits the words and images.
 * Version: 0.9.2
 * Author: Rino de Boer
 * Text Domain: pencil-by-rino
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'PENCIL_VERSION', '0.9.2' );
define( 'PENCIL_PLUGIN_FILE', __FILE__ );
define( 'PENCIL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PENCIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Ideas and feedback form shown on the Release notes tab.
define( 'PENCIL_FEEDBACK_URL', 'https://rinodeboer.fillout.com/pencil-by-rino' );
define( 'PENCIL_SUPPORT_URL', 'https://wordpress.org/support/plugin/pencil-by-rino/' );

require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-fields.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-activity.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-admin.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-plugin.php';
require_once PENCIL_PLUGIN_PATH . 'includes/template-functions.php';

register_activation_hook( PENCIL_PLUGIN_FILE, array( 'Pencil_Activity', 'install' ) );

Pencil_Activity::init();
Pencil_Admin::init();
Pencil_Plugin::init();
