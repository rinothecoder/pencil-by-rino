<?php
if (!defined('ABSPATH')) { exit; }

/** Published public GitHub releases; no site token is required. */
final class Pencilino_Updates {
    const REPO = 'rinothecoder/pencilino';
    const UPDATE_URI = 'https://github.com/rinothecoder/pencilino/';
    const API = 'https://api.github.com/repos/rinothecoder/pencilino';
    const MAX_PACKAGE_BYTES = 20971520;

    public static function boot() {
        add_filter('update_plugins_github.com', [__CLASS__, 'update'], 10, 4);
        add_filter('plugins_api', [__CLASS__, 'information'], 10, 3);
        add_filter('upgrader_pre_download', [__CLASS__, 'download'], 10, 4);
        add_filter('upgrader_source_selection', [__CLASS__, 'source'], 10, 4);
        add_action('admin_post_pencilino_check_updates', [__CLASS__, 'check']);
    }
    private static function cache_key() { return 'pencilino_github_public_release'; }
    private static function args() {
        return ['timeout'=>15, 'redirection'=>0, 'limit_response_size'=>262144,
            'user-agent'=>'Pencilino Updater/' . PENCIL_VERSION,
            'headers'=>['Accept'=>'application/vnd.github+json'], 'cookies'=>[]];
    }
    private static function error($code, $message) { return new WP_Error('pencilino_' . $code, $message); }
    private static function json($path) {
        $response = wp_safe_remote_get(self::API . $path, self::args());
        if (is_wp_error($response)) { return self::error('github_unavailable', 'Could not reach GitHub. Your editor continues working normally.'); }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code === 401 || $code === 404) {
            return self::error('github_access', 'The public Pencilino repository is unavailable. Try again later.');
        }
        if ($code !== 200) { return self::error('github_unavailable', 'GitHub could not complete the check. Try again later.'); }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : self::error('github_invalid', 'GitHub returned invalid release information.');
    }
    public static function validate($data, $commit) {
        if (!is_array($data) || !empty($data['draft']) || !empty($data['prerelease'])
            || !is_string($data['tag_name'] ?? null) || !preg_match('/^v?(\d+\.\d+\.\d+)$/D', $data['tag_name'], $version)
            || !is_string($commit) || !preg_match('/^[a-f0-9]{40}$/D', $commit)) {
            return self::error('github_invalid', 'Publish a stable Pencilino release with a tag such as v0.1.3.');
        }
        return ['version'=>$version[1], 'commit'=>$commit, 'download_url'=>self::API . '/zipball/' . $commit,
            'homepage'=>self::UPDATE_URI . 'releases/tag/' . rawurlencode($data['tag_name']),
            'changelog'=>nl2br(esc_html(substr((string)($data['body'] ?? ''), 0, 16000)))];
    }
    public static function release($force = false) {
        $cached = $force ? false : get_site_transient(self::cache_key());
        if (is_array($cached)) { return $cached['release'] ?? self::error($cached['code'], $cached['error']); }
        $release = self::json('/releases/latest');
        if (is_wp_error($release) && $release->get_error_code() === 'pencilino_github_access') {
            // GitHub returns 404 when there are no published releases.
            $repo = self::json('');
            if (!is_wp_error($repo)) { $release = self::error('github_no_release', 'No published Pencilino release yet.'); }
        }
        if (!is_wp_error($release)) {
            $tag = $release['tag_name'] ?? '';
            if (!is_string($tag) || !preg_match('/^v?\d+\.\d+\.\d+$/D', $tag) || !empty($release['draft']) || !empty($release['prerelease'])) {
                $release = self::error('github_invalid', 'Publish a stable Pencilino release with a tag such as v0.1.3.');
            } else {
                // Pin the offered release to its commit, not a movable branch or tag.
                $commit = self::json('/commits/' . rawurlencode($tag));
                $release = is_wp_error($commit) ? $commit : self::validate($release, $commit['sha'] ?? '');
            }
        }
        set_site_transient(self::cache_key(), ['release'=>is_wp_error($release) ? null : $release,
            'code'=>is_wp_error($release) ? substr($release->get_error_code(), strlen('pencilino_')) : '',
            'error'=>is_wp_error($release) ? $release->get_error_message() : ''],
            is_wp_error($release) ? 15 * MINUTE_IN_SECONDS : 6 * HOUR_IN_SECONDS);
        return $release;
    }
    public static function update($update, $plugin_data, $plugin_file, $locales) {
        if ($plugin_file !== plugin_basename(PENCIL_PLUGIN_FILE)) { return $update; }
        $release = self::release();
        if (is_wp_error($release)) { return false; }
        return ['id'=>self::UPDATE_URI, 'slug'=>'pencilino-by-rino', 'version'=>$release['version'],
            'url'=>$release['homepage'], 'package'=>$release['download_url'],
            'requires'=>'6.4', 'requires_php'=>'7.4', 'pencilino_commit'=>$release['commit']];
    }
    public static function information($result, $action, $args) {
        if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'pencilino-by-rino') { return $result; }
        $release = self::release();
        if (is_wp_error($release)) { return $release; }
        return (object)['name'=>'Pencilino by Rino', 'slug'=>'pencilino-by-rino', 'version'=>$release['version'], 'author'=>'Rino de Boer',
            'homepage'=>$release['homepage'], 'requires'=>'6.4', 'requires_php'=>'7.4', 'download_link'=>$release['download_url'],
            'sections'=>['description'=>'Frontend content editing for AI-built WordPress themes.', 'changelog'=>$release['changelog']]];
    }
    private static function own_update($extra) {
        return ($extra['plugin'] ?? '') === plugin_basename(PENCIL_PLUGIN_FILE)
            && ($extra['action'] ?? '') === 'update' && ($extra['type'] ?? '') === 'plugin';
    }
    private static function offered() {
        $updates = get_site_transient('update_plugins');
        return $updates->response[plugin_basename(PENCIL_PLUGIN_FILE)] ?? null;
    }
    /** Only the published release commit may be downloaded. */
    public static function package_allowed($url, $commit) {
        return is_string($commit) && preg_match('/^[a-f0-9]{40}$/D', $commit) && $url === self::API . '/zipball/' . $commit;
    }
    public static function download_url_allowed($url, $commit) {
        if (!is_string($url) || strlen($url) > 4096 || !preg_match('/^[a-f0-9]{40}$/D', (string)$commit)) { return false; }
        $parts = wp_parse_url($url);
        return $parts && ($parts['scheme'] ?? '') === 'https' && ($parts['host'] ?? '') === 'codeload.github.com'
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['fragment'])
            && (!isset($parts['port']) || $parts['port'] === 443)
            && in_array($parts['path'] ?? '', ['/' . self::REPO . '/legacy.zip/' . $commit, '/' . self::REPO . '/zip/' . $commit], true);
    }
    public static function download($reply, $package, $upgrader, $extra) {
        if (!self::own_update($extra) || is_wp_error($reply)) { return $reply; }
        $offered = self::offered();
        if (!$offered || ($offered->package ?? '') !== $package
            || !self::package_allowed($package, $offered->pencilino_commit ?? '')) {
            return self::error('package_unverified', 'Check for Pencilino updates again before installing.');
        }
        if (!version_compare($offered->new_version ?? '0', PENCIL_VERSION, '>')) {
            return self::error('package_old', 'This release is not newer than the installed version.');
        }
        // Resolve the archive without sending site credentials or cookies.
        $response = wp_safe_remote_get($package, self::args());
        if (is_wp_error($response) || (int)wp_remote_retrieve_response_code($response) !== 302) {
            return self::error('package_unavailable', 'GitHub could not provide the update download. Try checking for updates again.');
        }
        $url = wp_remote_retrieve_header($response, 'location');
        if (!self::download_url_allowed($url, $offered->pencilino_commit)) {
            return self::error('package_redirect', 'GitHub returned an unexpected update download location.');
        }
        if (!function_exists('wp_tempnam')) { require_once ABSPATH . 'wp-admin/includes/file.php'; }
        $file = wp_tempnam('pencilino-update.zip');
        if (!$file) { return self::error('package_temp', 'Could not create a temporary update file.'); }
        $response = wp_safe_remote_get($url, ['timeout'=>60, 'redirection'=>0, 'stream'=>true, 'filename'=>$file,
            'limit_response_size'=>self::MAX_PACKAGE_BYTES, 'user-agent'=>'Pencilino Updater/' . PENCIL_VERSION,
            'cookies'=>[], 'headers'=>[]]);
        $handle = fopen($file, 'rb'); $signature = $handle ? fread($handle, 4) : '';
        if ($handle) { fclose($handle); }
        if (is_wp_error($response) || (int)wp_remote_retrieve_response_code($response) !== 200
            || $signature !== "PK\x03\x04" || filesize($file) >= self::MAX_PACKAGE_BYTES) {
            wp_delete_file($file);
            return self::error('package_invalid', 'The update ZIP could not be downloaded. Your installed plugin has not been changed.');
        }
        return $file;
    }
    public static function source($source, $remote, $upgrader, $extra) {
        if (!self::own_update($extra) || is_wp_error($source)) { return $source; }
        global $wp_filesystem;
        $offered = self::offered();
        $header = $wp_filesystem->get_contents(trailingslashit($source) . 'pencilino-by-rino.php');
        $core = $header;
        if (!$offered || !is_string($header) || !is_string($core)
            || !preg_match('/^\s*\* Plugin Name:\s*Pencilino by Rino\s*$/m', $header)
            || !preg_match('/^\s*\* Version:\s*(\d+\.\d+\.\d+)\s*$/m', $header, $version)
            || $version[1] !== ($offered->new_version ?? '') || false === strpos($core, "define( 'PENCIL_VERSION', '" . $version[1] . "' );")) {
            return self::error('package_version', 'The downloaded plugin does not match the published release version.');
        }
        foreach (['includes/class-pencil-plugin.php', 'includes/class-pencil-fields.php', 'includes/class-pencil-admin.php', 'includes/class-pencil-activity.php', 'includes/class-pencil-client.php', 'includes/class-pencil-comments.php', 'includes/template-functions.php', 'includes/class-pencilino-updates.php', 'assets/js/editor.js', 'assets/css/editor.css', 'admin/js/admin.js', 'admin/css/admin.css'] as $required) {
            if (!$wp_filesystem->is_file(trailingslashit($source) . $required)) { return self::error('package_incomplete', 'The published Pencilino release is missing required files.'); }
        }
        // GitHub archives use an owner/repo/hash folder; WordPress needs pencilino/.
        $target = trailingslashit(dirname(untrailingslashit($source))) . 'pencilino-by-rino/';
        if (untrailingslashit($source) === untrailingslashit($target)) { return $source; }
        return $wp_filesystem->move($source, $target, true) ? $target : self::error('package_folder', 'Could not prepare the Pencilino update folder.');
    }
    public static function check() {
        if (!current_user_can('update_plugins')) { wp_die('You cannot check plugin updates.'); }
        check_admin_referer('pencilino_check_updates');
        self::release(true); wp_clean_plugins_cache(); wp_update_plugins();
        wp_safe_redirect(admin_url('admin.php?page=pencil-by-rino#settings')); exit;
    }
    public static function screen() {
        $cached = get_site_transient(self::cache_key());
        $release = is_array($cached) ? ($cached['release'] ?? null) : null;
        $message = ($release ? (version_compare($release['version'], PENCIL_VERSION, '>') ? 'Version ' . $release['version'] . ' is available.' : 'You have the latest published version.')
                : ($cached['error'] ?? 'Ready to check GitHub for published releases.'));
        ?>
        <section id="pencilino-updates" class="pencil-content"><div class="pencil-card pencil-card--full"><h2>Plugin updates</h2>
            <p>Installed version: <strong><?php echo esc_html(PENCIL_VERSION); ?></strong></p>
            <p role="status"><?php echo esc_html($message); ?></p>
            <p class="pencil-settings-notice">Updates come directly from GitHub releases. Updates keep your content, comments, and settings. Automatic updates follow your WordPress choice.</p>
            <?php if (current_user_can('update_plugins')): ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="pencilino_check_updates"><?php wp_nonce_field('pencilino_check_updates'); ?><button class="pencil-button pencil-button--quiet">Check for updates</button></form>
                <?php if ($release && version_compare($release['version'], PENCIL_VERSION, '>')): ?><p><a class="pencil-button" href="<?php echo esc_url(admin_url('plugins.php')); ?>">Open Plugins to update</a></p><?php endif; ?>
            <?php endif; ?>
            <p class="pencil-settings-notice">This public repository needs no GitHub token or wp-config.php changes. Existing site tokens are not used by Pencilino.</p>
        </div></section>
        <?php
    }
}
