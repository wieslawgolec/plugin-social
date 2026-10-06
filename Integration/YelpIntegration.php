<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\YelpApiHelper;
final class YelpIntegration extends SocialIntegration
{
    public function getName(): string { return 'Yelp'; }
    public function getIdentifierFields(): array { return ['yelp']; }
    public function getSupportedFeatures(): array { return ['public_profile']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return ['api_key' => 'mautic.integration.keyfield.apitoken'];
    }
    public function getApiUrl($endpoint): string { return YelpApiHelper::url((string) $endpoint); }
    public function searchBusinesses(string $term, string $location, int $limit = 20): array|false
    {
        $response = $this->makeRequest(
            YelpApiHelper::searchUrl(),
            YelpApiHelper::buildSearchQuery($term, $location, $limit),
            'GET',
            ['headers' => ['Authorization' => 'Bearer '.($this->keys['api_key'] ?? '')]]
        );
        return is_array($response) ? $response : false;
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $response = $this->makeRequest(
            YelpApiHelper::businessUrl((string) $identifier),
            [],
            'GET',
            ['headers' => ['Authorization' => 'Bearer '.($this->keys['api_key'] ?? '')]]
        );
        if (is_array($response) && isset($response['id'])) {
            $socialCache['profile'] = YelpApiHelper::mapBusiness($response);
            $socialCache['id'] = $response['id'];
        }
    }
    public function getFormType() { return null; }
}
