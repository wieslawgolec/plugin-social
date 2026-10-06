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
    public static function oauthAuthorizeUrl(string $instanceBase): string
    {
        return self::instanceRoot($instanceBase).'/oauth/authorize';
    }
    public static function oauthTokenUrl(string $instanceBase): string
    {
        return self::instanceRoot($instanceBase).'/oauth/token';
    }
    public static function instanceRoot(string $instanceBase): string
    {
        $base = rtrim($instanceBase, '/');
        if (!preg_match('#^https?://#i', $base)) {
            $base = 'https://'.$base;
        }
        return $base;
    }
    public static function cleanHandle(string $handle): string
    {
        $handle = trim($handle);
        return str_starts_with($handle, '@') ? substr($handle, 1) : $handle;
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
    public static function buildStatusPayload(string $text, string $visibility = 'public'): array
    {
        return ['status' => $text, 'visibility' => $visibility];
    }
    public static function mapAccountToProfile(array $account): array
    {
        return [
            'id' => (string) ($account['id'] ?? ''),
            'profileHandle' => $account['acct'] ?? ($account['username'] ?? ''),
            'name' => $account['display_name'] ?? ($account['username'] ?? ''),
            'description' => strip_tags((string) ($account['note'] ?? '')),
            'url' => $account['url'] ?? '',
            'profileImage' => $account['avatar'] ?? '',
            'followers' => $account['followers_count'] ?? 0,
            'following' => $account['following_count'] ?? 0,
        ];
    }
    public static function accountLookupEndpoint(string $acct): string
    {
        return 'accounts/lookup?acct='.rawurlencode(self::cleanHandle($acct));
    }
    public static function statusesEndpoint(): string
    {
        return 'statuses';
    }
}
