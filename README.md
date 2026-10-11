# FleekDash Snippets

Community collection of **fleeklets** — small, drop-in PHP snippets that run inside WordPress through FleekDash. Copy what you need, extend it, ship it back.

## Install

Drop a snippet into `wp-content/fleeklets/` — FleekDash discovers it, lists it in the Hub, and runs it with per-file isolation (one broken snippet can't take down the rest).

```text
wp-content/
└── fleeklets/
    ├── disable-admin-email-check.php      ← loose snippet (one file)
    └── maintenance-mode/                  ← package snippet
        ├── fleekdash.json                 ← manifest (metadata + settings)
        └── maintenance-mode.php           ← entry point
```

Every snippet lives in this repo under `snippets/`:

- **Loose** — `snippets/<slug>.php`: a single file. Install = download the raw file into `fleeklets/`.
- **Package** — `snippets/<slug>/`: a directory with `fleekdash.json` + `<slug>.php` (see `entry`). Install = copy the directory into `fleeklets/<slug>/`. Use this form when the snippet declares settings or ships extra files.

The Hub card for a snippet is toggleable; its settings (declared via `settings`/`sections` in `fleekdash.json`, or `fleekdash_register_snippet()` in the file) render in FleekDash settings and are stored as plain WordPress options (`option_key` explicit, or the `fleekdash_{id}_{field}` convention).

## Snippet contract

A snippet is plain PHP, loaded at (mu-)plugin stage — write it accordingly:

```php
<?php
/**
 * Snippet Name: My Snippet       ← header the discovery reads ("Plugin Name" also works)
 * Description: What it does.
 * Version: 1.0.0
 * Author: You
 */

\defined('ABSPATH') || exit;

// Runs early: plugins_loaded is not guaranteed to have fired yet for
// registration helpers — FleekDash defers them internally.
fleekdash_register_snippet('my-snippet', [
    'description' => 'What it does',
    'version'     => '1.0.0',
]);
```

Rules that keep snippets safe and swappable:

- **Snake filename.** The snippet id is the file slug — `my_snippet.php` → `my-snippet` (lowercase, hyphens). Same rule across discovery, loader and Hub.
- **Global-namespace prefix.** Prefix WP/global calls with `\` (`\add_action`, `\get_option`) and guard function definitions with `if (!function_exists(...))`.
- **`option_key` for settings.** Declare an explicit `option_key` per field so renaming the field never orphans saved values.
- **Fail isolated.** Throwing is fine — the loader wraps each file; the Hub surfaces it as `load_error` health on the card. What's not fine: fataling during discovery (keep top-level code free of FD classes — they may not be loaded yet).
- **No `wp-admin`-only assumptions.** Snippets run on front-end, admin, REST, cron and AJAX alike.

### `fleekdash.json` (package form)

Validated in CI against [`schema/fleekdash-manifest.schema.json`](schema/fleekdash-manifest.schema.json):

```json
{
  "kind": "snippet",
  "id": "maintenance-mode",
  "name": "Maintenance Mode",
  "version": "1.0.0",
  "entry": "maintenance-mode.php",
  "settings": {
    "enabled": { "type": "boolean", "label": "Enable", "option_key": "fleekdash_mm_enabled", "default": false }
  }
}
```

`settings` renders as the snippet's own settings card. `sections` injects fields into existing FleekDash sections — prefer `settings` unless you know the target section slug.

## Contributing

One snippet per PR. Loose file or package dir under `snippets/`, CI must pass (`php -l`, manifest schema, `Snippet Name` header on loose files), English docs/comments. FleekDash crew reviews for contract fit — we may adjust the slug or settings shape before merging.

## License

GPL-2.0-or-later — same as WordPress and FleekDash.
