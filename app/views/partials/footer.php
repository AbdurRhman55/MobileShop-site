<?php
/**
* Shared storefront footer.
*
* @var array  $navCategories
* @var string $siteName
* @var string $siteTagline
* @var string $csrf
*/
use App\Models\SiteContent;

$navCategories = $navCategories ?? [];
$siteName   = $siteName ?? 'Lala Mobiles';
$siteTagline = $siteTagline ?? 'Your Mobile, Our Priority';
$phone = ( string ) \App\Seo\SeoSettings::get( 'organization_phone', '' );
$email = ( string ) \App\Seo\SeoSettings::get( 'organization_email', '' );
$address = ( string ) \App\Seo\SeoSettings::get( 'organization_address', '' );
$social  = SiteContent::socialLinks();
$payments = SiteContent::paymentMethods();
$footerNote = ( string ) \App\Seo\SeoSettings::get( 'footer_note', '' );
$year = ( int ) date( 'Y' );

// Only link to pages that genuinely exist ( verified against the `pages` table ).
// Terms & Conditions is omitted because no such page is published yet — a dead
// footer link is worse than an absent one.
$serviceLinks = [
    '/page/contact'                => 'Contact Us',
    '/page/about'                  => 'About Us',
    '/page/delivery-information'   => 'Shipping & Delivery',
    '/page/warranty-return-policy' => 'Warranty & Returns',
    '/page/privacy-policy'         => 'Privacy Policy',
    '/repair/screen-replacement'   => 'Book a Repair',
];
?>
<footer class = 'bg-navy-900 text-navy-200 mt-16'>
<div class = 'shell py-14'>
<div class = 'grid gap-10 lg:grid-cols-12'>

<!-- Brand -->
<div class = 'lg:col-span-4'>
<img src = '/img/logo.png' alt = "<?= e($siteName) ?>" width = '220' height = '143' class = 'h-14 w-auto object-contain brightness-110 drop-shadow-md'>
<p class = 'mt-4 text-sm leading-relaxed text-navy-300 max-w-sm'>
<?= e( $footerNote !== '' ? $footerNote : 'Your trusted destination for new, used and refurbished smartphones in Pakistan — tested, warranted and delivered to your door.' ) ?>
</p>
<?php if ( $social !== [] ): ?>
<ul class = 'mt-6 flex flex-wrap gap-2.5'>
<?php foreach ( $social as $s ): ?>
<li>
<a href = "<?= e($s['url']) ?>" target = '_blank' rel = 'noopener noreferrer' class = 'h-9 w-9 rounded-full bg-white/8 hover:bg-electric-500 grid place-items-center text-navy-200 hover:text-white transition-colors' aria-label = "<?= e($s['label']) ?>">
<svg viewBox = '0 0 24 24' fill = 'currentColor' class = 'h-4 w-4'><path d = "<?= e($s['path']) ?>"/></svg>
</a>
</li>
<?php endforeach;
?>
</ul>
<?php endif;
?>
</div>

<!-- Shop -->
<nav class = 'lg:col-span-2' aria-label = 'Shop categories'>
<h2 class = 'text-white font-bold text-sm uppercase tracking-wider mb-4'>Shop</h2>
<ul class = 'space-y-2.5 text-sm'>
<?php foreach ( array_slice( $navCategories, 0, 7 ) as $cat ): ?>
<li><a href = "/category/<?= e($cat['slug']) ?>" class = 'hover:text-white transition-colors'><?= e( $cat[ 'name' ] ) ?></a></li>
<?php endforeach;
?>
<li><a href = '/#flash-sale' class = 'text-gold-300 font-semibold hover:text-gold-200 transition-colors'>Deals &amp;
Offers</a></li>
</ul>
</nav>

<!-- Customer service -->
<nav class = 'lg:col-span-2' aria-label = 'Customer service'>
<h2 class = 'text-white font-bold text-sm uppercase tracking-wider mb-4'>Support</h2>
<ul class = 'space-y-2.5 text-sm'>
<?php foreach ( $serviceLinks as $href => $label ): ?>
<li><a href = "<?= e($href) ?>" class = 'hover:text-white transition-colors'><?= e( $label ) ?></a></li>
<?php endforeach;
?>
<li><a href = '/account' class = 'hover:text-white transition-colors'>My Account &amp;
Orders</a></li>
<li><a href = '/wishlist' class = 'hover:text-white transition-colors'>Wishlist</a></li>
</ul>
</nav>

<!-- Contact + newsletter -->
<div class = 'lg:col-span-4'>
<h2 class = 'text-white font-bold text-sm uppercase tracking-wider mb-4'>Get in touch</h2>
<ul class = 'space-y-3 text-sm'>
<?php if ( $address !== '' ): ?>
<li class = 'flex gap-3'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-4.5 w-4.5 shrink-0 mt-0.5 text-electric-300' style = 'width:1.15rem;height:1.15rem'><path d = 'M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'/><circle cx = '12' cy = '10' r = '3'/></svg>
<span class = 'text-navy-300'><?= e( $address ) ?></span>
</li>
<?php endif;
?>
<li class = 'flex gap-3'>
<svg viewBox = '0 0 24 24' fill = 'currentColor' class = 'h-4.5 w-4.5 shrink-0 mt-0.5 text-emerald-400' style = 'width:1.15rem;height:1.15rem'><path d = 'M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z'/></svg>
<a href = 'https://wa.me/923005970504' target = '_blank' rel = 'noopener noreferrer' class = 'hover:text-emerald-300 transition-colors font-bold text-emerald-400'>WhatsApp: +92 300 5970504</a>
</li>
<?php if ( $phone !== '' ): ?>
<li class = 'flex gap-3'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-4.5 w-4.5 shrink-0 mt-0.5 text-electric-300' style = 'width:1.15rem;height:1.15rem'><path d = 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z'/></svg>
<a href = "tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class = 'hover:text-white transition-colors font-semibold'><?= e( $phone ) ?></a>
</li>
<?php endif;
?>
<?php if ( $email !== '' ): ?>
<li class = 'flex gap-3'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-4.5 w-4.5 shrink-0 mt-0.5 text-electric-300' style = 'width:1.15rem;height:1.15rem'><rect x = '2' y = '4' width = '20' height = '16' rx = '2'/><path d = 'm2 7 10 6 10-6'/></svg>
<a href = "mailto:<?= e($email) ?>" class = 'hover:text-white transition-colors'><?= e( $email ) ?></a>
</li>
<?php endif;
?>
</ul>

<h2 class = 'text-white font-bold text-sm uppercase tracking-wider mt-8 mb-3'>Newsletter</h2>
<form method = 'post' action = '/newsletter' class = 'flex gap-2' data-newsletter>
<?= \App\Core\Csrf::field() ?>
<label for = 'footer-newsletter' class = 'sr-only'>Email address</label>
<input type = 'email' id = 'footer-newsletter' name = 'email' required placeholder = 'you@example.com'

class = 'flex-1 min-w-0 rounded-lg bg-white/8 border border-white/15 px-3.5 py-2.5 text-sm text-white placeholder:text-navy-400 focus:outline-none focus:border-electric-400 focus:bg-white/12'>
<button type = 'submit' class = 'btn btn-primary btn-sm rounded-lg px-4'>Join</button>
</form>
<p class = 'mt-2 text-[12px] text-navy-400'>Product news and offers. Unsubscribe anytime.</p>

<h2 class = 'text-white font-bold text-sm uppercase tracking-wider mt-8 mb-3'>Payment Method</h2>
<ul class = 'flex flex-wrap gap-2'>
<li class = 'px-3 py-1.5 rounded-lg bg-white/8 text-[12px] font-semibold text-emerald-300 border border-emerald-500/30 flex items-center gap-2'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4'><path d = 'M3 10h18M5 10V6l7-3 7 3v4M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18'/></svg>
Soneri Bank (Direct Bank Transfer)
</li>
</ul>
</div>
</div>
</div>

<div class = 'border-t border-white/10'>
<div class = 'shell py-5 flex flex-col md:flex-row items-center justify-between gap-3 text-[13px] text-navy-400'>
<p>&copy;
<?= $year ?> <?= e( $siteName ) ?>. All rights reserved.</p>
<p><?= e( $siteTagline ) ?></p>
</div>
</div>
</footer>

<!-- Toast host -->
<div id = 'toast-host' class = 'fixed bottom-4 left-1/2 -translate-x-1/2 z-[90] flex flex-col items-center gap-2 pointer-events-none' aria-live = 'polite'></div>
