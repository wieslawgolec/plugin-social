<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class YelpApiHelper
{
    public const API_BASE = 'https://api.yelp.com/v3';
    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }
    public static function searchUrl(): string { return self::url('businesses/search'); }
    public static function businessUrl(string $id): string
    {
        return self::url('businesses/'.rawurlencode($id));
    }
    public static function buildSearchQuery(string $term, string $location, int $limit = 20): array
    {
        return ['term' => $term, 'location' => $location, 'limit' => max(1, min(50, $limit))];
    }
    public static function mapBusiness(array $business): array
    {
        return [
            'id' => $business['id'] ?? '',
            'name' => $business['name'] ?? '',
            'url' => $business['url'] ?? '',
            'phone' => $business['display_phone'] ?? ($business['phone'] ?? ''),
            'rating' => $business['rating'] ?? null,
            'reviewCount' => $business['review_count'] ?? null,
            'image' => $business['image_url'] ?? '',
            'categories' => array_values(array_filter(array_map(
                static fn ($c) => is_array($c) ? ($c['title'] ?? '') : '',
                $business['categories'] ?? []
            ))),
            'address' => $business['location']['display_address'] ?? [],
        ];
    }
}
