<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Helper\DiscordApiHelper;

final class DiscordIntegration extends SocialIntegration
{
    public function getName(): string { return 'Discord'; }
    public function getIdentifierFields(): array { return ['discord']; }
    public function getSupportedFeatures(): array { return ['share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'webhook_url' => 'mautic.integration.keyfield.webhook_url',
            'bot_token' => 'mautic.integration.keyfield.bot_token',
        ];
    }
    public function getApiUrl($endpoint): string { return DiscordApiHelper::apiUrl((string) $endpoint); }
    public function getFormType() { return null; }
}
