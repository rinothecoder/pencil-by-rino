<?php
/**
 * Pencil admin page: Changes, Get started, About, Changelog, Support.
 *
 * @package Pencil
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
	 * Add the Pencil top-level menu.
	 *
	 * The icon is passed as a data URI so WordPress recolours it to match the
	 * user's admin colour scheme.
	 *
	 * @return void
	 */
	public static function register_menu() {
		$svg  = file_get_contents( PENCIL_PLUGIN_PATH . 'admin/img/pencil-icon-white.svg' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		self::$page_hook = add_menu_page(
			__( 'Pencil by Rino', 'pencil-by-rino' ),
			__( 'Pencil', 'pencil-by-rino' ),
			'edit_pages',
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			$icon,
			58
		);
	}

	/**
	 * Add an "Open Pencil" shortcut to the admin bar while viewing the site.
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
				'title' => __( 'Open Pencil', 'pencil-by-rino' ),
				'href'  => esc_url( $editor_url ),
				'meta'  => array(
					'class' => 'pencil-admin-bar-shortcut',
				),
			)
		);
	}

	/**
	 * Load styles and scripts only on the Pencil admin page.
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
			wp_die( esc_html__( 'You do not have permission to view this page.', 'pencil-by-rino' ) );
		}

		$tabs = array(
			'changes'     => __( 'Changes', 'pencil-by-rino' ),
			'get-started' => __( 'Get started', 'pencil-by-rino' ),
			'about'       => __( 'About', 'pencil-by-rino' ),
			'changelog'   => __( 'Changelog', 'pencil-by-rino' ),
		);
		?>
		<div class="pencil-wrap">

			<header class="pencil-header">
				<div class="pencil-header__left">
					<img src="<?php echo esc_url( PENCIL_PLUGIN_URL . 'admin/img/pencil-icon-color.svg' ); ?>" alt="" aria-hidden="true" class="pencil-header__icon" width="22" height="22">
					<h1 class="pencil-header__title"><?php esc_html_e( 'Pencil by Rino', 'pencil-by-rino' ); ?></h1>
					<span class="pencil-header__version">v<?php echo esc_html( PENCIL_VERSION ); ?></span>
				</div>
				<nav class="pencil-header__nav">
					<?php foreach ( $tabs as $slug => $label ) : ?>
						<a href="#<?php echo esc_attr( $slug ); ?>" data-tab="<?php echo esc_attr( $slug ); ?>" class="pencil-header__link">
							<?php echo esc_html( $label ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			</header>

			<div data-tab-panel="changes"><?php self::render_changes(); ?></div>
			<div data-tab-panel="get-started" hidden><?php self::render_get_started(); ?></div>
			<div data-tab-panel="about" hidden><?php self::render_about(); ?></div>
			<div data-tab-panel="changelog" hidden><?php self::render_changelog(); ?></div>

		</div>
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
		<div class="pencil-content pencil-content--wide">

			<div class="pencil-open-card">
				<div>
					<h2><?php esc_html_e( 'Edit your content on the live site', 'pencil-by-rino' ); ?></h2>
					<p><?php esc_html_e( 'Pencil opens on the homepage. Click any highlighted text, image or button to change it.', 'pencil-by-rino' ); ?></p>
				</div>
				<a class="pencil-button" href="<?php echo esc_url( add_query_arg( 'pencil-edit', '1', home_url( '/' ) ) ); ?>">
					<?php esc_html_e( 'Open Pencil', 'pencil-by-rino' ); ?>
				</a>
			</div>

			<h2 class="pencil-changes__heading"><?php esc_html_e( 'Recent changes', 'pencil-by-rino' ); ?></h2>

			<?php if ( empty( $activities ) ) : ?>
				<div class="pencil-admin__empty">
					<span class="dashicons dashicons-clock" aria-hidden="true"></span>
					<h3><?php esc_html_e( 'No changes yet', 'pencil-by-rino' ); ?></h3>
					<p><?php esc_html_e( 'Every change saved through Pencil appears here, with the value before and after.', 'pencil-by-rino' ); ?></p>
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
		$prompt_intro      = __( 'We are going to build a custom WordPress theme that works with the Pencil by Rino plugin. Pencil has specific theme-building instructions that you must follow throughout the project. Find and read the complete instructions in WordPress admin under Pencil > Get Started > For AI Agents, or locate docs/agent-instructions.md inside the active Pencil plugin. If your environment cannot access them, ask me to copy them into this conversation.', 'pencil-by-rino' );
		$idea_prompt       = implode(
			"\n\n",
			array(
				$prompt_intro,
				__( 'Generate the website design and code inside the theme according to the prompt I provide. Build the first complete version and follow the Pencil instructions throughout the project.', 'pencil-by-rino' ),
				__( 'Website idea or design brief:', 'pencil-by-rino' ),
			)
		);
		$html_prompt       = implode(
			"\n\n",
			array(
				$prompt_intro,
				__( 'I am providing an HTML template. Start with the complete template package I provide and convert it into a maintainable custom WordPress theme by following the Pencil instructions.', 'pencil-by-rino' ),
				__( 'HTML template location or files:', 'pencil-by-rino' ),
			)
		);
		?>
		<div class="pencil-content pencil-content--onboarding">
			<div class="pencil-card pencil-card--full">
				<div data-subtabs>
					<div class="pencil-subtabs" role="tablist">
						<button type="button" class="pencil-subtabs__btn pencil-subtabs__btn--active" data-subtab="humans" role="tab" aria-selected="true"><?php esc_html_e( 'For you', 'pencil-by-rino' ); ?></button>
						<button type="button" class="pencil-subtabs__btn" data-subtab="agents" role="tab" aria-selected="false"><?php esc_html_e( 'For AI agents', 'pencil-by-rino' ); ?></button>
					</div>

					<div data-subtab-panel="humans">
						<div class="pencil-get-started__intro">
							<span class="pencil-eyebrow"><?php esc_html_e( 'Choose your starting point', 'pencil-by-rino' ); ?></span>
							<h2><?php esc_html_e( 'Build a custom theme with your AI coding tool', 'pencil-by-rino' ); ?></h2>
							<p><?php esc_html_e( 'Pencil provides the editable content layer and the rules your agent follows. Your coding tool builds the WordPress theme.', 'pencil-by-rino' ); ?></p>
						</div>

						<div class="pencil-agent-tools" aria-label="<?php esc_attr_e( 'Compatible AI coding tools', 'pencil-by-rino' ); ?>">
							<span class="pencil-agent-tools__label"><?php esc_html_e( 'Works with', 'pencil-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Codex', 'pencil-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Claude', 'pencil-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Cursor', 'pencil-by-rino' ); ?></span>
							<span class="pencil-agent-tool"><?php esc_html_e( 'Any other coding agents', 'pencil-by-rino' ); ?></span>
						</div>

						<div class="pencil-paths">
							<article class="pencil-path">
								<div class="pencil-path__icon"><span class="dashicons dashicons-art" aria-hidden="true"></span></div>
								<span class="pencil-path__number"><?php esc_html_e( 'Path 1', 'pencil-by-rino' ); ?></span>
								<h3><?php esc_html_e( 'Start from an idea', 'pencil-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Describe the website and let your agent design and build the complete theme from scratch.', 'pencil-by-rino' ); ?></p>
								<button type="button" class="pencil-button" data-copy="pencil-idea-prompt" data-copied-label="<?php esc_attr_e( 'Prompt copied', 'pencil-by-rino' ); ?>">
									<?php esc_html_e( 'Copy starter prompt', 'pencil-by-rino' ); ?>
								</button>
								<textarea id="pencil-idea-prompt" class="pencil-copy-source" readonly tabindex="-1" aria-hidden="true"><?php echo esc_textarea( $idea_prompt ); ?></textarea>
							</article>

							<article class="pencil-path">
								<div class="pencil-path__icon"><span class="dashicons dashicons-media-code" aria-hidden="true"></span></div>
								<span class="pencil-path__number"><?php esc_html_e( 'Path 2', 'pencil-by-rino' ); ?></span>
								<h3><?php esc_html_e( 'Convert an HTML template', 'pencil-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Give your agent the complete HTML package and have it preserve the design while turning it into a proper WordPress theme.', 'pencil-by-rino' ); ?></p>
								<button type="button" class="pencil-button" data-copy="pencil-html-prompt" data-copied-label="<?php esc_attr_e( 'Prompt copied', 'pencil-by-rino' ); ?>">
									<?php esc_html_e( 'Copy conversion prompt', 'pencil-by-rino' ); ?>
								</button>
								<textarea id="pencil-html-prompt" class="pencil-copy-source" readonly tabindex="-1" aria-hidden="true"><?php echo esc_textarea( $html_prompt ); ?></textarea>
							</article>
						</div>

						<h3 class="pencil-section-heading"><?php esc_html_e( 'What happens next', 'pencil-by-rino' ); ?></h3>
						<ol class="pencil-steps">
							<li>
								<strong><?php esc_html_e( 'Give the agent its starting material', 'pencil-by-rino' ); ?></strong>
								<?php esc_html_e( 'Paste the complete starter prompt into your coding tool and add your idea or HTML package. Connect the agent through project files, MCP, or another WordPress integration when available.', 'pencil-by-rino' ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Let the agent build and configure WordPress', 'pencil-by-rino' ); ?></strong>
								<?php esc_html_e( 'The agent builds the theme, creates the pages, imports editable images, and follows the complete instructions included with Pencil.', 'pencil-by-rino' ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Review the result in Pencil', 'pencil-by-rino' ); ?></strong>
								<?php esc_html_e( 'Check every page, open Pencil, and test text, buttons and image replacement before handing the website to a client.', 'pencil-by-rino' ); ?>
							</li>
						</ol>

						<div class="pencil-get-started__details">
							<div>
								<h3><?php esc_html_e( 'Later design changes', 'pencil-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Return to your agent with the same instructions. Saved content remains intact as long as the field IDs and types stay the same.', 'pencil-by-rino' ); ?></p>
							</div>
							<div>
								<h3><?php esc_html_e( 'Client access', 'pencil-by-rino' ); ?></h3>
								<p><?php esc_html_e( 'Administrators and Editors can use Pencil. The Editor role gives clients frontend editing without access to plugins, themes or settings.', 'pencil-by-rino' ); ?></p>
							</div>
						</div>
					</div>

					<div data-subtab-panel="agents" hidden>
						<div class="pencil-agent-instructions__intro">
							<span class="pencil-eyebrow"><?php esc_html_e( 'The complete contract', 'pencil-by-rino' ); ?></span>
							<h2><?php esc_html_e( 'Instructions for the coding agent', 'pencil-by-rino' ); ?></h2>
							<p><?php esc_html_e( 'Copy these instructions into any coding agent, or let a connected agent read them through the WordPress files or MCP environment.', 'pencil-by-rino' ); ?></p>
						</div>
						<div class="pencil-instructions__actions">
							<button type="button" class="pencil-button" data-copy="pencil-agent-instructions" data-copied-label="<?php esc_attr_e( 'Copied', 'pencil-by-rino' ); ?>">
								<?php esc_html_e( 'Copy instructions', 'pencil-by-rino' ); ?>
							</button>
							<span class="pencil-instructions__path"><?php esc_html_e( 'Bundled with Pencil:', 'pencil-by-rino' ); ?> <code>docs/agent-instructions.md</code></span>
						</div>
						<textarea id="pencil-agent-instructions" class="pencil-instructions__text" readonly spellcheck="false" aria-label="<?php esc_attr_e( 'Instructions for AI agents', 'pencil-by-rino' ); ?>"><?php echo esc_textarea( $instructions ); ?></textarea>
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
				<img src="<?php echo esc_url( PENCIL_PLUGIN_URL . 'admin/img/rino-profile.jpg' ); ?>" alt="Rino de Boer" class="pencil-about__photo" width="80" height="80">

				<p>Hey, my name is Rino. I'm a Dutch web designer, and I have built websites with a pagebuilder for years.</p>

				<p>Lately I've been experimenting with something else. Letting AI build the whole theme. No builder, just code. And honestly, it's fast. Really fast.</p>

				<p>But then I hit a wall. Every time.</p>

				<p>The site works, it looks good, and then I hand it to the client and they can't change a single word without calling me.</p>

				<p>And I know my clients. Their products are in WordPress. Their bookings, their team, their blog. They know where to click. They don't want to leave, and I don't want them to.</p>

				<p>So the obvious answer is a pagebuilder. But here's the uncomfortable part. Pagebuilders have become professional tools. Classes, variables, design systems. Great for us. Almost impossible for the average client, who just wants to change some text and swap a picture.</p>

				<p>So I built Pencil.</p>

				<p>The theme owns the design. Other plugins keep owning their own data. And every piece of static text or image the AI writes into the theme becomes a field the client can edit, right on the page. One field, one owner. Nothing lives in two places.</p>

				<p>Let me be honest about what Pencil is not. It does nothing on its own. It needs an AI like Codex or Claude to build the theme, following the instructions in the Get started tab. It won't make an existing site editable. And the quality of the site depends on the model you use, not on this plugin.</p>

				<p>Before it went public, I made sure it follows WordPress's plugin standards and passes the official Plugin Check.</p>

				<p>For smaller projects, I don't think we need a pagebuilder anymore. That's a strange thing to say after all those years. But it's where I am right now.</p>

				<p>I hope Pencil makes handing over an AI-built site a little easier. That's really all it's supposed to do.</p>

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

				<div class="pencil-changelog__ideas">
					<p><?php esc_html_e( 'Got an idea, or feedback on something that could be better? I would like to hear it. The best ideas end up in the plugin, with your name next to them.', 'pencil-by-rino' ); ?></p>
					<a href="<?php echo esc_url( PENCIL_FEEDBACK_URL ); ?>" target="_blank" rel="noopener noreferrer" class="pencil-button">
						<?php esc_html_e( 'Share an idea or feedback', 'pencil-by-rino' ); ?>
					</a>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v0.9.1</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencil-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'Protected the frontend Media Library from common theme CSS class collisions.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Added clearer AI-agent rules and tests for WordPress interface compatibility.', 'pencil-by-rino' ); ?></li>
					</ul>
				</div>

				<div class="pencil-changelog__release">
					<div class="pencil-changelog__release-header">
						<span class="pencil-changelog__version">v0.9</span>
						<span class="pencil-changelog__date"><?php esc_html_e( 'September 2026', 'pencil-by-rino' ); ?></span>
					</div>
					<ul class="pencil-changelog__list">
						<li><?php esc_html_e( 'First public release. Written to WordPress plugin standards and checked with the official Plugin Check.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Text, rich text, button and image fields, editable on the live page.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Managed regions for content owned by ACF, JetEngine, WooCommerce or WordPress, with a link to the right edit screen.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Per-page content for templates shared by several pages.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'A Changes log with who, where, when, and the value before and after.', 'pencil-by-rino' ); ?></li>
						<li><?php esc_html_e( 'Instructions for AI agents, included in the plugin.', 'pencil-by-rino' ); ?></li>
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
				<h2><?php esc_html_e( 'Found a bug?', 'pencil-by-rino' ); ?></h2>
				<p><?php esc_html_e( 'Pencil is a free plugin. There is no official support, but if you run into a bug I would like to know about it so I can fix it.', 'pencil-by-rino' ); ?></p>
				<p><?php esc_html_e( 'The best place to report a bug is the WordPress support forum. Describe what happened, which AI tool built the theme, and I will take a look when I can.', 'pencil-by-rino' ); ?></p>
				<a href="<?php echo esc_url( PENCIL_SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer" class="pencil-button">
					<?php esc_html_e( 'Go to support forum', 'pencil-by-rino' ); ?>
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
		$user_name  = $activity->user_name ? $activity->user_name : __( 'Unknown user', 'pencil-by-rino' );
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
			$where_markup = sprintf( '<span class="pencil-admin__muted">%s</span>', esc_html__( 'a site-wide field', 'pencil-by-rino' ) );
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
							__( '%1$s updated %2$s on %3$s', 'pencil-by-rino' ),
							$who_markup,
							$where_markup,
							$when_markup
						)
					);
					?>
				</p>
				<a class="pencil-admin__edit" href="<?php echo esc_url( $edit_url ); ?>">
					<?php esc_html_e( 'Edit', 'pencil-by-rino' ); ?>
				</a>
			</div>
		</div>
		<div class="pencil-admin__change">
			<?php self::render_value( __( 'Before:', 'pencil-by-rino' ), $activity->old_value, $activity->field_type ); ?>
			<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			<?php self::render_value( __( 'After:', 'pencil-by-rino' ), $activity->new_value, $activity->field_type ); ?>
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
				return __( 'No image', 'pencil-by-rino' );
			}

			$title = get_the_title( $attachment_id );

			return $title ? $title : sprintf(
				/* translators: %d: Media attachment ID. */
				__( 'Media #%d', 'pencil-by-rino' ),
				$attachment_id
			);
		}

		if ( 'button' === $field_type ) {
			$button = json_decode( (string) $value, true );

			if ( is_array( $button ) && ! empty( $button['text'] ) ) {
				return sprintf( '%s (%s)', $button['text'], isset( $button['url'] ) ? $button['url'] : '' );
			}

			return __( 'Empty', 'pencil-by-rino' );
		}

		$value = trim( wp_strip_all_tags( (string) $value ) );

		if ( '' === $value ) {
			return __( 'Empty', 'pencil-by-rino' );
		}

		return $value;
	}
}
