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
    public static function userAccountUrl(): string { return self::url('user_account'); }
    public static function pinsUrl(): string { return self::url('pins'); }
    public static function boardsUrl(): string { return self::url('boards'); }
    public static function cleanUsername(string $username): string
    {
        return ltrim(trim($username), '@');
    }
    public static function buildCreatePinPayload(string $boardId, string $imageUrl, string $title = '', string $description = ''): array
    {
        $payload = [
            'board_id' => $boardId,
            'media_source' => ['source_type' => 'image_url', 'url' => $imageUrl],
        ];
        if ('' !== $title) {
            $payload['title'] = $title;
        }
        if ('' !== $description) {
            $payload['description'] = $description;
        }
        return $payload;
    }
    public static function mapUserAccount(array $user): array
    {
        return [
            'id' => $user['id'] ?? '',
            'profileHandle' => $user['username'] ?? '',
            'name' => $user['business_name'] ?? ($user['username'] ?? ''),
            'description' => $user['about'] ?? '',
            'profileImage' => $user['profile_image'] ?? '',
            'url' => isset($user['username']) ? 'https://www.pinterest.com/'.$user['username'].'/' : '',
            'followers' => $user['follower_count'] ?? null,
        ];
    }
}
