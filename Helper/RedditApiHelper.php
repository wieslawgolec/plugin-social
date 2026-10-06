<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class RedditApiHelper
{
    public const API_BASE = 'https://oauth.reddit.com';
    public const AUTH_URL = 'https://www.reddit.com/api/v1/authorize';
    public const TOKEN_URL = 'https://www.reddit.com/api/v1/access_token';

    public static function apiUrl(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function cleanUsername(string $username): string
    {
        $username = trim($username);
        if (str_starts_with($username, 'u/')) {
            $username = substr($username, 2);
        }
        if (str_starts_with($username, '/u/')) {
            $username = substr($username, 3);
        }

        return $username;
    }

    public static function cleanSubreddit(string $name): string
    {
        $name = trim($name);
        if (str_starts_with($name, 'r/')) {
            $name = substr($name, 2);
        }

        return $name;
    }

    public static function userAboutEndpoint(string $username): string
    {
        return 'user/'.rawurlencode(self::cleanUsername($username)).'/about';
    }

    public static function submitEndpoint(): string
    {
        return 'api/submit';
    }
}
