<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Helper\RedditApiHelper;

final class RedditIntegration extends SocialIntegration
{
    public function getName(): string { return 'Reddit'; }
    public function getIdentifierFields(): array { return ['reddit']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getAuthenticationUrl(): string { return RedditApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return RedditApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'identity read submit'; }
    public function getApiUrl($endpoint): string { return RedditApiHelper::apiUrl((string) $endpoint); }
    public function getFormType() { return null; }
}
