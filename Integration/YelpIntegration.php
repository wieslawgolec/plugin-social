<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

/**
 * Yelp Fusion API integration — replacement for removed Foursquare.
 */
final class YelpIntegration extends SocialIntegration
{
    public const API_BASE = 'https://api.yelp.com/v3';

    public function getName(): string
    {
        return 'Yelp';
    }

    public function getDisplayName(): string
    {
        return 'Yelp Places';
    }

    public function getIdentifierFields(): array
    {
        return ['yelp'];
    }

    public function getSupportedFeatures(): array
    {
        return ['public_profile'];
    }

    public function getAuthenticationType(): string
    {
        return 'key';
    }

    public function getRequiredKeyFields(): array
    {
        return ['api_key' => 'mautic.integration.keyfield.apikey'];
    }

    public function getApiUrl($endpoint): string
    {
        return self::API_BASE.'/'.ltrim((string) $endpoint, '/');
    }

    public function searchBusinesses(string $term, string $location, array $params = []): array
    {
        $params = array_merge(['term' => $term, 'location' => $location, 'limit' => 20], $params);
        $response = $this->makeRequest($this->getApiUrl('businesses/search'), $params, 'GET', [
            'headers' => ['Authorization' => 'Bearer '.($this->keys['api_key'] ?? '')],
        ]);
        return is_array($response) ? $response : [];
    }

    public function getBusiness(string $id): ?array
    {
        $response = $this->makeRequest($this->getApiUrl('businesses/'.$id), [], 'GET', [
            'headers' => ['Authorization' => 'Bearer '.($this->keys['api_key'] ?? '')],
        ]);
        return is_array($response) ? $response : null;
    }

    public function getUserData($identifier, &$socialCache): void
    {
        $business = $this->getBusiness($identifier);
        if (!$business) {
            return;
        }
        $socialCache['profile'] = [
            'name' => $business['name'] ?? '',
            'url' => $business['url'] ?? '',
            'phone' => $business['display_phone'] ?? '',
            'rating' => $business['rating'] ?? null,
            'reviewCount' => $business['review_count'] ?? null,
            'image' => $business['image_url'] ?? '',
        ];
        $socialCache['id'] = $business['id'] ?? $identifier;
    }

    public function getAvailableLeadFields(array $settings = []): array
    {
        return [
            'name' => ['type' => 'string'],
            'phone' => ['type' => 'string'],
            'url' => ['type' => 'string'],
            'rating' => ['type' => 'string'],
        ];
    }

    public function getFormType()
    {
        return null;
    }
}
