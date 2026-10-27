/**
 * Lala Mobiles — storefront UI layer.
 *
 * Responsibilities (all progressive-enhancement; the site still works with JS
 * off because forms post server-side):
 *   1. AJAX add-to-cart  → posts to /cart/add, updates the live cart count,
 *      shows a toast. Double-submit guarded and disabled while in flight.
 *   2. Wishlist toggle    → posts to /wishlist/toggle, syncs heart + count.
 *   3. Live search        → debounced suggestions fetched from /search/suggest.
 *   4. Flash countdown    → interpolates from an ABSOLUTE end timestamp, so a
 *      refresh never resets the timer and expired sales stop rendering.
 *   5. Mobile drawer + search sheet, mega-menu, carousels, newsletter.
 *
 * No third-party dependencies. Respects prefers-reduced-motion.
 */
(function () {
    'use strict';

    var CFG = window.LALA_UI || {};
    var CSRF = CFG.csrf || '';
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ----------------------------------------------------------- helpers -- */

    function $(sel, root) { return (root || document).querySelector(sel); }
    function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }

    /* ----------------------------------------------------------- toasts -- */

    function toast(message, kind) {
        var host = document.getElementById('toast-host');
        if (!host) return;
        var el = document.createElement('div');
        var tone = kind === 'error'
            ? 'bg-flame-600 text-white'
            : kind === 'info'
                ? 'bg-navy-900 text-white'
                : 'bg-jade-600 text-white';
        el.className = 'pointer-events-auto flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold shadow-pop ' + tone;
        el.setAttribute('role', 'status');
        el.innerHTML = (kind === 'error'
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4"><path d="M20 6 9 17l-5-5"/></svg>')
            + '<span></span>';
        el.lastChild.textContent = message;
        host.appendChild(el);
        if (!reduced) {
            el.style.transform = 'translateY(8px)';
            el.style.opacity = '0';
            el.style.transition = 'transform .2s ease, opacity .2s ease';
            requestAnimationFrame(function () { el.style.transform = 'translateY(0)'; el.style.opacity = '1'; });
        }
        setTimeout(function () {
            if (reduced) { el.remove(); return; }
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            setTimeout(function () { el.remove(); }, 220);
        }, 2600);
    }

    /* ------------------------------------------------------- count sync -- */

    function setCount(selector, value) {
        $$(selector).forEach(function (el) {
            el.textContent = value;
            if (value > 0) el.classList.remove('hidden');
            else el.classList.add('hidden');
        });
    }

    /* --------------------------------------------------------- cart AJAX -- */

    var cartBusy = false;

    function addToCart(btn) {
        if (cartBusy) return;
        var id = btn.getAttribute('data-product-id');
        if (!id) return;
        var qtyInput = document.querySelector('[data-qty-input]');
        var variantInput = document.querySelector('[data-variant-input]');
        var qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

        cartBusy = true;
        var label = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('opacity-70');

        var body = new URLSearchParams();
        body.set('product_id', id);
        body.set('quantity', String(qty));
        if (variantInput && variantInput.value) body.set('variant_id', variantInput.value);
        body.set('_token', CSRF);
        body.set('ajax', '1');

        fetch('/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body.toString(),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || res.data.error) {
                    toast(res.data.error || 'Could not add to cart.', 'error');
                    return;
                }
                if (typeof res.data.cart_count === 'number') setCount('[data-cart-count]', res.data.cart_count);
                var name = res.data.name || 'Item';
                var message = $('[data-mini-cart-message]');
                var subtotal = $('[data-mini-cart-subtotal]');
                if (message) message.textContent = name + ' is in your cart.';
                if (subtotal && typeof res.data.subtotal === 'number') subtotal.textContent = 'Rs ' + Number(res.data.subtotal).toLocaleString();
                openMiniCart();
                bump(btn);
            })
            .catch(function () { toast('Network error — please try again.', 'error'); })
            .finally(function () {
                cartBusy = false;
                btn.disabled = false;
                btn.classList.remove('opacity-70');
                btn.innerHTML = label;
            });
    }

    function bump(btn) {
        if (reduced) return;
        btn.animate(
            [{ transform: 'scale(1)' }, { transform: 'scale(0.94)' }, { transform: 'scale(1)' }],
            { duration: 220, easing: 'ease-out' }
        );
    }

    /* ------------------------------------------------------ wishlist AJAX -- */

    /* One state contract for every heart on the page: the button carries
     * data-active="true|false" and CSS derives colour/fill from it, so the
     * card heart, the product-page tile and any future surface stay identical. */
    function paintWishButtons(productId, active) {
        $$('[data-wishlist-toggle][data-product-id="' + productId + '"]').forEach(function (b) {
            b.setAttribute('data-active', active ? 'true' : 'false');
            b.setAttribute('aria-pressed', active ? 'true' : 'false');

            var label = b.getAttribute('aria-label') || '';
            b.setAttribute('aria-label', active
                ? label.replace(/^Add to/, 'Remove from')
                : label.replace(/^Remove from/, 'Add to'));

            b.classList.add('is-pop');
            setTimeout(function () { b.classList.remove('is-pop'); }, 380);
        });
    }

    /* The wishlist page is a list of saved cards, so removing one should take
     * the card with it — and reveal the empty state once the last one goes. */
    function dropWishlistCard(productId) {
        var card = document.querySelector(
            '[data-product-card][data-ctx="wishlist"][data-product-id="' + productId + '"]'
        );
        if (!card) return;

        card.style.pointerEvents = 'none';
        card.style.transition = 'opacity 180ms ease, transform 180ms ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.97)';

        setTimeout(function () {
            card.remove();
            syncWishlistPage();
        }, 190);
    }

    function syncWishlistPage() {
        var grid = $('[data-wishlist-grid]');
        if (!grid) return;

        var remaining = grid.querySelectorAll('[data-product-card]').length;
        var empty = $('[data-wishlist-empty]');
        var bar = $('[data-wishlist-toolbar]');
        var foot = $('[data-wishlist-foot]');
        var countEls = $$('[data-wishlist-count-text]');

        countEls.forEach(function (el) {
            el.textContent = remaining === 0
                ? 'Nothing saved yet'
                : remaining + (remaining === 1 ? ' saved item' : ' saved items');
        });

        if (remaining === 0) {
            grid.classList.add('hidden');
            if (bar) bar.classList.add('hidden');
            if (foot) foot.classList.add('hidden');
            if (empty) {
                empty.classList.remove('hidden');
                var h = empty.querySelector('h2');
                if (h) h.focus({ preventScroll: true });
            }
        }
    }

    function toggleWishlist(btn) {
        var id = btn.getAttribute('data-product-id');
        if (!id) return;
        if (btn.getAttribute('data-busy') === '1') return;
        btn.setAttribute('data-busy', '1');
        btn.setAttribute('aria-busy', 'true');

        var body = new URLSearchParams();
        body.set('product_id', id);
        body.set('_token', CSRF);
        body.set('ajax', '1');

        fetch('/wishlist/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body.toString(),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || res.data.error) { toast(res.data.error || 'Could not update wishlist.', 'error'); return; }

                var active = !!res.data.active;
                paintWishButtons(id, active);

                if (typeof res.data.wishlist_count === 'number') {
                    setCount('[data-wishlist-count]', res.data.wishlist_count);
                }

                toast(active ? 'Saved to wishlist' : 'Removed from wishlist');

                if (!active) dropWishlistCard(id);
                else {
                    var card = document.querySelector('[data-product-card][data-product-id="' + id + '"]');
                    if (card) bump(card);
                }
            })
            .catch(function () { toast('Network error — please try again.', 'error'); })
            .then(function () {
                btn.removeAttribute('data-busy');
                btn.removeAttribute('aria-busy');
            });
    }

    /* ------------------------------------------------------ live search -- */

    function initSearch() {
        var inputs = $$('[data-search-input]');
        inputs.forEach(function (input) {
            var panel = input.closest('form').querySelector('[data-search-suggest]');
            if (!panel) return;
            var timer = null, lastQ = '';

            function hide() { panel.classList.add('hidden'); input.setAttribute('aria-expanded', 'false'); }

            input.addEventListener('input', function () {
                var q = input.value.trim();
                clearTimeout(timer);
                if (q.length < 2) { hide(); return; }
                timer = setTimeout(function () { fetchSuggest(q, panel, input); }, 220);
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') hide();
            });
            document.addEventListener('click', function (e) {
                if (!input.closest('form').contains(e.target)) hide();
            });
        });
    }

    function fetchSuggest(q, panel, input) {
        fetch('/search/suggest?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var items = (data && data.results) || [];
                if (items.length === 0) { panel.classList.add('hidden'); input.setAttribute('aria-expanded', 'false'); return; }
                panel.innerHTML = '';
                items.slice(0, 6).forEach(function (p) {
                    var a = document.createElement('a');
                    a.href = p.url || ('/product/' + encodeURIComponent(p.slug));
                    a.className = 'flex items-center gap-3 px-3 py-2.5 hover:bg-surface-sunken transition-colors';
                    a.setAttribute('role', 'option');
                    var meta = document.createElement('span');
                    meta.className = 'min-w-0';
                    meta.innerHTML = '<span class="block truncate text-sm font-semibold text-ink"></span><span class="block text-[11px] font-bold uppercase tracking-wide text-ink-muted"></span>';
                    meta.children[0].textContent = p.name;
                    if (p.type === 'category' || p.type === 'brand') {
                        meta.children[1].textContent = p.type;
                        a.classList.add('px-4');
                    } else {
                        var img = document.createElement('img');
                        img.src = p.main_image || '/img/placeholder.svg';
                        img.alt = '';
                        img.width = 40; img.height = 40;
                        img.loading = 'lazy';
                        img.className = 'h-10 w-10 rounded-lg object-cover bg-surface-sunken shrink-0';
                        meta.children[1].textContent = 'Rs ' + Number(p.price).toLocaleString();
                        a.appendChild(img);
                    }
                    a.appendChild(meta);
                    panel.appendChild(a);
                });
                var all = document.createElement('a');
                all.href = '/search?q=' + encodeURIComponent(input.value.trim());
                all.className = 'block px-3 py-2.5 text-center text-sm font-bold text-electric-600 hover:bg-electric-50 border-t border-line';
                all.textContent = 'See all results';
                panel.appendChild(all);
                panel.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
            })
            .catch(function () { /* silent: suggestions are non-critical */ });
    }

    /* -------------------------------------------------------- countdown -- */

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    /**
     * Parse a countdown target into unix SECONDS.
     * Accepts an ISO-8601 string (per-product strips) or an integer epoch
     * already in seconds (the flash-sale section). Both come from the server's
     * stored end time, so a refresh never resets the timer.
     */
    function endSeconds(raw) {
        if (!raw) return NaN;
        if (/^\d+$/.test(raw)) return parseInt(raw, 10);
        var ms = Date.parse(raw);
        return isNaN(ms) ? NaN : Math.floor(ms / 1000);
    }

    function initCountdowns() {
        var strips  = $$('[data-flash-strip]');
        var blocks  = $$('[data-flash-countdown]');
        if (strips.length === 0 && blocks.length === 0) return;

        function tick() {
            var nowSec = Math.floor(Date.now() / 1000);

            // Per-product inline strip: "Ends in HH:MM:SS".
            strips.forEach(function (strip) {
                var out = strip.querySelector('[data-countdown]');
                if (!out) return;
                var endSec = endSeconds(strip.getAttribute('data-end'));
                if (isNaN(endSec)) { out.textContent = '—'; return; }
                var diff = endSec - nowSec;
                if (diff <= 0) {
                    out.textContent = 'Ended';
                    // Stop presenting it as a live sale.
                    strip.classList.add('opacity-60');
                    return;
                }
                out.textContent = pad(Math.floor(diff / 3600)) + ':' +
                                  pad(Math.floor((diff % 3600) / 60)) + ':' +
                                  pad(diff % 60);
            });

            // Flash-sale section: separate H / M / S cells.
            blocks.forEach(function (block) {
                var endSec = endSeconds(block.getAttribute('data-end'));
                if (isNaN(endSec)) return;
                var diff = endSec - nowSec;
                if (diff <= 0) {
                    // Campaign genuinely over → never show a live countdown.
                    block.setAttribute('data-expired', 'true');
                    var label = block.querySelector('[data-expired-label]');
                    if (label) { label.classList.remove('hidden'); }
                    var clock = block.querySelector('[data-clock]');
                    if (clock) { clock.classList.add('hidden'); }
                    return;
                }
                var units = {
                    h: Math.floor(diff / 3600),
                    m: Math.floor((diff % 3600) / 60),
                    s: diff % 60
                };
                Object.keys(units).forEach(function (k) {
                    var cell = block.querySelector('[data-unit="' + k + '"]');
                    if (cell) cell.textContent = pad(units[k]);
                });
            });
        }

        tick();
        setInterval(tick, 1000);
    }

    /* ---------------------------------------------------------- drawer -- */

    function initDrawer() {
        var toggle = $('#nav-toggle');
        var drawer = $('#mobile-drawer');
        var overlay = $('#drawer-overlay');
        var closeBtn = $('#drawer-close');
        if (!drawer) return;

        function open() {
            drawer.classList.remove('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'false');
            if (overlay) overlay.classList.remove('hidden');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            if (closeBtn) closeBtn.focus();
        }
        function close() {
            drawer.classList.add('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'true');
            if (overlay) overlay.classList.add('hidden');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            if (toggle) toggle.focus();
        }
        if (toggle) toggle.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);
        if (overlay) overlay.addEventListener('click', close);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.getAttribute('aria-hidden') === 'false') close();
        });

        var mToggle = $('#mobile-search-toggle');
        var mSearch = $('#mobile-search');
        if (mToggle && mSearch) {
            mToggle.addEventListener('click', function () {
                var open = !mSearch.classList.contains('hidden');
                mSearch.classList.toggle('hidden', open);
                if (!open) { var i = mSearch.querySelector('input'); if (i) i.focus(); }
            });
        }
    }

    /* --------------------------------------------------------- mega nav -- */

    function initMega() {
        $$('[data-mega]').forEach(function (wrap) {
            var trigger = wrap.querySelector('[data-mega-trigger]');
            var panel = wrap.querySelector('[data-mega-panel]');
            if (!trigger || !panel) return;
            var hideTimer = null, showTimer = null;

            /**
             * Keep the wide mega panel inside the viewport.
             *
             * Before this, the panel was anchored `left:0` to its trigger, so
             * menus near the right end (Gaming, Wearables) ran 300-600px past
             * the right edge, and tall menus (Audio) were cut off at the
             * bottom on shorter screens. We clamp horizontally and let the
             * card scroll vertically instead of spilling.
             */
            function placePanel() {
                var vw = document.documentElement.clientWidth;
                var vh = window.innerHeight;
                var wrapRect = wrap.getBoundingClientRect();

                panel.style.left = '0px';
                var width = panel.offsetWidth;
                // Centre the panel under the navigation (all mega panels share
                // one width), then clamp so it can never leave the viewport.
                var left = Math.round((vw - width) / 2);
                left = Math.max(16, Math.min(left, vw - 16 - width));
                panel.style.left = Math.round(left - wrapRect.left) + 'px';

                var card = panel.querySelector('.card, [class*="rounded-2xl"]');
                if (card) {
                    var top = card.getBoundingClientRect().top;
                    // Reserve room for the fixed cookie banner so the last row
                    // of a tall menu is never hidden behind it.
                    var banner = document.getElementById('consent-banner');
                    var reserve = banner && !banner.classList.contains('hidden') ? banner.offsetHeight : 0;
                    var maxH = Math.max(220, vh - top - 16 - reserve);
                    card.style.maxHeight = maxH + 'px';
                    card.style.overflowY = 'auto';
                    card.style.overscrollBehavior = 'contain';
                }
            }

            function show() {
                clearTimeout(hideTimer);
                clearTimeout(showTimer);
                panel.classList.remove('hidden');
                placePanel();
                trigger.setAttribute('aria-expanded', 'true');
            }
            function scheduleShow() { clearTimeout(hideTimer); showTimer = setTimeout(show, 150); }
            function scheduleHide() { clearTimeout(showTimer); hideTimer = setTimeout(hide, 150); }
            function hide() { panel.classList.add('hidden'); trigger.setAttribute('aria-expanded', 'false'); }
            window.addEventListener('resize', function () {
                if (!panel.classList.contains('hidden')) placePanel();
            });
            wrap.addEventListener('mouseenter', scheduleShow);
            wrap.addEventListener('mouseleave', scheduleHide);
            trigger.addEventListener('focus', show);
            wrap.addEventListener('focusout', function (e) { if (!wrap.contains(e.relatedTarget)) hide(); });
            trigger.addEventListener('click', function (e) {
                if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches && panel.classList.contains('hidden')) {
                    e.preventDefault(); show();
                }
            });
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); show(); var first = panel.querySelector('a'); if (first) first.focus(); }
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.classList.contains('hidden')) { hide(); trigger.focus(); } });
        });
    }

    /* ----------------------------------------------- mini cart drawer -- */
    function openMiniCart() {
        var drawer = $('[data-mini-cart]'), overlay = $('[data-mini-cart-overlay]');
        if (!drawer || !overlay) return;
        drawer.classList.remove('translate-x-full');
        drawer.setAttribute('aria-hidden', 'false');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        var close = $('[data-mini-cart-close]');
        if (close) close.focus();
    }

    function initMiniCart() {
        var drawer = $('[data-mini-cart]'), overlay = $('[data-mini-cart-overlay]');
        if (!drawer || !overlay) return;
        function close() {
            drawer.classList.add('translate-x-full');
            drawer.setAttribute('aria-hidden', 'true');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        var closeBtn = $('[data-mini-cart-close]');
        if (closeBtn) closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.getAttribute('aria-hidden') === 'false') close(); });
    }

    function initAnnouncement() {
        var bar = $('[data-announcement-bar]'), button = $('[data-announcement-close]');
        if (!bar || !button) return;
        try { if (localStorage.getItem('lala-announcement-dismissed') === '1') bar.remove(); } catch (e) { /* storage may be disabled */ }
        button.addEventListener('click', function () {
            bar.remove();
            try { localStorage.setItem('lala-announcement-dismissed', '1'); } catch (e) { /* optional persistence */ }
        });
    }

    function toggleCompare(btn) {
        var body = new URLSearchParams();
        body.set('product_id', btn.getAttribute('data-product-id') || '0');
        body.set('_token', CSRF);
        body.set('ajax', '1');
        btn.disabled = true;
        fetch('/compare/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body.toString(), credentials: 'same-origin'
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) { return { ok: response.ok, data: data }; });
        }).then(function (result) {
            if (!result.ok || result.data.error) { toast(result.data.error || 'Could not update comparison.', 'error'); return; }
            var active = !!result.data.active;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            btn.textContent = active ? 'Compared' : 'Compare';
            toast(active ? 'Added to comparison' : 'Removed from comparison');
        }).catch(function () { toast('Network error — please try again.', 'error'); })
            .finally(function () { btn.disabled = false; });
    }

    /* -------------------------------------------------------- carousel -- */

    function initCarousels() {
        $$('[data-carousel]').forEach(function (wrap) {
            var track = wrap.querySelector('[data-carousel-track]');
            var prev = wrap.querySelector('[data-carousel-prev]');
            var next = wrap.querySelector('[data-carousel-next]');
            if (!track) return;
            function step() {
                var first = track.firstElementChild;
                return first ? first.getBoundingClientRect().width + 20 : 300;
            }
            if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: reduced ? 'auto' : 'smooth' }); });
            if (next) next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: reduced ? 'auto' : 'smooth' }); });
        });
    }

    /* ------------------------------------------------------- newsletter -- */

    function initNewsletter() {
        $$('[data-newsletter]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = form.querySelector('button[type="submit"]');
                var email = form.querySelector('input[type="email"]');
                if (!email || !email.value) return;
                var body = new URLSearchParams();
                body.set('email', email.value);
                body.set('_token', CSRF);
                body.set('ajax', '1');
                if (btn) { btn.disabled = true; btn.textContent = 'Joining…'; }
                fetch('/newsletter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: body.toString(), credentials: 'same-origin'
                })
                    .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
                    .then(function (res) {
                        if (res.ok && !res.data.error) { toast('You are subscribed — welcome aboard!'); form.reset(); }
                        else toast(res.data.error || 'Could not subscribe.', 'error');
                    })
                    .catch(function () { toast('Network error — please try again.', 'error'); })
                    .finally(function () { if (btn) { btn.disabled = false; btn.textContent = 'Join'; } });
            });
        });
    }

    /* ------------------------------------------------- product page -- */

    /** Gallery: swap the main image when a thumbnail is chosen. */
    function initGallery() {
        var main = document.getElementById('gallery-main');
        if (!main) return;
        $$('[data-gallery-thumb]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = btn.getAttribute('data-src');
                if (!src) return;
                main.src = src;
                var alt = btn.getAttribute('data-alt');
                if (alt) main.alt = alt;
                $$('[data-gallery-thumb]').forEach(function (b) {
                    b.setAttribute('aria-pressed', 'false');
                    b.classList.remove('border-electric-500');
                    b.classList.add('border-line');
                });
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.remove('border-line');
                btn.classList.add('border-electric-500');
            });
        });

        var lightbox = $('[data-gallery-lightbox]');
        var preview = $('[data-gallery-lightbox-image]');
        var close = $('[data-gallery-lightbox-close]');
        function hidePreview() {
            if (!lightbox) return;
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.body.style.overflow = '';
            main.focus();
        }
        if (lightbox && preview) {
            main.setAttribute('tabindex', '0');
            main.addEventListener('click', function () {
                preview.src = main.src;
                preview.alt = main.alt;
                lightbox.classList.remove('hidden');
                lightbox.classList.add('flex');
                document.body.style.overflow = 'hidden';
                if (close) close.focus();
            });
            if (close) close.addEventListener('click', hidePreview);
            lightbox.addEventListener('click', function (event) { if (event.target === lightbox) hidePreview(); });
            document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) hidePreview(); });
        }
    }

    function initStickyBuy() {
        var bar = $('[data-sticky-buy]');
        var form = document.getElementById('product-cart-form');
        if (!bar || !form) return;
        var submit = $('[data-sticky-buy-submit]', bar);
        if (submit) submit.addEventListener('click', function () {
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        });
        if (!('IntersectionObserver' in window)) return;
        var observer = new IntersectionObserver(function (entries) {
            var visible = entries[0] && entries[0].isIntersecting;
            bar.hidden = !!visible;
        }, { threshold: 0.15 });
        observer.observe(form);
    }

    /** Quantity stepper — clamps to the input's min/max (never exceeds stock). */
    function initQtyStepper() {
        var input = document.querySelector('[data-qty-input]');
        if (!input) return;
        function clamp(v) {
            var min = parseInt(input.getAttribute('min'), 10) || 1;
            var max = parseInt(input.getAttribute('max'), 10) || 99;
            return Math.min(Math.max(v, min), max);
        }
        var dec = document.querySelector('[data-qty-dec]');
        var inc = document.querySelector('[data-qty-inc]');
        if (dec) dec.addEventListener('click', function () {
            input.value = clamp((parseInt(input.value, 10) || 1) - 1);
        });
        if (inc) inc.addEventListener('click', function () {
            input.value = clamp((parseInt(input.value, 10) || 1) + 1);
        });
        input.addEventListener('change', function () {
            input.value = clamp(parseInt(input.value, 10) || 1);
        });
    }

    /* --------------------------------------------------- filter auto-apply -- */

    /**
     * Selects marked with [data-filter-auto] (sort, condition, rating) submit
     * their form immediately, so picking an option visibly filters the list
     * instead of waiting for the Apply button. Checkboxes keep the explicit
     * Apply button so people can tick several brands in one go.
     */
    function initFilterAutoSubmit() {
        $$('[data-filter-auto]').forEach(function (el) {
            el.addEventListener('change', function () {
                var form = el.form;
                if (!form) { return; }
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        });
    }

    /**
     * Facet sidebar.
     *
     * On desktop the panel is a permanent sidebar, so it must start open —
     * previously it rendered collapsed inside <details> while the summary had
     * pointer-events disabled, which made every filter unreachable with a
     * mouse. On mobile it stays a tap-to-open accordion.
     */
    function initFilterPanel() {
        var panel = $('[data-facet-panel]');
        if (panel && window.matchMedia('(min-width: 1024px)').matches) {
            panel.open = true;
        }
        // Empty selects/text inputs would otherwise submit as
        // ?condition=&rating=&min_price=... — disabling them on submit keeps
        // the URL clean and shareable.
        $$('form[data-filter-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                $$('input, select', form).forEach(function (el) {
                    if (!el.name || el.disabled || el.type === 'hidden' || el.type === 'submit') { return; }
                    if (el.type === 'checkbox' && !el.checked) { el.disabled = true; return; }
                    if (el.type !== 'checkbox' && String(el.value) === '') { el.disabled = true; }
                });
            });
        });
    }

    /* ----------------------------------------------------- scroll reveal -- */

    function initScrollReveal() {
        var selectors = '.reveal, .reveal-left, .reveal-right, .reveal-scale, [data-reveal], .stagger, [data-stagger]';
        if (reduced) {
            $$(selectors).forEach(function (el) {
                el.classList.add('is-inview');
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            return;
        }
        if (!('IntersectionObserver' in window)) {
            $$(selectors).forEach(function (el) {
                el.classList.add('is-inview');
            });
            return;
        }
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-inview');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -60px 0px' });
        $$(selectors).forEach(function (el) {
            obs.observe(el);
        });
    }

    /* -------------------------------------------------------- hero slider -- */

    function initHeroSlider() {
        var sliders = $$('[data-hero-slider]');
        sliders.forEach(function (wrap) {
            var slides = $$('[data-hero-slide]', wrap);
            if (slides.length <= 1) return;
            var dots = $$('[data-hero-dot]', wrap);
            var prev = wrap.querySelector('[data-hero-prev]');
            var next = wrap.querySelector('[data-hero-next]');
            var idx = 0;
            var timer = null;

            function go(i) {
                idx = (i + slides.length) % slides.length;
                slides.forEach(function (s, j) {
                    s.classList.toggle('is-active', j === idx);
                });
                dots.forEach(function (d, j) {
                    d.classList.toggle('is-active', j === idx);
                    d.setAttribute('aria-selected', j === idx ? 'true' : 'false');
                });
            }
            function start() {
                if (reduced) return;
                stop();
                timer = setInterval(function () { go(idx + 1); }, 5500);
            }
            function stop() {
                if (timer) { clearInterval(timer); timer = null; }
            }
            if (prev) prev.addEventListener('click', function () { go(idx - 1); start(); });
            if (next) next.addEventListener('click', function () { go(idx + 1); start(); });
            dots.forEach(function (d, i) {
                d.addEventListener('click', function () { go(i); start(); });
            });
            wrap.addEventListener('mouseenter', stop);
            wrap.addEventListener('mouseleave', start);
            wrap.addEventListener('focusin', stop);
            wrap.addEventListener('focusout', start);
            start();
        });
    }

    /* ------------------------------------------------------------- init -- */

    document.addEventListener('DOMContentLoaded', function () {
        initDrawer();
        initFilterAutoSubmit();
        initFilterPanel();
        initMega();
        initMiniCart();
        initAnnouncement();
        initSearch();
        initCountdowns();
        initCarousels();
        initHeroSlider();
        initScrollReveal();
        initNewsletter();
        initGallery();
        initStickyBuy();
        initQtyStepper();

        document.addEventListener('click', function (e) {
            var addBtn = e.target.closest('[data-add-to-cart]');
            if (addBtn) { e.preventDefault(); addToCart(addBtn); return; }
            var wishBtn = e.target.closest('[data-wishlist-toggle]');
            if (wishBtn) { e.preventDefault(); toggleWishlist(wishBtn); }
            var compareBtn = e.target.closest('[data-compare-toggle]');
            if (compareBtn) { e.preventDefault(); toggleCompare(compareBtn); }
        });
    });
})();
