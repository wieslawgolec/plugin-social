<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\TikTokApiHelper;
final class TikTokIntegration extends SocialIntegration
{
    public function getName(): string { return 'TikTok'; }
    public function getIdentifierFields(): array { return ['tiktok']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'public_activity']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return TikTokApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return TikTokApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'user.info.basic,user.info.profile,user.info.stats,video.list'; }
    public function getApiUrl($endpoint): string { return TikTokApiHelper::url((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $response = $this->makeRequest(TikTokApiHelper::userInfoUrl(), TikTokApiHelper::buildUserInfoQuery(), 'GET');
        if (is_array($response)) {
            $profile = TikTokApiHelper::mapUser($response);
            if ('' !== ($profile['id'] ?? '')) {
                $socialCache['profile'] = $profile;
                $socialCache['id'] = $profile['id'];
            }
        }
    }
    public function listVideos(int $maxCount = 20, ?int $cursor = null): array|false
    {
        $response = $this->makeRequest(
            TikTokApiHelper::videoListUrl(),
            TikTokApiHelper::buildVideoListPayload($maxCount, $cursor),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json']]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['TikTok: Login Kit + Display API. Redirect = Mautic callback. Scopes user.info.* video.list. Content Posting needs extra review.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
