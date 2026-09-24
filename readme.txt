=== Pencil by Rino ===
Contributors: rinodeboer
Tags: frontend editing, ai, custom theme, content editing, client editing
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend content editing for AI-built WordPress themes. The theme owns the design, your client edits the words and images.

== Description ==

Pencil is the missing piece for websites built with an AI tool like Codex or Claude instead of a page builder.

An AI can build a fast, custom WordPress theme. But once it is done, the client cannot change a single word without calling you. Pencil fixes that. Every piece of static text, every image and every button the AI writes into the theme becomes a field the client can edit right on the live page, with one **Open Pencil** button.

The theme keeps full control over layout, styling and behaviour. Clients only change content. They cannot break the design, because the design was never theirs to touch.

**One field, one owner**

Content that already belongs to another system stays there. A product price stays in WooCommerce, a custom field stays in ACF, a post title stays in WordPress. Pencil marks that output as a read-only region with a link to the right edit screen, and never stores a second copy.

**Important: Pencil does nothing on its own**

Pencil needs an AI agent to build the theme. The plugin ships with instructions for that agent, in the **Get started** tab. It will not make an existing page builder or block theme editable, and the quality of the site depends on the model you use, not on this plugin.

**What you get**

* Single-line text, rich text (bold, italic, links, lists), buttons with editable text and link, and Media Library images
* Fixed image frames, so a replaced image never changes the layout
* Per-page content for templates shared by several pages
* An **Open Pencil** button on the frontend for Administrators and Editors
* A **Changes** tab listing who changed what, where and when, with before and after
* A **Get started** tab with separate workflows for building from an idea or converting an HTML template
* Copyable starter prompts and complete instructions for your AI coding agent
* A reusable default theme screenshot for projects that do not yet have a custom one
* PHP helpers for the theme: `pencil_text()`, `pencil_richtext()`, `pencil_button()`, `pencil_image()`, and `pencil_managed_region_open()` / `pencil_managed_region_close()`

== Installation ==

1. Install and activate Pencil.
2. Open **Pencil → Get started** and choose whether to start from an idea or convert an existing HTML template.
3. Copy the matching starter prompt and give it to a coding tool such as Codex, Claude, Cursor, or another agent with access to the WordPress project.
4. Activate the theme the agent built. Check that every public page appears under **Pages** and that the homepage is set as the static front page.
5. Open the site while logged in as an Administrator or Editor and click **Open Pencil**.

== Frequently Asked Questions ==

= Does Pencil work with my existing theme or page builder? =

No. Pencil only works with a theme that was built to use its helpers. Sites built with a page builder or with block templates are not supported.

= Who can edit content? =

Administrators and Editors. Use the `pencil_can_edit` filter to change that.

= Is my content safe when the theme changes? =

Yes. Each field has a stable ID, and a saved value always wins over the theme's default. When the AI redesigns a section later, the client's content stays.

== Changelog ==

= 0.9.1 =
* Protect the frontend WordPress Media Library from common theme CSS class collisions.
* Warn developers when the media modal remains collapsed after opening.
* Add theme-building guidance and checks for avoiding WordPress core UI class names and unsafe global CSS resets.

= 0.9 =
* First public release.
