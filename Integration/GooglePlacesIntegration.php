<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\GooglePlacesApiHelper;
final class GooglePlacesIntegration extends SocialIntegration
{
    public function getName(): string { return 'GooglePlaces'; }
    public function getDisplayName(): string { return 'Google Places'; }
    public function getIdentifierFields(): array { return ['googleplaces']; }
    public function getSupportedFeatures(): array { return ['public_profile']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return ['api_key' => 'mautic.integration.keyfield.apitoken'];
    }
    public function getApiUrl($endpoint): string { return GooglePlacesApiHelper::url((string) $endpoint); }
    public function searchText(string $query, int $maxResults = 10): array|false
    {
        $response = $this->makeRequest(
            GooglePlacesApiHelper::searchTextUrl(),
            GooglePlacesApiHelper::buildSearchTextPayload($query, $maxResults),
            'POST',
            [
                'encode_parameters' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $this->keys['api_key'] ?? '',
                    'X-Goog-FieldMask' => GooglePlacesApiHelper::defaultFieldMask(),
                ],
            ]
        );
        return is_array($response) ? $response : false;
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $placeId = GooglePlacesApiHelper::cleanPlaceId((string) $identifier);
        $response = $this->makeRequest(
            GooglePlacesApiHelper::placeDetailsUrl($placeId),
            [],
            'GET',
            ['headers' => [
                'X-Goog-Api-Key' => $this->keys['api_key'] ?? '',
                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,rating,userRatingCount,websiteUri,nationalPhoneNumber',
            ]]
        );
        if (is_array($response) && (isset($response['id']) || isset($response['name']))) {
            $socialCache['profile'] = GooglePlacesApiHelper::mapPlace($response);
            $socialCache['id'] = $socialCache['profile']['id'] ?? '';
        }
    }
    public function getFormType() { return null; }
}
