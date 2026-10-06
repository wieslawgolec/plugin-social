<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\WeChatApiHelper;
final class WeChatIntegration extends SocialIntegration
{
    public function getName(): string { return 'WeChat'; }
    public function getDisplayName(): string { return 'WeChat Official Account'; }
    public function getIdentifierFields(): array { return ['wechat', 'openid']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'app_id' => 'mautic.integration.keyfield.app_id',
            'app_secret' => 'mautic.integration.keyfield.app_secret',
        ];
    }
    public function getApiUrl($endpoint): string { return WeChatApiHelper::url((string) $endpoint); }
    public function fetchAccessToken(): ?string
    {
        if (!empty($this->keys['access_token']) && !empty($this->keys['token_expires']) && time() < (int) $this->keys['token_expires'] - 60) {
            return $this->keys['access_token'];
        }
        $response = $this->makeRequest(
            WeChatApiHelper::tokenUrl(),
            WeChatApiHelper::buildTokenQuery($this->keys['app_id'] ?? '', $this->keys['app_secret'] ?? ''),
            'GET',
            ['authorize' => false]
        );
        if (!is_array($response) || empty($response['access_token'])) {
            return null;
        }
        $this->keys['access_token'] = $response['access_token'];
        $this->keys['token_expires'] = time() + (int) ($response['expires_in'] ?? 7200);
        return $this->keys['access_token'];
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $token = $this->fetchAccessToken();
        if (!$token || '' === (string) $identifier) {
            return;
        }
        $response = $this->makeRequest(
            WeChatApiHelper::userInfoUrl(),
            ['access_token' => $token, 'openid' => (string) $identifier, 'lang' => 'en_US'],
            'GET',
            ['authorize' => false]
        );
        if (is_array($response) && !empty($response['openid'])) {
            $socialCache['profile'] = WeChatApiHelper::mapUser($response);
            $socialCache['id'] = $response['openid'];
        }
    }
    public function sendText(string $openId, string $content): array|false
    {
        $token = $this->fetchAccessToken();
        if (!$token) {
            return false;
        }
        $response = $this->makeRequest(
            WeChatApiHelper::customMessageUrl().'?access_token='.rawurlencode($token),
            WeChatApiHelper::buildTextMessagePayload($openId, $content),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json'], 'authorize' => false]
        );
        if (!is_array($response) || !WeChatApiHelper::isSuccess($response)) {
            return is_array($response) ? $response : false;
        }
        return $response;
    }
    public function sendTemplate(string $openId, string $templateId, array $data, string $url = ''): array|false
    {
        $token = $this->fetchAccessToken();
        if (!$token) {
            return false;
        }
        $response = $this->makeRequest(
            WeChatApiHelper::templateMessageUrl().'?access_token='.rawurlencode($token),
            WeChatApiHelper::buildTemplatePayload($openId, $templateId, $data, $url),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json'], 'authorize' => false]
        );
        if (!is_array($response) || !WeChatApiHelper::isSuccess($response)) {
            return is_array($response) ? $response : false;
        }
        return $response;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['WeChat OA: AppID + AppSecret from mp.weixin.qq.com. CS messages need 48h user interaction; else use templates.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
