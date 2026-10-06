<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class TelegramApiHelper
{
    public const API_BASE = 'https://api.telegram.org';

    public static function methodUrl(string $botToken, string $method): string
    {
        $token = trim($botToken);
        $method = ltrim($method, '/');

        return self::API_BASE.'/bot'.$token.'/'.$method;
    }

    public static function sendMessageUrl(string $botToken): string
    {
        return self::methodUrl($botToken, 'sendMessage');
    }

    public static function getMeUrl(string $botToken): string
    {
        return self::methodUrl($botToken, 'getMe');
    }

    public static function cleanChatId(string|int $chatId): string
    {
        return (string) $chatId;
    }
}
