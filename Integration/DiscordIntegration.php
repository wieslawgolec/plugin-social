<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\DiscordApiHelper;
final class DiscordIntegration extends SocialIntegration
{
    public function getName(): string { return 'Discord'; }
    public function getIdentifierFields(): array { return ['discord']; }
    public function getSupportedFeatures(): array { return ['share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'webhook_url' => 'mautic.integration.keyfield.webhook_url',
            'bot_token' => 'mautic.integration.keyfield.bot_token',
        ];
    }
    public function getApiUrl($endpoint): string
    {
        return DiscordApiHelper::apiUrl((string) $endpoint);
    }
    public function sendWebhookMessage(string $content, ?string $username = null): array|bool
    {
        $parsed = DiscordApiHelper::parseWebhookUrl($this->keys['webhook_url'] ?? '');
        if (!$parsed) {
            return false;
        }
        $response = $this->makeRequest(
            DiscordApiHelper::webhookUrl($parsed['id'], $parsed['token']),
            DiscordApiHelper::buildWebhookPayload($content, $username),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json']]
        );
        return is_array($response) ? $response : true;
    }
    public function sendChannelMessage(string $channelId, string $content): array|false
    {
        $response = $this->makeRequest(
            DiscordApiHelper::channelMessageUrl($channelId),
            DiscordApiHelper::buildChannelMessagePayload($content),
            'POST',
            ['encode_parameters' => false, 'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bot '.($this->keys['bot_token'] ?? ''),
            ]]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
}
