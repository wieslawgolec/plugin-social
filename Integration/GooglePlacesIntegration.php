<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Helper\GooglePlacesApiHelper;

final class GooglePlacesIntegration extends SocialIntegration
{
    public function getName(): string { return 'GooglePlaces'; }
    public function getDisplayName(): string { return 'Google Places'; }
    public function getIdentifierFields(): array { return ['google_places']; }
    public function getSupportedFeatures(): array { return ['public_profile']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return ['api_key' => 'mautic.integration.keyfield.apikey'];
    }
    public function getApiUrl($endpoint): string
    {
        return GooglePlacesApiHelper::url((string) $endpoint);
    }
    public function getFormType() { return null; }
}
