<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class TwitchApiHelper
{
    public const API_BASE = 'https://api.twitch.tv/helix';
    public const AUTH_URL = 'https://id.twitch.tv/oauth2/authorize';
    public const TOKEN_URL = 'https://id.twitch.tv/oauth2/token';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function usersUrl(): string
    {
        return self::url('users');
    }

    public static function searchChannelsUrl(): string
    {
        return self::url('search/channels');
    }

    public static function streamsUrl(): string
    {
        return self::url('streams');
    }

    public static function chatMessagesUrl(): string
    {
        return self::url('chat/messages');
    }

    public static function cleanLogin(string $login): string
    {
        $login = trim($login);
        $login = ltrim($login, '@');
        // Use ~ delimiter so # is not treated as pattern terminator
        if (preg_match('~twitch\.tv/([^/?&]+)~i', $login, $m)) {
            return strtolower($m[1]);
        }

        return strtolower($login);
    }

    public static function buildUsersQuery(?string $login = null, ?string $id = null): array
    {
        $q = [];
        if (null !== $login && '' !== $login) {
            $q['login'] = self::cleanLogin($login);
        }
        if (null !== $id && '' !== $id) {
            $q['id'] = $id;
        }

        return $q;
    }

    public static function buildSearchChannelsQuery(string $query, int $first = 20): array
    {
        return [
            'query' => $query,
            'first' => max(1, min(100, $first)),
        ];
    }

    public static function buildChatMessagePayload(string $broadcasterId, string $senderId, string $message): array
    {
        return [
            'broadcaster_id' => $broadcasterId,
            'sender_id' => $senderId,
            'message' => $message,
        ];
    }

    public static function mapUser(array $user): array
    {
        return [
            'id' => $user['id'] ?? '',
            'profileHandle' => $user['login'] ?? '',
            'name' => $user['display_name'] ?? ($user['login'] ?? ''),
            'description' => $user['description'] ?? '',
            'profileImage' => $user['profile_image_url'] ?? '',
            'url' => isset($user['login']) ? 'https://www.twitch.tv/'.$user['login'] : '',
            'viewCount' => $user['view_count'] ?? null,
            'broadcasterType' => $user['broadcaster_type'] ?? '',
        ];
    }
}
