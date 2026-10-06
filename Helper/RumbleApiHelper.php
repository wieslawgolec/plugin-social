<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class RumbleApiHelper
{
    public const API_BASE = 'https://rumble.com';
    public const API_SERVICE = 'https://rumble.com/api';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function apiUrl(string $endpoint): string
    {
        return self::API_SERVICE.'/'.ltrim($endpoint, '/');
    }

    public static function cleanChannelSlug(string $slug): string
    {
        $slug = trim($slug);
        $slug = ltrim($slug, '@/');
        if (preg_match('#rumble\.com/(?:c-)?([^/?#]+)#i', $slug, $m)) {
            return $m[1];
        }
        return $slug;
    }

    public static function channelPageUrl(string $slug): string
    {
        $slug = self::cleanChannelSlug($slug);
        if (!str_starts_with($slug, 'c-') && !str_starts_with($slug, 'user/')) {
            return self::url('c-'.$slug);
        }
        return self::url($slug);
    }

    public static function buildVideoMetaPayload(string $title, string $description = '', string $visibility = 'public', ?string $channel = null): array
    {
        $payload = ['title' => $title, 'description' => $description, 'visibility' => $visibility];
        if (null !== $channel && '' !== $channel) {
            $payload['channel'] = self::cleanChannelSlug($channel);
        }
        return $payload;
    }

    public static function mapChannel(array $data): array
    {
        return [
            'id' => (string) ($data['id'] ?? $data['channel_id'] ?? ''),
            'profileHandle' => $data['username'] ?? ($data['slug'] ?? ''),
            'name' => $data['title'] ?? ($data['name'] ?? ''),
            'description' => $data['description'] ?? '',
            'profileImage' => $data['thumbnail'] ?? ($data['avatar'] ?? ''),
            'url' => $data['url'] ?? (isset($data['slug']) ? self::channelPageUrl((string) $data['slug']) : ''),
            'followers' => $data['followers'] ?? ($data['subscribers'] ?? null),
        ];
    }
}
