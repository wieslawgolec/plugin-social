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
    public function getFormType() { return null; }
}
