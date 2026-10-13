<?php
/**
 * Plugin Name: Pencilino by Rino
 * Description: Frontend content editing for AI-built WordPress themes. The theme owns the design, your client edits the words and images.
 * Version: 2.0.4
 * Plugin URI: https://github.com/rinothecoder/pencilino
 * Update URI: https://github.com/rinothecoder/pencilino/
 * Author: Rino de Boer
 * Text Domain: pencilino-by-rino
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'PENCIL_VERSION', '2.0.4' );
define( 'PENCIL_PLUGIN_FILE', __FILE__ );
define( 'PENCIL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PENCIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Ideas and feedback form shown on the Release notes tab.
define( 'PENCIL_FEEDBACK_URL', 'https://rinodeboer.fillout.com/pencil-by-rino' );
define( 'PENCIL_SUPPORT_URL', 'https://github.com/rinothecoder/pencilino/issues' );

require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-fields.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-activity.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-admin.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-plugin.php';
require_once PENCIL_PLUGIN_PATH . 'includes/template-functions.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-client.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-comments.php';
require_once PENCIL_PLUGIN_PATH . 'includes/class-pencil-abilities.php';

register_activation_hook( PENCIL_PLUGIN_FILE, array( 'Pencil_Activity', 'install' ) );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'pencilino-by-rino', false, dirname( plugin_basename( PENCIL_PLUGIN_FILE ) ) . '/languages' );
	},
	1
);

Pencil_Activity::init();
Pencil_Admin::init();
Pencil_Plugin::init();
Pencil_Client::init();
Pencil_Comments::init();
Pencil_Abilities::init();

require_once PENCIL_PLUGIN_PATH . 'includes/class-pencilino-updates.php';
Pencilino_Updates::boot();
