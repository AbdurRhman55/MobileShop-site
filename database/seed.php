<?php
/**
 * Idempotent, additive demo catalogue and commerce seed. Run with:
 *   php database/seed.php
 *
 * Demo customers/orders use reserved example.invalid addresses and DEMO
 * references. The script never creates fabricated product reviews/ratings.
 */
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$photo = static fn(string $id, int $width = 960): string =>
    'https://images.unsplash.com/' . $id . '?auto=format&fit=crop&w=' . $width . '&q=82';

$taxonomy = [
    'Audio' => ['audio', 'headphones', 'photo-1608043152269-423dbba4e7e1', 'groups' => [
        'Speakers' => ['bluetooth-speakers' => 'Bluetooth speakers', 'portable-speakers' => 'Portable speakers', 'smart-speakers' => 'Smart speakers', 'soundbars' => 'Soundbars', 'home-theatre' => 'Home theatre'],
        'Headphones' => ['over-ear-headphones' => 'Over-ear', 'on-ear-headphones' => 'On-ear', 'gaming-headsets' => 'Gaming headsets', 'wireless-headphones' => 'Wireless'],
        'Earbuds and AirPods' => ['true-wireless-earbuds' => 'True wireless', 'neckband-earphones' => 'Neckband', 'wired-earphones' => 'Wired', 'noise-cancelling-earbuds' => 'Noise-cancelling'],
        'Handsfree' => ['wired-handsfree' => 'Wired', 'bluetooth-handsfree' => 'Bluetooth', 'type-c-handsfree' => 'Type-C'],
        'Microphones' => ['wireless-microphones' => 'Wireless', 'lavalier-microphones' => 'Lavalier', 'studio-usb-microphones' => 'Studio / USB'],
        'Audio Accessories' => ['ear-tips' => 'Ear tips', 'audio-cases' => 'Cases', 'audio-cables' => 'Cables', 'audio-adapters' => 'Adapters'],
    ]],
    'Mobiles and Tablets' => ['mobiles-tablets', 'smartphone', 'photo-1511707171634-5f897ff02aa9', 'groups' => [
        'Smartphones' => ['android-phones' => 'Android phones', 'iphone' => 'iPhone', 'refurbished-smartphones' => 'Refurbished phones'],
        'Tablets' => ['android-tablets' => 'Android tablets', 'ipad' => 'iPad'],
        'Cases and Covers' => ['phone-cases' => 'Protective cases', 'phone-covers' => 'Covers'],
        'Screen Protectors' => ['tempered-glass' => 'Tempered glass', 'privacy-protectors' => 'Privacy protectors'],
        'Chargers and Power' => ['phone-chargers' => 'Chargers', 'power-banks' => 'Power banks', 'phone-cables' => 'Cables'],
    ]],
    'Computers and Laptops' => ['computers-laptops', 'laptop', 'photo-1496181133206-80ce9b88a853', 'groups' => [
        'Laptops' => ['windows-laptops' => 'Windows laptops', 'macbooks' => 'MacBooks'],
        'Desktops' => ['desktop-computers' => 'Desktop computers', 'mini-pcs' => 'Mini PCs'],
        'Monitors' => ['office-monitors' => 'Office monitors', 'gaming-monitors' => 'Gaming monitors'],
        'Keyboards and Mice' => ['keyboards' => 'Keyboards', 'computer-mice' => 'Mice'],
        'Webcams and Storage' => ['webcams' => 'Webcams', 'computer-storage' => 'Storage', 'networking' => 'Networking'],
    ]],
    'Smart Watches and Wearables' => ['wearables', 'watch', 'photo-1523275335684-37898b6baf30', 'groups' => [
        'Smart Watches' => ['smart-watches' => 'Smart watches'], 'Fitness Bands' => ['fitness-bands' => 'Fitness bands'],
        'Straps' => ['watch-straps' => 'Straps'], 'Wearable Chargers' => ['watch-chargers' => 'Chargers'],
    ]],
    'Accessories' => ['accessories', 'cable', 'photo-1583394838336-acd977736f90', 'groups' => [
        'Bags' => ['tech-bags' => 'Tech bags'], 'Stands and Holders' => ['device-stands' => 'Device stands', 'phone-holders' => 'Phone holders'],
        'Cables and Adapters' => ['cables-adapters' => 'Cables and adapters'], 'Car Accessories' => ['car-accessories' => 'Car accessories'],
        'Gadgets' => ['everyday-gadgets' => 'Everyday gadgets'],
    ]],
    'Gaming' => ['gaming', 'gamepad-2', 'photo-1593305841991-05c297ba4575', 'groups' => [
        'Consoles' => ['gaming-consoles' => 'Consoles'], 'Controllers' => ['gaming-controllers' => 'Controllers'],
        'Gaming Headsets' => ['gaming-audio' => 'Gaming headsets'], 'Gaming Chairs' => ['gaming-chairs' => 'Gaming chairs'],
        'Gaming Accessories' => ['gaming-accessories' => 'Accessories'],
    ]],
    'Cameras and Drones' => ['cameras-drones', 'camera', 'photo-1516035069371-29a1b244cc32', 'groups' => [
        'Digital Cameras' => ['digital-cameras' => 'Digital cameras'], 'Lenses' => ['camera-lenses' => 'Lenses'],
        'Action Cameras' => ['action-cameras' => 'Action cameras'], 'Drones' => ['camera-drones' => 'Drones'],
    ]],
    'Home and Smart Devices' => ['home-smart-devices', 'house', 'photo-1558002038-1055907df827', 'groups' => [
        'Smart Home' => ['smart-home-devices' => 'Smart home'], 'Home Networking' => ['home-networking' => 'Home networking'],
        'Smart Lighting' => ['smart-lighting' => 'Smart lighting'], 'Home Audio' => ['home-audio' => 'Home audio'],
    ]],
];

$pdo = Database::pdo();
$pdo->beginTransaction();
try {
    foreach ($taxonomy as $name => $department) {
        [$slug, $icon, $photoId] = $department;
        $groups = $department['groups'];
        $image = $photo($photoId, 1600);
        $intro = 'Explore ' . $name . ' selected for quality, compatibility and everyday value.';
        $row = Database::fetch('SELECT id FROM categories WHERE slug = ?', [$slug]);
        if ($row === null) {
            $rootId = Database::insert('categories', [
                'parent_id' => null, 'slug' => $slug, 'name' => $name, 'intro' => $intro,
                'image' => $image, 'banner_image' => $image, 'icon' => $icon,
                'meta_title' => $name . ' | Lala Mobiles', 'meta_description' => excerpt($intro, 160),
                'position' => 20, 'sort_order' => 20, 'status' => 'active', 'is_active' => 1,
            ]);
        } else {
            $rootId = (int) $row['id'];
            Database::query(
                'UPDATE categories SET name = ?, icon = COALESCE(icon, ?), image = COALESCE(image, ?),
                 banner_image = COALESCE(banner_image, ?), meta_title = COALESCE(meta_title, ?),
                 meta_description = COALESCE(meta_description, ?), is_active = 1 WHERE id = ?',
                [$name, $icon, $image, $image, $name . ' | Lala Mobiles', excerpt($intro, 160), $rootId]
            );
        }
        if ($slug === 'mobiles-tablets') {
            foreach (['smartphones', 'used-phones'] as $legacySlug) {
                Database::query('UPDATE categories SET parent_id = ? WHERE slug = ? AND parent_id IS NULL', [$rootId, $legacySlug]);
            }
        }
        foreach ($groups as $groupName => $leafCategories) {
            $groupId = ensureCategory(slugify($groupName), $groupName, $rootId, $image, $icon);
            foreach ($leafCategories as $leafSlug => $leafName) {
                ensureCategory($leafSlug, $leafName, $groupId, $image, $icon);
            }
        }
    }
    \App\Models\Catalog::flushNavigationCache();
    seedBrands();
    seedProducts($photo);
    upgradeProductImages();
    seedStoreDefaults();
    seedDemoCommerce();
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    throw $error;
}

echo "Catalog seed complete. Demo orders are labelled DEMO; no synthetic ratings or reviews were created.\n";

function ensureCategory(string $slug, string $name, int $parentId, string $image, string $icon): int
{
    $row = Database::fetch('SELECT id FROM categories WHERE slug = ?', [$slug]);
    if ($row !== null) { return (int) $row['id']; }
    $intro = 'Browse ' . $name . ' at Lala Mobiles with nationwide delivery across Pakistan.';
    return Database::insert('categories', [
        'parent_id' => $parentId, 'slug' => $slug, 'name' => $name, 'intro' => $intro,
        'image' => $image, 'banner_image' => $image, 'icon' => $icon,
        'meta_title' => $name . ' | Lala Mobiles', 'meta_description' => excerpt($intro, 160),
        'position' => 0, 'sort_order' => 0, 'status' => 'active', 'is_active' => 1,
    ]);
}

function seedStoreDefaults(): void
{
    $defaults = [
        'site_name' => 'Lala Mobiles',
        'site_tagline' => 'Technology for everyday life',
        'canonical_domain' => 'http://127.0.0.1:8088',
        'organization_name' => 'Lala Mobiles',
        'robots_index_site' => '1',
        'sitemap_enabled' => '1',
        'blog_enabled' => '1',
        'feed_enabled' => '1',
        'analytics_consent_required' => '1',
        'review_min_for_aggregate' => '3',
    ];
    foreach ($defaults as $key => $value) {
        Database::query('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)', [$key, $value]);
    }
    $username = getenv('ADMIN_USER') ?: 'admin';
    if (Database::fetch('SELECT id FROM admins WHERE username = ?', [$username]) === null) {
        $password = getenv('ADMIN_PASS') ?: 'Lala@Mobiles2026';
        Database::insert('admins', [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'display_name' => 'Store Admin',
        ]);
    }
}

function seedBrands(): void
{
    $brands = [
        ['sony', 'Sony', 'https://www.sony.com/'], ['jbl', 'JBL', 'https://www.jbl.com/'], ['bose', 'Bose', 'https://www.bose.com/'],
        ['logitech', 'Logitech', 'https://www.logitech.com/'], ['sennheiser', 'Sennheiser', 'https://www.sennheiser.com/'],
        ['soundcore', 'Soundcore', 'https://www.soundcore.com/'], ['dell', 'Dell', 'https://www.dell.com/'],
        ['lenovo', 'Lenovo', 'https://www.lenovo.com/'], ['hp', 'HP', 'https://www.hp.com/'], ['asus', 'ASUS', 'https://www.asus.com/'],
        ['acer', 'Acer', 'https://www.acer.com/'], ['corsair', 'Corsair', 'https://www.corsair.com/'],
        ['razer', 'Razer', 'https://www.razer.com/'], ['canon', 'Canon', 'https://www.canon.com/'],
        ['nikon', 'Nikon', 'https://www.nikon.com/'], ['garmin', 'Garmin', 'https://www.garmin.com/'],
        ['lg', 'LG', 'https://www.lg.com/'], ['xiaomi', 'Xiaomi', 'https://www.mi.com/'],
        ['tp-link', 'TP-Link', 'https://www.tp-link.com/'], ['belkin', 'Belkin', 'https://www.belkin.com/'],
        ['anker', 'Anker', 'https://www.anker.com/'], ['apple', 'Apple', 'https://www.apple.com/'],
        ['samsung', 'Samsung', 'https://www.samsung.com/'], ['infinix', 'Infinix', 'https://www.infinixmobility.com/'],
        ['tecno', 'Tecno', 'https://www.tecno-mobile.com/'], ['oppo', 'Oppo', 'https://www.oppo.com/'],
        ['vivo', 'Vivo', 'https://www.vivo.com/'], ['realme', 'Realme', 'https://www.realme.com/'],
        ['baseus', 'Baseus', 'https://www.baseus.com/'],
    ];
    foreach ($brands as [$slug, $name, $website]) {
        if (Database::fetch('SELECT id FROM brands WHERE slug = ?', [$slug]) !== null) { continue; }
        Database::insert('brands', [
            'slug' => $slug, 'name' => $name, 'website' => $website,
            'intro' => $name . ' technology and consumer electronics products.', 'status' => 'active',
        ]);
    }
}

function seedProducts(Closure $photo): void
{
    $brandRows = Database::fetchAll('SELECT id, slug FROM brands');
    $brandIds = [];
    foreach ($brandRows as $row) { $brandIds[(string) $row['slug']] = (int) $row['id']; }
    $categoryRows = Database::fetchAll("SELECT id, slug FROM categories WHERE status = 'active'");
    $categoryIds = [];
    foreach ($categoryRows as $row) { $categoryIds[(string) $row['slug']] = (int) $row['id']; }
    $products = [
        ['sony-wh-ch720n', 'Sony WH-CH720N Wireless Noise-Cancelling Headphones', 'sony', 'wireless-headphones', 42999, 38999, 24, 'Lightweight wireless headphones with active noise cancellation and comfortable daily listening.'],
        ['jbl-tune-770nc', 'JBL Tune 770NC Wireless Headphones', 'jbl', 'over-ear-headphones', 36999, null, 19, 'Over-ear wireless sound with adaptive noise cancelling and a foldable travel-friendly build.'],
        ['bose-quietcomfort-headphones', 'Bose QuietComfort Wireless Headphones', 'bose', 'wireless-headphones', 109999, null, 8, 'Comfort-first wireless headphones with adjustable noise cancellation for travel and focus.'],
        ['soundcore-life-q30', 'Soundcore Life Q30 Hybrid ANC Headphones', 'soundcore', 'over-ear-headphones', 24999, 21999, 31, 'Hybrid noise cancelling and multipoint listening in a padded over-ear design.'],
        ['jbl-flip-6', 'JBL Flip 6 Portable Bluetooth Speaker', 'jbl', 'bluetooth-speakers', 39999, null, 15, 'Portable Bluetooth speaker with bold stereo sound and a durable outdoor-ready design.'],
        ['sony-srs-xb100', 'Sony SRS-XB100 Compact Bluetooth Speaker', 'sony', 'bluetooth-speakers', 19999, 18499, 27, 'Compact wireless speaker with a carrying strap and clear sound for small spaces.'],
        ['sony-wf-c700n', 'Sony WF-C700N Truly Wireless Earbuds', 'sony', 'true-wireless-earbuds', 34999, null, 13, 'Truly wireless earbuds with noise cancelling and a compact charging case.'],
        ['jbl-wave-beam-2', 'JBL Wave Beam 2 True Wireless Earbuds', 'jbl', 'true-wireless-earbuds', 22999, 20999, 22, 'Everyday true wireless earbuds with a secure fit and pocketable charging case.'],
        ['logitech-g435', 'Logitech G435 LIGHTSPEED Wireless Gaming Headset', 'logitech', 'gaming-headsets', 29999, null, 11, 'Lightweight wireless gaming headset with low-latency connectivity.'],
        ['sennheiser-profile-usb', 'Sennheiser Profile USB Microphone', 'sennheiser', 'studio-usb-microphones', 42999, null, 6, 'USB condenser microphone for clear voice capture in streaming and calls.'],
        ['soundcore-motion-300', 'Soundcore Motion 300 Portable Speaker', 'soundcore', 'portable-speakers', 27999, null, 14, 'Portable stereo sound in a compact speaker for indoor and outdoor listening.'],
        ['jbl-bar-500', 'JBL Bar 500 5.1-Channel Soundbar', 'jbl', 'soundbars', 169999, null, 4, 'Home soundbar with wireless subwoofer and immersive surround support.'],
        ['bose-soundlink-flex', 'Bose SoundLink Flex Portable Speaker', 'bose', 'portable-speakers', 74999, null, 5, 'Portable Bluetooth speaker designed for clear listening in varied settings.'],
        ['soundcore-p40i', 'Soundcore P40i Noise-Cancelling Earbuds', 'soundcore', 'noise-cancelling-earbuds', 14999, 13999, 36, 'True wireless earbuds with adaptive noise cancelling.'],
        ['sony-linkbuds-s', 'Sony LinkBuds S Truly Wireless Earbuds', 'sony', 'noise-cancelling-earbuds', 44999, null, 7, 'Lightweight earbuds with noise cancelling and ambient listening modes.'],
        ['jbl-quantum-100', 'JBL Quantum 100 Gaming Headset', 'jbl', 'gaming-headsets', 12999, null, 18, 'Wired gaming headset with detachable microphone and cushioned ear pads.'],
        ['logitech-blue-yeti', 'Logitech Blue Yeti USB Microphone', 'logitech', 'studio-usb-microphones', 49999, null, 6, 'Desktop USB microphone for voice, streaming and home recording.'],
        ['belkin-usb-c-audio', 'Belkin USB-C to 3.5 mm Audio Adapter', 'belkin', 'audio-adapters', 4999, null, 42, 'Compact audio adapter for wired headphones and USB-C devices.'],
        ['anker-usb-c-cable', 'Anker USB-C Braided Cable', 'anker', 'audio-cables', 3999, null, 58, 'Braided USB-C cable for charging and compatible device accessories.'],
        ['samsung-galaxy-tab-s10', 'Samsung Galaxy Tab S10+ Wi-Fi Tablet', 'samsung', 'android-tablets', 299999, null, 7, 'Large-screen Android tablet for productivity and entertainment.'],
        ['apple-ipad-air-11', 'Apple iPad Air 11-inch', 'apple', 'ipad', 229999, null, 8, 'Versatile tablet for creative work, study and everyday browsing.'],
        ['apple-airpods-4', 'Apple AirPods 4', 'apple', 'true-wireless-earbuds', 45999, null, 10, 'Wireless earbuds with compact charging case and Apple device pairing.'],
        ['xiaomi-redmi-buds-6', 'Xiaomi Redmi Buds 6 Active', 'xiaomi', 'true-wireless-earbuds', 5999, null, 45, 'Lightweight true wireless earbuds for calls and commuting.'],
        ['xiaomi-smart-band-9', 'Xiaomi Smart Band 9', 'xiaomi', 'fitness-bands', 11999, null, 28, 'Fitness band with activity and sleep tracking.'],
        ['garmin-vivoactive-5', 'Garmin vívoactive 5 Smartwatch', 'garmin', 'smart-watches', 89999, null, 5, 'GPS smartwatch with health insights and activity tracking.'],
        ['samsung-galaxy-watch7', 'Samsung Galaxy Watch7', 'samsung', 'smart-watches', 79999, null, 6, 'Smartwatch with fitness insights and health tracking.'],
        ['lenovo-ideapad-slim-5', 'Lenovo IdeaPad Slim 5 Laptop', 'lenovo', 'windows-laptops', 219999, null, 4, 'Slim everyday laptop for study, office tasks and productivity.'],
        ['hp-pavilion-15', 'HP Pavilion 15 Laptop', 'hp', 'windows-laptops', 199999, null, 5, '15-inch laptop for everyday work and home computing.'],
        ['dell-inspiron-14', 'Dell Inspiron 14 Laptop', 'dell', 'windows-laptops', 239999, null, 3, 'Portable 14-inch laptop for work and study.'],
        ['asus-vivobook-16', 'ASUS Vivobook 16 Laptop', 'asus', 'windows-laptops', 204999, null, 4, 'Large-screen laptop for home and office workflows.'],
        ['apple-macbook-air-13', 'Apple MacBook Air 13-inch', 'apple', 'macbooks', 379999, null, 3, 'Thin notebook for everyday productivity and creative work.'],
        ['acer-nitro-v-15', 'Acer Nitro V 15 Gaming Laptop', 'acer', 'windows-laptops', 329999, null, 3, 'Performance laptop with a high-refresh display for games.'],
        ['lg-ultragear-27', 'LG UltraGear 27-inch Gaming Monitor', 'lg', 'gaming-monitors', 99999, null, 5, 'High-refresh 27-inch monitor for responsive gaming.'],
        ['dell-p2425h-monitor', 'Dell 24-inch USB-C Monitor', 'dell', 'office-monitors', 79999, null, 7, 'Full HD office display with ergonomic stand.'],
        ['logitech-mx-master-3s', 'Logitech MX Master 3S Wireless Mouse', 'logitech', 'computer-mice', 34999, null, 10, 'Ergonomic wireless mouse with precise tracking.'],
        ['logitech-mx-keys-s', 'Logitech MX Keys S Wireless Keyboard', 'logitech', 'keyboards', 39999, null, 8, 'Low-profile wireless keyboard for multi-device workflows.'],
        ['logitech-c920-webcam', 'Logitech C920 HD Pro Webcam', 'logitech', 'webcams', 24999, null, 11, '1080p webcam for clear video calls and meetings.'],
        ['tp-link-archer-ax55', 'TP-Link Archer AX55 Wi-Fi 6 Router', 'tp-link', 'networking', 29999, null, 9, 'Dual-band Wi-Fi 6 router for connected-home coverage.'],
        ['samsung-t7-shield-1tb', 'Samsung T7 Shield 1TB Portable SSD', 'samsung', 'computer-storage', 39999, null, 12, 'Rugged portable solid-state storage for backups.'],
        ['razer-blackwidow-v4', 'Razer BlackWidow V4 Mechanical Keyboard', 'razer', 'keyboards', 54999, null, 4, 'Mechanical keyboard for gaming and desk use.'],
        ['corsair-k55-rgb-pro', 'Corsair K55 RGB PRO Keyboard', 'corsair', 'gaming-accessories', 17999, null, 7, 'Full-size keyboard with dedicated media controls.'],
        ['sony-ps5-dualsense', 'Sony DualSense Wireless Controller', 'sony', 'gaming-controllers', 24999, null, 12, 'Wireless controller with haptic feedback.'],
        ['sony-playstation-5-slim', 'Sony PlayStation 5 Slim Console', 'sony', 'gaming-consoles', 189999, null, 3, 'Current-generation home console in a compact design.'],
        ['canon-eos-r50', 'Canon EOS R50 Mirrorless Camera', 'canon', 'digital-cameras', 279999, null, 3, 'Compact mirrorless camera for photography and video.'],
        ['nikon-z30', 'Nikon Z30 Mirrorless Creator Camera', 'nikon', 'digital-cameras', 239999, null, 2, 'Lightweight mirrorless camera designed for video.'],
        ['xiaomi-smart-camera-c500', 'Xiaomi Smart Camera C500 Pro', 'xiaomi', 'smart-home-devices', 17999, null, 9, 'Connected indoor camera with app controls.'],
        ['tp-link-tapo-smart-plug', 'TP-Link Tapo Wi-Fi Smart Plug', 'tp-link', 'smart-home-devices', 4999, null, 31, 'App-controlled smart plug for compatible home devices.'],
        ['anker-737-power-bank', 'Anker 737 Power Bank 24K', 'anker', 'power-banks', 49999, null, 6, 'High-capacity portable power with USB-C fast charging.'],
        ['belkin-boostcharge-3in1', 'Belkin 3-in-1 Wireless Charging Stand', 'belkin', 'phone-chargers', 49999, null, 5, 'Wireless charging stand for compatible phones and wearables.'],
        ['samsung-galaxy-a56-audio-shop', 'Samsung Galaxy A56 5G', 'samsung', 'android-phones', 144999, 139999, 20, 'Mid-range smartphone with a vivid display, multi-camera system and fast charging.'],
        ['apple-iphone-16-demo', 'Apple iPhone 16 128GB', 'apple', 'iphone', 384999, null, 8, 'Current-generation smartphone with a bright display and advanced camera system.'],
        ['xiaomi-redmi-note-14', 'Xiaomi Redmi Note 14 Pro 5G', 'xiaomi', 'android-phones', 89999, 84999, 19, '5G smartphone with a high-resolution camera and smooth AMOLED display.'],
        ['infinix-note-50', 'Infinix Note 50 Pro+', 'infinix', 'android-phones', 74999, 69999, 16, 'Everyday smartphone with fast charging, a high refresh display and versatile cameras.'],
        ['tecno-camon-30-demo', 'Tecno Camon 30 5G', 'tecno', 'android-phones', 64999, null, 23, 'Photography-focused smartphone with a smooth AMOLED display.'],
        ['oppo-reno-13-demo', 'Oppo Reno 13 5G', 'oppo', 'android-phones', 134999, null, 9, 'Slim 5G smartphone with portrait camera features and a polished design.'],
        ['vivo-v50-lite-demo', 'Vivo V50 Lite 5G', 'vivo', 'android-phones', 109999, 99999, 14, 'Long-lasting smartphone with fast charging and capable everyday photography.'],
        ['realme-14-pro-demo', 'Realme 14 Pro 5G', 'realme', 'android-phones', 119999, null, 12, '5G smartphone with a curved display and a versatile camera system.'],
        ['baseus-65w-gan', 'Baseus 65W GaN Fast Charger', 'baseus', 'phone-chargers', 8999, null, 45, 'Compact multi-port GaN charger for compatible phones and laptops.'],
        ['anker-powercore-20k', 'Anker PowerCore 20000mAh Power Bank', 'anker', 'power-banks', 17999, 15999, 38, 'High-capacity portable charging with USB-C fast charging support.'],
    ];
    $images = [
        $photo('photo-1608043152269-423dbba4e7e1'), $photo('photo-1606220945770-b5b6c2c55bf1'),
        $photo('photo-1590658268037-6bf12165a8df'), $photo('photo-1583394838336-acd977736f90'),
        $photo('photo-1511707171634-5f897ff02aa9'), $photo('photo-1496181133206-80ce9b88a853'),
        $photo('photo-1523275335684-37898b6baf30'), $photo('photo-1516035069371-29a1b244cc32'),
        $photo('photo-1593305841991-05c297ba4575'), $photo('photo-1558002038-1055907df827'),
    ];
    $productCount = (int) Database::fetchColumn('SELECT COUNT(*) FROM products');
    $audio = Database::fetch('SELECT id FROM categories WHERE slug = ?', ['audio']);
    if ($audio === null) { return; }
    foreach ($products as $index => [$slug, $name, $brandSlug, $categorySlug, $price, $salePrice, $stock, $description]) {
        if (Database::fetch('SELECT id FROM products WHERE slug = ?', [$slug]) !== null || $productCount >= 100) { continue; }
        $image = $images[$index % count($images)];
        $id = Database::insert('products', [
            'slug' => $slug, 'name' => $name, 'brand_id' => $brandIds[$brandSlug] ?? null,
            'category_id' => $categoryIds[$categorySlug] ?? (int) $audio['id'],
            'short_desc' => $description, 'description' => '<p>' . e($description) . '</p>',
            'condition' => 'new', 'price' => $price, 'sale_price' => $salePrice, 'stock' => $stock,
            'sku' => strtoupper(str_replace('-', '', $slug)), 'warranty' => 'Demo listing — confirm availability and warranty before purchase.',
            'main_image' => $image, 'image_alt' => $name . ' product photo',
            'rating_avg' => 0, 'rating_count' => 0, 'featured' => 0, 'status' => 'active', 'published_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ([1, 2, 3, 4] as $position) {
            Database::insert('product_images', [
                'product_id' => $id, 'path' => $images[($index + $position) % count($images)],
                'alt' => $name . ' gallery view ' . $position, 'position' => $position,
            ]);
        }
        foreach ([['Brand', ucfirst($brandSlug)], ['Category', str_replace('-', ' ', ucfirst($categorySlug))], ['Condition', 'New demo listing']] as $position => [$label, $value]) {
            Database::insert('product_specs', ['product_id' => $id, 'spec_group' => 'General', 'spec_label' => $label, 'spec_value' => $value, 'position' => $position]);
        }
        $productCount++;
    }
}

function upgradeProductImages(): void
{
    $rows = Database::fetchAll("SELECT id, slug, name, main_image, image_alt FROM products WHERE status <> 'deleted' ORDER BY id");
    $photos = [
        'photo-1608043152269-423dbba4e7e1', 'photo-1606220945770-b5b6c2c55bf1',
        'photo-1590658268037-6bf12165a8df', 'photo-1583394838336-acd977736f90',
        'photo-1511707171634-5f897ff02aa9', 'photo-1496181133206-80ce9b88a853',
        'photo-1523275335684-37898b6baf30', 'photo-1516035069371-29a1b244cc32',
        'photo-1593305841991-05c297ba4575', 'photo-1558002038-1055907df827',
    ];
    foreach ($rows as $row) {
        $hash = abs(crc32((string) $row['slug']));
        $main = (string) ($row['main_image'] ?? '');
        if ($main === '' || str_starts_with($main, 'img/products/') || str_ends_with(strtolower($main), '.svg')) {
            $main = 'https://images.unsplash.com/' . $photos[$hash % count($photos)] . '?auto=format&fit=crop&w=960&q=82';
            Database::query('UPDATE products SET main_image = ? WHERE id = ?', [$main, (int) $row['id']]);
        }
        $alt = trim((string) ($row['image_alt'] ?? '')) ?: (string) $row['name'] . ' product photo';
        $primary = Database::fetch('SELECT id FROM product_images WHERE product_id = ? AND position = 0 LIMIT 1', [(int) $row['id']]);
        if ($primary === null) {
            Database::insert('product_images', ['product_id' => (int) $row['id'], 'path' => $main, 'alt' => $alt, 'position' => 0]);
        } else {
            Database::query('UPDATE product_images SET path = ?, alt = ? WHERE id = ?', [$main, $alt, (int) $primary['id']]);
        }
        for ($position = 1; $position <= 4; $position++) {
            if (Database::fetch('SELECT id FROM product_images WHERE product_id = ? AND position = ?', [(int) $row['id'], $position]) !== null) { continue; }
            $path = 'https://images.unsplash.com/' . $photos[($hash + $position) % count($photos)] . '?auto=format&fit=crop&w=960&q=82';
            Database::insert('product_images', ['product_id' => (int) $row['id'], 'path' => $path, 'alt' => $alt . ' gallery view ' . $position, 'position' => $position]);
        }
    }
}

function seedDemoCommerce(): void
{
    $customers = [];
    for ($i = 1; $i <= 24; $i++) {
        $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $email = 'demo-customer-' . $suffix . '@example.invalid';
        if (Database::fetch('SELECT id FROM customers WHERE email = ?', [$email]) === null) {
            Database::insert('customers', ['name' => 'Demo Customer ' . $suffix, 'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), 'phone' => '+92-000-000' . $suffix]);
        }
        $customer = Database::fetch('SELECT id FROM customers WHERE email = ?', [$email]);
        $customers[] = ['id' => (int) $customer['id'], 'name' => 'Demo Customer ' . $suffix, 'email' => $email];
    }
    $products = Database::fetchAll("SELECT id, name, sku, price, sale_price FROM products WHERE status = 'active' ORDER BY id");
    if ($products === []) { return; }
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    $cities = ['Karachi', 'Lahore', 'Rawalpindi', 'Islamabad', 'Multan', 'Peshawar'];
    $methods = ['cash_on_delivery', 'bank_transfer', 'mobile_wallet'];
    $now = new DateTimeImmutable('now', new DateTimeZone((string) \App\Core\Config::get('app.timezone', 'Asia/Karachi')));
    for ($i = 0; $i < 48; $i++) {
        $reference = 'DEMO-2026-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT);
        $customer = $customers[$i % count($customers)];
        $product = $products[$i % count($products)];
        $quantity = ($i % 4) + 1;
        $unit = (float) ($product['sale_price'] ?? $product['price']);
        $subtotal = $unit * $quantity;
        $shipping = $subtotal >= 50000 ? 0 : 250;
        $status = $statuses[$i % count($statuses)];
        $method = $methods[$i % count($methods)];
        $created = $now->modify('-' . ((47 - $i) * 7) . ' days')->setTime(12 + ($i % 8), ($i * 7) % 60)->format('Y-m-d H:i:s');
        Database::query(
            'INSERT INTO orders (reference, customer_id, customer_name, email, phone, address, city, subtotal, shipping, total, payment_method, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE reference = VALUES(reference)',
            [$reference, $customer['id'], $customer['name'], $customer['email'], '+92-000-000' . str_pad((string) (($i % 24) + 1), 2, '0', STR_PAD_LEFT),
                'DEMO DATA — Test Street ' . ($i + 1), $cities[$i % count($cities)], $subtotal, $shipping, $subtotal + $shipping, $method, $status, $created]
        );
        $order = Database::fetch('SELECT id FROM orders WHERE reference = ?', [$reference]);
        $orderId = (int) $order['id'];
        if ((int) Database::fetchColumn('SELECT COUNT(*) FROM order_items WHERE order_id = ?', [$orderId]) === 0) {
            Database::insert('order_items', ['order_id' => $orderId, 'product_id' => (int) $product['id'], 'item_name' => $product['name'], 'sku' => $product['sku'], 'unit_price' => $unit, 'quantity' => $quantity]);
        }
        $transaction = 'DEMO-TXN-' . ($i + 1);
        if (Database::fetch('SELECT id FROM payments WHERE transaction_id = ?', [$transaction]) === null) {
            $paymentStatus = match ($status) { 'delivered' => 'paid', 'refunded' => 'refunded', 'cancelled' => 'failed', default => 'pending' };
            Database::insert('payments', ['order_id' => $orderId, 'provider' => $method, 'transaction_id' => $transaction,
                'amount' => $subtotal + $shipping, 'currency' => 'PKR', 'status' => $paymentStatus, 'paid_at' => $paymentStatus === 'paid' ? $created : null]);
        }
    }
    foreach ([['DEMOWELCOME', 'percent', 10, 5000], ['DEMOAUDIO', 'fixed', 750, 10000], ['DEMOSUMMER', 'percent', 5, 15000]] as [$code, $type, $value, $minimum]) {
        Database::query('INSERT INTO coupons (code, type, value, min_subtotal, is_active) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE value = VALUES(value)', [$code, $type, $value, $minimum]);
    }
    for ($i = 1; $i <= 12; $i++) {
        $email = 'demo-subscriber-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . '@example.invalid';
        Database::query("INSERT INTO newsletter_subscribers (email, status, consented_at) VALUES (?, 'subscribed', CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE email = VALUES(email)", [$email]);
    }
}
