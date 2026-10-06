<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class PinterestApiHelper
{
    public const API_BASE = 'https://api.pinterest.com/v5';
    public const AUTH_URL = 'https://www.pinterest.com/oauth';
    public const TOKEN_URL = 'https://api.pinterest.com/v5/oauth/token';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function userAccountUrl(): string
    {
        return self::url('user_account');
    }

    public static function pinsUrl(): string
    {
        return self::url('pins');
    }

    public static function boardsUrl(): string
    {
        return self::url('boards');
    }

    public static function cleanUsername(string $username): string
    {
        return ltrim(trim($username), '@');
    }
}
