<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class TelegramApiHelper
{
    public const API_BASE = 'https://api.telegram.org';
    public static function methodUrl(string $botToken, string $method): string
    {
        return self::API_BASE.'/bot'.trim($botToken).'/'.ltrim($method, '/');
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
    public static function buildSendMessagePayload(string|int $chatId, string $text, ?string $parseMode = null): array
    {
        $payload = ['chat_id' => self::cleanChatId($chatId), 'text' => $text];
        if (null !== $parseMode && '' !== $parseMode) {
            $payload['parse_mode'] = $parseMode;
        }
        return $payload;
    }
    public static function mapSendMessageResult(array $response): ?array
    {
        if (empty($response['ok']) || empty($response['result'])) {
            return null;
        }
        $r = $response['result'];
        return [
            'message_id' => $r['message_id'] ?? null,
            'chat_id' => $r['chat']['id'] ?? null,
            'text' => $r['text'] ?? '',
            'date' => $r['date'] ?? null,
        ];
    }
    public static function mapBotInfo(array $response): ?array
    {
        if (empty($response['ok']) || empty($response['result'])) {
            return null;
        }
        $r = $response['result'];
        return [
            'id' => $r['id'] ?? null,
            'username' => $r['username'] ?? '',
            'name' => $r['first_name'] ?? '',
            'is_bot' => $r['is_bot'] ?? true,
        ];
    }
}
