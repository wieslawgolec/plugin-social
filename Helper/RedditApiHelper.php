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
        if (str_starts_with($username, '/u/')) {
            $username = substr($username, 3);
        } elseif (str_starts_with($username, 'u/')) {
            $username = substr($username, 2);
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

    public static function submitEndpoint(): string { return 'api/submit'; }
    public static function searchEndpoint(): string { return 'search'; }
    public static function meEndpoint(): string { return 'api/v1/me'; }

    public static function buildSubmitPayload(string $subreddit, string $title, string $kind = 'self', string $bodyOrUrl = ''): array
    {
        $payload = ['api_type' => 'json', 'kind' => $kind, 'sr' => self::cleanSubreddit($subreddit), 'title' => $title];
        if ('self' === $kind) {
            $payload['text'] = $bodyOrUrl;
        } else {
            $payload['url'] = $bodyOrUrl;
        }
        return $payload;
    }

    public static function buildSearchQuery(string $query, int $limit = 25, string $sort = 'new'): array
    {
        return ['q' => $query, 'limit' => max(1, min(100, $limit)), 'sort' => $sort, 'type' => 'link'];
    }

    public static function mapUserAbout(array $data): array
    {
        $d = $data['data'] ?? $data;
        return [
            'id' => $d['id'] ?? '',
            'profileHandle' => $d['name'] ?? '',
            'name' => $d['subreddit']['title'] ?? ($d['name'] ?? ''),
            'description' => $d['subreddit']['public_description'] ?? '',
            'profileImage' => $d['icon_img'] ?? ($d['snoovatar_img'] ?? ''),
            'karma' => ($d['link_karma'] ?? 0) + ($d['comment_karma'] ?? 0),
            'url' => isset($d['name']) ? 'https://www.reddit.com/user/'.$d['name'] : '',
        ];
    }
}
