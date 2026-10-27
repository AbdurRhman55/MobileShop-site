<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * Resolves the complete meta-tag set for any public page.
 *
 * Priority: admin override (seo_meta row) → auto-generated default from
 * entity content → global fallback. Every page receives a unique title and
 * description; the audit report surfaces anything missing or duplicated.
 *
 * The resulting array feeds the shared <head> partial and the JSON-LD layer.
 */
final class MetaResolver
{
    /** Maximum practical title/description lengths (Google truncates beyond these). */
    public const TITLE_MAX       = 60;       // ~60 chars / 580px
    public const DESCRIPTION_MAX = 160;      // ~160 chars

    /**
     * @return array{
     *   title:string, description:string, canonical:string, robots:string,
     *   og:array{title:string,description:string,image:string,url:string,type:string,site_name:string,locale:string},
     *   twitter:array{card:string,title:string,description:string,image:string},
     *   custom_head:string, prev:?string, next:?string
     * }
     */
    public static function resolve(
        string $entityType,
        int $entityId,
        string $autoTitle,
        string $autoDescription,
        string $canonicalPath,
        array $overrides = [],
    ): array {
        $row = self::seoRow($entityType, $entityId);

        $siteName = (string) SeoSettings::get('site_name', 'Lala Mobiles');

        /* ------------------------------------------------------- title -- */
        $title = trim((string) ($row['seo_title'] ?? ''));
        if ($title === '') {
            $title = trim($autoTitle);
        }
        if ($title === '') {
            $title = (string) SeoSettings::get('default_seo_title', $siteName);
        }

        /* ------------------------------------------------ description -- */
        $description = trim((string) ($row['meta_description'] ?? ''));
        if ($description === '') {
            $description = trim($autoDescription);
        }
        if ($description === '') {
            $description = (string) SeoSettings::get('default_seo_description', '');
        }

        /* ---------------------------------------------------- canonical -- */
        $canonicalPathResolved = Url::canonicalPath($canonicalPath, $row['canonical_path'] ?? null);
        $canonical = Url::absolute($canonicalPathResolved);

        /* ------------------------------------------------------ robots -- */
        $robots = trim((string) ($row['robots'] ?? ''));
        if ($robots === '') {
            $robots = $overrides['robots'] ?? 'index,follow';
        }
        // Staging / whole-site noindex guard always wins.
        if (!SeoSettings::siteIndexable()) {
            $robots = 'noindex,nofollow,noarchive';
        }

        /* ---------------------------------------------------- OpenGraph -- */
        $ogImage = trim((string) ($row['og_image'] ?? ''));
        if ($ogImage === '') {
            $ogImage = (string) ($overrides['og_image'] ?? SeoSettings::get('og_default_image', 'img/og-default.svg'));
        }
        $ogImageUrl = preg_match('#^https?://#i', $ogImage) ? $ogImage : Url::absolute($ogImage);

        $og = [
            'title'       => trim((string) ($row['og_title'] ?? '')) ?: $title,
            'description' => trim((string) ($row['og_description'] ?? '')) ?: $description,
            'image'       => $ogImageUrl,
            'url'         => $canonical,
            'type'        => trim((string) ($row['og_type'] ?? '')) ?: ($overrides['og_type'] ?? 'website'),
            'site_name'   => $siteName,
            'locale'      => 'en_PK',
        ];

        /* ------------------------------------------------------ Twitter -- */
        $twImage = trim((string) ($row['twitter_image'] ?? ''));
        if ($twImage === '') {
            $twImage = $ogImage;
        }
        $twitter = [
            'card'        => trim((string) ($row['twitter_card'] ?? '')) ?: (string) SeoSettings::get('twitter_default_card', 'summary_large_image'),
            'title'       => trim((string) ($row['twitter_title'] ?? '')) ?: $og['title'],
            'description' => trim((string) ($row['twitter_description'] ?? '')) ?: $og['description'],
            'image'       => preg_match('#^https?://#i', $twImage) ? $twImage : Url::absolute($twImage),
        ];

        return [
            'title'       => $title,
            'description' => $description,
            'canonical'   => $canonical,
            'robots'      => $robots,
            'og'          => $og,
            'twitter'     => $twitter,
            'custom_head' => self::sanitizedCustomHead((string) ($row['custom_head'] ?? '')),
            'prev'        => $overrides['prev'] ?? null,
            'next'        => $overrides['next'] ?? null,
        ];
    }

    /** Fetch the seo_meta row for an entity (or null). */
    public static function seoRow(string $entityType, int $entityId): ?array
    {
        return Database::fetch(
            'SELECT * FROM seo_meta WHERE entity_type = ? AND entity_id = ?',
            [$entityType, $entityId]
        );
    }

    /**
     * Sanitize admin-supplied "custom head" HTML.
     *
     * Only <meta>, <link> and <title> elements are allowed — script, style,
     * iframe and event handlers are stripped to prevent stored XSS.
     */
    public static function sanitizedCustomHead(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $allowed = ['meta', 'link', 'title'];
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Wrap so multiple root-level tags parse correctly.
        $doc->loadHTML(
            '<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (!$root instanceof \DOMElement) {
            return '';
        }

        $out = [];
        foreach (iterator_to_array($root->childNodes) as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->tagName);
            if (!in_array($tag, $allowed, true)) {
                continue; // drop scripts, styles, iframes, divs …
            }
            // Strip on* handlers and javascript: URLs from attributes.
            foreach (iterator_to_array($child->attributes) as $attr) {
                $name  = strtolower($attr->nodeName);
                $value = trim($attr->nodeValue ?? '');
                if (str_starts_with($name, 'on')) {
                    $child->removeAttribute($attr->nodeName);
                    continue;
                }
                if (preg_match('#^\s*javascript:#i', $value)) {
                    $child->removeAttribute($attr->nodeName);
                }
            }
            $out[] = $doc->saveHTML($child);
        }
        return implode("\n", $out);
    }

    /* -------------------------------------------------- length checks -- */

    public static function titleTooLong(string $t): bool
    {
        return mb_strlen($t) > self::TITLE_MAX + 15; // soft overage before flagging
    }

    public static function descriptionTooLong(string $d): bool
    {
        return mb_strlen($d) > self::DESCRIPTION_MAX + 20;
    }
}
