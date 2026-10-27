<?php

declare(strict_types=1);

namespace App\Support;

use App\Seo\SeoSettings;

/**
 * Strict, human-facing email rule for account registration and newsletters.
 *
 * Order updates, invoices and payment confirmations are sent to the address on
 * the account, so throwaway inboxes and obviously fake domains are rejected:
 *
 *   1. RFC-valid syntax (FILTER_VALIDATE_EMAIL) within a sane length.
 *   2. A real, dotted domain with a plausible TLD.
 *   3. No disposable / example / reserved domains.
 *   4. The domain must publish an MX or A record (checked once per request and
 *      skipped automatically when the host has no working DNS at all, so local
 *      development and CI are not blocked).
 *
 * The DNS toggle lives in settings as `email_dns_check`.
 */
final class EmailRule
{
    /** Disposable, temporary and reserved domains that must never receive orders. */
    private const BLOCKED_DOMAINS = [
        'example.com', 'example.net', 'example.org', 'example.edu',
        'test.com', 'test.net', 'test.org', 'localhost', 'localhost.localdomain',
        'mailinator.com', 'mailinator.net', 'yopmail.com', 'yopmail.fr',
        'tempmail.com', 'temp-mail.org', 'tempmailo.com', 'tempmail.net',
        '10minutemail.com', '10minutemail.net', 'guerrillamail.com',
        'guerrillamail.net', 'guerrillamail.org', 'guerrillamailblock.com',
        'sharklasers.com', 'grr.la', 'throwawaymail.com', 'trashmail.com',
        'fakeinbox.com', 'dispostable.com', 'getnada.com', 'nada.email',
        'maildrop.cc', 'spam4.me', 'mintemail.com', 'mailnesia.com',
        'emailondeck.com', 'burnermail.io', 'mohmal.com', 'inboxkitten.com',
        'dropmail.me', 'mail.tm', '1secmail.com', '1secmail.net',
        'luxusmail.org', 'byom.de', 'tempr.email', 'discard.email',
        'emailnax.com', 'mailcatch.com', 'spamgourmet.com', 'mytemp.email',
    ];

    /** Reserved / non-routable TLDs (RFC 2606 and friends). */
    private const BLOCKED_TLDS = [
        'test', 'invalid', 'example', 'localhost', 'local', 'internal',
        'lan', 'home', 'corp',
    ];

    /** @var array<string, bool> */
    private static array $dnsCache = [];

    private static ?bool $dnsAvailable = null;

    /** Return a customer-facing error, or null when the address is acceptable. */
    public static function error(string $email): ?string
    {
        $email = mb_strtolower(trim($email));

        if ($email === '') {
            return 'Enter your email address.';
        }
        if (mb_strlen($email) > 190) {
            return 'That email address is too long (190 characters maximum).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a valid email address, for example name@gmail.com.';
        }

        [$local, $domain] = explode('@', $email, 2);

        if (mb_strlen($local) > 64
            || str_starts_with($local, '.')
            || str_ends_with($local, '.')
            || str_contains($local, '..')) {
            return 'Enter a valid email address — the part before @ is malformed.';
        }

        if (!str_contains($domain, '.')
            || str_starts_with($domain, '.')
            || str_ends_with($domain, '.')
            || str_contains($domain, '..')
            || !preg_match('/\.[a-z]{2,24}$/', $domain)) {
            return 'Enter a complete email address with a real domain, for example name@gmail.com.';
        }

        if (in_array($domain, self::BLOCKED_DOMAINS, true)) {
            return 'Disposable and example email addresses are not accepted. Use a permanent inbox so we can send your order updates.';
        }

        $tld = substr($domain, (int) strrpos($domain, '.') + 1);
        if (in_array($tld, self::BLOCKED_TLDS, true)) {
            return 'That email domain cannot receive order updates. Please use a real, permanent email address.';
        }

        if (!self::domainAcceptsMail($domain)) {
            return 'We could not verify that email domain. Check the spelling, or use a different email address.';
        }

        return null;
    }

    public static function valid(string $email): bool
    {
        return self::error($email) === null;
    }

    /**
     * The domain must have an MX (preferred) or A record.
     *
     * If DNS itself is unavailable (offline sandbox, CI without a resolver),
     * the check is skipped rather than rejecting every genuine address.
     */
    private static function domainAcceptsMail(string $domain): bool
    {
        if ((string) SeoSettings::get('email_dns_check', '1') !== '1') {
            return true;
        }

        if (array_key_exists($domain, self::$dnsCache)) {
            return self::$dnsCache[$domain];
        }

        if (!function_exists('checkdnsrr')) {
            return self::$dnsCache[$domain] = true;
        }

        if (self::$dnsAvailable === null) {
            self::$dnsAvailable = @checkdnsrr('gmail.com', 'MX') || @checkdnsrr('gmail.com', 'A');
        }

        if (!self::$dnsAvailable) {
            return self::$dnsCache[$domain] = true;
        }

        return self::$dnsCache[$domain] = @checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A');
    }
}
