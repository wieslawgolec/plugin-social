<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

/**
 * Yelp Fusion API integration — replacement for removed Foursquare.
 *
 * Uses API key authentication (Bearer). Obtain a key at https://www.yelp.com/developers
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
        return [
            'public_profile',
        ];
    }

    public function getAuthenticationType(): string
    {
        return 'key';
    }

    /**
     * Yelp uses a single API key (Bearer token).
     *
     * @return array<string, string>
     */
    public function getRequiredKeyFields(): array
    {
        return [
            'api_key' => 'mautic.integration.keyfield.apikey',
        ];
    }

    public function getApiUrl($endpoint): string
    {
        return self::API_BASE.'/'.ltrim((string) $endpoint, '/');
    }

    /**
     * Business search.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function searchBusinesses(string $term, string $location, array $params = []): array
    {
        $params = array_merge([
            'term'     => $term,
            'location' => $location,
            'limit'    => 20,
        ], $params);

        $response = $this->makeRequest(
            $this->getApiUrl('businesses/search'),
            $params,
            'GET',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->keys['api_key'] ?? '',
                ],
            ]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBusiness(string $id): ?array
    {
        $response = $this->makeRequest(
            $this->getApiUrl('businesses/'.$id),
            [],
            'GET',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->keys['api_key'] ?? '',
                ],
            ]
        );

        return is_array($response) ? $response : null;
    }

    public function getUserData($identifier, &$socialCache): void
    {
        $business = $this->getBusiness($identifier);
        if (!$business) {
            return;
        }

        $socialCache['profile'] = [
            'name'        => $business['name'] ?? '',
            'url'         => $business['url'] ?? '',
            'phone'       => $business['display_phone'] ?? '',
            'rating'      => $business['rating'] ?? null,
            'reviewCount' => $business['review_count'] ?? null,
            'categories'  => array_map(
                static fn ($c) => $c['title'] ?? '',
                $business['categories'] ?? []
            ),
            'location'    => $business['location']['display_address'] ?? [],
            'image'       => $business['image_url'] ?? '',
        ];
        $socialCache['id'] = $business['id'] ?? $identifier;
    }

    public function getAvailableLeadFields(array $settings = []): array
    {
        return [
            'name'   => ['type' => 'string'],
            'phone'  => ['type' => 'string'],
            'url'    => ['type' => 'string'],
            'rating' => ['type' => 'string'],
        ];
    }

    public function getFormType()
    {
        return null;
    }

    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return [
                'Yelp Fusion API key authentication. Create an app at https://www.yelp.com/developers and paste the API key. Replaces the removed Foursquare integration for local business / place data.',
                'info',
            ];
        }

        return parent::getFormNotes($section);
    }
}
