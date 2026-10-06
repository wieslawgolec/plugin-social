<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Helper\BlueskyApiHelper;

final class BlueskyIntegration extends SocialIntegration
{
    public function getName(): string { return 'Bluesky'; }
    public function getIdentifierFields(): array { return ['bluesky']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'handle' => 'mautic.integration.keyfield.handle',
            'app_password' => 'mautic.integration.keyfield.app_password',
            'pds' => 'mautic.integration.keyfield.pds',
        ];
    }
    public function getApiUrl($endpoint): string
    {
        return BlueskyApiHelper::xrpcUrl($this->keys['pds'] ?? BlueskyApiHelper::DEFAULT_PDS, (string) $endpoint);
    }
    public function getFormType() { return null; }
}
