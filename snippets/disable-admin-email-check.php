<?php
/**
 * Snippet Name: Disable Admin Email Check
 * Description: Skips the "Confirm the administration email address" screen shown after login.
 * Version: 1.0.0
 * Author: FleekDash
 *
 * @package FleekDash\Snippets
 */

\defined('ABSPATH') || exit;

// A falsy interval disables the confirmation screen entirely.
\add_filter('admin_email_check_interval', '__return_zero');
