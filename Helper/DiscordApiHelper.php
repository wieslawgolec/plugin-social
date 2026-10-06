<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class DiscordApiHelper
{
    public const API_BASE = 'https://discord.com/api/v10';
    public const WEBHOOK_BASE = 'https://discord.com/api/webhooks';

    public static function apiUrl(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function webhookUrl(string $webhookId, string $webhookToken): string
    {
        return self::WEBHOOK_BASE.'/'.rawurlencode($webhookId).'/'.rawurlencode($webhookToken);
    }

    public static function parseWebhookUrl(string $url): ?array
    {
        if (preg_match('#discord(?:app)?\.com/api/webhooks/(\d+)/([A-Za-z0-9_-]+)#', $url, $m)) {
            return ['id' => $m[1], 'token' => $m[2]];
        }

        return null;
    }

    public static function channelMessageUrl(string $channelId): string
    {
        return self::apiUrl('channels/'.rawurlencode($channelId).'/messages');
    }
}
