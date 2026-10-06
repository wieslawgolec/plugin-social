<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\WeComApiHelper;
final class WeComIntegration extends SocialIntegration
{
    public function getName(): string { return 'WeCom'; }
    public function getDisplayName(): string { return 'WeCom (Enterprise WeChat)'; }
    public function getIdentifierFields(): array { return ['wecom', 'userid']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'corp_id' => 'mautic.integration.keyfield.corp_id',
            'corp_secret' => 'mautic.integration.keyfield.corp_secret',
            'agent_id' => 'mautic.integration.keyfield.agent_id',
        ];
    }
    public function getApiUrl($endpoint): string { return WeComApiHelper::url((string) $endpoint); }
    public function fetchAccessToken(): ?string
    {
        if (!empty($this->keys['access_token']) && !empty($this->keys['token_expires']) && time() < (int) $this->keys['token_expires'] - 60) {
            return $this->keys['access_token'];
        }
        $response = $this->makeRequest(
            WeComApiHelper::tokenUrl(),
            WeComApiHelper::buildTokenQuery($this->keys['corp_id'] ?? '', $this->keys['corp_secret'] ?? ''),
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
            WeComApiHelper::userGetUrl(),
            ['access_token' => $token, 'userid' => (string) $identifier],
            'GET',
            ['authorize' => false]
        );
        if (is_array($response) && WeComApiHelper::isSuccess($response) && !empty($response['userid'])) {
            $socialCache['profile'] = WeComApiHelper::mapUser($response);
            $socialCache['id'] = $response['userid'];
        }
    }
    public function sendText(string $toUser, string $content): array|false
    {
        $token = $this->fetchAccessToken();
        if (!$token) {
            return false;
        }
        $agentId = (int) ($this->keys['agent_id'] ?? 0);
        $response = $this->makeRequest(
            WeComApiHelper::messageSendUrl().'?access_token='.rawurlencode($token),
            WeComApiHelper::buildTextMessagePayload($toUser, $agentId, $content),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json'], 'authorize' => false]
        );
        if (!is_array($response) || !WeComApiHelper::isSuccess($response)) {
            return is_array($response) ? $response : false;
        }
        return $response;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['WeCom: Corp ID + App Secret + Agent ID from work.weixin.qq.com. touser = member userid.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
