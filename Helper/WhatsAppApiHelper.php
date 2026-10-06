<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class WhatsAppApiHelper
{
    public const API_BASE = 'https://graph.facebook.com/v26.0';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function messagesUrl(string $phoneNumberId): string
    {
        return self::url(rawurlencode($phoneNumberId).'/messages');
    }

    public static function mediaUrl(string $phoneNumberId): string
    {
        return self::url(rawurlencode($phoneNumberId).'/media');
    }

    public static function cleanPhone(string $phone): string
    {
        $phone = trim($phone);
        $phone = preg_replace('/[^\d]/', '', $phone) ?? $phone;

        return $phone;
    }

    public static function buildTextMessagePayload(string $toPhone, string $body, bool $previewUrl = false): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => self::cleanPhone($toPhone),
            'type' => 'text',
            'text' => [
                'body' => $body,
                'preview_url' => $previewUrl,
            ],
        ];
    }

    public static function buildTemplatePayload(string $toPhone, string $templateName, string $languageCode = 'en_US', array $parameters = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => self::cleanPhone($toPhone),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];
        if ([] !== $parameters) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    static fn (string $v) => ['type' => 'text', 'text' => $v],
                    array_values($parameters)
                ),
            ]];
        }

        return $payload;
    }

    public static function mapSendResult(array $response): ?array
    {
        $msg = $response['messages'][0] ?? null;
        if (!is_array($msg)) {
            return null;
        }

        return [
            'message_id' => $msg['id'] ?? null,
            'contacts' => $response['contacts'] ?? [],
        ];
    }
}
