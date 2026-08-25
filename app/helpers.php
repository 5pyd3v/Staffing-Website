<?php

declare(strict_types=1);

/**
 * Global view helpers. Deliberately global (not namespaced) so plain-PHP
 * view files can call them without a use-statement on every template.
 */

use App\Core\View;
use App\Helpers\Icon;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Appends a filemtime-based cache-busting query string, so every CSS/JS
     * edit automatically invalidates the browser's cached copy instead of
     * requiring a hard refresh. Falls back to the raw path if the file isn't
     * found on disk (shouldn't happen outside of a broken deploy).
     */
    function asset_url(string $path): string
    {
        $fullPath = ROOT_PATH . '/public' . $path;
        if (!is_file($fullPath)) {
            return $path;
        }

        return $path . '?v=' . filemtime($fullPath);
    }
}

if (!function_exists('icon')) {
    function icon(string $name, string $class = 'icon'): string
    {
        return Icon::render($name, $class);
    }
}

if (!function_exists('logo_mark')) {
    /**
     * The brand glyph: two nodes joined by a bridge arc (TalentBridge — the
     * two sides of the marketplace, connected). Used everywhere instead of
     * a generic icon so the mark stays consistent and intentional.
     */
    function logo_mark(): string
    {
        static $svg = null;
        if ($svg === null) {
            $svg = file_get_contents(VIEW_PATH . '/partials/logo-mark.php');
        }

        return $svg;
    }
}

if (!function_exists('avatar')) {
    /**
     * Initials-based avatar (no photo library exists for candidates/companies),
     * colored deterministically from the name so the same person/company
     * always gets the same hue rather than a random one per page load.
     */
    function avatar(string $name, string $size = 'md'): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        $initials = $initials !== '' ? $initials : '?';

        $hue = array_sum(array_map('ord', str_split($name))) % 360;

        return sprintf(
            '<span class="avatar avatar--%s" style="--avatar-hue:%d" aria-hidden="true">%s</span>',
            htmlspecialchars($size, ENT_QUOTES),
            $hue,
            htmlspecialchars($initials, ENT_QUOTES)
        );
    }
}

if (!function_exists('field_error')) {
    /**
     * First validation message for a field, or null. Used by the public
     * forms (login, wizards) to render inline errors under each input
     * instead of only a top-of-form summary — see the form UX audit in
     * ARCHITECTURE.md.
     */
    function field_error(array $errors, string $field): ?string
    {
        $messages = $errors[$field] ?? null;
        if (!$messages) {
            return null;
        }

        return is_array($messages) ? (string) reset($messages) : (string) $messages;
    }
}

if (!function_exists('field_class')) {
    function field_class(array $errors, string $field, string $base = 'form-field'): string
    {
        return field_error($errors, $field) !== null ? $base . ' has-error' : $base;
    }
}
