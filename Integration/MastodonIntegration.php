<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\MastodonApiHelper;
final class MastodonIntegration extends SocialIntegration
{
    public function getName(): string { return 'Mastodon'; }
    public function getIdentifierFields(): array { return ['mastodon']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'public_activity', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'instance_url' => 'mautic.integration.keyfield.instance_url',
            'client_id' => 'mautic.integration.keyfield.clientid',
            'client_secret' => 'mautic.integration.keyfield.clientsecret',
        ];
    }
    public function getApiUrl($endpoint): string
    {
        return MastodonApiHelper::apiUrl($this->keys['instance_url'] ?? 'https://mastodon.social', (string) $endpoint);
    }
    public function getAuthenticationUrl(): string
    {
        return MastodonApiHelper::oauthAuthorizeUrl($this->keys['instance_url'] ?? 'https://mastodon.social');
    }
    public function getAccessTokenUrl(): string
    {
        return MastodonApiHelper::oauthTokenUrl($this->keys['instance_url'] ?? 'https://mastodon.social');
    }
    public function getAuthScope(): string { return 'read write'; }
    public function postStatus(string $text, string $visibility = 'public'): array|false
    {
        $response = $this->makeRequest(
            $this->getApiUrl(MastodonApiHelper::statusesEndpoint()),
            MastodonApiHelper::buildStatusPayload($text, $visibility),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json']]
        );
        return is_array($response) ? $response : false;
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $response = $this->makeRequest($this->getApiUrl(MastodonApiHelper::accountLookupEndpoint((string) $identifier)), [], 'GET');
        if (is_array($response) && isset($response['id'])) {
            $socialCache['profile'] = MastodonApiHelper::mapAccountToProfile($response);
            $socialCache['id'] = $response['id'];
        }
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        $transKey = 'mautic.social.mastodon.notes.'.$section;
        $text = $this->translator->trans($transKey);
        if ($text !== $transKey) {
            return [$text, 'info'];
        }
        if ('authorization' === $section) {
            return [$this->translator->trans('mautic.social.oauth.callback_hint'), 'info'];
        }
        return parent::getFormNotes($section);
    }
}
