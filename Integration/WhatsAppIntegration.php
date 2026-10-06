<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\WhatsAppApiHelper;
final class WhatsAppIntegration extends SocialIntegration
{
    public function getName(): string { return 'WhatsApp'; }
    public function getDisplayName(): string { return 'WhatsApp Business'; }
    public function getIdentifierFields(): array { return ['whatsapp', 'mobile', 'phone']; }
    public function getSupportedFeatures(): array { return ['share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'access_token' => 'mautic.integration.keyfield.apitoken',
            'phone_number_id' => 'mautic.integration.keyfield.phone_number_id',
            'waba_id' => 'mautic.integration.keyfield.waba_id',
        ];
    }
    public function getApiUrl($endpoint): string { return WhatsAppApiHelper::url((string) $endpoint); }
    public function sendText(string $toPhone, string $body): array|false
    {
        $response = $this->makeRequest(
            WhatsAppApiHelper::messagesUrl($this->keys['phone_number_id'] ?? ''),
            WhatsAppApiHelper::buildTextMessagePayload($toPhone, $body),
            'POST',
            ['encode_parameters' => false, 'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.($this->keys['access_token'] ?? ''),
            ]]
        );
        if (!is_array($response)) {
            return false;
        }
        return WhatsAppApiHelper::mapSendResult($response) ?? $response;
    }
    public function sendTemplate(string $toPhone, string $templateName, string $lang = 'en_US', array $params = []): array|false
    {
        $response = $this->makeRequest(
            WhatsAppApiHelper::messagesUrl($this->keys['phone_number_id'] ?? ''),
            WhatsAppApiHelper::buildTemplatePayload($toPhone, $templateName, $lang, $params),
            'POST',
            ['encode_parameters' => false, 'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.($this->keys['access_token'] ?? ''),
            ]]
        );
        if (!is_array($response)) {
            return false;
        }
        return WhatsAppApiHelper::mapSendResult($response) ?? $response;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        $transKey = 'mautic.social.whatsapp.notes.'.$section;
        $text = $this->translator->trans($transKey);
        if ($text !== $transKey) {
            return [$text, 'info'];
        }
        return parent::getFormNotes($section);
    }
}
