# Pencil by Rino

Frontend content editing for AI-built WordPress themes. The theme owns the design; clients edit the words and images directly on the website.

Pencil is designed for custom themes built by coding agents such as Codex, Claude, Cursor, or another connected agent. It provides a controlled content layer rather than a page builder.

## What Pencil does

- Edits text, rich text, buttons, and Media Library images on the frontend.
- Keeps layout, styling, and behaviour in the theme.
- Preserves content through stable field IDs when the theme design changes.
- Marks content owned by WordPress, ACF, JetEngine, WooCommerce, or another provider as managed elsewhere.
- Records content changes with the editor, page, time, and before-and-after values.
- Includes two agent workflows: build a theme from an idea or convert an HTML template.

## Requirements

- WordPress 6.4 or newer
- PHP 7.4 or newer
- A custom theme built to use Pencil's helper functions

Pencil does not automatically make an existing page-builder or block theme editable.

## Installation

1. Download `pencil-by-rino.zip` from the latest GitHub release.
2. In WordPress, open **Plugins → Add Plugin → Upload Plugin**.
3. Upload the ZIP and activate **Pencil by Rino**.
4. Open **Pencil → Get started**.
5. Copy the starter prompt for your workflow and give it to your coding agent.

## Building a compatible theme

The complete contract for coding agents is available in [`docs/agent-instructions.md`](docs/agent-instructions.md) and inside WordPress under **Pencil → Get started → For AI Agents**.

The theme remains responsible for its design and safe fallback output. Pencil owns only content registered through its helpers:

- `pencil_text()`
- `pencil_richtext()`
- `pencil_button()`
- `pencil_image()`
- `pencil_managed_region_open()` and `pencil_managed_region_close()`

## Status

Version 0.9.2 is a public prerelease for testing and feedback before a possible WordPress.org 1.0 release.

[Share an idea or report a problem](https://rinodeboer.fillout.com/pencil-by-rino)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
