<?php
/**
 * Seed admin-managed homepage/promo content for the redesign.
 *
 * Usage: php bin/seed-redesign.php
 *
 * Integrity rules honoured here:
 *  - Every marketing string is ordinary storefront copy an admin can edit.
 *  - Trust indicators restate service facts already published on the site
 *    (warranty, Cash on Delivery, nationwide shipping, returns). No new
 *    partnership, certification or award is invented.
 *  - The flash sale uses a REAL end timestamp and a campaign price that is
 *    genuinely below each product's normal price. The countdown is driven by
 *    that stored end time, so nothing is fabricated scarcity.
 *  - No reviews, ratings or "units sold" are created here — those come only
 *    from real approved reviews and real order_items rows.
 *
 * Idempotent: safe to re-run (skips keys that already exist).
 */

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$tz = (string) App\Core\Config::get('app.timezone', 'Asia/Karachi');
$now = new DateTimeImmutable('now', new DateTimeZone($tz));

echo "Seeding redesign content...\n";

/* ---------------------------------------------------- 1. Settings ------- */

$settings = [
    // Announcement bar: "Text" or "Text|/relative-url" per line.
    // Every target must be a page that actually exists in `pages` — the real
    // slugs are delivery-information and warranty-return-policy.
    'announcement_items' => implode("\n", [
        'Free nationwide shipping on orders over Rs 5,000|/page/delivery-information',
        'Cash on Delivery available across Pakistan',
        'Warranty & 7-day returns on every device|/page/warranty-return-policy',
    ]),
    // Only methods this site genuinely supports (see checkout).
    'payment_methods' => implode("\n", [
        'Cash on Delivery',
        'Bank Transfer',
        'Easypaisa / JazzCash',
    ]),
    'footer_note' => 'Your trusted destination for new, used and refurbished smartphones in Pakistan — every device tested, warranted and delivered to your door.',
    // Trust strip claims — each one already appears elsewhere on the site.
    'trust_indicator_seed' => '1', // marker so we only seed once
];

foreach ($settings as $key => $value) {
    if ($key === 'trust_indicator_seed') {
        continue;
    }
    Database::query(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = setting_value',
        [$key, $value]
    );
    echo "  [setting] $key\n";
}

/* --------------------------------------------- 2. Homepage sections ----- */

// section_key => [position, title|null, subtitle|null]
$sections = [
    ['hero',         0,   null,                                                        null],
    ['trust',        10,  null,                                                        null],
    ['categories',   20,  null,                                                        'Browse the full range — every listing links to a live category page.'],
    ['audio',        25,  'Sound, made personal',                                      'Headphones, portable speakers and audio essentials.'],
    ['flash',        30,  null,                                                        null],
    ['bestsellers',  40,  null,                                                        'Ranked by units sold in real orders.'],
    ['new-arrivals', 50,  null,                                                        null],
    ['brands',       60,  null,                                                        'Only brands we actually stock.'],
    ['banners',      70,  null,                                                        null],
    ['recommended',  80,  null,                                                        null],
    ['repairs',      90,  null,                                                        null],
    ['reviews',      100, null,                                                        'Only approved, verified-purchase reviews are shown.'],
    ['blog',         105, 'Guides & Tips',                                             null],
    ['newsletter',   110, null,                                                        null],
];

foreach ($sections as [$key, $pos, $title, $subtitle]) {
    Database::query(
        'INSERT INTO homepage_sections (section_key, position, title, subtitle, is_active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE position = VALUES(position)',
        [$key, $pos, $title, $subtitle]
    );
    echo "  [section] $key ($pos)\n";
}

/* --------------------------------------------- 3. Trust indicators ------ */

$existingTrust = (int) Database::fetchColumn('SELECT COUNT(*) FROM trust_indicators');
if ($existingTrust === 0) {
    $trust = [
        // icon key, label, detail
        ['shield',   'Warranty included',          'Every device ships with a written warranty.'],
        ['truck',    'Nationwide delivery',        'Cash on Delivery and courier shipping across Pakistan.'],
        ['return',   '7-day returns',              'Changed your mind? Send it back in original condition.'],
        ['genuine',  'Tested before dispatch',     'Battery health, screen and cameras checked by a technician.'],
        ['support',  'Real human support',         'Call or WhatsApp us before and after you buy.'],
    ];
    foreach ($trust as $i => [$icon, $label, $detail]) {
        Database::insert('trust_indicators', [
            'icon'      => $icon,
            'label'     => $label,
            'detail'    => $detail,
            'position'  => $i * 10,
            'is_active' => 1,
        ]);
        echo "  [trust] $label\n";
    }
} else {
    echo "  [trust] skipped ({$existingTrust} rows exist)\n";
}

/* ------------------------------------------------- 4. Hero slides ------- */

$existingHero = (int) Database::fetchColumn('SELECT COUNT(*) FROM hero_slides');
if ($existingHero === 0) {
    $slides = [
        [
            'title'    => 'The phones you actually want — in stock today',
            'subtitle' => 'New, used and refurbished smartphones from Samsung, Apple, Xiaomi, Vivo and more. Tested, warranted, delivered.',
            'badge'    => 'Pakistan\'s mobile marketplace',
            'cta_label'=> 'Shop smartphones',
            'cta_url'  => '/category/smartphones',
            'image'    => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1800&q=85',
            'theme'    => 'tech',
            'position' => 0,
        ],
        [
            'title'    => 'Certified used & refurbished, priced fairly',
            'subtitle' => 'Save on devices with checked battery health and a real warranty — no surprises when it arrives.',
            'badge'    => 'Save more',
            'cta_label'=> 'Browse used phones',
            'cta_url'  => '/category/used-phones',
            'image'    => 'https://images.unsplash.com/photo-1512499617640-c74ae3a79d37?auto=format&fit=crop&w=1800&q=85',
            'theme'    => 'navy',
            'position' => 10,
        ],
        [
            'title'    => 'Cracked screen? We fix it.',
            'subtitle' => 'Certified repairs for iPhone, Samsung and Xiaomi — screen, battery and charging port replacements.',
            'badge'    => 'Repair services',
            'cta_label'=> 'Book a repair',
            'cta_url'  => '/repair/screen-replacement',
            'image'    => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1800&q=85',
            'theme'    => 'custom',
            'position' => 20,
        ],
    ];
    foreach ($slides as $s) {
        Database::insert('hero_slides', $s + [
            'image'      => $s['image'],
            'image_mobile' => null,
            'overlay'    => 1,
            'is_active'  => 1,
            'starts_at'  => null,
            'ends_at'    => null,
        ]);
        echo "  [hero] {$s['title']}\n";
    }
} else {
    echo "  [hero] skipped ({$existingHero} rows exist)\n";
}

// Existing installations may have the original gradient-only slides. Fill
// their missing photography without overwriting imagery edited by an admin.
$heroPhotos = [
    ['The phones you actually want — in stock today', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1800&q=85'],
    ['Certified used & refurbished, priced fairly', 'https://images.unsplash.com/photo-1512499617640-c74ae3a79d37?auto=format&fit=crop&w=1800&q=85'],
    ['Cracked screen? We fix it.', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1800&q=85'],
];
foreach ($heroPhotos as [$title, $image]) {
    Database::query('UPDATE hero_slides SET image = ? WHERE title = ? AND (image IS NULL OR image = "")', [$image, $title]);
}

/* ------------------------------------------------ 5. Promo banners ------ */

$existingBanners = (int) Database::fetchColumn('SELECT COUNT(*) FROM promo_banners');
if ($existingBanners === 0) {
    $banners = [
        [
            'title'       => 'Budget phones under Rs 40,000',
            'description' => 'Solid daily drivers that don\'t break the bank.',
            'url'         => '/category/budget-phones',
            'theme'       => 'electric',
            'position'    => 0,
        ],
        [
            'title'       => 'Power banks & fast chargers',
            'description' => 'Keep going with Anker and Baseus accessories.',
            'url'         => '/category/power-banks',
            'theme'       => 'violet',
            'position'    => 10,
        ],
        [
            'title'       => 'Trade up to a refurbished iPhone',
            'description' => 'Premium hardware at a fraction of the price.',
            'url'         => '/category/refurbished-phones',
            'theme'       => 'navy',
            'position'    => 20,
        ],
        [
            'title'       => 'A more immersive way to listen',
            'description' => 'Explore wireless headphones, earbuds and portable speakers.',
            'url'         => '/category/audio',
            'theme'       => 'navy',
            'position'    => 30,
            'image'       => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=1200&q=85',
        ],
    ];
    foreach ($banners as $b) {
        Database::insert('promo_banners', $b + [
            'image'      => $b['image'] ?? null,
            'location'   => 'home',
            'is_active'  => 1,
            'starts_at'  => null,
            'ends_at'    => null,
        ]);
        echo "  [banner] {$b['title']}\n";
    }
} else {
    echo "  [banner] skipped ({$existingBanners} rows exist)\n";
}

// Bring legacy null-image promo rows forward and add the Audio feature once.
$bannerPhotos = [
    ['/category/budget-phones', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1200&q=85'],
    ['/category/power-banks', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1200&q=85'],
    ['/category/refurbished-phones', 'https://images.unsplash.com/photo-1512499617640-c74ae3a79d37?auto=format&fit=crop&w=1200&q=85'],
];
foreach ($bannerPhotos as [$url, $image]) {
    Database::query('UPDATE promo_banners SET image = ? WHERE url = ? AND (image IS NULL OR image = "")', [$image, $url]);
}
if (Database::fetch('SELECT id FROM promo_banners WHERE url = ?', ['/category/audio']) === null) {
    Database::insert('promo_banners', [
        'title' => 'A more immersive way to listen',
        'description' => 'Explore wireless headphones, earbuds and portable speakers.',
        'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=1200&q=85',
        'url' => '/category/audio', 'theme' => 'navy', 'location' => 'home', 'position' => 30, 'is_active' => 1,
    ]);
    echo "  [banner] Audio feature\n";
}

/* ---------------------------------------------- 6. Flash sale ---------- */

$existingSales = (int) Database::fetchColumn('SELECT COUNT(*) FROM flash_sales');
if ($existingSales === 0) {
    // Genuine campaign: a real end time 4 days out, and campaign prices that
    // are each genuinely below the product's normal price. The countdown the
    // customer sees counts down to `ends_at` — if it lapses, the model stops
    // treating the campaign as live, so the banner never lies.
    $endsAt = $now->modify('+4 days')->setTime(23, 59, 0);

    $saleId = Database::insert('flash_sales', [
        'title'     => 'This week\'s price drops',
        'subtitle'  => 'Genuine markdowns on in-stock devices — ends at the time shown.',
        'theme'     => 'flame',
        'starts_at' => $now->modify('-1 day')->format('Y-m-d H:i:s'),
        'ends_at'   => $endsAt->format('Y-m-d H:i:s'),
        'is_active' => 1,
    ]);
    echo "  [flash] campaign #{$saleId} ends {$endsAt->format('Y-m-d H:i:s')}\n";

    // Each item: product id => explicit campaign price (must be < price).
    $picks = [
        25 => 84999,   // Xiaomi Redmi Note 14 Pro 5G (price 89,999)
        36 => 33999,   // Xiaomi Redmi 14C           (price 37,999)
        34 => 14999,   // Anker PowerCore 20000mAh   (price 17,999)
        37 => 11999,   // Anker Soundcore P40i       (price 14,999)
        30 => 94999,   // Vivo V50 Lite 5G           (price 109,999)
        21 => 134999,  // Samsung Galaxy A56 5G      (price 144,999)
    ];

    $added = 0;
    foreach ($picks as $productId => $campaignPrice) {
        $row = Database::fetch(
            'SELECT price FROM products WHERE id = ? AND status = "active"',
            [$productId]
        );
        if ($row === null) {
            echo "  [flash] skip product {$productId} (missing/inactive)\n";
            continue;
        }
        if ((float) $campaignPrice >= (float) $row['price']) {
            echo "  [flash] skip product {$productId} (campaign price not a real saving)\n";
            continue;
        }
        Database::insert('flash_sale_items', [
            'flash_sale_id'  => $saleId,
            'product_id'     => $productId,
            'campaign_price' => $campaignPrice,
            'discount_pct'   => null,
            'position'       => $added * 10,
        ]);
        $added++;
    }
    echo "  [flash] {$added} items (all genuinely below normal price)\n";
} else {
    echo "  [flash] skipped ({$existingSales} rows exist)\n";
}

echo "Done.\n";
