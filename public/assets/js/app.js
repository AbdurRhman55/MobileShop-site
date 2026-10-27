/**
 * Lala Mobiles — front-end JS.
 *
 * Responsibilities:
 *  1. Consent-aware analytics: GA4/GTM load ONLY after explicit consent when
 *     consentRequired is true. Declining stores a negative flag and loads nothing.
 *  2. GA4 e-commerce events: view_item, add_to_cart, begin_checkout, purchase.
 *  3. Purchase de-duplication: the purchase event fires once per transaction id
 *     (recorded in localStorage) so refreshing the confirmation page or revisiting
 *     the URL never double-counts a conversion.
 *
 * No third-party scripts are hardcoded here — they are injected only after consent.
 */
(function () {
    'use strict';

    var CFG = window.LALA || {};
    var CONSENT_KEY = 'lala_analytics_consent';
    var FIRED_ORDERS_KEY = 'lala_fired_orders';

    /* ----------------------------------------------------------- consent -- */

    function getConsent() {
        try { return localStorage.getItem(CONSENT_KEY); } catch (e) { return null; }
    }
    function setConsent(value) {
        try { localStorage.setItem(CONSENT_KEY, value); } catch (e) { /* ignore */ }
    }

    // Load GA4 (gtag.js) once consent is granted (or not required).
    var analyticsLoaded = false;
    function loadAnalytics() {
        if (analyticsLoaded) return;
        if (!CFG.ga4) return;
        analyticsLoaded = true;

        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(CFG.ga4);
        document.head.appendChild(s);

        window.dataLayer = window.dataLayer || [];
        function gtag() { window.dataLayer.push(arguments); }
        window.gtag = gtag;
        gtag('js', new Date());
        // Consent mode: default denied, updated to granted (cookieless pings off when declined).
        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'granted'
        });
        gtag('config', CFG.ga4, { send_page_view: true });
    }

    function initConsent() {
        var banner = document.getElementById('consent-banner');
        if (!CFG.consentRequired) {
            loadAnalytics();
            if (banner) banner.remove();
            return;
        }
        var stored = getConsent();
        if (stored === 'granted') {
            loadAnalytics();
            if (banner) banner.remove();
            return;
        }
        if (stored === 'denied') {
            if (banner) banner.remove();
            return;
        }
        // No decision yet: show the banner (do NOT load anything).
        if (banner) {
            banner.classList.remove('hidden');
            document.getElementById('consent-accept').addEventListener('click', function () {
                setConsent('granted');
                loadAnalytics();
                banner.remove();
            });
            document.getElementById('consent-decline').addEventListener('click', function () {
                setConsent('denied');
                banner.remove();
            });
        }
    }

    /* ------------------------------------------------------------- events -- */

    // Fire a GA4 event only if analytics is loaded (i.e. consent granted).
    function track(name, params) {
        if (typeof window.gtag === 'function') {
            window.gtag('event', name, params || {});
        }
    }

    // view_item — fired once when a product page carries data- ga itemView.
    function trackViewItem() {
        var el = document.querySelector('[data-ga-view-item]');
        if (!el) return;
        try {
            var d = JSON.parse(el.getAttribute('data-ga-view-item'));
            track('view_item', {
                currency: d.currency || 'PKR',
                value: d.value,
                items: [{ item_id: d.item_id, item_name: d.item_name, price: d.value, item_category: d.category || '' }]
            });
        } catch (e) { /* malformed payload — never break the page */ }
    }

    // add_to_cart — via fetch interceptor on the cart/add form.
    function bindAddToCart() {
        document.querySelectorAll('form[data-ga-add-to-cart]').forEach(function (form) {
            form.addEventListener('submit', function () {
                try {
                    var d = JSON.parse(form.getAttribute('data-ga-add-to-cart'));
                    var qtyInput = form.querySelector('input[name="quantity"]');
                    var qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
                    track('add_to_cart', {
                        currency: 'PKR',
                        value: d.price * qty,
                        items: [{ item_id: d.item_id, item_name: d.item_name, price: d.price, quantity: qty }]
                    });
                } catch (e) { /* ignore */ }
            });
        });
    }

    // begin_checkout — from the checkout page payload.
    function trackBeginCheckout() {
        var el = document.querySelector('[data-ga-begin-checkout]');
        if (!el) return;
        try {
            var d = JSON.parse(el.getAttribute('data-ga-begin-checkout'));
            track('begin_checkout', {
                currency: 'PKR',
                value: d.value,
                items: d.items || []
            });
        } catch (e) { /* ignore */ }
    }

    // purchase — fired once per transaction id (deduplicated via localStorage).
    function trackPurchase() {
        var el = document.querySelector('[data-ga-purchase]');
        if (!el) return;
        var txId;
        try {
            var d = JSON.parse(el.getAttribute('data-ga-purchase'));
            txId = d.transaction_id;
        } catch (e) { return; }

        if (!txId) return;

        var fired = [];
        try { fired = JSON.parse(localStorage.getItem(FIRED_ORDERS_KEY) || '[]'); } catch (e) { fired = []; }
        if (fired.indexOf(txId) !== -1) {
            return; // already counted — refresh / revisit must not double-fire
        }
        try {
            var payload = JSON.parse(el.getAttribute('data-ga-purchase'));
            track('purchase', {
                transaction_id: payload.transaction_id,
                currency: 'PKR',
                value: payload.value,
                shipping: payload.shipping,
                items: payload.items || []
            });
            fired.push(txId);
            localStorage.setItem(FIRED_ORDERS_KEY, JSON.stringify(fired));
        } catch (e) { /* ignore */ }
    }

    /* ----------------------------------------------------------- variant UI -- */

    function bindVariants() {
        var select = document.getElementById('variant-select');
        if (!select) return;
        select.addEventListener('change', function () {
            var url = select.getAttribute('data-base-url');
            var val = select.value;
            // Variant URLs canonicalise to the base product — never index the variant.
            window.location.href = val ? url + '?variant=' + encodeURIComponent(val) : url;
        });
    }

    /* --------------------------------------------------------------- init -- */

    document.addEventListener('DOMContentLoaded', function () {
        initConsent();
        trackViewItem();
        bindAddToCart();
        trackBeginCheckout();
        trackPurchase();
        bindVariants();
    });
})();
