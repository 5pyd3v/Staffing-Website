<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Small hand-authored inline SVG icon set (stroke-based, 24x24, currentColor)
 * so the UI never depends on an external icon font/CDN. Deliberately narrow —
 * only the icons the product actually uses.
 */
final class Icon
{
    private const PATHS = [
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><path d="M2 13h20"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'arrow-right-circle' => '<circle cx="12" cy="12" r="9"/><path d="M9 12h7"/><path d="M13 8l4 4-4 4"/>',
        'award' => '<circle cx="12" cy="8" r="5"/><path d="M8.5 12.5 7 21l5-3 5 3-1.5-8.5"/>',
        'file-text' => '<path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"/><path d="M14 2v5h5"/><path d="M8 13h8M8 17h8M8 9h3"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="18" cy="9" r="2.7"/><path d="M15.5 20a5.2 5.2 0 0 1 6.1-3.7"/>',
        'building' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1"/><path d="M10 21v-4h4v4"/>',
        'star' => '<path d="M12 2.5l2.9 6 6.6.6-5 4.5 1.5 6.4L12 16.8 6 19.9l1.5-6.4-5-4.5 6.6-.6z"/>',
        'trending-up' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 6h6v6"/>',
        'map-pin' => '<path d="M12 21s7-6.3 7-11.5A7 7 0 0 0 5 9.5C5 14.7 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="16" rx="1.5"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9.5"/><path d="M7.5 12.5l3 3 6-6.5"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.7-4.7"/>',
        'upload-cloud' => '<path d="M7 18a4.5 4.5 0 0 1-1-8.9 5.5 5.5 0 0 1 10.7-2A4.5 4.5 0 0 1 17 18"/><path d="M12 21v-8M9 16l3-3 3 3"/>',
        'mail' => '<rect x="2.5" y="4.5" width="19" height="15" rx="1.5"/><path d="M3 6l9 6.5L21 6"/>',
        'bell' => '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M9.5 19a2.5 2.5 0 0 0 5 0"/>',
        'chevron-right' => '<path d="M9 5l7 7-7 7"/>',
        'dollar-sign' => '<path d="M12 2v20"/><path d="M17 6.5c0-1.9-2.2-3-5-3s-5 1.2-5 3 2.2 2.6 5 3 5 1.1 5 3-2.2 3-5 3-5-1.1-5-3"/>',
        'shield-check' => '<path d="M12 2.5l8 3.5v6c0 5-3.4 8.5-8 9.5-4.6-1-8-4.5-8-9.5V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
        'trash' => '<path d="M4 7h16"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><path d="M9 7V4.5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1V7"/>',
        'edit' => '<path d="M3 21l3.5-1 11-11-2.5-2.5-11 11z"/><path d="M14 5.5L18.5 10"/>',
        'plus' => '<path d="M12 4v16M4 12h16"/>',
        'external-link' => '<path d="M9 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/><path d="M14 4h6v6M20 4l-9 9"/>',
        'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 13a7.6 7.6 0 0 0 0-2l2-1.6-2-3.4-2.4 1a7.6 7.6 0 0 0-1.7-1L15 3h-4l-.3 2.5a7.6 7.6 0 0 0-1.7 1l-2.4-1-2 3.4L6.6 11a7.6 7.6 0 0 0 0 2l-2 1.6 2 3.4 2.4-1a7.6 7.6 0 0 0 1.7 1L11 21h4l.3-2.5a7.6 7.6 0 0 0 1.7-1l2.4 1 2-3.4z"/>',
        'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'menu' => '<path d="M3.5 6.5h17M3.5 12h17M3.5 17.5h17"/>',
        'x' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'eye' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.3 4.2M6.6 6.6C4 8.3 2 12 2 12s3.6 7 10 7a10.6 10.6 0 0 0 3.4-.6"/><path d="M9.9 10a3 3 0 0 0 4.2 4.2"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'message-circle' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
        'kanban' => '<rect x="3" y="3" width="5" height="18" rx="1.2"/><rect x="9.5" y="3" width="5" height="11" rx="1.2"/><rect x="16" y="3" width="5" height="15" rx="1.2"/>',
    ];

    public static function render(string $name, string $class = 'icon'): string
    {
        $inner = self::PATHS[$name] ?? self::PATHS['sparkle'];

        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
    }
}
