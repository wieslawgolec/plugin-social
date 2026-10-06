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
    public function getFormType() { return null; }
}
