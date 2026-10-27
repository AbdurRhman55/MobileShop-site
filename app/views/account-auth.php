<?php
/**
 * Customer sign-in / create account.
 *
 * @var string $mode  'login'|'register'
 * @var ?string $error
 * @var string $next
 * @var bool $registered
 * @var array $old
 */
$isRegister = $mode === 'register';
$nextField  = $next !== '' ? $next : '';
$old        = $old ?? [];
?>
<div class="shell py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl border border-line bg-white shadow-card md:grid-cols-2">

        <!-- Brand panel -->
        <aside class="relative hidden overflow-hidden bg-navy-950 md:block">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(37,99,235,0.45),transparent_55%),radial-gradient(circle_at_85%_85%,rgba(124,58,237,0.35),transparent_50%)]"></div>
            <div class="relative flex h-full flex-col justify-between p-9 text-white">
                <a href="/" class="inline-flex items-center gap-3" aria-label="Lala Mobiles home">
                    <img src="/img/logo-white.svg" alt="Lala Mobiles" width="150" height="40" class="h-9 w-auto">
                </a>

                <div class="py-12">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-gold-300">Customer account</p>
                    <h1 class="mt-3 max-w-sm text-[30px] font-extrabold leading-[1.15] tracking-tight text-white">
                        Your orders, payments and invoices — in one place.
                    </h1>
                    <ul class="mt-7 space-y-4 text-[13.5px] text-white/80">
                        <?php foreach ([
                            ['Track every order', 'Live fulfilment status from confirmation to delivery.'],
                            ['Clear payment status', 'Pending until we actually receive and verify your payment.'],
                            ['Download invoices', 'A4 invoices you can print, save or attach to a warranty claim.'],
                        ] as [$title, $body]): ?>
                        <li class="flex gap-3">
                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/10 text-gold-300">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-3.5 w-3.5"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <span><span class="block font-bold text-white"><?= e($title) ?></span><?= e($body) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p class="text-[11.5px] text-white/50">Secure customer area · Lala Mobiles, Rawalpindi</p>
            </div>
        </aside>

        <!-- Form panel -->
        <section class="p-6 sm:p-10">
            <a href="/" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-ink-muted hover:text-electric-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back to store
            </a>

            <p class="mt-7 text-[11px] font-extrabold uppercase tracking-[0.2em] text-electric-600">Customer account</p>
            <h2 class="mt-2 text-[26px] font-extrabold tracking-tight text-ink">
                <?= $isRegister ? 'Create your account' : 'Welcome back' ?>
            </h2>
            <p class="mt-2 text-[13.5px] leading-relaxed text-ink-muted">
                <?php if ($isRegister): ?>
                    Use a real, permanent email address — order updates, payment confirmations and invoices are sent there. You'll sign in after creating the account.
                <?php else: ?>
                    Sign in to place an order, review payment status and download invoices.
                <?php endif; ?>
            </p>

            <?php if ($registered): ?>
            <div class="mt-5 flex gap-2.5 rounded-xl border border-jade-200 bg-jade-50 px-3.5 py-3 text-[13px] font-semibold text-jade-800" role="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="mt-0.5 h-4 w-4 shrink-0"><path d="M20 6L9 17l-5-5"/></svg>
                <span>Account created. Sign in to continue<?= $next === '/checkout' ? ' to checkout' : '' ?>.</span>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="mt-5 flex gap-2.5 rounded-xl border border-danger-200 bg-danger-50 px-3.5 py-3 text-[13px] font-semibold text-danger-800" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="mt-0.5 h-4 w-4 shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                <span><?= e($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= $isRegister ? '/account/register' : '/account/login' ?>" class="mt-6 space-y-4" novalidate>
                <?= \App\Core\Csrf::field() ?>
                <?php if ($nextField !== ''): ?>
                <input type="hidden" name="next" value="<?= e($nextField) ?>">
                <?php endif; ?>

                <?php if ($isRegister): ?>
                <div>
                    <label for="name" class="mb-1.5 block text-[13px] font-bold text-ink">Full name <span class="text-flame-500">*</span></label>
                    <input id="name" name="name" type="text" required minlength="3" maxlength="160" autocomplete="name"
                           value="<?= e((string) ($old['name'] ?? '')) ?>" class="input">
                </div>
                <?php endif; ?>

                <div>
                    <label for="email" class="mb-1.5 block text-[13px] font-bold text-ink">Email address <span class="text-flame-500">*</span></label>
                    <input id="email" name="email" type="email" required maxlength="190" autocomplete="email"
                           inputmode="email" spellcheck="false" value="<?= e((string) ($old['email'] ?? '')) ?>" class="input"
                           placeholder="you@gmail.com">
                    <?php if ($isRegister): ?>
                    <p class="field-hint">Disposable and example domains are rejected, so your order updates always reach you.</p>
                    <?php endif; ?>
                </div>

                <?php if ($isRegister): ?>
                <div>
                    <label for="phone" class="mb-1.5 block text-[13px] font-bold text-ink">Phone <span class="font-medium text-ink-muted">(for delivery calls)</span></label>
                    <input id="phone" name="phone" type="tel" maxlength="20" inputmode="tel" autocomplete="tel"
                           value="<?= e((string) ($old['phone'] ?? '')) ?>" class="input" placeholder="03XX-XXXXXXX">
                </div>
                <?php endif; ?>

                <div>
                    <label for="password" class="mb-1.5 block text-[13px] font-bold text-ink">Password <span class="text-flame-500">*</span></label>
                    <input id="password" name="password" type="password" required <?= $isRegister ? 'minlength="10"' : '' ?>
                           autocomplete="<?= $isRegister ? 'new-password' : 'current-password' ?>" class="input">
                    <?php if ($isRegister): ?><p class="field-hint">At least 10 characters.</p><?php endif; ?>
                </div>

                <?php if ($isRegister): ?>
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-[13px] font-bold text-ink">Confirm password <span class="text-flame-500">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="10"
                           autocomplete="new-password" class="input">
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <?= $isRegister ? 'Create account' : 'Sign in' ?>
                </button>

                <?php if ($isRegister): ?>
                <p class="text-[12px] leading-relaxed text-ink-muted">
                    By creating an account you agree to our
                    <a href="/page/warranty-return-policy" class="font-semibold text-electric-700 hover:underline">warranty &amp; return policy</a>
                    and <a href="/page/privacy-policy" class="font-semibold text-electric-700 hover:underline">privacy policy</a>.
                </p>
                <?php endif; ?>
            </form>

            <div class="mt-6 border-t border-line pt-5 text-[13px] text-ink-muted">
                <?php if ($isRegister): ?>
                    Already have an account?
                    <a href="/account/login<?= $nextField !== '' ? '?next=' . rawurlencode($nextField) : '' ?>" class="font-bold text-electric-700 hover:underline">Sign in</a>
                <?php else: ?>
                    New to Lala Mobiles?
                    <a href="/account/register<?= $nextField !== '' ? '?next=' . rawurlencode($nextField) : '' ?>" class="font-bold text-electric-700 hover:underline">Create an account</a>
                    — it takes under a minute.
                <?php endif; ?>
            </div>

            <?php if ($next === '/checkout'): ?>
            <p class="mt-4 rounded-xl bg-surface px-3.5 py-3 text-[12.5px] text-ink-muted">
                <span class="font-bold text-ink">Ordering is account-only.</span>
                We need a verified email and a phone number on file so we can confirm delivery and send your invoice.
            </p>
            <?php endif; ?>
        </section>
    </div>
</div>
