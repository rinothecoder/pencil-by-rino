# Pencilino by Rino

Frontend content editing for AI-built WordPress themes. The theme owns the design; clients edit the words and images directly on the website.

Pencilino is designed for custom themes built by coding agents such as Codex, Claude, Cursor, or another connected agent. It provides a controlled content layer rather than a page builder.

## What Pencilino does

- Edits text, rich text, buttons, and Media Library images on the frontend.
- Keeps layout, styling, and behaviour in the theme.
- Preserves content through stable field IDs when the theme design changes.
- Marks content owned by WordPress, ACF, JetEngine, WooCommerce, or another provider as managed elsewhere.
- Records content changes with the editor, page, time, and before-and-after values.
- Supports private comments with pinpoints, a separate inbox, and resolving feedback.
- Offers an optional Clients role with simplified editing screens and frontend-only static page editing.
- Includes two agent workflows: build a theme from an idea or convert an HTML template.

## Requirements

- WordPress 6.4 or newer
- PHP 7.4 or newer
- A custom theme built to use Pencilino's helper functions

Pencilino does not automatically make an existing page-builder or block theme editable.

## Installation

1. Download the versioned `pencilino-by-rino-*.zip` from the latest GitHub release.
2. In WordPress, open **Plugins → Add Plugin → Upload Plugin**.
3. Upload the ZIP and activate **Pencilino by Rino**.
4. Open **Pencilino → Get started**.
5. Copy the starter prompt for your workflow and give it to your coding agent.

## Building a compatible theme

The complete contract for coding agents is available in [`docs/agent-instructions.md`](docs/agent-instructions.md) and inside WordPress under **Pencilino → Get started → For AI Agents**.

The theme remains responsible for its design and safe fallback output. Pencilino owns only content registered through its helpers:

- `pencil_text()`
- `pencil_richtext()`
- `pencil_button()`
- `pencil_image()`
- `pencil_managed_region_open()` and `pencil_managed_region_close()`

## Version 2.0.3

Client logout appears separately at the bottom right only while Pencilino is open. Logging out opens a screen with a prominent **Back to website** button. Editing and comment popups follow the clicked point.

Version 2 uses the `pencilino-by-rino` plugin folder and entry file. When upgrading an older `pencil-by-rino` installation, deactivate the old plugin before activating Pencilino. Saved fields and change history remain compatible.

The WordPress.org-compatible plugin source does not include the former GitHub updater. Pushing commits does not update installed plugins.

[Share an idea or report a problem](https://rinodeboer.fillout.com/pencil-by-rino)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## GitHub updates

Published stable releases appear in WordPress plugin updates. No GitHub token or wp-config.php change is needed. Use **Pencilino → Settings → Check for updates** to refresh immediately. Automatic updates follow your WordPress choice. Updates preserve content, comments, and settings.

For a release, bump the plugin version and readme stable tag, commit the changes, and publish a matching stable GitHub release such as `v2.0.4`. Attach an installable ZIP containing the `pencilino-by-rino/` folder for first installations. Existing installations without an updater need a one-time ZIP update to get this version.
