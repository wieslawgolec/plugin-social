<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\RedditApiHelper;
final class RedditIntegration extends SocialIntegration
{
    public function getName(): string { return 'Reddit'; }
    public function getIdentifierFields(): array { return ['reddit']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'public_activity', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return RedditApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return RedditApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'identity read submit'; }
    public function getApiUrl($endpoint): string { return RedditApiHelper::apiUrl((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $endpoint = '' !== (string) $identifier
            ? RedditApiHelper::userAboutEndpoint((string) $identifier)
            : RedditApiHelper::meEndpoint();
        $response = $this->makeRequest($this->getApiUrl($endpoint), [], 'GET');
        if (is_array($response)) {
            $socialCache['profile'] = RedditApiHelper::mapUserAbout($response);
            $socialCache['id'] = $socialCache['profile']['id'] ?? '';
        }
    }
    public function submitPost(string $subreddit, string $title, string $body = '', string $kind = 'self'): array|false
    {
        $response = $this->makeRequest(
            $this->getApiUrl(RedditApiHelper::submitEndpoint()),
            RedditApiHelper::buildSubmitPayload($subreddit, $title, $kind, $body),
            'POST',
            ['headers' => ['User-Agent' => 'MauticSocial/2.0']]
        );
        return is_array($response) ? $response : false;
    }
    public function search(string $query, int $limit = 25): array|false
    {
        $response = $this->makeRequest(
            $this->getApiUrl(RedditApiHelper::searchEndpoint()),
            RedditApiHelper::buildSearchQuery($query, $limit),
            'GET',
            ['headers' => ['User-Agent' => 'MauticSocial/2.0']]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['Create a Reddit app at https://www.reddit.com/prefs/apps. Redirect URI = Mautic callback. Scopes: identity read submit.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
