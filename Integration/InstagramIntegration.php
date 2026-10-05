<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

/**
 * Instagram Business / Creator integration via Instagram Graph API (Facebook Login).
 *
 * Supports owned Business/Creator accounts only. Public consumer API is shut down.
 * Hashtag search is limited to 30 unique hashtags per 7 days per account.
 */
final class InstagramIntegration extends SocialIntegration
{
    public const GRAPH_VERSION = 'v26.0';

    public function getName(): string
    {
        return 'Instagram';
    }

    public function getIdentifierFields(): array
    {
        return ['instagram'];
    }

    public function getSupportedFeatures(): array
    {
        return [
            'public_profile',
            'public_activity',
        ];
    }

    public function getAuthenticationType(): string
    {
        return 'oauth2';
    }

    public function getAuthenticationUrl(): string
    {
        return 'https://www.facebook.com/'.self::GRAPH_VERSION.'/dialog/oauth';
    }

    public function getAccessTokenUrl(): string
    {
        return 'https://graph.facebook.com/'.self::GRAPH_VERSION.'/oauth/access_token';
    }

    public function getAuthScope(): string
    {
        return 'instagram_basic,instagram_manage_insights,pages_show_list,pages_read_engagement';
    }

    public function getApiUrl($endpoint): string
    {
        $endpoint = ltrim((string) $endpoint, '/');
        if (!str_starts_with($endpoint, 'v')) {
            $endpoint = self::GRAPH_VERSION.'/'.$endpoint;
        }

        return 'https://graph.facebook.com/'.$endpoint;
    }

    /**
     * Business Discovery for another professional account username.
     *
     * @return array<string, mixed>|null
     */
    public function businessDiscovery(string $igUserId, string $username): ?array
    {
        $response = $this->makeRequest(
            $this->getApiUrl($igUserId),
            [
                'fields' => 'business_discovery.username('.$username.'){followers_count,media_count,username,name,biography,website,profile_picture_url}',
            ],
            'GET'
        );

        if (is_array($response) && isset($response['business_discovery'])) {
            return $response['business_discovery'];
        }

        return null;
    }

    /**
     * Resolve hashtag ID (counts against 30 unique / 7-day quota).
     *
     * @return string|null
     */
    public function searchHashtag(string $igUserId, string $hashtag): ?string
    {
        $hashtag = ltrim($hashtag, '#');
        $response = $this->makeRequest(
            $this->getApiUrl('ig_hashtag_search'),
            [
                'user_id' => $igUserId,
                'q'       => $hashtag,
            ],
            'GET'
        );

        if (is_array($response) && !empty($response['data'][0]['id'])) {
            return (string) $response['data'][0]['id'];
        }

        return null;
    }

    public function getUserData($identifier, &$socialCache): void
    {
        // Requires a connected Instagram Business user id in keys / social cache
        $socialCache['profile'] = [
            'profileHandle' => ltrim((string) $identifier, '@'),
            'note'          => 'Instagram Graph requires a connected Business/Creator account. Use businessDiscovery() for professional accounts.',
        ];
    }

    public function getAvailableLeadFields(array $settings = []): array
    {
        return [
            'username'        => ['type' => 'string'],
            'name'            => ['type' => 'string'],
            'biography'       => ['type' => 'string'],
            'website'         => ['type' => 'string'],
            'followers_count' => ['type' => 'string'],
            'media_count'     => ['type' => 'string'],
        ];
    }

    public function getFormType()
    {
        return null;
    }

    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return [
                'Instagram Graph API for Business/Creator accounts only. Requires a Facebook Page linked to an Instagram professional account. Hashtag search: max 30 unique hashtags per 7 days.',
                'info',
            ];
        }

        return parent::getFormNotes($section);
    }
}
