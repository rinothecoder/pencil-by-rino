# Pencil: Instructions for AI Agents Building the Theme

## Role

You are building or modifying a custom WordPress theme that integrates with the Pencil plugin.

This is an AI-first alternative to the traditional page-builder workflow. You handle design, layout, new elements, functionality, and initial content in theme code. Pencil gives clients protected frontend editing for all static site content after the theme is built.

Build normal, maintainable WordPress theme code. Pencil is a controlled content layer, not a drag-and-drop layout system.

## Choose the starting path

Every Pencil theme starts through one of two paths. Determine which path applies before coding. If the user has not made it clear, ask them to choose.

### Path 1: Start from an idea

Use this path when there is no finished HTML template. Work from the user's design brief, content, brand assets, references, and functional requirements.

1. Inspect everything the user supplied and the existing WordPress installation.
2. Confirm the intended pages, primary action, navigation approach, dynamic content, integrations, and important design constraints.
3. Create the information architecture and visual direction before building when those decisions are not already defined.
4. Build the custom theme and its responsive behaviour directly in WordPress.
5. Apply the complete Pencil contract in this document from the first field onward.

Do not require an HTML prototype as an intermediate deliverable. It is valid to design and build the WordPress theme directly.

### Path 2: Convert an HTML template

Use this path when the user supplies an existing HTML design. Request the complete template package: HTML, CSS, JavaScript, fonts, images, and any build files or dependencies it needs.

Before converting, inventory:

- every intended public page and template;
- every section, heading, paragraph, label, button, link, image, form, and navigation item;
- local and external CSS, JavaScript, fonts, images, icons, and other assets;
- responsive behaviour, animation, and interactive states;
- content that is static, content that belongs to WordPress, and content owned by another plugin or API.

Then split the static template into maintainable WordPress theme files, enqueue its assets through WordPress, preserve the design and responsive behaviour, and replace static client-facing content with the correct Pencil integration. Do not paste the complete HTML into Gutenberg, a single PHP string, or one unmaintainable template file.

### Requirements shared by both paths

The agent needs access to the complete WordPress project, not only a screenshot or isolated HTML file. It also needs a way to create pages, configure WordPress, and import Media Library files through WP-CLI, an API, an integration, or the WordPress admin.

If optional installed skills are relevant to the task, name the ones you propose to use and ask the user before applying them.

Both paths finish with the same required checks at the end of this document.

## Pencil theme compatibility contract

Pencil does not automatically make an ordinary WordPress theme editable. You must build and maintain a Pencil-compatible theme that follows this contract from the start.

The plugin and theme have separate responsibilities:

- Pencil provides the frontend editing interface, storage, validation, permissions, and WordPress Media Library integration.
- The theme registers every static client-facing content field, supplies stable field IDs, and defines the layout and presentation constraints around those fields.
- WordPress, ACF, JetEngine, WooCommerce, and other providers continue to own their dynamic or structured content.

Do not finish a theme by adding Pencil to only the most important headings or images. Pencil compatibility applies to the complete rendered theme. Every new section or component added during later development must follow the same contract.

## Required safe theme integration

The website must keep rendering its initial content if Pencil is temporarily unavailable or deactivated. Never let a missing plugin function cause a fatal error on the public site.

- Put theme-prefixed wrapper functions in `functions.php` for text, rich text, buttons, images, and managed regions.
- Inside each wrapper, call the matching Pencil helper only when `function_exists()` confirms that it is available.
- Otherwise render the supplied default value with the correct escaping and the same theme-controlled classes and surrounding layout.
- Do not redeclare functions whose names begin with `pencil_`; those belong to the plugin.
- After building, temporarily deactivate Pencil and verify that every public page still loads without PHP errors. Then reactivate Pencil and verify editing.

Minimal text-wrapper pattern:

```php
function project_text( $id, $args = array() ) {
    if ( function_exists( 'pencil_text' ) ) {
        pencil_text( $id, $args );
        return;
    }

    echo esc_html( isset( $args['default'] ) ? $args['default'] : '' );
}
```

Use a prefix derived from the real theme slug instead of `project_`, and apply the same pattern to the other Pencil field types. The fallback exists for resilience; Pencil remains a required plugin for frontend editing.

## Content continuity

You may write the initial content for a Pencil-owned field by providing its `default` value. If a client later saves a replacement through Pencil, that saved value becomes the current content and must take precedence over the theme default.

When continuing development:

- Preserve existing Pencil field IDs and field types.
- Treat the rendered or saved Pencil value as the latest version of the content.
- Do not change a theme default with the expectation that it will overwrite a client's saved value.
- Keep client content intact while changing layout, markup, styling, or functionality around it.
- Use a new Pencil field only for genuinely new content, with a stable descriptive ID and an appropriate initial default.

Content continuity depends on stable field IDs and Pencil storage. It does not require copying content into theme files or synchronising Pencil with the AI tool.

## Non-negotiable rules

1. The theme owns layout, HTML structure, CSS, JavaScript, responsive behaviour, animations, and functionality.
2. Every content value has one owner and one source of truth.
3. Use Pencil helpers for every static client-facing content value authored in the theme. You may provide the initial value, but a client's saved Pencil value overrides that default.
4. Never copy, mirror, or synchronise data from ACF, JetEngine, WooCommerce, WordPress, or external APIs into Pencil.
5. Never make layout, CSS, HTML, classes, spacing, or typography editable through Pencil.
6. Use semantic HTML and keep the markup around Pencil helper calls under theme control.
7. Do not add editor-only interfaces for visitors; Pencil handles edit-mode metadata and assets.
8. Import every static client-facing image into the WordPress Media Library and render it through a stable Pencil image field. Never leave such images in the theme directory.
9. Give every Pencil image field a design-defined aspect ratio and enforce a fixed image frame. Replacing an image must never change the component's dimensions or page layout.
10. Create a normal WordPress Page record for every public page. A theme template by itself is not a complete page.
11. Make the homepage a real published Page and assign it as the static front page in WordPress.
12. Do not use Gutenberg block content to build Pencil-managed page layouts.

## Pages, URLs, and templates

WordPress Pages remain the source of truth for page administration and routing even though Gutenberg is not used as the page builder.

For every public page:

1. Create or reuse a normal WordPress Page record.
2. Set its administrative title, slug, publication status, parent, and template assignment.
3. Build its layout with the correct theme template, using WordPress template hierarchy conventions.
4. Register all static rendered content through Pencil fields.
5. Verify that the page appears under **Pages** and that its public URL renders the intended template.

For the homepage:

1. Create or reuse a published Page named **Home**.
2. Set WordPress to display a static page and assign **Home** as `page_on_front`.
3. Put the homepage layout in `front-page.php`, not in the fallback `index.php`.
4. Keep the Page's block content empty and hide the Gutenberg content editor when it is not used.

The WordPress Page record owns its dashboard entry, status, slug, URL, hierarchy, and template assignment. The theme owns the layout. Pencil owns the static content shown by the template. Do not store a full rendered page in `post_content`, and do not leave a public template without a corresponding Page record.

If several pages share one design, such as individual coffee-flavour pages, create a Page record for every flavour and use a stable page-specific Pencil scope so their content remains separate. Use a custom post type instead only when the items form a genuine structured collection rather than normal site pages.

To scope a field to its page, pass `'scope' => 'page'` to every Pencil helper in the shared template. Pencil then stores a separate value for each page, under `page-{page ID}.{field ID}`. Decide this when the field is first created: adding or removing `scope` later changes the stored ID and disconnects any saved client value.

## Navigation

Confirm which navigation approach the user wants before implementing it. Pencil supports both approaches.

### Theme-defined navigation

Use this for a tightly controlled, fully agentic site where navigation structure belongs to the theme.

- Create every destination as a real WordPress Page.
- Generate internal URLs with `home_url()`, `get_permalink()`, or other WordPress URL functions. Never hardcode the site's domain.
- Render static navigation labels with stable `pencil_text()` fields through the theme's safe wrapper.
- Use `pencil_button()` when both the label and URL should be client-editable. It outputs an anchor and may be styled as a plain text link when appropriate.

### WordPress-managed navigation

Use this when the user wants to manage menu items, labels, URLs, nesting, and order through WordPress.

1. Register named menu locations with `register_nav_menus()`.
2. Render them with `wp_nav_menu()` using semantic theme-controlled containers and classes.
3. Create and assign the initial menu through WordPress rather than relying on an accidental page-list fallback.
4. Treat the menu data as WordPress-owned content. Do not duplicate its labels or URLs in Pencil fields.
5. Mark the rendered menu as one WordPress-managed region and link its edit action to the appropriate WordPress menu screen when that screen exists.
6. Verify that adding, removing, nesting, reordering, and renaming menu items in WordPress updates the frontend correctly.

Example registration and rendering:

```php
register_nav_menus(
    array(
        'primary' => __( 'Primary navigation', 'project-theme' ),
    )
);

project_managed_region_open(
    array(
        'provider' => 'wordpress',
        'label'    => __( 'Editable in WordPress Menus', 'project-theme' ),
        'edit_url' => admin_url( 'nav-menus.php' ),
    )
);

wp_nav_menu(
    array(
        'theme_location' => 'primary',
        'container'      => false,
        'fallback_cb'    => false,
    )
);

project_managed_region_close();
```

In production, call the managed-region helpers through the theme's safe wrappers described above.

## Theme identity and presentation

Every generated theme must look intentional in **Appearance → Themes**.

- Use a website-specific theme name, such as `{Website name} by Pencil`. Never leave names such as “Test Theme”, “AI Theme”, or “HTML Conversion”.
- Add an accurate description, version, author, and text domain to the `style.css` theme header.
- Use a stable, sanitized folder name and matching text domain.
- Add a 1200 × 900 `screenshot.png` to the theme root.
- Prefer a custom screenshot that reflects the finished website and includes a subtle Pencil maker mark.
- If a custom screenshot cannot be created, copy `assets/img/default-theme-screenshot.png` from the active Pencil plugin into the theme root as `screenshot.png`.
- Never reference the screenshot from the plugin directory. Copy it so the theme remains a complete package.

## Decide who owns content before writing code

Use this decision process for every content region:

```text
Does an existing system own this value?
│
├─ Yes: render that system's value normally.
│        Mark the surrounding output as a Pencil managed region.
│        Do not create a Pencil field for it.
│
└─ No: is it static client-facing site content?
         │
         ├─ Yes: render it with the appropriate Pencil helper.
         │       This includes labels, numbering, captions, and decorative text.
         │       Provide initial content as its default when needed.
         │       Preserve any saved client value during later development.
         └─ No: keep non-content markup or configuration under theme control.
```

Existing owners include WordPress posts, options, ACF, JetEngine, WooCommerce, and external APIs.

Do not use visual importance or intended meaning to decide whether static content is editable. If changing a hard-coded value would change what a visitor reads, register it with Pencil.

## Pencil-owned content

Use descriptive, stable IDs in this format:

```text
{scope}.{section}.{field}
```

Examples:

```text
home.hero.title
home.hero.description
home.hero.cta
global.contact.phone
```

Do not use random IDs, presentation-only IDs, or IDs that change when markup changes.

The field ID is also what allows development to continue from the client's latest content. Renaming or replacing it creates a different field and can disconnect the saved value.

Use the type that matches the content. The example below uses `project_` as a placeholder theme prefix; replace it with the real theme prefix and implement these wrappers as described above.

```php
<section class="hero">
    <div class="hero__content">
        <h1>
            <?php project_text( 'home.hero.title', [
                'label'      => 'Hero heading',
                'default'    => 'Build better websites',
                'max_length' => 80,
            ] ); ?>
        </h1>

        <?php project_richtext( 'home.hero.description', [
            'label'   => 'Hero description',
            'default' => 'We design and build websites that are easy to maintain.',
            'class'   => 'hero__description',
        ] ); ?>

        <?php project_button( 'home.hero.cta', [
            'label'   => 'Primary call to action',
            'default' => [
                'text' => 'Start a project',
                'url'  => '/contact',
            ],
            'class'   => 'site-button site-button--primary',
            'after'   => '<span class="site-button__icon" aria-hidden="true">→</span>',
        ] ); ?>
    </div>

    <div class="hero__visual">
        <?php project_image( 'home.hero.image', [
            'label'           => 'Hero image',
            'aspect_ratio'    => '3/2',
            'object_fit'      => 'cover',
            'object_position' => '50% 50%',
        ] ); ?>
    </div>
</section>
```

Keep the surrounding section, classes, and component structure in the theme. Pencil field options may declare required content constraints such as an image slot's aspect ratio, fit, and initial focal position; all other styling and layout remain in the theme.

### Field types

- `pencil_text()`: one line of plain text, such as headings, labels, numbers, and navigation labels. It outputs only the text, so wrap it in the element the design needs. Options: `label`, `default`, `max_length` (default 160).
- `pencil_richtext()`: paragraphs with bold, italic, links, and bulleted or numbered lists, such as descriptions and body copy. It always outputs `<div class="pencil-richtext">` plus any `class` you pass, for visitors and editors alike. Place it only where a block element is allowed, never inside `<p>`, a heading, `<span>`, or `<a>`. Style its inner `p`, `ul`, `ol`, and `a` elements in the theme. Options: `label`, `default` (plain text or simple HTML), `class`, `max_length` (plain-text characters, default unlimited).
- `pencil_button()`: a complete link button whose text and URL are both editable. It outputs the whole `<a>` element, so never wrap it in another link or hard-code its `href`. Options: `label`, `default` as `[ 'text' => ..., 'url' => ... ]`, `class`, `max_length` (text, default 40), `before` and `after` for decorative theme markup inside the link such as an icon, and `target => '_blank'` only when the design requires a new tab. With `before` or `after`, the editable text is wrapped in `<span class="pencil-button__text">`.
- `pencil_image()`: a Media Library image. Options: `label`, `default` (attachment ID), `size`, `class`, `loading`, `fetchpriority`, `aspect_ratio` (such as `'3/2'`), `object_fit` (default `cover` when an aspect ratio is set), and `object_position`. Pencil applies the last three as inline `aspect-ratio`, `object-fit`, and `object-position` styles on the `<img>`. The theme must still render the fixed frame described below.

All helpers accept `'scope' => 'page'` for templates shared by several pages.

## Avoid conflicts with WordPress UI on the frontend

Pencil loads the WordPress Media Library modal on the public page and may load other WordPress administration UI there in the future. The theme stylesheet remains active on that page, so a theme selector that matches a WordPress core UI class can break the editing interface.

Never define theme CSS using class names that WordPress core UI uses. Avoid `media-*`, `attachment*`, `thumbnail`, `centered`, `filename`, `details`, `check`, `button`, `button-*`, `wp-*`, `screen-reader-text`, `hidden`, `spinner`, `dashicons*`, `notice*`, `uploader-*`, and any other class prefixed with `media-` or `wp-`. Use a theme-specific prefix or name the component by its meaning, such as `project-image-frame`, `site-button`, or `hero-media`, instead of generic names.

Keep global element resets safe. Do not apply layout-changing properties such as `position`, `overflow`, `height`, `display`, or fixed widths to `*` or bare `div`, `ul`, `li`, `img`, `button`, `input`, or `label` selectors. Global `box-sizing` and margin resets are acceptable. Put layout rules on theme-owned component classes.

## Required image workflow

For every new static client-facing image:

1. Generate or obtain the image as a temporary source file.
2. Create or choose the initial image in the aspect ratio required by the design.
3. Import or upload it into the site's WordPress Media Library using the available WordPress API, CLI, MCP tool, or admin interface.
4. Add an appropriate title and alternative text in WordPress.
5. Register the returned attachment ID as the initial value of a stable `pencil_image()` field.
6. Declare the slot's `aspect_ratio`, `object_fit`, and initial `object_position`.
7. Render the image inside a fixed frame and verify that a replacement with different source dimensions is cropped without changing the layout.
8. Verify that **Replace image** opens on the WordPress Media Library.
9. Remove the temporary source file from the theme project after the Media Library import is confirmed.

Do not use `get_stylesheet_directory_uri()`, theme-relative image paths, or hard-coded CSS `url(...)` values for static client-facing images. For a hero or other visual background, render the Pencil image as a positioned `<img>` and use theme CSS such as `object-fit` and `object-position` to preserve the design.

This rule applies to editable content imagery such as hero photos, team portraits, project images, product-style visuals, and meaningful illustrations. Structural design assets such as interface icons, decorative textures, masks, cursors, and non-content SVG ornaments may remain in the theme when clients are not expected to replace them. A client-editable logo or brand image belongs in the Media Library; a purely structural icon belongs to the theme.

The theme owns the presentation of an image, including its aspect ratio, crop, sizing, position, and responsive behaviour. WordPress owns the media file and its metadata. Pencil stores the selected attachment ID so the client can replace the image without changing the layout.

The uploaded file's natural width and height must never determine the dimensions of the rendered component. Enforce the declared frame independently from the source image:

```css
.component__image-frame {
    aspect-ratio: 2 / 3;
    overflow: hidden;
}

.component__image-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}
```

A client may select an image with any source aspect ratio. Crop it into the fixed frame rather than stretching it, rejecting it, or resizing the layout. Treat focal-point adjustment as an optional enhancement; the default crop must remain predictable without it.

If an image already comes from ACF, WooCommerce, a featured-image field, or another provider, keep it with that provider and do not upload or store a duplicate in Pencil.

## Managed content from other providers

When a value comes from another provider, render it through that provider as usual. Use a managed region only to tell Pencil that the output is locked and where the editor should go instead.

Provider ownership must be identified while the theme is being built. Do not expect Pencil to detect a value's source from the final rendered HTML; after rendering, ACF, JetEngine, WooCommerce, WordPress meta, and custom PHP output may look identical.

Use this workflow for every provider-owned value:

1. Inspect the active plugins, template code, field APIs, and storage source.
2. Identify the system that owns the value and the screen where a client can edit it.
3. Render the value directly from that provider. Never copy it into Pencil.
4. Wrap the smallest useful visible value or logical group in a managed region.
5. Pass the correct provider key and a working `edit_url`.
6. Verify in frontend edit mode that the region shows `Editable in {Provider}` and that **Edit** opens the correct editing screen.

Do not wrap an entire card, post, product, loop item, or listing when its individual values can be identified. The theme owns the container and layout. Mark the title, image, price, description, and other provider fields separately so clients can see exactly what is managed.

Keep mixed ownership accurate. For example, a custom post type may be registered through ACF while its title and featured image are still WordPress-owned:

- WordPress post title, content, excerpt, or featured image: `provider => 'wordpress'`
- ACF field or ACF Options value: `provider => 'acf'`
- WooCommerce product data such as price, stock, SKU, or product image: `provider => 'woocommerce'`
- JetEngine field, relation, option, or listing value: `provider => 'jetengine'`
- Other plugins or APIs: use a stable lowercase provider key and provide a clear provider label when its name cannot be derived correctly

The edit destination depends on the actual source:

- WordPress, ACF post fields, JetEngine post meta, and WooCommerce product fields usually use the relevant post editing URL.
- ACF Options values must link to their ACF Options page.
- JetEngine options, relations, profiles, or other global data must link to the corresponding JetEngine administration screen.
- External or computed values should link to a real management screen only when one exists. Do not invent an edit destination.

For query and listing output, keep the query and layout under theme control. Mark each rendered provider value inside the result, not the whole loop or listing container.

If ownership or the correct edit destination cannot be determined, inspect the provider's registered fields, APIs, and administration screens. Do not guess. Flag the unresolved value before calling the theme complete.

Example with ACF:

```php
<section class="service-description">
    <?php project_managed_region_open( [
        'provider' => 'acf',
        'edit_url' => get_edit_post_link( $post_id ),
    ] ); ?>

    <?php the_field( 'service_description' ); ?>

    <?php project_managed_region_close(); ?>
</section>
```

Examples of managed content:

- ACF or JetEngine fields
- WordPress post title and content
- WooCommerce product details and prices
- Query and listing output
- Booking availability, calculations, logged-in-user data, and API results

Do not replace such values with a Pencil field. Do not save a second copy in Pencil.

## Required checks before completing a theme change

- Every public page has a WordPress Page record and appears under **Pages**.
- The chosen starting path was followed: direct theme build or complete HTML-template conversion.
- The homepage is a published Page assigned as the static front page.
- `front-page.php` owns the homepage layout; the fallback `index.php` is not being used as a hidden homepage.
- Gutenberg block content is not being used as the layout source for Pencil-managed pages.
- Every static client-facing content value uses one appropriate Pencil helper.
- Every static client-facing image exists in the WordPress Media Library and uses `pencil_image()`.
- Every Pencil image field declares a design aspect ratio and renders inside a fixed frame.
- Replacing an image with landscape, portrait, and square test images does not change the layout.
- On every template containing a `pencil_image()` field, enter Pencil edit mode, click **Replace image**, and confirm that the Media Library grid, attachment details panel, and **Use image** button are visible and usable at desktop and mobile widths.
- No theme class name collides with a WordPress core UI class name.
- Each Pencil ID is unique, stable, and clearly named.
- Existing Pencil IDs and saved client values remain intact during continued development.
- No editable Pencil field duplicates a third-party value.
- Dynamic and provider-owned values are marked with the correct provider and a verified edit URL.
- Managed regions wrap the smallest useful value or logical group, not whole cards, loop items, or mixed-owner containers.
- Every managed-region **Edit** action opens the correct provider editing screen.
- The chosen navigation approach works: theme-defined links resolve to real pages, or the registered WordPress menu can be edited and reordered successfully.
- The theme has a website-specific name, description, version, author, text domain, and a 1200 × 900 screenshot.
- With Pencil temporarily deactivated, every public page still renders its initial content without a fatal error.
- The browser console and PHP logs contain no new errors on the tested pages.
- The theme has been checked at representative desktop and mobile widths.
- Templates remain semantic, accessible, and layout-controlled by the theme.
- No client editing control can modify design or behaviour.

## Never do these things

- Build a public page only as a theme template without creating its WordPress Page record.
- Put the homepage only in `index.php` without assigning a real static front page.
- Store a Pencil-managed page layout in Gutenberg `post_content`.
- Hardcode static client-facing text, images, button labels, or URLs.
- Leave the theme with a generic development name or without a screenshot.
- Call Pencil helpers in production templates without a safe theme fallback.
- Hardcode the site's domain into internal navigation links.
- Duplicate WordPress-managed menu labels or URLs in Pencil fields.
- Put a static client-facing image in a theme `assets` directory or reference it with a theme-relative URL.
- Define theme styles for WordPress core UI class names such as `.media-frame`, `.attachment`, `.thumbnail`, or `.button`.
- Let an uploaded image's natural dimensions determine a component's size.
- Omit the aspect ratio because the initial image already happens to fit the design.
- Save complete page HTML in Pencil.
- Put CSS classes, markup, PHP, or JavaScript in Pencil content fields.
- Change a field ID or type casually; this may orphan saved content.
- Treat query output as independent static content.
- Edit ACF, JetEngine, WooCommerce, or external data through Pencil storage.
- Rely on runtime HTML inspection to guess which provider owns a value.
- Mark a mixed-owner card or loop item as though one provider owns the complete layout.
- Add a provider edit link without verifying that it opens the correct record or settings screen.
- Build Elementor, Divi, WPBakery, or Gutenberg-style layout editing into the theme.
