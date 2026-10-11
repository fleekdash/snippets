<?php
/**
 * Snippet Name: Maintenance Mode
 * Description: Shows a 503 maintenance page to logged-out visitors while you work on the site.
 * Version: 1.0.0
 * Author: FleekDash
 *
 * @package FleekDash\Snippets
 */

\defined('ABSPATH') || exit;

if (!\function_exists('fleekdash_mm_enabled')) {
    /**
     * Reads the toggle from the option declared in fleekdash.json
     * (option_key: fleekdash_mm_enabled) — works at mu-plugin stage.
     */
    function fleekdash_mm_enabled(): bool
    {
        return \filter_var(\get_option('fleekdash_mm_enabled', false), \FILTER_VALIDATE_BOOLEAN);
    }
}

\add_action('template_redirect', function () {
    if (!fleekdash_mm_enabled()) {
        return;
    }

    // Logged-in admins keep full access — maintenance must never lock out its owner.
    if (\is_user_logged_in() && \current_user_can('manage_options')) {
        return;
    }

    \wp_die(
        '<h1>Maintenance</h1><p>We are performing scheduled maintenance — back shortly.</p>',
        'Maintenance',
        ['response' => 503]
    );
});
