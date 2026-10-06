<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class TikTokApiHelper
{
    public const API_BASE = 'https://open.tiktokapis.com/v2';
    public const AUTH_URL = 'https://www.tiktok.com/v2/auth/authorize';
    public const TOKEN_URL = 'https://open.tiktokapis.com/v2/oauth/token/';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function userInfoUrl(): string { return self::url('user/info/'); }
    public static function videoListUrl(): string { return self::url('video/list/'); }
    public static function videoQueryUrl(): string { return self::url('video/query/'); }

    public static function cleanOpenId(string $id): string { return trim($id); }

    public static function defaultUserFields(): array
    {
        return ['open_id', 'union_id', 'avatar_url', 'display_name', 'bio_description', 'profile_deep_link', 'is_verified', 'follower_count', 'following_count', 'likes_count', 'video_count'];
    }

    public static function buildUserInfoQuery(?array $fields = null): array
    {
        return ['fields' => implode(',', $fields ?? self::defaultUserFields())];
    }

    public static function buildVideoListPayload(int $maxCount = 20, ?int $cursor = null): array
    {
        $payload = ['max_count' => max(1, min(20, $maxCount))];
        if (null !== $cursor) {
            $payload['cursor'] = $cursor;
        }
        return $payload;
    }

    public static function mapUser(array $user): array
    {
        $data = $user['data']['user'] ?? $user['user'] ?? $user;
        return [
            'id' => $data['open_id'] ?? '',
            'profileHandle' => $data['display_name'] ?? '',
            'name' => $data['display_name'] ?? '',
            'description' => $data['bio_description'] ?? '',
            'profileImage' => $data['avatar_url'] ?? '',
            'url' => $data['profile_deep_link'] ?? '',
            'followers' => $data['follower_count'] ?? null,
            'following' => $data['following_count'] ?? null,
            'likes' => $data['likes_count'] ?? null,
            'videos' => $data['video_count'] ?? null,
            'verified' => $data['is_verified'] ?? false,
        ];
    }
}
