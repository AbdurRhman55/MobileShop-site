<?php
/**
* Storefront layout.
*
* The <head> is assembled from the $seo array produced by Seo\MetaResolver:
*   title, meta description, canonical, robots, Open Graph, Twitter card,
*   Google/Bing verification, and any sanitized custom head markup.
* JSON-LD arrives pre-rendered in $schemaScripts.
*
* @var array  $seo
* @var string $schemaScripts
* @var string $content
*/
$seo = $seo ?? [];
$og  = $seo[ 'og' ] ?? [];
$tw  = $seo[ 'twitter' ] ?? [];
?><!doctype html>
<html lang = 'en'>
<head>
<meta charset = 'utf-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1'>
<link rel = 'preconnect' href = 'https://fonts.googleapis.com'>
<link rel = 'preconnect' href = 'https://fonts.gstatic.com' crossorigin>
<link href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap' rel = 'stylesheet'>

<title><?= e( $seo[ 'title' ] ?? 'Lala Mobiles' ) ?></title>

<?php if ( !empty( $seo[ 'description' ] ) ): ?>
<meta name = 'description' content = "<?= e($seo['description']) ?>">
<?php endif;
?>

<?php if ( !empty( $seo[ 'canonical' ] ) ): ?>
<link rel = 'canonical' href = "<?= e($seo['canonical']) ?>">
<?php endif;
?>

<meta name = 'robots' content = "<?= e($seo['robots'] ?? 'index,follow') ?>">

<?php if ( !empty( $seo[ 'prev' ] ) ): ?>
<link rel = 'prev' href = "<?= e($seo['prev']) ?>">
<?php endif;
?>
<?php if ( !empty( $seo[ 'next' ] ) ): ?>
<link rel = 'next' href = "<?= e($seo['next']) ?>">
<?php endif;
?>

<!-- Open Graph ( Facebook / WhatsApp / general ) -->
<meta property = 'og:site_name' content = "<?= e($og['site_name'] ?? 'Lala Mobiles') ?>">
<meta property = 'og:locale' content = "<?= e($og['locale'] ?? 'en_PK') ?>">
<meta property = 'og:type' content = "<?= e($og['type'] ?? 'website') ?>">
<meta property = 'og:title' content = "<?= e($og['title'] ?? ($seo['title'] ?? '')) ?>">
<?php if ( !empty( $og[ 'description' ] ) ): ?>
<meta property = 'og:description' content = "<?= e($og['description']) ?>">
<?php endif;
?>
<?php if ( !empty( $og[ 'url' ] ) ): ?>
<meta property = 'og:url' content = "<?= e($og['url']) ?>">
<?php endif;
?>
<?php if ( !empty( $og[ 'image' ] ) ): ?>
<meta property = 'og:image' content = "<?= e($og['image']) ?>">
<meta property = 'og:image:width' content = '800'>
<meta property = 'og:image:height' content = '800'>
<?php endif;
?>

<!-- X / Twitter -->
<meta name = 'twitter:card' content = "<?= e($tw['card'] ?? 'summary_large_image') ?>">
<meta name = 'twitter:title' content = "<?= e($tw['title'] ?? ($seo['title'] ?? '')) ?>">
<?php if ( !empty( $tw[ 'description' ] ) ): ?>
<meta name = 'twitter:description' content = "<?= e($tw['description']) ?>">
<?php endif;
?>
<?php if ( !empty( $tw[ 'image' ] ) ): ?>
<meta name = 'twitter:image' content = "<?= e($tw['image']) ?>">
<?php endif;
?>

<?php if ( !empty( $googleVerification ) ): ?>
<meta name = 'google-site-verification' content = "<?= e($googleVerification) ?>">
<?php endif;
?>

<?php
$bingVerification = \App\Seo\SeoSettings::raw( 'bing_webmaster_verification' );
if ( $bingVerification !== '' ): ?>
<meta name = 'msvalidate.01' content = "<?= e($bingVerification) ?>">
<?php endif;
?>

<?php if ( !empty( $seo[ 'custom_head' ] ) ): ?>
<?= $seo[ 'custom_head' ] /* pre-sanitized server-side;
see MetaResolver::sanitizedCustomHead */ ?>
<?php endif;
?>

<link rel = 'icon' href = '/img/logo-mark.png' type = 'image/png'>
<link rel = 'apple-touch-icon' href = '/img/logo-mark.png'>

<link rel = 'preload' as = 'image' href = '/img/logo.png'>
<?php
// Cache-bust the compiled CSS/JS with the file mtime so a rebuild is picked
// up immediately instead of being served from a stale browser cache.
$assetVer = static function (string $rel): string {
    $file = BASE_PATH . '/public' . $rel;
    return is_file($file) ? (string) filemtime($file) : '1';
};
?>
<link rel = 'stylesheet' href = '/assets/css/app.css?v=<?= e($assetVer('/assets/css/app.css')) ?>'>
<style>
    .reveal{opacity:0;transform:translateY(28px);transition:opacity .9s cubic-bezier(.25,.46,.45,.94),transform .9s cubic-bezier(.25,.46,.45,.94);will-change:opacity,transform}
    .reveal-left{opacity:0;transform:translateX(-28px);transition:opacity .9s cubic-bezier(.25,.46,.45,.94),transform .9s cubic-bezier(.25,.46,.45,.94)}
    .reveal-right{opacity:0;transform:translateX(28px);transition:opacity .9s cubic-bezier(.25,.46,.45,.94),transform .9s cubic-bezier(.25,.46,.45,.94)}
    .reveal-scale{opacity:0;transform:scale(.96);transition:opacity .9s cubic-bezier(.25,.46,.45,.94),transform .9s cubic-bezier(.25,.46,.45,.94)}
    .reveal.is-inview,.reveal-left.is-inview,.reveal-right.is-inview{opacity:1;transform:translateX(0) translateY(0)}
    .reveal-scale.is-inview{opacity:1;transform:scale(1)}
    .stagger>*{opacity:0;transform:translateY(18px);transition:opacity .8s cubic-bezier(.25,.46,.45,.94),transform .8s cubic-bezier(.25,.46,.45,.94)}
    .stagger.is-inview>*:nth-child(1){transition-delay:.05s}
    .stagger.is-inview>*:nth-child(2){transition-delay:.1s}
    .stagger.is-inview>*:nth-child(3){transition-delay:.15s}
    .stagger.is-inview>*:nth-child(4){transition-delay:.2s}
    .stagger.is-inview>*:nth-child(5){transition-delay:.25s}
    .stagger.is-inview>*:nth-child(6){transition-delay:.3s}
    .stagger.is-inview>*:nth-child(7){transition-delay:.35s}
    .stagger.is-inview>*:nth-child(8){transition-delay:.4s}
    .stagger.is-inview>*:nth-child(n+9){transition-delay:.45s}
    .stagger.is-inview>*{opacity:1;transform:translateY(0)}
    [data-hero-slide]{opacity:0;transition:opacity 1s ease}
    [data-hero-slide].is-active{opacity:1}
    .hero-parallax img{transform:scale(1.06);transition:transform 12s ease}
    [data-hero-slide].is-active .hero-parallax img{transform:scale(1)}
    .card-hover{transition:transform .3s cubic-bezier(.25,.46,.45,.94),box-shadow .3s cubic-bezier(.25,.46,.45,.94)}
    .card-hover:hover{transform:translateY(-6px);box-shadow:0 30px 60px -30px rgba(15,23,42,.4)}
    .img-zoom{overflow:hidden}
    .img-zoom img{transition:transform .6s cubic-bezier(.25,.46,.45,.94)}
    .img-zoom:hover img{transform:scale(1.08)}
    @media (prefers-reduced-motion: reduce){
        .reveal,.reveal-left,.reveal-right,.reveal-scale,.stagger>*{opacity:1;transform:none;transition:none}
        .hero-parallax img{transform:none;transition:none}
        [data-hero-slide]{opacity:1;transition:none}
        .card-hover,.img-zoom img{transition:none;transform:none}
    }
</style>

<?php if ( !empty( $schemaScripts ) ): ?>
<?= $schemaScripts /* JSON-LD: validated + escaped by StructuredData::toScriptTag */ ?>
<?php endif;
?>

<?php /* Consent-aware analytics: no tags fire before consent when consentRequired. */ ?>
<script>
window.dataLayer = window.dataLayer || [];
window.LALA = {
    consentRequired: <?= !empty( $consentRequired ) ? 'true' : 'false' ?>,
    ga4: <?= json_encode( $ga4Id ?? '' ) ?>,
    gtm: <?= json_encode( $gtmId ?? '' ) ?>,
    csrf: <?= json_encode( \App\Core\Csrf::token() ) ?>
}
;
</script>
<script src = '/assets/js/app.js?v=<?= e($assetVer('/assets/js/app.js')) ?>' defer></script>
<script src = '/assets/js/ui.js?v=<?= e($assetVer('/assets/js/ui.js')) ?>' defer></script>
<script>
window.LALA_UI = {
    csrf: <?= json_encode( \App\Core\Csrf::token() ) ?> }
    ;
    </script>
    </head>
    <body class = 'bg-surface-muted text-ink antialiased'>
    <?php include BASE_PATH . '/app/views/partials/header.php';
    ?>

    <main id = 'main' role = 'main'>
    <?= $content ?>
    </main>

    <?php include BASE_PATH . '/app/views/partials/footer.php';
    ?>

    <!-- Floating WhatsApp Quick Connect Button (Icon only) -->
    <aside class="fixed bottom-6 right-6 z-[85]" aria-label="WhatsApp Support">
        <a href="https://wa.me/923005970504?text=Salam%20Lala%20Mobiles!%20I%20have%20an%20inquiry."
           target="_blank" rel="noopener noreferrer"
           class="group relative grid h-14 w-14 place-items-center rounded-full bg-[#25D366] text-white shadow-2xl transition-all duration-300 hover:scale-110 hover:bg-[#20ba59] hover:shadow-emerald-500/40"
           title="WhatsApp Chat (+92 300 5970504)"
           aria-label="Chat on WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
            <span class="absolute -top-0.5 -right-0.5 flex h-3.5 w-3.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-300"></span>
            </span>
        </a>
    </aside>

    <div id = 'mini-cart-overlay' class = 'fixed inset-0 z-[80] hidden bg-slate-950/40 backdrop-blur-[2px]' data-mini-cart-overlay></div>
    <aside id = 'mini-cart' class = 'fixed inset-y-0 right-0 z-[81] flex w-full max-w-md translate-x-full flex-col border-l border-line bg-white shadow-2xl transition-transform duration-200' role = 'dialog' aria-modal = 'true' aria-labelledby = 'mini-cart-title' aria-hidden = 'true' data-mini-cart>
    <header class = 'flex items-center justify-between border-b border-line px-5 py-4'>
    <div><p class = 'text-xs font-semibold uppercase tracking-wide text-ink-muted'>Your bag</p><h2 id = 'mini-cart-title' class = 'mt-0.5 text-lg font-extrabold'>Added to cart</h2></div>
    <button type = 'button' class = 'grid h-10 w-10 place-items-center rounded-lg text-ink-muted hover:bg-surface-sunken hover:text-ink' data-mini-cart-close aria-label = 'Close cart drawer'><svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-5 w-5'><path d = 'm18 6-12 12M6 6l12 12'/></svg></button>
    </header>
    <div class = 'flex-1 p-5'><p class = 'text-sm text-ink-muted' data-mini-cart-message>Your item is in your cart.</p><div class = 'mt-5 rounded-xl border border-line bg-surface-muted p-4'><p class = 'text-xs font-semibold uppercase tracking-wide text-ink-muted'>Cart subtotal</p><p class = 'mt-1 text-xl font-extrabold text-ink' data-mini-cart-subtotal>—</p><p class = 'mt-1 text-xs text-ink-muted'>Shipping and discounts are calculated at checkout.</p></div></div>
    <footer class = 'space-y-2 border-t border-line p-5'><a href = '/cart' class = 'btn btn-primary btn-md btn-block'>View cart</a><a href = '/checkout' class = 'btn btn-ghost btn-md btn-block'>Continue to checkout</a></footer>
    </aside>

    <?php if ( !empty( $consentRequired ) ): ?>
    <!-- Consent banner: analytics only load after an explicit choice. -->
    <div id = 'consent-banner' class = 'fixed bottom-0 inset-x-0 z-50 hidden border-t border-line bg-white shadow-soft' role = 'dialog' aria-live = 'polite' aria-label = 'Cookie consent'>
    <div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between'>
    <p class = 'text-sm text-ink-muted'>We use optional analytics cookies to understand how the site is used. You can accept or continue without them.</p>
    <div class = 'flex gap-2 shrink-0'>
    <button id = 'consent-decline' class = 'px-4 py-2 text-sm rounded-lg border border-line hover:bg-surface-sunken'>Decline</button>
    <button id = 'consent-accept' class = 'btn btn-dark btn-sm'>Accept analytics</button>
    </div>
    </div>
    </div>
    <?php endif;
    ?>
    </body>
    </html>
