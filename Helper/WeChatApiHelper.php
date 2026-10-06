<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class WeChatApiHelper
{
    public const API_BASE = 'https://api.weixin.qq.com';

    public static function tokenUrl(): string
    {
        return self::API_BASE.'/cgi-bin/token';
    }

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function userInfoUrl(): string
    {
        return self::url('cgi-bin/user/info');
    }

    public static function customMessageUrl(): string
    {
        return self::url('cgi-bin/message/custom/send');
    }

    public static function templateMessageUrl(): string
    {
        return self::url('cgi-bin/message/template/send');
    }

    public static function buildTokenQuery(string $appId, string $appSecret): array
    {
        return ['grant_type' => 'client_credential', 'appid' => $appId, 'secret' => $appSecret];
    }

    public static function buildTextMessagePayload(string $openId, string $content): array
    {
        return ['touser' => trim($openId), 'msgtype' => 'text', 'text' => ['content' => $content]];
    }

    public static function buildTemplatePayload(string $openId, string $templateId, array $data, string $url = ''): array
    {
        $payload = ['touser' => trim($openId), 'template_id' => $templateId, 'data' => $data];
        if ('' !== $url) {
            $payload['url'] = $url;
        }

        return $payload;
    }

    public static function mapUser(array $user): array
    {
        return [
            'id' => $user['openid'] ?? '',
            'profileHandle' => $user['openid'] ?? '',
            'name' => $user['nickname'] ?? '',
            'description' => $user['remark'] ?? '',
            'profileImage' => $user['headimgurl'] ?? '',
            'city' => $user['city'] ?? '',
            'province' => $user['province'] ?? '',
            'country' => $user['country'] ?? '',
            'subscribe' => $user['subscribe'] ?? 0,
        ];
    }

    public static function isSuccess(array $response): bool
    {
        if (isset($response['errcode']) && 0 !== (int) $response['errcode']) {
            return false;
        }

        return true;
    }
}
