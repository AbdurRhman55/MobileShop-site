<?php /** @var ?string $error @var string $csrf */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Admin Sign In — Lala Mobiles</title>
    <link rel="icon" href="/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="grid min-h-screen place-items-center bg-navy-900 p-4 text-ink antialiased">
<!-- Subtle brand glow so the page isn't a flat slab of navy. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-electric-600/25 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
    <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold-400/10 blur-3xl"></div>
</div>

<main class="relative w-full max-w-sm">
    <div class="card p-7 sm:p-8">
        <div class="flex items-center gap-3">
            <img src="/img/logo-mark.png" alt="Lala Mobiles" width="48" height="48" class="h-12 w-12 rounded-xl object-contain">
            <div class="min-w-0">
                <h1 class="text-[17px] font-extrabold tracking-tight text-ink">Lala Mobiles</h1>
                <p class="text-[12.5px] font-semibold text-ink-muted">Store control panel</p>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="mt-5 flex gap-2.5 rounded-xl border border-flame-200 bg-flame-50 px-3.5 py-3 text-[13.5px] font-semibold text-flame-700" role="alert" tabindex="-1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" action="/admin/login" class="mt-6 space-y-4">
            <input type="hidden" name="_token" value="<?= e($csrf) ?>">

            <div>
                <label for="username" class="mb-1.5 block text-[13px] font-bold text-ink">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username"
                       autocapitalize="none" spellcheck="false" class="input">
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-[13px] font-bold text-ink">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" class="input">
            </div>

            <button type="submit" class="btn btn-primary btn-md btn-block mt-2">
                Sign in
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>

        <p class="mt-5 border-t border-line pt-4 text-center text-[12.5px] text-ink-muted">
            <a href="/" class="font-semibold text-electric-600 hover:text-electric-700">← Back to the storefront</a>
        </p>
    </div>

    <p class="mt-5 text-center text-[12px] text-navy-300">This area is for staff only and is never indexed.</p>
</main>
</body>
</html>
