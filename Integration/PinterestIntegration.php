<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\PinterestApiHelper;
final class PinterestIntegration extends SocialIntegration
{
    public function getName(): string { return 'Pinterest'; }
    public function getIdentifierFields(): array { return ['pinterest']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return PinterestApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return PinterestApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'boards:read,pins:read,pins:write,user_accounts:read'; }
    public function getApiUrl($endpoint): string { return PinterestApiHelper::url((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $response = $this->makeRequest(PinterestApiHelper::userAccountUrl(), [], 'GET');
        if (is_array($response) && isset($response['username'])) {
            $socialCache['profile'] = PinterestApiHelper::mapUserAccount($response);
            $socialCache['id'] = $response['id'] ?? '';
        }
    }
    public function createPin(string $boardId, string $imageUrl, string $title = '', string $description = ''): array|false
    {
        $response = $this->makeRequest(
            PinterestApiHelper::pinsUrl(),
            PinterestApiHelper::buildCreatePinPayload($boardId, $imageUrl, $title, $description),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json']]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['Pinterest API v5: redirect URI = Mautic callback. Scopes boards:read,pins:read,pins:write,user_accounts:read.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
