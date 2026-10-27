<?php
/**
 * Seed script: php bin/seed.php [--fresh]
 *
 * Populates the database with the Lala Mobiles catalogue, blog, service pages,
 * default settings and a default admin account. Images are generated as SVG
 * placeholders so the site is fully self-contained.
 */

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

if (in_array('--fresh', $argv, true)) {
    foreach (['order_items','orders','reviews','product_specs','product_images','product_variants',
              'seo_meta','redirects','filter_pages','blog_posts','pages','products','categories',
              'brands','admins','settings'] as $table) {
        Database::query("DELETE FROM `$table`");
    }
    echo "Fresh start: all tables truncated.\n";
}

/* ------------------------------------------------------------ settings -- */
$settings = [
    // Canonical domain — canonical URLs are ALWAYS built from this value,
    // never from the request Host header.
    'canonical_domain'        => 'https://www.lalamobiles.pk',
    'site_name'               => 'Lala Mobiles',
    'site_tagline'            => 'Pakistan’s Trusted Mobile Phone Store',
    'default_seo_title'       => 'Lala Mobiles — New, Used & Refurbished Phones in Pakistan',
    'default_seo_description' => 'Buy new, used and refurbished mobile phones in Pakistan at best prices. Samsung, iPhone, Xiaomi & more with warranty, Cash on Delivery and nationwide shipping.',
    'homepage_seo_title'      => 'Lala Mobiles — Mobile Phones in Pakistan at Best Prices',
    'homepage_seo_description'=> 'Shop the latest Samsung, iPhone, Xiaomi, Infinix and Tecno phones in Pakistan. Genuine devices, official warranty, Cash on Delivery and fast nationwide delivery.',
    'og_default_image'        => 'img/og-default.svg',
    'twitter_default_card'    => 'summary_large_image',
    'organization_name'       => 'Lala Mobiles',
    'organization_logo'       => 'img/logo.svg',
    'organization_phone'      => '+92-300-0000000',
    'organization_email'      => 'info@lalamobiles.pk',
    'organization_address'    => 'Main Mobile Market, Saddar, Rawalpindi, Punjab, Pakistan',
    'organization_city'       => 'Rawalpindi',
    'organization_region'     => 'Punjab',
    'organization_postal'     => '46000',
    'organization_country'    => 'PK',
    'geo_latitude'            => '33.5942',
    'geo_longitude'           => '73.0551',
    'social_facebook'         => 'https://www.facebook.com/lalamobiles',
    'social_instagram'        => 'https://www.instagram.com/lalamobiles',
    'social_tiktok'           => '',
    'social_youtube'          => '',
    // Indexing controls (managed from admin → SEO → Settings)
    'robots_index_site'       => '1',       // 0 = noindex whole site (staging guard)
    'robots_txt_extra'        => '',        // extra admin-managed robots.txt lines
    'sitemap_enabled'         => '1',
    'blog_enabled'            => '1',
    'feed_enabled'            => '1',
    'feed_shipping_country'   => 'PK',
    'feed_shipping_region'    => 'Punjab',
    'feed_shipping_service'   => 'Standard',
    'feed_shipping_price'     => '0.00 PKR',
    'feed_free_shipping_over' => '5000.00 PKR',
    // Verification / integrations (values are public tokens, not secrets)
    'google_search_console_verification' => '',
    'google_site_verification_method'    => 'meta',   // meta | file
    'ga4_measurement_id'                 => '',
    'gtm_container_id'                   => '',
    'bing_webmaster_verification'        => '',
    'pinterest_verification'             => '',
    'merchant_center_id'                 => '',
    'analytics_consent_required'         => '1',      // consent-aware: no cookie pings before consent
    'review_min_for_aggregate'           => '3',      // minimum approved reviews before AggregateRating
];
foreach ($settings as $key => $value) {
    Database::query(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

/* --------------------------------------------------------------- admin -- */
$adminUser = getenv('ADMIN_USER') ?: 'admin';
$adminPass = getenv('ADMIN_PASS') ?: 'Lala@Mobiles2026';
Database::query(
    'INSERT INTO admins (username, password_hash, display_name) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)',
    [$adminUser, password_hash($adminPass, PASSWORD_DEFAULT), 'Store Admin']
);
echo "Admin account: $adminUser / $adminPass (change via ADMIN_USER / ADMIN_PASS env on first run)\n";

/* -------------------------------------------------------------- brands -- */
$brands = [
    ['samsung',  'Samsung',  'https://www.samsung.com/pk/',  'South Korean electronics giant, maker of the Galaxy series.'],
    ['apple',    'Apple',    'https://www.apple.com/',       'American maker of iPhone, iPad and Mac.'],
    ['xiaomi',   'Xiaomi',   'https://www.mi.com/pk/',       'Chinese brand known for value flagship phones.'],
    ['infinix',  'Infinix',  'https://www.infinixmobile.com/', 'Popular budget brand backed by Transsion.'],
    ['tecno',    'Tecno',    'https://www.tecno-mobile.com/',  'Transsion brand focused on emerging markets.'],
    ['oppo',     'Oppo',     'https://www.oppo.com/pk/',     'Chinese brand strong on camera phones.'],
    ['vivo',     'Vivo',     'https://www.vivo.com/pk/',     'Chinese brand with wide service network in Pakistan.'],
    ['realme',   'Realme',   'https://www.realme.com/pk/',   'Young brand under BBK with aggressive pricing.'],
    ['anker',    'Anker',    'https://www.anker.com/',       'Accessories brand known for power banks and chargers.'],
    ['baseus',   'Baseus',   'https://www.baseus.com/',      'Accessories brand for cables, chargers and cases.'],
];
$brandIds = [];
foreach ($brands as [$slug, $name, $website, $intro]) {
    $brandIds[$slug] = Database::insert('brands', [
        'slug' => $slug, 'name' => $name, 'website' => $website, 'intro' => $intro,
        'status' => 'active',
    ]);
}

/* ---------------------------------------------------------- categories -- */
$categories = [
    ['smartphones',      null,     'Smartphones',        'Explore new, used and refurbished smartphones from every major brand, backed by warranty and Cash on Delivery across Pakistan.'],
    ['samsung-phones',   'smartphones', 'Samsung Phones', 'Latest Samsung Galaxy phones in Pakistan — S series flagships, A series mid-rangers and M series value phones with official warranty.'],
    ['iphone',           'smartphones', 'iPhone',         'Brand-new and certified refurbished iPhones in Pakistan, from the latest iPhone 16 lineup to proven favourites like iPhone 13.'],
    ['xiaomi-phones',    'smartphones', 'Xiaomi Phones',  'Xiaomi and POCO phones in Pakistan with powerful specs at competitive prices.'],
    ['budget-phones',    'smartphones', 'Budget Phones',  'Best budget smartphones under Rs 40,000 in Pakistan — great performance without breaking the bank.'],
    ['used-phones',      null,     'Used Phones',        'Tested and graded used phones in Pakistan. Every device is inspected for battery health and display quality before sale.'],
    ['refurbished-phones','used-phones', 'Refurbished Phones', 'Certified refurbished phones with replacement-grade parts and warranty — premium devices at reduced prices.'],
    ['accessories',      null,     'Mobile Accessories', 'Mobile accessories in Pakistan — power banks, chargers, cables, earbuds and cases from trusted brands.'],
    ['power-banks',      'accessories', 'Power Banks',   'Anker and Baseus power banks with fast charging support, available with warranty in Pakistan.'],
    ['repair-services',  null,     'Repair Services',    'Professional mobile repair services in Pakistan — screen replacement, battery replacement and water damage repair with genuine parts.'],
];
$catIds = [];
foreach ($categories as [$slug, $parent, $name, $intro]) {
    $catIds[$slug] = Database::insert('categories', [
        'parent_id' => $parent !== null ? $catIds[$parent] : null,
        'slug' => $slug, 'name' => $name, 'intro' => $intro, 'status' => 'active',
    ]);
}

/* ------------------------------------------------------------- images --- */
// Generate simple SVG placeholders so the site works without binary assets.
$makeImage = static function (string $file, string $label, string $bg = '#1f2937', string $fg = '#f9fafb') : string {
    $dir = BASE_PATH . '/public/img';
    $sub = dirname("$dir/$file");
    if (!is_dir($sub)) {
        mkdir($sub, 0775, true);
    }
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800" role="img" aria-label="$label">
  <rect width="800" height="800" fill="$bg"/>
  <rect x="290" y="170" width="220" height="460" rx="28" fill="none" stroke="$fg" stroke-width="10"/>
  <rect x="316" y="210" width="168" height="380" rx="10" fill="$fg" opacity="0.18"/>
  <text x="400" y="700" font-family="Arial, sans-serif" font-size="30" fill="$fg" text-anchor="middle">$label</text>
</svg>
SVG;
    file_put_contents("$dir/$file", $svg);
    return "img/$file";
};

$makeImage('logo.svg', 'Lala Mobiles', '#dc2626');
$makeImage('og-default.svg', 'Lala Mobiles', '#991b1b');

/* ------------------------------------------------------------ products -- */
// [slug, name, brand, category, condition, price, sale_price, stock, short, description, specs, alt, extra]
$products = [
    ['samsung-galaxy-s25-ultra', 'Samsung Galaxy S25 Ultra 5G', 'samsung', 'samsung-phones', 'new', 549999, null, 14,
     'Galaxy S25 Ultra with Snapdragon 8 Elite, 200MP camera and S Pen — official Pakistani warranty.',
     '<p>The Samsung Galaxy S25 Ultra is the most powerful Galaxy ever, pairing the Snapdragon 8 Elite chipset with a 200MP main camera and a built-in S Pen. The 6.9-inch QHD+ Dynamic AMOLED 2X display delivers buttery-smooth 120Hz scrolling, while the 5000mAh battery easily covers a full day of heavy use.</p><p>At Lala Mobiles, every S25 Ultra ships sealed with official Pakistani warranty and Samsung’s 15W+ fast charging support.</p>',
     [['General','Brand','Samsung'],['General','Model','Galaxy S25 Ultra'],['General','Condition','New — Sealed'],['Display','Size','6.9 inches'],['Display','Type','Dynamic AMOLED 2X, 120Hz'],['Performance','Processor','Snapdragon 8 Elite'],['Performance','RAM','12GB'],['Performance','Storage','512GB'],['Camera','Main','200MP'],['Battery','Capacity','5000 mAh'],['Battery','Charging','45W Fast Charging']],
     'Samsung Galaxy S25 Ultra 5G in Titanium Grey', true, ['8GB / 256GB — Titanium Black' => ['8GB','256GB','Titanium Black', 0], '12GB / 512GB — Titanium Grey' => ['12GB','512GB','Titanium Grey', 60000]]],

    ['samsung-galaxy-a56', 'Samsung Galaxy A56 5G', 'samsung', 'samsung-phones', 'new', 144999, 139999, 32,
     'Galaxy A56 5G with 50MP OIS camera, 6.7-inch Super AMOLED display and 5 years of updates.',
     '<p>The Samsung Galaxy A56 5G brings flagship-grade features to the mid-range: a 6.7-inch 120Hz Super AMOLED display, a 50MP main camera with OIS and 45W fast charging over a 5000mAh battery.</p><p>Samsung promises five generations of OS and security updates, making the A56 one of the best long-term values in its class. Available now at Lala Mobiles with official warranty.</p>',
     [['General','Brand','Samsung'],['General','Model','Galaxy A56 5G'],['General','Condition','New — Sealed'],['Display','Size','6.7 inches'],['Display','Type','Super AMOLED, 120Hz'],['Performance','Processor','Exynos 1580'],['Performance','RAM','8GB'],['Performance','Storage','256GB'],['Camera','Main','50MP OIS'],['Battery','Capacity','5000 mAh'],['Battery','Charging','45W Fast Charging']],
     'Samsung Galaxy A56 5G in Awesome Graphite', true, ['8GB / 128GB — Awesome Graphite' => ['8GB','128GB','Awesome Graphite', -10000], '8GB / 256GB — Awesome Olive' => ['8GB','256GB','Awesome Olive', 0]]],

    ['apple-iphone-16', 'Apple iPhone 16 (128GB)', 'apple', 'iphone', 'new', 384999, null, 21,
     'iPhone 16 with A18 chip, 48MP Fusion camera and Camera Control button — PTA approved.',
     '<p>The iPhone 16 pairs Apple’s A18 chip with a 48MP Fusion camera and the new Camera Control button, in a lighter aluminium design with Ceramic Shield protection.</p><p>Every iPhone sold by Lala Mobiles is PTA approved with full Apple service eligibility in Pakistan. Cash on Delivery and nationwide shipping available.</p>',
     [['General','Brand','Apple'],['General','Model','iPhone 16'],['General','Condition','New — Sealed'],['Display','Size','6.1 inches'],['Display','Type','Super Retina XDR OLED'],['Performance','Processor','Apple A18'],['Performance','Storage','128GB'],['Camera','Main','48MP Fusion'],['Battery','Capacity','3561 mAh']],
     'Apple iPhone 16 in Ultramarine', true, null],

    ['apple-iphone-16-pro-max', 'Apple iPhone 16 Pro Max (256GB)', 'apple', 'iphone', 'new', 544999, 529999, 9,
     'iPhone 16 Pro Max with A18 Pro, 5x tetraprism zoom and 6.9-inch titanium design.',
     '<p>The iPhone 16 Pro Max is Apple’s biggest phone yet: a 6.9-inch titanium body, A18 Pro chip, 48MP Fusion camera with 5x optical zoom and up to 33 hours of video playback.</p><p>PTA approved and sealed, sold with Lala Mobiles’ shop warranty in addition to Apple service coverage.</p>',
     [['General','Brand','Apple'],['General','Model','iPhone 16 Pro Max'],['General','Condition','New — Sealed'],['Display','Size','6.9 inches'],['Display','Type','Super Retina XDR OLED, 120Hz'],['Performance','Processor','Apple A18 Pro'],['Performance','Storage','256GB'],['Camera','Main','48MP Fusion + 5x Telephoto'],['Battery','Capacity','4685 mAh']],
     'Apple iPhone 16 Pro Max in Natural Titanium', false, null],

    ['iphone-13-refurbished', 'Apple iPhone 13 (128GB) — Refurbished', 'apple', 'refurbished-phones', 'refurbished', 149999, null, 6,
     'Certified refurbished iPhone 13 with new battery (85%+ health) and 3-month shop warranty.',
     '<p>A certified refurbished iPhone 13 with a replaced battery (minimum 85% health), a grade-A display and a fresh body. Each unit passes a 32-point functional test covering Face ID, cameras, speakers and charging.</p><p>Covered by a 3-month Lala Mobiles warranty. Stock is limited and refreshed weekly — contact us to reserve a unit.</p>',
     [['General','Brand','Apple'],['General','Model','iPhone 13'],['General','Condition','Refurbished — Grade A'],['Display','Size','6.1 inches'],['Performance','Storage','128GB'],['Camera','Main','12MP Dual'],['Battery','Health','85%+ (replaced)']],
     'Certified refurbished Apple iPhone 13 in Midnight', false, null],

    ['xiaomi-redmi-note-14-pro', 'Xiaomi Redmi Note 14 Pro 5G', 'xiaomi', 'xiaomi-phones', 'new', 89999, 84999, 40,
     'Redmi Note 14 Pro 5G with 200MP camera, 120Hz AMOLED and 45W fast charging.',
     '<p>The Xiaomi Redmi Note 14 Pro 5G features a 200MP main camera with OIS, a 6.67-inch 120Hz AMOLED display and 45W turbo charging for its 5110mAh battery.</p><p>Excellent value for money with HyperOS and two years of security updates. Available with warranty at Lala Mobiles.</p>',
     [['General','Brand','Xiaomi'],['General','Model','Redmi Note 14 Pro 5G'],['General','Condition','New — Sealed'],['Display','Size','6.67 inches'],['Display','Type','AMOLED, 120Hz'],['Performance','Processor','Dimensity 7300 Ultra'],['Performance','RAM','8GB'],['Performance','Storage','256GB'],['Camera','Main','200MP OIS'],['Battery','Capacity','5110 mAh']],
     'Xiaomi Redmi Note 14 Pro 5G in Lavender Purple', true, null],

    ['xiaomi-poco-x7-pro', 'POCO X7 Pro 5G', 'xiaomi', 'xiaomi-phones', 'new', 114999, null, 18,
     'POCO X7 Pro with Dimensity 8400 Ultra, 6000mAh battery and 90W HyperCharge.',
     '<p>The POCO X7 Pro targets performance seekers with the Dimensity 8400 Ultra chipset, a 6.67-inch 120Hz AMOLED panel and a massive 6000mAh battery with 90W charging.</p><p>A great choice for gaming and heavy multitasking, backed by Lala Mobiles’ warranty.</p>',
     [['General','Brand','POCO'],['General','Model','X7 Pro 5G'],['General','Condition','New — Sealed'],['Display','Size','6.67 inches'],['Performance','Processor','Dimensity 8400 Ultra'],['Performance','RAM','12GB'],['Performance','Storage','512GB'],['Battery','Capacity','6000 mAh'],['Battery','Charging','90W HyperCharge']],
     'POCO X7 Pro 5G in Green', false, null],

    ['infinix-note-50-pro', 'Infinix Note 50 Pro+', 'infinix', 'budget-phones', 'new', 74999, 69999, 27,
     'Infinix Note 50 Pro+ with 108MP camera, 144Hz AMOLED and 90W charging.',
     '<p>The Infinix Note 50 Pro+ combines a 108MP camera, a 6.78-inch 144Hz AMOLED display and 90W fast charging at a mid-range price that is hard to beat in Pakistan.</p><p>Runs XOS on Android 15 with a large vapour-chamber cooling system for sustained gaming performance.</p>',
     [['General','Brand','Infinix'],['General','Model','Note 50 Pro+'],['General','Condition','New — Sealed'],['Display','Size','6.78 inches'],['Display','Type','AMOLED, 144Hz'],['Performance','RAM','12GB'],['Performance','Storage','256GB'],['Camera','Main','108MP'],['Battery','Capacity','5200 mAh']],
     'Infinix Note 50 Pro+ in Titanium Grey', false, null],

    ['tecno-camon-30', 'Tecno Camon 30 5G', 'tecno', 'budget-phones', 'new', 64999, null, 35,
     'Tecno Camon 30 5G with 50MP selfie camera, 120Hz AMOLED and 7000mAh-class battery life.',
     '<p>The Tecno Camon 30 5G is built around photography: a 50MP selfie shooter, a 50MP main camera and Tecno’s AI-enhanced imaging pipeline, on a 6.78-inch 120Hz AMOLED display.</p><p>Long battery life and a wide service network make it a sensible daily driver in Pakistan.</p>',
     [['General','Brand','Tecno'],['General','Model','Camon 30 5G'],['General','Condition','New — Sealed'],['Display','Size','6.78 inches'],['Performance','RAM','8GB'],['Performance','Storage','256GB'],['Battery','Capacity','5000 mAh']],
     'Tecno Camon 30 5G in Iceland', false, null],

    ['oppo-reno-13', 'Oppo Reno 13 5G', 'oppo', 'smartphones', 'new', 134999, null, 12,
     'Oppo Reno 13 5G with Dimensity 8350, IP69 rating and 50MP portrait camera.',
     '<p>The Oppo Reno 13 5G brings an IP69 dust-and-water rating, a Dimensity 8350 chipset and a 50MP portrait-tuned camera system to the premium mid-range.</p><p>Sleek design, capable performance and Oppo’s after-sales network across Pakistan.</p>',
     [['General','Brand','Oppo'],['General','Model','Reno 13 5G'],['General','Condition','New — Sealed'],['Display','Size','6.59 inches'],['Performance','Processor','Dimensity 8350'],['Performance','RAM','12GB'],['Performance','Storage','256GB'],['Camera','Main','50MP'],['Battery','Capacity','5600 mAh']],
     'Oppo Reno 13 5G in Sky Blue', false, null],

    ['vivo-v50-lite', 'Vivo V50 Lite 5G', 'vivo', 'smartphones', 'new', 109999, 99999, 22,
     'Vivo V50 Lite 5G with 50MP Sony camera, 120Hz AMOLED and 6500mAh battery.',
     '<p>The Vivo V50 Lite 5G pairs a 50MP Sony main sensor with a huge 6500mAh battery and 90W flash charging, wrapped in a slim 7.5mm body.</p><p>A reliable all-rounder with strong battery life — ideal for heavy social media use.</p>',
     [['General','Brand','Vivo'],['General','Model','V50 Lite 5G'],['General','Condition','New — Sealed'],['Display','Size','6.77 inches'],['Performance','RAM','8GB'],['Performance','Storage','256GB'],['Camera','Main','50MP Sony'],['Battery','Capacity','6500 mAh']],
     'Vivo V50 Lite 5G in Titanium Gold', false, null],

    ['realme-14-pro', 'Realme 14 Pro 5G', 'realme', 'smartphones', 'new', 119999, null, 15,
     'Realme 14 Pro 5G with 50MP IMX882 camera, curved AMOLED and 80W charging.',
     '<p>The Realme 14 Pro 5G offers a curved 120Hz AMOLED display, a 50MP Sony IMX882 main camera and 80W SuperVOOC charging in a slim, pearl-finish body.</p><p>Solid performance for the price, available with warranty at Lala Mobiles.</p>',
     [['General','Brand','Realme'],['General','Model','14 Pro 5G'],['General','Condition','New — Sealed'],['Display','Size','6.7 inches'],['Performance','Processor','Dimensity 7300 Energy'],['Performance','RAM','12GB'],['Performance','Storage','256GB'],['Battery','Capacity','5800 mAh']],
     'Realme 14 Pro 5G in Pearl White', false, null],

    ['samsung-galaxy-a16', 'Samsung Galaxy A16 4G', 'samsung', 'budget-phones', 'new', 64999, null, 45,
     'Galaxy A16 4G with 6.7-inch Super AMOLED, 50MP camera and 6 years of updates.',
     '<p>The Samsung Galaxy A16 4G is the dependable budget pick: a 6.7-inch Super AMOLED display, a 50MP camera and Samsung’s class-leading six-year update promise.</p><p>One of the safest budget purchases in Pakistan — sealed with official warranty at Lala Mobiles.</p>',
     [['General','Brand','Samsung'],['General','Model','Galaxy A16 4G'],['General','Condition','New — Sealed'],['Display','Size','6.7 inches'],['Display','Type','Super AMOLED'],['Performance','RAM','6GB'],['Performance','Storage','128GB'],['Battery','Capacity','5000 mAh']],
     'Samsung Galaxy A16 4G in Blue Black', false, null],

    ['iphone-12-used', 'Apple iPhone 12 (64GB) — Used', 'apple', 'used-phones', 'used', 99999, null, 4,
     'Tested used iPhone 12 with 80%+ battery health and 1-month shop warranty.',
     '<p>A carefully tested used iPhone 12 with battery health of 80% or better, no display defects and fully functional Face ID. Each unit is cleaned, factory-reset and PTA approved.</p><p>Backed by a 1-month Lala Mobiles warranty. Photos are of the exact unit you will receive — contact us for the current lot.</p>',
     [['General','Brand','Apple'],['General','Model','iPhone 12'],['General','Condition','Used — Tested'],['Display','Size','6.1 inches'],['Performance','Storage','64GB'],['Battery','Health','80%+']],
     'Used Apple iPhone 12 in White', false, null],

    ['anker-power-bank-20000', 'Anker PowerCore 20000mAh Power Bank', 'anker', 'power-banks', 'new', 17999, 15999, 60,
     'Anker 20000mAh power bank with 30W USB-C PD fast charging.',
     '<p>The Anker PowerCore 20000mAh gives you multiple full charges for any modern smartphone. The 30W USB-C PD port can even charge laptops and tablets, while PowerIQ keeps every device charging safely.</p><p>18-month Anker warranty, available with Cash on Delivery across Pakistan.</p>',
     [['General','Brand','Anker'],['General','Condition','New'],['Capacity','Battery','20000 mAh'],['Ports','Output','USB-C 30W + USB-A'],['Warranty','Period','18 months']],
     'Anker PowerCore 20000mAh power bank in black', false, null],

    ['baseus-65w-charger', 'Baseus 65W GaN Fast Charger', 'baseus', 'accessories', 'new', 8999, null, 80,
     'Baseus 65W GaN III charger with 2×USB-C + USB-A — charge laptop and phone together.',
     '<p>This Baseus 65W GaN III charger packs laptop-class power into a pocketable body: two USB-C ports and one USB-A share 65W, enough for a MacBook Air plus a phone at once.</p><p>GaN technology means less heat and a smaller charger. Includes 12-month warranty.</p>',
     [['General','Brand','Baseus'],['General','Condition','New'],['Output','Power','65W'],['Ports','Output','2×USB-C + USB-A'],['Warranty','Period','12 months']],
     'Baseus 65W GaN fast charger in white', false, null],

    ['xiaomi-redmi-14c', 'Xiaomi Redmi 14C', 'xiaomi', 'budget-phones', 'new', 37999, 34999, 55,
     'Redmi 14C with 6.88-inch 120Hz display and 5160mAh battery — best phone under Rs 40,000.',
     '<p>The Xiaomi Redmi 14C is our top pick for budget buyers: a huge 6.88-inch 120Hz display, a 5160mAh battery and a capable Helio G81 chip, all under Rs 40,000.</p><p>Ideal as a first smartphone or a secondary device, with a clean HyperOS experience.</p>',
     [['General','Brand','Xiaomi'],['General','Model','Redmi 14C'],['General','Condition','New — Sealed'],['Display','Size','6.88 inches'],['Performance','Processor','Helio G81 Ultra'],['Performance','RAM','4GB'],['Performance','Storage','128GB'],['Battery','Capacity','5160 mAh']],
     'Xiaomi Redmi 14C in Starry Blue', false, null],

    ['anker-soundcore-p40i', 'Anker Soundcore P40i Wireless Earbuds', 'anker', 'accessories', 'new', 14999, 12999, 48,
     'Soundcore P40i earbuds with ANC, 60-hour battery life and IPX5 water resistance.',
     '<p>The Soundcore P40i earbuds deliver hybrid active noise cancellation, punchy BassUp sound and up to 60 hours of total playtime with the case.</p><p>IPX5 rated for workouts, with wireless charging support. 12-month warranty included.</p>',
     [['General','Brand','Anker Soundcore'],['General','Model','P40i'],['General','Condition','New'],['Battery','Playtime','60 hours (with case)'],['Features','ANC','Hybrid Active Noise Cancelling']],
     'Anker Soundcore P40i wireless earbuds in black', false, null],
];

$productIds = [];
foreach ($products as [$slug, $name, $brand, $cat, $cond, $price, $sale, $stock, $short, $desc, $specs, $alt, $featured, $variants]) {
    $img = $makeImage("products/$slug.svg", $name, '#374151', '#e5e7eb');
    $id = Database::insert('products', [
        'slug' => $slug, 'name' => $name,
        'brand_id' => $brandIds[$brand] ?? null,
        'category_id' => $catIds[$cat] ?? null,
        'short_desc' => $short, 'description' => $desc,
        'condition' => $cond, 'price' => $price, 'sale_price' => $sale,
        'stock' => $stock, 'sku' => strtoupper(str_replace('-', '', $slug)),
        'warranty' => $cond === 'new' ? 'Official warranty' : 'Shop warranty',
        'main_image' => $img, 'image_alt' => $alt,
        'featured' => $featured ? 1 : 0, 'status' => 'active',
        'published_at' => date('Y-m-d H:i:s'),
    ]);
    $productIds[$slug] = $id;

    Database::insert('product_images', ['product_id' => $id, 'path' => $img, 'alt' => $alt, 'position' => 0]);
    foreach ($specs as $i => [$group, $label, $value]) {
        Database::insert('product_specs', [
            'product_id' => $id, 'spec_group' => $group,
            'spec_label' => $label, 'spec_value' => $value, 'position' => $i,
        ]);
    }
    if ($variants) {
        $vPos = 0;
        foreach ($variants as $label => $variant) {
            [$ram, $storage, $color, $delta] = $variant;
            Database::insert('product_variants', [
                'product_id' => $id, 'label' => $label, 'ram' => $ram, 'storage' => $storage,
                'color' => $color, 'price_delta' => $delta, 'stock' => 10, 'position' => $vPos++,
            ]);
        }
    }
}

// Genuinely-missing identifiers stay NULL — never invent GTINs.
Database::query('UPDATE products SET gtin = NULL, mpn = NULL');

/* ------------------------------------------------------------- reviews -- */
$reviewData = [
    ['samsung-galaxy-a56', 'Ahmed R.', 5, 'Excellent value', 'Bought the A56 last month. Battery easily lasts a full day and the camera is surprisingly good at night. Official warranty card was included.', 1, 1],
    ['samsung-galaxy-a56', 'Sana K.', 4, 'Great phone, fast delivery', 'Delivery to Lahore took two days, packaging was secure. Phone is smooth, only wish it had a faster charger in the box.', 1, 1],
    ['samsung-galaxy-a56', 'Bilal H.', 5, 'Recommended seller', 'Genuine sealed unit, IMEI verified before payment. Will buy from Lala Mobiles again.', 1, 1],
    ['apple-iphone-16', 'Hassan M.', 5, 'Sealed and PTA approved', 'Sealed box, PTA approved, Face ID working perfectly. Best price I found in Rawalpindi.', 1, 1],
    ['apple-iphone-16', 'Ayesha T.', 5, 'Worth every rupee', 'Switched from Android and very happy. The Camera Control button is actually useful.', 1, 1],
    ['iphone-13-refurbished', 'Usman S.', 4, 'Battery like new', 'Refurbished unit came with 91% battery health, better than promised. Minor scuff on the frame but display is flawless.', 1, 1],
    ['anker-power-bank-20000', 'Faisal A.', 5, 'Charges my laptop too', 'The 30W USB-C port charged my MacBook Air without issues. Genuine Anker product with warranty card.', 1, 1],
    ['xiaomi-redmi-note-14-pro', 'Zara L.', 4, 'Camera is the highlight', '200MP camera captures great detail in daylight. HyperOS takes some getting used to.', 1, 1],
    ['xiaomi-redmi-note-14-pro', 'Imran D.', 3, 'Good but heats up', 'Phone is fine overall but gets warm while gaming. Charger in the box is slower than advertised speed.', 0, 1], // unverified but approved
    ['vivo-v50-lite', 'Nida Q.', 5, 'Battery champion', 'Two days on a single charge with moderate use. Very happy with this purchase.', 0, 0], // pending review — NOT in structured data
];
foreach ($reviewData as [$slug, $author, $rating, $title, $body, $verified, $approved]) {
    Database::insert('reviews', [
        'product_id' => $productIds[$slug], 'author' => $author, 'rating' => $rating,
        'title' => $title, 'body' => $body,
        'verified_purchase' => $verified, 'approved' => $approved,
    ]);
}
// Keep denormalised rating counters in sync with APPROVED reviews only.
foreach (array_values($productIds) as $pid) {
    Database::query(
        'UPDATE products p SET
            p.rating_count = (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.approved = 1),
            p.rating_avg   = COALESCE((SELECT ROUND(AVG(rating),2) FROM reviews r WHERE r.product_id = p.id AND r.approved = 1), 0)
         WHERE p.id = ?',
        [$pid]
    );
}

/* --------------------------------------------------------- blog posts --- */
$blog = [
    ['best-phones-under-50000-pakistan-2026', 'Best Phones Under Rs 50,000 in Pakistan (2026 Guide)',
     'Our pick of the best smartphones you can buy in Pakistan under Rs 50,000, with honest notes on where each one shines.',
     '<p>Finding a capable smartphone under Rs 50,000 in Pakistan is easier than ever, but the spec sheets can be misleading. Here is what actually matters at this price: battery size, display quality and after-sales service.</p><h2>1. Xiaomi Redmi 14C — Best overall under Rs 40,000</h2><p>The Redmi 14C pairs a 6.88-inch 120Hz display with a 5160mAh battery. If your budget is tight, this is the one to beat.</p><h2>2. Tecno Camon 30 5G — Best camera under Rs 70,000</h2><p>When budgets stretch to Rs 70,000, the Camon 30’s 50MP selfie shooter and 120Hz AMOLED display pull ahead.</p><h2>What to check before buying</h2><p>Always confirm PTA approval, verify the IMEI before payment, and buy from a seller who offers a written warranty. At Lala Mobiles, every unit is tested and documented before it ships.</p>',
     'Lala Mobiles Team', '2026-09-14 12:00:00'],
    ['refurbished-vs-used-phones-pakistan', 'Refurbished vs Used Phones in Pakistan: What’s the Real Difference?',
     'Grades, battery health, warranties — a practical guide to buying refurbished or used phones in Pakistan without surprises.',
     '<p>“Used” and “refurbished” get used interchangeably in Pakistani markets, but they describe very different products. Understanding the difference can save you thousands of rupees.</p><h2>What refurbished actually means</h2><p>A refurbished phone has had faulty parts replaced — typically the battery or display — and passed a full functional test. At Lala Mobiles, refurbished units carry a 3-month shop warranty and a minimum 85% battery health guarantee.</p><h2>Grades to expect</h2><p><strong>Grade A:</strong> light cosmetic wear, no functional faults. <strong>Grade B:</strong> visible scratches, fully functional. Anything with a cracked display or dead pixel should be priced (and labelled) much lower.</p><h2>Red flags when buying used</h2><p>Ask for the original box and PTA approval slip, check battery health in settings, and test Face ID or the fingerprint sensor in person. Never pay in full before checking the IMEI against the PTA database.</p>',
     'Lala Mobiles Team', '2026-08-30 12:00:00'],
    ['iphone-pta-approval-guide-pakistan', 'iPhone PTA Approval in Pakistan (2026): Complete Guide',
     'How PTA approval works for iPhones, how to check IMEI status, and what happens to unapproved phones.',
     '<p>Every phone used on Pakistani networks must be PTA approved. Here is how the process works in 2026 and what it means when you buy an iPhone.</p><h2>Checking IMEI status</h2><p>Dial *#06# to find your IMEI, then check it on the PTA DIRBS portal before paying any advance. An approved device shows the approval status immediately.</p><h2>Costs and timelines</h2><p>PTA duty is calculated on the device value and varies by model. New phones sold through official channels have duty included in the price — always ask for the invoice.</p><h2>Unapproved phones</h2><p>Unapproved IMEIs are blocked from cellular networks after the grace period. Wi-Fi still works, but a phone without cellular service is not much of a phone. Every iPhone at Lala Mobiles is PTA approved before it reaches you.</p>',
     'Lala Mobiles Team', '2026-08-05 12:00:00'],
];
foreach ($blog as [$slug, $title, $excerpt, $body, $author, $pub]) {
    Database::insert('blog_posts', [
        'slug' => $slug, 'title' => $title, 'excerpt' => $excerpt, 'body' => $body,
        'author_name' => $author, 'status' => 'published', 'published_at' => $pub,
    ]);
}

/* --------------------------------------------------------------- pages -- */
$pages = [
    ['about', 'About Lala Mobiles', 'static', 'Who we are and why thousands of customers across Pakistan trust Lala Mobiles.',
     '<p>Lala Mobiles has been serving customers from Saddar, Rawalpindi since 2015. We specialise in new, used and refurbished smartphones, with every device tested before it leaves our shop.</p><h2>What we promise</h2><ul><li>Genuine devices with verified IMEI</li><li>Transparent grading for used and refurbished stock</li><li>Written warranty on every purchase</li><li>Cash on Delivery and nationwide shipping</li></ul><h2>Visit us</h2><p>Main Mobile Market, Saddar, Rawalpindi. Open daily from 11am to 9pm. Call us at +92-300-0000000 before visiting to confirm stock.</p>', null, null, null, null],
    ['contact', 'Contact Us', 'static', 'Questions about stock, warranty or delivery? Reach the Lala Mobiles team.',
     '<p>We reply to WhatsApp and phone enquiries within business hours (11am–9pm, Pakistan time).</p><h2>Get in touch</h2><ul><li><strong>Phone / WhatsApp:</strong> +92-300-0000000</li><li><strong>Email:</strong> info@lalamobiles.pk</li><li><strong>Shop:</strong> Main Mobile Market, Saddar, Rawalpindi</li></ul><h2>Delivery and returns</h2><p>Orders ship nationwide via courier, usually within 24–48 hours. Returns are accepted within 7 days for sealed products in original packaging; refurbished and used units carry a shop warranty instead.</p>', null, null, null, null],
    ['warranty-return-policy', 'Warranty & Return Policy', 'static', 'Warranty coverage, return windows and refund timelines for every purchase at Lala Mobiles.',
     '<h2>Warranty</h2><p>New phones carry the manufacturer’s official warranty where applicable. Used and refurbished phones carry a 1-month and 3-month Lala Mobiles shop warranty respectively, covering hardware faults.</p><h2>Returns</h2><p>Sealed products can be returned within 7 days in original packaging. Used and refurbished units are covered by warranty rather than returns, since each unit is individually tested and graded.</p><h2>Refunds</h2><p>Approved refunds are processed within 7 working days to the original payment method or via bank transfer for Cash on Delivery orders.</p>', null, null, null, null],
    ['delivery-information', 'Delivery Information', 'static', 'Shipping times, Cash on Delivery coverage and charges across Pakistan.',
     '<h2>Nationwide delivery</h2><p>We deliver to all major cities in Pakistan, including Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, Multan and Peshawar. Orders placed before 2pm are usually dispatched the same day.</p><h2>Cash on Delivery</h2><p>Cash on Delivery is available nationwide. For orders above Rs 100,000 we may request a small advance to confirm the order.</p><h2>Charges</h2><p>Delivery is free above Rs 50,000. Below that, a flat Rs 250 courier charge applies.</p>', null, null, null, null],
    ['privacy-policy', 'Privacy Policy', 'static', 'How Lala Mobiles collects, uses and protects your personal information.',
     '<p>This policy explains what information we collect when you order from Lala Mobiles and how we use it.</p><h2>What we collect</h2><p>Name, phone number, delivery address and order history. We do not store card details — online payments are handled by our payment partner.</p><h2>How we use it</h2><p>To process and deliver orders, handle warranty claims and answer your questions. We do not sell personal data to third parties.</p><h2>Cookies</h2><p>We use essential cookies for the shopping cart. Analytics cookies are only set after you give consent.</p>', null, null, null, null],
    ['screen-replacement', 'Mobile Screen Replacement Service', 'repair', 'Professional screen replacement for Samsung, iPhone, Xiaomi and more — genuine parts, same-day service.',
     '<p>A cracked display is the most common phone fault we see. Our technicians replace screens using genuine or original-quality parts, with same-day turnaround for most models.</p><h2>What’s included</h2><ul><li>Free diagnostic check</li><li>Genuine or original-quality display</li><li>Water-seal reapplication where applicable</li><li>30-day warranty on the replaced part</li></ul><h2>How it works</h2><p>Walk in at our Saddar shop or call ahead to reserve parts for your model. Most screen replacements are completed within 2–3 hours.</p>',
     'Screen Replacement', 9500, 45000, 'Rawalpindi, Islamabad, Lahore, Karachi'],
    ['battery-replacement', 'Mobile Battery Replacement', 'repair', 'Same-day battery replacement with genuine-capacity cells and health report.',
     '<p>If your phone barely survives half a day, a battery replacement is usually cheaper than a new phone. We stock genuine-capacity cells for all popular models.</p><h2>What’s included</h2><ul><li>Battery health test before and after</li><li>Genuine-capacity replacement cell</li><li>Adhesive and water-seal reapplication</li><li>30-day warranty on the battery</li></ul>',
     'Battery Replacement', 4500, 15000, 'Rawalpindi, Islamabad, Lahore, Karachi'],
    ['water-damage-repair', 'Water Damage Repair Service', 'repair', 'Ultrasonic cleaning and board-level diagnosis for liquid-damaged phones.',
     '<p>Liquid damage responds best to fast action. Bring your phone in as soon as possible — we ultrasonically clean the board, replace corroded components where feasible, and provide an honest assessment of what is recoverable.</p><h2>Honest diagnosis</h2><p>Board-level repair is never guaranteed. We quote only after inspection and will tell you plainly when a repair is not worth the cost.</p>',
     'Water Damage Repair', 3500, 25000, 'Rawalpindi, Islamabad'],
];
foreach ($pages as [$slug, $title, $type, $intro, $body, $svc, $pfrom, $pto, $area]) {
    Database::insert('pages', [
        'slug' => $slug, 'title' => $title, 'page_type' => $type,
        'intro' => $intro, 'body' => $body,
        'service_name' => $svc, 'price_from' => $pfrom, 'price_to' => $pto,
        'area_served' => $area, 'status' => 'published',
    ]);
}

/* -------------------------------------------------------- filter pages -- */
$filterPages = [
    ['samsung-phones-under-150000', 'Samsung Phones Under Rs 150,000', 'samsung-phones', $brandIds['samsung'],
     '{"max_price":150000,"condition":"new"}',
     '<p>Every new Samsung phone currently under Rs 150,000 at Lala Mobiles, from the A-series workhorses to last-generation flagships — all with official warranty and Cash on Delivery.</p>'],
    ['refurbished-iphones', 'Refurbished iPhones in Pakistan', 'refurbished-phones', $brandIds['apple'],
     '{"condition":"refurbished"}',
     '<p>Certified refurbished iPhones with minimum 85% battery health and a 3-month shop warranty. Each unit is individually tested and photographed.</p>'],
    ['best-power-banks-pakistan', 'Best Power Banks in Pakistan', 'power-banks', null,
     '{"condition":"new"}',
     '<p>Anker and Baseus power banks with genuine capacity, fast charging support and local warranty — no fake mAh claims, just tested batteries.</p>'],
];
foreach ($filterPages as [$slug, $name, $cat, $brand, $filters, $intro]) {
    Database::insert('filter_pages', [
        'slug' => $slug, 'name' => $name,
        'category_id' => $cat !== null ? $catIds[$cat] : null,
        'brand_id' => $brand, 'filters' => $filters,
        'intro' => $intro, 'is_indexable' => 1, 'status' => 'published',
    ]);
}

/* ------------------------------------------------------- custom SEO ----- */
// A couple of hand-written overrides to demonstrate admin customisation.
Database::insert('seo_meta', [
    'entity_type' => 'product', 'entity_id' => $productIds['apple-iphone-16'],
    'seo_title' => 'Apple iPhone 16 Price in Pakistan — PTA Approved | Lala Mobiles',
    'meta_description' => 'iPhone 16 (128GB) price in Pakistan: Rs 384,999. PTA approved, sealed with warranty. Cash on Delivery nationwide. Order from Lala Mobiles today.',
]);

echo "Seed complete: " . count($products) . " products, " . count($blog) . " blog posts, "
    . count($pages) . " pages, " . count($filterPages) . " filter pages.\n";
