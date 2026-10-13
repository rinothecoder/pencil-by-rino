<?php
/**
 * Pencilino admin page: Changes, Get started, About, Changelog, Support.
 *
 * @package Pencilino
 */

defined( 'ABSPATH' ) || exit;

final class Pencil_Admin {
	const MENU_SLUG = 'pencil-by-rino';

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private static $page_hook = '';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'register_admin_bar_shortcut' ), 80 );
	}

	/**
	 * Add the Pencilino top-level menu.
	 *
	 * The icon is passed as a data URI so WordPress recolours it to match the
	 * user's admin colour scheme.
	 *
	 * @return void
	 */
	public static function register_menu() {
		$svg  = file_get_contents( PENCIL_PLUGIN_PATH . 'admin/img/pencil-icon-white.svg' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		/**
		 * Filter the admin menu label.
		 *
		 * @param string $title Menu label.
		 */
		$menu_title = (string) apply_filters( 'pencil_admin_menu_title', __( 'Pencilino', 'pencilino-by-rino' ) );

		/**
		 * Filter the parent menu slug. Empty keeps Pencil as a top-level menu;
		 * a slug such as `edit.php?post_type=page` moves it under that menu.
		 *
		 * @param string $parent Parent menu slug.
		 */
		$parent = (string) apply_filters( 'pencil_admin_menu_parent', '' );

		if ( '' !== $parent ) {
			self::$page_hook = (string) add_submenu_page(
				$parent,
				__( 'Pencilino by Rino', 'pencilino-by-rino' ),
				$menu_title,
				'edit_pages',
				self::MENU_SLUG,
				array( __CLASS__, 'render_page' )
			);
			return;
		}

		self::$page_hook = add_menu_page(
__( 'Pencilino by Rino', 'pencilino-by-rino' ),
			$menu_title,
			'edit_pages',
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			$icon,
			58
		);
	}

	/**
	 * Add an "Open Pencilino" shortcut to the admin bar while viewing the site.
	 *
	 * @param WP_Admin_Bar $admin_bar WordPress admin bar instance.
	 * @return void
	 */
	public static function register_admin_bar_shortcut( $admin_bar ) {
		if ( is_admin() || ! Pencil_Plugin::can_edit() ) {
			return;
		}

		$current_url = home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) );
		$editor_url  = add_query_arg( 'pencil-edit', '1', $current_url );

		$admin_bar->add_node(
			array(
				'id'    => 'pencil-open-editor',
				'title' => __( 'Open Pencilino', 'pencilino-by-rino' ),
				'href'  => esc_url( $editor_url ),
				'meta'  => array(
					'class' => 'pencil-admin-bar-shortcut',
				),
			)
		);
	}

	/**
	 * Load styles and scripts only on the Pencilino admin page.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( self::$page_hook !== $hook_suffix ) {
			return;
		}

		$css_path = PENCIL_PLUGIN_PATH . 'admin/css/admin.css';
		$js_path  = PENCIL_PLUGIN_PATH . 'admin/js/admin.js';

		wp_enqueue_style(
			'pencil-admin',
			PENCIL_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			file_exists( $css_path ) ? (string) filemtime( $css_path ) : PENCIL_VERSION
		);
		wp_enqueue_script(
			'pencil-admin',
			PENCIL_PLUGIN_URL . 'admin/js/admin.js',
			array(),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : PENCIL_VERSION,
			true
		);
	}

	/**
	 * Render the page. Every tab is in the DOM; JS switches between them.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'pencilino-by-rino' ) );
		}

		$tabs = array(
			'changes'     => __( 'Changes', 'pencilino-by-rino' ),
			'comments'    => __( 'Comments', 'pencilino-by-rino' ),
			'get-started' => __( 'Get started', 'pencilino-by-rino' ),
			'about'       => __( 'About', 'pencilino-by-rino' ),
			'changelog'   => __( 'Release notes', 'pencilino-by-rino' ),
		);
if ( current_user_can( 'manage_options' ) ) {
			$tabs['settings'] = __( 'Settings', 'pencilino-by-rino' );
		}

		/**
		 * Filter the admin tabs. Removing a tab also removes its panel.
		 *
		 * @param array<string, string> $tabs Tab slug => label.
		 */
		$tabs   = (array) apply_filters( 'pencil_admin_tabs', $tabs );
		$panels = array(
			'changes'     => 'render_changes',
			'comments'    => 'render_comments',
			'settings'    => 'render_settings',
			'get-started' => 'render_get_started',
			'about'       => 'render_about',
			'changelog'   => 'render_changelog',
		);
		?>
		<div class="pencil-wrap">

			<header class="pencil-header">
				<div class="pencil-header__left">
					<img src="<?php echo esc_url( add_query_arg( 'ver', filemtime( PENCIL_PLUGIN_PATH . 'admin/img/pencilino-icon-color.svg' ), PENCIL_PLUGIN_URL . 'admin/img/pencilino-icon-color.svg' ) ); ?>" alt="" aria-hidden="true" class="pencil-header__icon" width="32" height="32">
					<h1 class="pencil-header__title"><?php esc_html_e( 'Pencilino by Rino', 'pencilino-by-rino' ); ?></h1>
					<span class="pencil-header__version">v<?php echo esc_html( PENCIL_VERSION ); ?></span>
				</div>
				<nav class="pencil-header__nav">
					<?php foreach ( $tabs as $slug => $label ) : ?>
						<a href="#<?php echo esc_attr( $slug ); ?>" data-tab="<?php echo esc_attr( $slug ); ?>" class="pencil-header__link">
							<?php echo esc_html( $label ); ?>
						</a>
					<?php endforeach; ?>
					<a class="pencil-header__open" href="<?php echo esc_url( add_query_arg( 'pencil-edit', '1', home_url( '/' ) ) ); ?>">
						<?php esc_html_e( 'Open Pencilino', 'pencilino-by-rino' ); ?>
					</a>
				</nav>
			</header>
			<hr class="wp-header-end">

<?php
			$first = true;
			foreach ( $panels as $slug => $method ) :
				if ( ! isset( $tabs[ $slug ] ) ) {
					continue;
				}
				?>
				<div data-tab-panel="<?php echo esc_attr( $slug ); ?>"<?php echo $first ? '' : ' hidden'; ?>><?php self::$method(); ?></div>
				<?php
				$first = false;
			endforeach;
			?>

		</div>
		<?php
	}

	private static function render_settings() {
		?>
		<div class="pencil-content"><div class="pencil-card pencil-card--full">
			<div class="pencil-page-intro"><span class="pencil-eyebrow"><?php esc_html_e( 'A simpler editing experience', 'pencilino-by-rino' ); ?></span><h2><?php esc_html_e( 'Client mode', 'pencilino-by-rino' ); ?></h2></div>
			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success notice.
			if ( isset( $_GET['pencil-settings-saved'] ) ) : ?><p class="pencil-settings-notice" role="status"><?php esc_html_e( 'Settings saved.', 'pencilino-by-rino' ); ?></p><?php endif; ?>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="pencil_save_settings"><?php wp_nonce_field( 'pencil_save_settings' ); ?>
				<label class="pencil-setting-row"><span><strong><?php esc_html_e( 'Enable client mode', 'pencilino-by-rino' ); ?></strong><small><?php esc_html_e( 'Create the Clients role and simplify their editing screens.', 'pencilino-by-rino' ); ?></small></span><span class="pencil-setting-switch"><input type="checkbox" name="client_mode" value="1" role="switch" aria-label="<?php esc_attr_e( 'Enable client mode', 'pencilino-by-rino' ); ?>" <?php checked( Pencil_Client::enabled() ); ?>><span class="pencil-setting-switch__track" aria-hidden="true"></span></span></label>
				<p><?php esc_html_e( 'Assign the Clients role to your client accounts in WordPress. Clients start on the website and use the existing managed-content links to open the correct editor.', 'pencilino-by-rino' ); ?></p>
				<p class="pencil-settings-notice"><?php esc_html_e( 'The WordPress top bar and left menu are hidden. The editor sidebar stays available for featured images, categories, and content fields.', 'pencilino-by-rino' ); ?></p>
				<p><?php esc_html_e( 'Static page content stays editable through the pencil. Clients can create, edit, and trash blog posts, products, and frontend-facing custom post types. Pages remain unavailable in the backend.', 'pencilino-by-rino' ); ?></p>
				<p><?php esc_html_e( 'Disabling client mode keeps assigned users in the Clients role but removes their content-editing access until you enable it again or assign another role.', 'pencilino-by-rino' ); ?></p>
				<button class="pencil-button" type="submit"><?php esc_html_e( 'Save settings', 'pencilino-by-rino' ); ?></button>
			</form>
		</div></div>
		<?php
		Pencilino_Updates::screen();
	}

	private static function render_comments() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only inbox filtering.
		$resolved = isset( $_GET['pencil-comment-status'] ) && 'resolved' === $_GET['pencil-comment-status'];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination.
		$page = isset( $_GET['pencil-comment-page'] ) ? max( 1, absint( $_GET['pencil-comment-page'] ) ) : 1;
		$rows = Pencil_Comments::inbox( $resolved, $page );
		$more = count( $rows ) > 50;
		$rows = array_slice( $rows, 0, 50 );
		$base = add_query_arg( 'page', self::MENU_SLUG, admin_url( 'admin.php' ) );
		?>
		<div class="pencil-content pencil-content--wide"><div class="pencil-card pencil-card--full">
			<div class="pencil-page-intro"><span class="pencil-eyebrow"><?php esc_html_e( 'Client feedback', 'pencilino-by-rino' ); ?></span><h2><?php esc_html_e( 'Comments', 'pencilino-by-rino' ); ?></h2><p><?php esc_html_e( 'Private notes attached to the website. Content changes stay in their own tab.', 'pencilino-by-rino' ); ?></p></div>
			<nav class="pencil-comment-filters" aria-label="<?php esc_attr_e( 'Comment status', 'pencilino-by-rino' ); ?>">
				<a class="pencil-button<?php echo $resolved ? ' pencil-button--quiet' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'pencil-comment-status', 'open', $base ) . '#comments' ); ?>"><?php esc_html_e( 'Open', 'pencilino-by-rino' ); ?></a>
				<a class="pencil-button<?php echo ! $resolved ? ' pencil-button--quiet' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'pencil-comment-status', 'resolved', $base ) . '#comments' ); ?>"><?php esc_html_e( 'Resolved', 'pencilino-by-rino' ); ?></a>
			</nav>
			<?php if ( ! $rows ) : ?><p class="pencil-comment-empty"><?php echo esc_html( $resolved ? __( 'No resolved comments yet.', 'pencilino-by-rino' ) : __( 'No open comments.', 'pencilino-by-rino' ) ); ?></p><?php endif; ?>
			<?php foreach ( $rows as $row ) : $target = json_decode( $row->target, true ); ?>
				<article class="pencil-comment-record">
					<div class="pencil-comment-heading"><h3><?php echo esc_html( $target['label'] ?? __( 'Page element', 'pencilino-by-rino' ) ); ?></h3><span class="pencil-comment-badge"><?php echo esc_html( $resolved ? __( 'Resolved', 'pencilino-by-rino' ) : __( 'Comment', 'pencilino-by-rino' ) ); ?></span></div>
					<p class="pencil-comment-text"><?php echo esc_html( $row->comment_text ); ?></p>
					<p class="pencil-comment-meta"><?php echo esc_html( $row->user_name . ' · ' . $row->page_title . ' · ' . wp_date( 'j M Y, H:i', strtotime( $row->created_at . ' UTC' ) ) ); ?></p>
					<div class="pencil-comment-actions"><a class="pencil-button pencil-button--quiet" href="<?php echo esc_url( add_query_arg( array( 'pencil-edit' => '1', 'pencil-comment' => $row->comment_id ), $row->page_url ) ); ?>"><?php esc_html_e( 'View on website', 'pencilino-by-rino' ); ?></a>
					<?php if ( current_user_can( 'manage_options' ) ) : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="pencil_resolve_comment"><input type="hidden" name="comment_id" value="<?php echo absint( $row->comment_id ); ?>"><input type="hidden" name="resolved" value="<?php echo $resolved ? '0' : '1'; ?>"><?php wp_nonce_field( 'pencil_resolve_comment_' . $row->comment_id ); ?><button class="pencil-button" type="submit"><?php echo esc_html( $resolved ? __( 'Reopen', 'pencilino-by-rino' ) : __( 'Resolve', 'pencilino-by-rino' ) ); ?></button></form><?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
			<nav class="pencil-comment-actions" aria-label="<?php esc_attr_e( 'Comment pages', 'pencilino-by-rino' ); ?>"><?php if ( $page > 1 ) : ?><a class="pencil-button pencil-button--quiet" href="<?php echo esc_url( add_query_arg( array( 'pencil-comment-status' => $resolved ? 'resolved' : 'open', 'pencil-comment-page' => $page - 1 ), $base ) . '#comments' ); ?>"><?php esc_html_e( 'Previous', 'pencilino-by-rino' ); ?></a><?php endif; ?><?php if ( $more ) : ?><a class="pencil-button pencil-button--quiet" href="<?php echo esc_url( add_query_arg( array( 'pencil-comment-status' => $resolved ? 'resolved' : 'open', 'pencil-comment-page' => $page + 1 ), $base ) . '#comments' ); ?>"><?php esc_html_e( 'Next', 'pencilino-by-rino' ); ?></a><?php endif; ?></nav>
		</div></div>
		<?php
	}

	/**
	 * Changes tab: open button and the activity log.
	 *
	 * @return void
	 */
	private static function render_changes() {
		$activities = Pencil_Activity::get_recent( 100 );
		?>
		<div class="pencil-content pencil-content--onboarding">
			<div class="pencil-card pencil-card--full pencil-changes">
				<div class="pencil-page-intro">
					<span class="pencil-eyebrow"><?php esc_html_e( 'Content history', 'pencilino-by-rino' ); ?></span>
					<h2><?php esc_html_e( 'Changes', 'pencilino-by-rino' ); ?></h2>
				</div>

				<?php if ( empty( $activities ) ) : ?>
					<div class="pencil-admin__empty">
						<span class="dashicons dashicons-clock" aria-hidden="true"></span>
						<h3><?php esc_html_e( 'No changes yet', 'pencilino-by-rino' ); ?></h3>
						<p><?php esc_html_e( 'Every change saved through Pencilino appears here, with the value before and after.', 'pencilino-by-rino' ); ?></p>
					</div>
				<?php else : ?>
					<ul class="pencil-admin__list">
						<?php foreach ( $activities as $activity ) : ?>
							<li class="pencil-admin__record">
								<?php self::render_activity_row( $activity ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Get started tab: one part for people, one for the AI agent.
	 *
	 * @return void
	 */
	private static function render_get_started() {
		$instructions_file = PENCIL_PLUGIN_PATH . 'docs/agent-instructions.md';
		$instructions      = file_exists( $instructions_file ) ? file_get_contents( $instructions_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$prompt_intro      = __( 'We are going to build a custom WordPress theme that works with the Pencilino by Rino plugin. Pencilino has specific theme-building instructions that you must follow throughout the project. Find and read the complete instructions in WordPress admin under Pencilino > Get Started > For AI Agents, or locate docs/agent-instructions.md inside the active Pencilino plugin. If your environment cannot access them, ask me to copy them into this conversation.', 'pencilino-by-rino' );
		$idea_prompt       = implode(
			"\n\n",
			array(
				$prompt_intro,
				__( 'Generate the website design and code inside the theme according to the prompt I provide. Build the first complete version and follow the Pencilino instructions throughout the project.', 'pencilino-by-rino' ),
				__( 'Website link: [Insert link]', 'pencilino-by-rino' ),
				__( 'Website idea or design brief:', 'pencilino-by-rino' ),
			)
		);
		$html_prompt       = implode(
			"\n\n",
			array(
				$prompt_intro,
				__( 'I am providing an HTML template. Start with the complete template package I provide and convert it into a maintainable custom WordPress theme by following the Pencilino instructions.', 'pencilino-by-rino' ),
				__( 'Website link: [Insert link]', 'pencilino-by-rino' ),
				__( 'HTML template location or files:', 'pencilino-by-rino' ),
			)
		);
		?>
		<div class="pencil-content pencil-content--onboarding">
			<div class="pencil-card pencil-card--full">
				<div data-subtabs>
					<div class="pencil-subtabs" role="tablist">
						<button type="button" class="pencil-subtabs__btn pencil-subtabs__btn--active" data-subtab="humans" role="tab" aria-selected="true"><?php esc_html_e( 'For you', 'pencilino-by-rino' ); ?></button>
						<button type="button" class="pencil-subtabs__btn" data-subtab="agents" role="tab" aria-selected="false"><?php esc_html_e( 'For AI agents', 'pencilino-by-rino' ); ?></button>
					</div>

					<div data-subtab-panel="humans">
						<div class="pencil-get-started__intro">
							<span class="pencil-eyebrow"><?php esc_html_e( 'Choose your starting point', 'pencilino-by-rino' ); ?></span>
							<h2><?php esc_html_e( 'Build a custom theme with your AI coding tool', 'pencilino-by-rino' ); ?></h2>
							<p><?php esc_html_e( 'Pencilino provides the editable content layer and the rules your agent follows. Your coding tool builds the WordPress theme.', 'pencilino-by-rino' ); ?></p>
						</div>

						<div class="pencil-agent-tools" aria-label="<?php esc_attr_e( 'Compatible AI coding tools', 'pencilino-by-rino' ); ?>">
							<span class="pencil-agent-tools__label"><?php esc_html_e( 'Works with', 'pencilino-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Codex', 'pencilino-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Claude', 'pencilino-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Cursor', 'pencilino-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Any other coding agents', 'pencilino-by-rino' ); ?></span>
						</div>

						<div class="pencil-paths">
							<article class="pencil-path">
								<div class="pencil-path__icon"><span class="dashicons dashicons-art" aria-hidden="true"></span></div>
								<span class="pencil-path__number"><?php esc_html_e( 'Path 1', 'pencilino-by-rino' ); ?></span>
								<h3><?php esc_html_e( 'Start from an idea', 'pencilino-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Describe the website and let your agent design and build the complete theme from scratch.', 'pencilino-by-rino' ); ?></p>
								<button type="button" class="pencil-button" data-copy="pencil-idea-prompt" data-copied-label="<?php esc_attr_e( 'Prompt copied', 'pencilino-by-rino' ); ?>">
									<?php esc_html_e( 'Copy starter prompt', 'pencilino-by-rino' ); ?>
								</button>
								<textarea id="pencil-idea-prompt" class="pencil-copy-source" readonly tabindex="-1" aria-hidden="true"><?php echo esc_textarea( $idea_prompt ); ?></textarea>
							</article>

							<article class="pencil-path">
								<div class="pencil-path__icon"><span class="dashicons dashicons-media-code" aria-hidden="true"></span></div>
								<span class="pencil-path__number"><?php esc_html_e( 'Path 2', 'pencilino-by-rino' ); ?></span>
								<h3><?php esc_html_e( 'Convert an HTML template', 'pencilino-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Give your agent the complete HTML package and have it preserve the design while turning it into a proper WordPress theme.', 'pencilino-by-rino' ); ?></p>
								<button type="button" class="pencil-button" data-copy="pencil-html-prompt" data-copied-label="<?php esc_attr_e( 'Prompt copied', 'pencilino-by-rino' ); ?>">
									<?php esc_html_e( 'Copy conversion prompt', 'pencilino-by-rino' ); ?>
								</button>
								<textarea id="pencil-html-prompt" class="pencil-copy-source" readonly tabindex="-1" aria-hidden="true"><?php echo esc_textarea( $html_prompt ); ?></textarea>
							</article>
						</div>

						<h3 class="pencil-section-heading"><?php esc_html_e( 'How to use it', 'pencilino-by-rino' ); ?></h3>
						<ol class="pencil-steps">
							<li>
								<strong><?php esc_html_e( 'Connect your coding tool to WordPress', 'pencilino-by-rino' ); ?></strong>
								<?php esc_html_e( 'Connect it to this website through MCP or another WordPress integration, or open your local WordPress installation as a project folder.', 'pencilino-by-rino' ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Use a starter prompt', 'pencilino-by-rino' ); ?></strong>
								<?php esc_html_e( 'Copy the prompt for your starting point, then add the website link and your idea or HTML template location.', 'pencilino-by-rino' ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Let the agent build or convert the theme', 'pencilino-by-rino' ); ?></strong>
								<?php esc_html_e( 'The agent builds the theme, creates the pages, imports editable images, and follows the Pencilino instructions.', 'pencilino-by-rino' ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Review and refine the result', 'pencilino-by-rino' ); ?></strong>
								<?php esc_html_e( 'Check every page, test text, buttons and images in Pencilino, and ask your agent to fix anything that needs work.', 'pencilino-by-rino' ); ?>
							</li>
						</ol>

						<div class="pencil-get-started__details">
							<div>
								<h3><?php esc_html_e( 'Later design changes', 'pencilino-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Return to your agent with the same instructions. Saved content remains intact as long as the field IDs and types stay the same.', 'pencilino-by-rino' ); ?></p>
							</div>
							<div>
								<h3><?php esc_html_e( 'Client access', 'pencilino-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Administrators and Editors can use Pencilino. For a simpler client experience, enable client mode in Settings and assign the Clients role to your client accounts.', 'pencilino-by-rino' ); ?></p>
							</div>
						</div>
					</div>

					<div data-subtab-panel="agents" hidden>
						<div class="pencil-agent-instructions__intro">
							<span class="pencil-eyebrow"><?php esc_html_e( 'The complete contract', 'pencilino-by-rino' ); ?></span>
							<h2><?php esc_html_e( 'Instructions for the coding agent', 'pencilino-by-rino' ); ?></h2>
							<p><?php esc_html_e( 'Copy these instructions into any coding agent, or let a connected agent read them through the WordPress files or MCP environment.', 'pencilino-by-rino' ); ?></p>
						</div>
						<div class="pencil-instructions__actions">
							<button type="button" class="pencil-button" data-copy="pencil-agent-instructions" data-copied-label="<?php esc_attr_e( 'Copied', 'pencilino-by-rino' ); ?>">
								<?php esc_html_e( 'Copy instructions', 'pencilino-by-rino' ); ?>
							</button>
							<span class="pencil-instructions__path"><?php esc_html_e( 'Bundled with Pencilino:', 'pencilino-by-rino' ); ?> <code>docs/agent-instructions.md</code></span>
						</div>
						<textarea id="pencil-agent-instructions" class="pencil-instructions__text" readonly spellcheck="false" aria-label="<?php esc_attr_e( 'Instructions for AI agents', 'pencilino-by-rino' ); ?>"><?php echo esc_textarea( $instructions ); ?></textarea>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * About tab.
	 *
	 * @return void
	 */
	private static function render_about() {
		?>
		<div class="pencil-content">
			<div class="pencil-card pencil-about">
				<div class="pencil-page-intro">
					<span class="pencil-eyebrow"><?php esc_html_e( 'About', 'pencilino-by-rino' ); ?></span>
					<h2><?php esc_html_e( 'The story behind Pencilino', 'pencilino-by-rino' ); ?></h2>
				</div>
				<img src="<?php echo esc_url( PENCIL_PLUGIN_URL . 'admin/img/rino-profile.jpg' ); ?>" alt="Rino de Boer" class="pencil-about__photo" width="80" height="80">

				<p>Hey, my name is Rino. I'm a Dutch web designer, and I have built websites with a pagebuilder for years.</p>

				<p>Lately I've been experimenting with something else. Letting AI build the whole theme. No builder, just code. And honestly, it's fast. Really fast.</p>

				<p>But then I hit a wall. Every time.</p>

				<p>The site works, it looks good, and then I hand it to the client and they can't change a single word without calling me.</p>

				<p>And I know my clients. Their products are in WordPress. Their bookings, their team, their blog. They know where to click. They don't want to leave, and I don't want them to.</p>

				<p>So the obvious answer is a pagebuilder. But here's the uncomfortable part. Pagebuilders have become professional tools. Classes, variables, design systems. Great for us. Almost impossible for the average client, who just wants to change some text and swap a picture.</p>

				<p>So I built Pencilino.</p>

				<p>The theme owns the design. Other plugins keep owning their own data. And every piece of static text or image the AI writes into the theme becomes a field the client can edit, right on the page. One field, one owner. Nothing lives in two places.</p>

				<p>Let me be honest about what Pencilino is not. It does nothing on its own. It needs an AI like Codex or Claude to build the theme, following the instructions in the Get started tab. It won't make an existing site editable. And the quality of the site depends on the model you use, not on this plugin.</p>

				<p>Before it went public, I made sure it follows WordPress's plugin standards and passes the official Plugin Check.</p>

				<p>For smaller projects, I don't think we need a pagebuilder anymore. That's a strange thing to say after all those years. But it's where I am right now.</p>

				<p>I hope Pencilino makes handing over an AI-built site a little easier. That's really all it's supposed to do.</p>

				<img src="<?php echo esc_url( PENCIL_PLUGIN_URL . 'admin/img/rino-signature.svg' ); ?>" alt="Rino" class="pencil-about__signature" width="60" height="32" aria-hidden="true">
			</div>
		</div>
		<?php
	}

	/**
	 * Changelog tab.
	 *
	 * @return void
	 */
	private static function render_changelog() {
		?>
		<div class="pencil-content">
			<div class="pencil-card">
				<div class="pencil-page-intro">
					<span class="pencil-eyebrow"><?php esc_html_e( 'Release notes', 'pencilino-by-rino' ); ?></span>
					<h2><?php esc_html_e( 'What is new in Pencilino', 'pencilino-by-rino' ); ?></h2>
				</div>

				<div class="pencil-changelog__ideas">
					<p><?php esc_html_e( 'Got an idea, or feedback on something that could be better? I would like to hear it. The best ideas end up in the plugin, with your name next to them.', 'pencilino-by-rino' ); ?></p>
					<a href="<?php echo esc_url( PENCIL_FEEDBACK_URL ); ?>" target="_blank" rel="noopener noreferrer" class="pencil-button">
						<?php esc_html_e( 'Share an idea or feedback', 'pencilino-by-rino' ); ?>
					</a>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header"><span class="pencil-changelog__version">v2.0.0</span></div>
					<ul class="pencil-changelog__list"><li><?php esc_html_e( 'Private comments anywhere on the website, with a separate inbox and Resolve action.', 'pencilino-by-rino' ); ?></li><li><?php esc_html_e( 'Optional Clients role with simplified post and product editing screens.', 'pencilino-by-rino' ); ?></li></ul>
				</div>
				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v1.0.0</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencilino-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'First stable release of Pencilino\'s frontend content editing workflow.', 'pencilino-by-rino' ); ?></li>
					</ul>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v0.9.2</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencilino-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'Refined the History, About and Release notes screens so they share the same page structure.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Made the active frontend editing control easier to recognise.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Clarified the setup steps and added a website link field to both starter prompts.', 'pencilino-by-rino' ); ?></li>
					</ul>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v0.9.1</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencilino-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'Protected the frontend Media Library from common theme CSS class collisions.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Added clearer AI-agent rules and tests for WordPress interface compatibility.', 'pencilino-by-rino' ); ?></li>
					</ul>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v0.9</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencilino-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'First public release. Written to WordPress plugin standards and checked with the official Plugin Check.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Text, rich text, button and image fields, editable on the live page.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Managed regions for content owned by ACF, JetEngine, WooCommerce or WordPress, with a link to the right edit screen.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Per-page content for templates shared by several pages.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'A Changes log with who, where, when, and the value before and after.', 'pencilino-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Instructions for AI agents, included in the plugin.', 'pencilino-by-rino' ); ?></li>
					</ul>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Support tab.
	 *
	 * @return void
	 */
	private static function render_support() {
		?>
		<div class="pencil-content">
			<div class="pencil-card">
				<h2><?php esc_html_e( 'Found a bug?', 'pencilino-by-rino' ); ?></h2>
				<p><?php esc_html_e( 'Pencilino is a free plugin. There is no official support, but if you run into a bug I would like to know about it so I can fix it.', 'pencilino-by-rino' ); ?></p>
				<p><?php esc_html_e( 'The best place to report a bug is GitHub Issues. Describe what happened, which AI tool built the theme, and I will take a look when I can.', 'pencilino-by-rino' ); ?></p>
				<a href="<?php echo esc_url( PENCIL_SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer" class="pencil-button">
					<?php esc_html_e( 'Report a bug on GitHub', 'pencilino-by-rino' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render one activity record.
	 *
	 * @param object $activity Activity database record.
	 * @return void
	 */
	private static function render_activity_row( $activity ) {
		$timestamp  = strtotime( $activity->created_at . ' UTC' );
		$when       = $timestamp ? wp_date( 'j M Y, H:i', $timestamp ) : $activity->created_at;
		$user_name  = $activity->user_name ? $activity->user_name : __( 'Unknown user', 'pencilino-by-rino' );
		$page_id    = absint( $activity->page_id );
		$page_title = $page_id ? get_the_title( $page_id ) : '';
		$page_url   = $page_id ? get_permalink( $page_id ) : '';
		$edit_url   = add_query_arg(
			array(
				'pencil-edit'  => '1',
				'pencil-field' => $activity->field_id,
			),
			$page_url ? $page_url : home_url( '/' )
		);

		$who_markup = sprintf(
			'<span class="pencil-admin__user">%s<span>%s</span></span>',
			get_avatar( absint( $activity->user_id ), 20, '', '', array( 'class' => 'pencil-admin__avatar' ) ),
			esc_html( $user_name )
		);

		if ( $page_title && $page_url ) {
			$where_markup = sprintf( '<a href="%s">%s</a>', esc_url( $page_url ), esc_html( $page_title ) );
		} else {
			$where_markup = sprintf( '<span class="pencil-admin__muted">%s</span>', esc_html__( 'a site-wide field', 'pencilino-by-rino' ) );
		}

		$when_markup = sprintf( '<span class="pencil-admin__when">%s</span>', esc_html( $when ) );
		?>
		<div class="pencil-admin__record-head">
			<div class="pencil-admin__record-col pencil-admin__record-col--before">
				<strong class="pencil-admin__field" title="<?php echo esc_attr( $activity->field_id ); ?>"><?php echo esc_html( $activity->field_label ); ?></strong>
			</div>
			<span class="pencil-admin__record-gutter" aria-hidden="true"></span>
			<div class="pencil-admin__record-col pencil-admin__record-col--after">
				<p class="pencil-admin__meta-line">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: 1: user name with avatar, 2: page or location, 3: date and time. */
							__( '%1$s updated %2$s on %3$s', 'pencilino-by-rino' ),
							$who_markup,
							$where_markup,
							$when_markup
						)
					);
					?>
				</p>
				<a class="pencil-admin__edit" href="<?php echo esc_url( $edit_url ); ?>">
					<?php esc_html_e( 'Edit', 'pencilino-by-rino' ); ?>
				</a>
			</div>
		</div>
		<div class="pencil-admin__change">
			<?php self::render_value( __( 'Before:', 'pencilino-by-rino' ), $activity->old_value, $activity->field_type ); ?>
			<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			<?php self::render_value( __( 'After:', 'pencilino-by-rino' ), $activity->new_value, $activity->field_type ); ?>
		</div>
		<?php
	}

	/**
	 * Render one before or after value box.
	 *
	 * Rich text keeps its formatting so a change such as adding bold is visible.
	 *
	 * @param string $label      Screen-reader label.
	 * @param string $value      Stored value.
	 * @param string $field_type Field type.
	 * @return void
	 */
	private static function render_value( $label, $value, $field_type ) {
		$is_rich = 'richtext' === $field_type && '' !== trim( wp_strip_all_tags( (string) $value ) );
		?>
		<div class="pencil-admin__value-text<?php echo $is_rich ? ' pencil-admin__value-text--rich' : ''; ?>">
			<span class="screen-reader-text"><?php echo esc_html( $label ); ?> </span>
			<?php
			if ( $is_rich ) {
				echo wp_kses( $value, Pencil_Fields::richtext_tags() );
			} else {
				echo esc_html( self::display_value( $value, $field_type ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Return a readable plain-text activity value.
	 *
	 * @param string $value      Stored value.
	 * @param string $field_type Field type.
	 * @return string
	 */
	private static function display_value( $value, $field_type ) {
		if ( 'image' === $field_type ) {
			$attachment_id = absint( $value );

			if ( ! $attachment_id ) {
				return __( 'No image', 'pencilino-by-rino' );
			}

			$title = get_the_title( $attachment_id );

			return $title ? $title : sprintf(
				/* translators: %d: Media attachment ID. */
				__( 'Media #%d', 'pencilino-by-rino' ),
				$attachment_id
			);
		}

		if ( 'button' === $field_type ) {
			$button = json_decode( (string) $value, true );

			if ( is_array( $button ) && ! empty( $button['text'] ) ) {
				return sprintf( '%s (%s)', $button['text'], isset( $button['url'] ) ? $button['url'] : '' );
			}

			return __( 'Empty', 'pencilino-by-rino' );
		}

		$value = trim( wp_strip_all_tags( (string) $value ) );

		if ( '' === $value ) {
			return __( 'Empty', 'pencilino-by-rino' );
		}

		return $value;
	}
}
