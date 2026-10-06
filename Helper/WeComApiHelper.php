<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class WeComApiHelper
{
    public const API_BASE = 'https://qyapi.weixin.qq.com';

    public static function tokenUrl(): string
    {
        return self::API_BASE.'/cgi-bin/gettoken';
    }

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function messageSendUrl(): string
    {
        return self::url('cgi-bin/message/send');
    }

    public static function userGetUrl(): string
    {
        return self::url('cgi-bin/user/get');
    }

    public static function buildTokenQuery(string $corpId, string $corpSecret): array
    {
        return ['corpid' => $corpId, 'corpsecret' => $corpSecret];
    }

    public static function buildTextMessagePayload(string $toUser, int $agentId, string $content): array
    {
        return [
            'touser' => str_replace(',', '|', trim($toUser)),
            'msgtype' => 'text',
            'agentid' => $agentId,
            'text' => ['content' => $content],
            'safe' => 0,
        ];
    }

    public static function buildMarkdownPayload(string $toUser, int $agentId, string $content): array
    {
        return [
            'touser' => str_replace(',', '|', trim($toUser)),
            'msgtype' => 'markdown',
            'agentid' => $agentId,
            'markdown' => ['content' => $content],
        ];
    }

    public static function mapUser(array $user): array
    {
        return [
            'id' => $user['userid'] ?? '',
            'profileHandle' => $user['userid'] ?? '',
            'name' => $user['name'] ?? '',
            'description' => $user['position'] ?? '',
            'profileImage' => $user['avatar'] ?? '',
            'email' => $user['email'] ?? ($user['biz_mail'] ?? ''),
            'mobile' => $user['mobile'] ?? '',
            'department' => $user['department'] ?? [],
        ];
    }

    public static function isSuccess(array $response): bool
    {
        return isset($response['errcode']) && 0 === (int) $response['errcode'];
    }
}
