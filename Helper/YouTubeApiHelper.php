<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class YouTubeApiHelper
{
    public const API_BASE = 'https://www.googleapis.com/youtube/v3';
    public const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    public const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function channelsUrl(): string
    {
        return self::url('channels');
    }

    public static function searchUrl(): string
    {
        return self::url('search');
    }

    public static function videosUrl(): string
    {
        return self::url('videos');
    }

    public static function cleanChannelId(string $id): string
    {
        $id = trim($id);
        if (preg_match('#youtube\.com/(channel|c|@)/([^/?#]+)#i', $id, $m)) {
            return $m[2];
        }

        return $id;
    }
}
