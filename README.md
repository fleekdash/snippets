# FleekDash Snippets

A community collection of **fleeklets**: small, drop in PHP snippets that run
inside WordPress through FleekDash. Copy what you need, adapt it, ship your
own back.

If you have ever pasted a snippet into `functions.php` or installed a whole
plugin just to flip one WordPress behavior, fleeklets are the same idea done
properly: one file per tweak, discoverable in the FleekDash Hub, toggleable,
with optional settings screens.

## Install

Drop a snippet into `wp-content/fleeklets/` and FleekDash takes it from
there: the snippet is discovered, listed in the Hub, and loaded with per
file isolation so one broken snippet cannot take down the rest.

```text
wp-content/
└── fleeklets/
    ├── disable-admin-email-check.php      loose snippet (one file)
    └── maintenance-mode/                  package snippet
        ├── fleekdash.json                 manifest (metadata + settings)
        └── maintenance-mode.php           entry point
```

## Snippets in this repo

| Snippet | What it does |
|---|---|
| [`disable-admin-email-check.php`](snippets/disable-admin-email-check.php) | Skips the "confirm the administration email" screen after login. One filter, one line. |
| [`maintenance-mode/`](snippets/maintenance-mode/) | A real maintenance mode with a Hub toggle: logged out visitors get a friendly 503 page while admins keep full access. |

## Loose vs package snippets

- **Loose** (`snippets/<slug>.php`): a single file. Installing it means
  downloading the raw file into `fleeklets/`. Best for quick tweaks.
- **Package** (`snippets/<slug>/`): a directory holding `fleekdash.json` plus
  the `<slug>.php` entry point. Installing it means copying the directory
  into `fleeklets/<slug>/`. Use this form when your snippet declares settings
  or ships extra files.

A snippet's Hub card is toggleable, and settings declared in `fleekdash.json`
(or via `fleekdash_register_snippet()`) render as real FleekDash settings
fields stored as plain WordPress options: an explicit `option_key`, or the
`fleekdash_{id}_{field}` convention.

## The snippet contract

A snippet is plain PHP loaded at (mu-)plugin stage. Write it accordingly:

```php
<?php
/**
 * Snippet Name: My Snippet       // header discovery reads ("Plugin Name" works too)
 * Description: What it does.
 * Version: 1.0.0
 * Author: You
 */

\defined('ABSPATH') || exit;

// Runs early: plugins_loaded is not guaranteed to have fired yet for
// registration helpers. FleekDash defers them internally.
fleekdash_register_snippet('my-snippet', [
    'description' => 'What it does',
    'version'     => '1.0.0',
]);
```

Rules that keep snippets safe and swappable:

- **Snake filename.** The snippet id is the file slug: `my_snippet.php`
  becomes `my-snippet` (lowercase, hyphens). Discovery, loader and Hub all
  follow the same rule.
- **Global namespace prefix.** Prefix WordPress and global calls with `\`
  (`\add_action`, `\get_option`) and guard function definitions with
  `if (!function_exists(...))`.
- **Explicit `option_key`.** Declare one per settings field so renaming a
  field never orphans saved values.
- **Fail isolated.** Throwing is fine: the loader wraps each file and the Hub
  surfaces failures as `load_error` health on the card. What is not fine is
  fataling during discovery, so keep top level code free of FleekDash classes
  that may not be loaded yet.
- **No wp-admin only assumptions.** Snippets run on front end, admin, REST,
  cron and AJAX alike.

### `fleekdash.json` for package snippets

Validated in CI against
[`schema/fleekdash-manifest.schema.json`](schema/fleekdash-manifest.schema.json):

```json
{
  "kind": "snippet",
  "id": "maintenance-mode",
  "name": "Maintenance Mode",
  "version": "1.0.0",
  "entry": "maintenance-mode.php",
  "settings": {
    "enabled": {
      "type": "boolean",
      "label": "Enable",
      "option_key": "fleekdash_mm_enabled",
      "default": false
    }
  }
}
```

`settings` renders as the snippet's own settings card. `sections` injects
fields into existing FleekDash sections; prefer `settings` unless you know
the target section slug.

## What will you ship?

Snippets are the cheapest way to extend FleekDash. Good candidates: disabling
annoying defaults, adding a dashboard widget, filtering admin menus,
injecting a welcome notice, tweaking login behavior, auto-tagging posts on
publish, or wiring a small webhook forwarder. If it fits in a file, it is a
fleeklet.

## Contributing

One snippet per PR. Loose file or package directory under `snippets/`, CI
must pass (`php -l`, manifest schema, `Snippet Name` header on loose files),
docs and comments in English. The FleekDash crew reviews for contract fit and
may adjust the slug or settings shape before merging.

## License

GPL-2.0-or-later, same as WordPress and FleekDash.
