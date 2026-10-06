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
        return rtrim($this->keys['instance_url'] ?? 'https://mastodon.social', '/').'/oauth/authorize';
    }
    public function getAccessTokenUrl(): string
    {
        return rtrim($this->keys['instance_url'] ?? 'https://mastodon.social', '/').'/oauth/token';
    }
    public function getAuthScope(): string { return 'read write'; }
    public function getFormType() { return null; }
}
