<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\YouTubeApiHelper;
final class YouTubeIntegration extends SocialIntegration
{
    public function getName(): string { return 'YouTube'; }
    public function getIdentifierFields(): array { return ['youtube']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'public_activity']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return YouTubeApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return YouTubeApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'https://www.googleapis.com/auth/youtube.readonly'; }
    public function getApiUrl($endpoint): string { return YouTubeApiHelper::url((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $query = YouTubeApiHelper::buildChannelsQuery((string) $identifier);
        $response = $this->makeRequest(YouTubeApiHelper::channelsUrl(), $query, 'GET');
        $item = is_array($response) ? ($response['items'][0] ?? null) : null;
        if (is_array($item)) {
            $socialCache['profile'] = YouTubeApiHelper::mapChannel($item);
            $socialCache['id'] = $item['id'] ?? '';
        }
    }
    public function search(string $query, int $maxResults = 10): array|false
    {
        $response = $this->makeRequest(YouTubeApiHelper::searchUrl(), [
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => max(1, min(50, $maxResults)),
        ], 'GET');
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['Enable YouTube Data API v3. OAuth redirect = Mautic callback. Scope youtube.readonly.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
