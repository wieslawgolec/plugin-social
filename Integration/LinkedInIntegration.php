<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\LinkedInApiHelper;
final class LinkedInIntegration extends SocialIntegration
{
    public function getName(): string { return 'LinkedIn'; }
    public function getIdentifierFields(): array { return ['linkedin', 'linkedinid']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return LinkedInApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return LinkedInApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'openid profile email w_member_social'; }
    public function getApiUrl($endpoint): string { return LinkedInApiHelper::url((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $response = $this->makeRequest(LinkedInApiHelper::userinfoUrl(), [], 'GET', [
            'headers' => ['LinkedIn-Version' => LinkedInApiHelper::VERSION_HEADER],
        ]);
        if (!is_array($response) || empty($response['sub'])) {
            $response = $this->makeRequest(LinkedInApiHelper::meUrl(), [], 'GET');
        }
        if (is_array($response) && (isset($response['sub']) || isset($response['id']))) {
            $socialCache['profile'] = LinkedInApiHelper::mapProfile($response);
            $socialCache['id'] = $socialCache['profile']['id'] ?? '';
            if (!empty($socialCache['id'])) {
                $this->keys['author_urn'] = LinkedInApiHelper::cleanPersonUrn((string) $socialCache['id']);
            }
        }
    }
    public function postText(string $text, ?string $authorUrn = null): array|false
    {
        $author = $authorUrn ?? ($this->keys['author_urn'] ?? $this->keys['person_urn'] ?? '');
        if ('' === $author) {
            return ['failed' => 1, 'error' => 'Missing author URN — authorize LinkedIn first'];
        }
        $response = $this->makeRequest(
            LinkedInApiHelper::postsUrl(),
            LinkedInApiHelper::buildTextPostPayload($author, $text),
            'POST',
            [
                'encode_parameters' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'LinkedIn-Version' => LinkedInApiHelper::VERSION_HEADER,
                    'X-Restli-Protocol-Version' => '2.0.0',
                ],
            ]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['LinkedIn: Sign In with LinkedIn + Share on LinkedIn products. Redirect = Mautic callback. Scopes: openid profile email w_member_social.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
