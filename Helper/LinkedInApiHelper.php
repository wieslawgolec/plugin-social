<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class LinkedInApiHelper
{
    public const API_BASE = 'https://api.linkedin.com/rest';
    public const AUTH_URL = 'https://www.linkedin.com/oauth/v2/authorization';
    public const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';
    public const VERSION_HEADER = '202503';

    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }

    public static function userinfoUrl(): string
    {
        return 'https://api.linkedin.com/v2/userinfo';
    }

    public static function meUrl(): string
    {
        return 'https://api.linkedin.com/v2/me';
    }

    public static function postsUrl(): string
    {
        return self::url('posts');
    }

    public static function cleanPersonUrn(string $idOrUrn): string
    {
        $id = trim($idOrUrn);
        if (str_starts_with($id, 'urn:li:person:') || str_starts_with($id, 'urn:li:organization:')) {
            return $id;
        }

        return 'urn:li:person:'.$id;
    }

    public static function buildTextPostPayload(string $authorUrn, string $text, string $visibility = 'PUBLIC'): array
    {
        return [
            'author' => self::cleanPersonUrn($authorUrn),
            'commentary' => $text,
            'visibility' => $visibility,
            'distribution' => [
                'feedDistribution' => 'MAIN_FEED',
                'targetEntities' => [],
                'thirdPartyDistributionChannels' => [],
            ],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];
    }

    public static function mapProfile(array $profile): array
    {
        $name = trim(($profile['given_name'] ?? $profile['localizedFirstName'] ?? '').' '.($profile['family_name'] ?? $profile['localizedLastName'] ?? ''));
        if ('' === $name) {
            $name = $profile['name'] ?? ($profile['localizedHeadline'] ?? '');
        }

        return [
            'id' => $profile['sub'] ?? ($profile['id'] ?? ''),
            'profileHandle' => $profile['email'] ?? ($profile['vanityName'] ?? ''),
            'name' => $name,
            'description' => $profile['headline'] ?? ($profile['localizedHeadline'] ?? ''),
            'profileImage' => $profile['picture'] ?? '',
            'url' => isset($profile['sub']) ? 'https://www.linkedin.com/in/'.$profile['sub'] : '',
        ];
    }
}
