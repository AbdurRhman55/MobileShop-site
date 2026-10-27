<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Support\EmailRule;

/**
 * Newsletter subscriptions.
 *
 * Consent timestamp is recorded. Re-subscribing an unsubscribed address
 * re-activates it and refreshes the consent time. The same strict email rule
 * as customer accounts applies, so disposable inboxes are rejected.
 */
final class Newsletter
{
    public static function subscribe(string $email): bool
    {
        $email = mb_strtolower(trim($email));
        if (!EmailRule::valid($email)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        Database::query(
            "INSERT INTO newsletter_subscribers (email, status, consented_at)
             VALUES (?, 'subscribed', ?)
             ON DUPLICATE KEY UPDATE status = 'subscribed', consented_at = VALUES(consented_at)",
            [strtolower($email), $now]
        );
        return true;
    }

    public static function unsubscribe(string $email): bool
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }
        Database::query(
            "UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?",
            [$email]
        );
        return true;
    }

    public static function count(): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed'"
        );
    }
}
