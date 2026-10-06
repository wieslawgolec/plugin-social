<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class MastodonApiHelper
{
    public static function apiUrl(string $instanceBase, string $endpoint): string
    {
        $base = rtrim($instanceBase, '/');
        if (!preg_match('#^https?://#i', $base)) {
            $base = 'https://'.$base;
        }

        return $base.'/api/v1/'.ltrim($endpoint, '/');
    }

    public static function cleanHandle(string $handle): string
    {
        $handle = trim($handle);
        if (str_starts_with($handle, '@')) {
            $handle = substr($handle, 1);
        }

        return $handle;
    }

    public static function parseAcct(string $acct): array
    {
        $acct = self::cleanHandle($acct);
        if (str_contains($acct, '@')) {
            [$local, $domain] = explode('@', $acct, 2);

            return [$local, $domain !== '' ? $domain : null];
        }

        return [$acct, null];
    }

    public static function statusesEndpoint(): string
    {
        return 'statuses';
    }

    public static function accountLookupEndpoint(string $acct): string
    {
        return 'accounts/lookup?acct='.rawurlencode(self::cleanHandle($acct));
    }
}
