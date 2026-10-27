<?php
/**
 * Global helper functions.
 */

declare(strict_types=1);

use App\Core\View;

/**
 * HTML-escape a value for safe output. Default helper for every dynamic value.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape a string for use inside an XML document (sitemap, feeds).
 */
function xml_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Render a view template with the given data.
 */
function view(string $template, array $data = []): string
{
    return View::render($template, $data);
}

/**
 * Format a PKR price, e.g. 149999.00 => "Rs 149,999".
 */
function price(mixed $amount): string
{
    $symbol = App\Core\Config::get('app.currency_symbol', 'Rs');
    return $symbol . ' ' . number_format((float) $amount);
}

/**
 * Absolute price for structured data / feeds, e.g. "149999.00 PKR".
 */
function price_decimal(mixed $amount): string
{
    return number_format((float) $amount, 2, '.', '');
}

/**
 * Slugify arbitrary text into a URL-safe slug (Latin script).
 */
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    // transliterate common non-ASCII characters
    $map = ['ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss','é'=>'e','è'=>'e','á'=>'a','à'=>'a','ñ'=>'n'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/**
 * Shorten text for meta descriptions while respecting word boundaries.
 */
function excerpt(string $text, int $maxLength = 155): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }
    $cut = mb_substr($text, 0, $maxLength + 1);
    $pos = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $pos ?: $maxLength), ' ,.;:-') . '…';
}

/**
 * Read a template-safe absolute URL for a stored image path.
 * Stored paths are either relative ("img/products/x.jpg") or full URLs.
 */
function image_url(?string $path, string $fallback = 'img/placeholder.svg'): string
{
    if ($path === null || $path === '') {
        return asset($fallback);
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return asset(ltrim($path, '/'));
}

/**
 * Absolute URL to a public asset (never canonical-domain based: assets are
 * served from the same host the page is served from to avoid mixed content).
 */
function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

/**
 * Whether the current request is an admin area request.
 */
function is_admin_request(): bool
{
    return str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/admin');
}

/**
 * Sanitize rich HTML (product descriptions, page bodies) against stored XSS.
 *
 * Allows a safe subset of formatting tags and strips everything else —
 * scripts, event handlers, javascript: URLs, styles, iframes and forms.
 * Used server-side before any rich text is persisted.
 */
function sanitizeRichHtml(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4>'
        . '<table><thead><tbody><tr><th><td><blockquote><a><img><figure><figcaption><dl><dt><dd><hr>';
    $html = strip_tags($html, $allowedTags);

    // Remove dangerous attributes; keep safe ones on selected tags.
    // The library may be absent (no composer/vendor in this deployment), so the
    // config is only constructed once we know both classes exist — otherwise the
    // eager createDefault() call fatals before the fallback below can run.
    if (class_exists(\HTMLPurifier::class) && class_exists(\HTMLPurifier_Config::class)) {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u',
            'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'hr',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'blockquote', 'dl', 'dt', 'dd', 'figure', 'figcaption',
            'a[href|title|rel|target]', 'img[src|alt|width|height|loading]',
        ]));
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    // Fallback when HTMLPurifier is unavailable: regex-based attribute scrub.
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/i', '$1="#"', $html) ?? $html;
    $html = preg_replace('/<script\b[\s\S]*?<\/script>/i', '', $html) ?? $html;
    $html = preg_replace('/<iframe\b[\s\S]*?<\/iframe>/i', '', $html) ?? $html;
    $html = preg_replace('/<object\b[\s\S]*?<\/object>/i', '', $html) ?? $html;
    $html = preg_replace('/<embed\b[^>]*>/i', '', $html) ?? $html;
    $html = preg_replace('/<form\b[\s\S]*?<\/form>/i', '', $html) ?? $html;
    $html = preg_replace('/style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    return $html;
}
