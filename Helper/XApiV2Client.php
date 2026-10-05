<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Helper;
final class XApiV2Client
{
    public const API_BASE = 'https://api.x.com/2';
    public const AUTH_URL = 'https://x.com/i/oauth2/authorize';
    public const TOKEN_URL = 'https://api.x.com/2/oauth2/token';
    public const DEFAULT_SCOPES = 'tweet.read tweet.write users.read offline.access';
    public static function apiUrl(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        if ('statuses/update' === $endpoint || '1.1/statuses/update.json' === $endpoint) {
            return self::API_BASE.'/tweets';
        }
        if (str_starts_with($endpoint, '1.1/') || str_starts_with($endpoint, '2/')) {
            $endpoint = preg_replace('#^(1\.1/|2/)#', '', $endpoint) ?? $endpoint;
            $endpoint = preg_replace('#\.json$#', '', $endpoint) ?? $endpoint;
        }
        return self::API_BASE.'/'.$endpoint;
    }
    public static function cleanIdentifier(string $identifier): string
    {
        if (preg_match('#https?://(www\.)?(twitter\.com|x\.com)/(.*?)(/.*?|$)#i', $identifier, $match)) {
            $identifier = $match[3];
        } elseif (str_starts_with($identifier, '@')) {
            $identifier = substr($identifier, 1);
        }
        return rawurlencode(trim($identifier));
    }
    public static function buildSearchQueryForHashtag(string $hashtag): string
    {
        return '#'.ltrim($hashtag, '#').' -is:retweet';
    }
    public static function buildSearchQueryForMention(string $handle): string
    {
        return '@'.ltrim($handle, '@').' -is:retweet';
    }
}
