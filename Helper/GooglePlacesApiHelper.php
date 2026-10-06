<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class GooglePlacesApiHelper
{
    public const API_BASE = 'https://places.googleapis.com/v1';
    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }
    public static function searchTextUrl(): string { return self::url('places:searchText'); }
    public static function placeDetailsUrl(string $placeId): string
    {
        $placeId = str_starts_with($placeId, 'places/') ? $placeId : 'places/'.$placeId;
        return self::url($placeId);
    }
    public static function cleanPlaceId(string $placeId): string
    {
        return preg_replace('#^places/#', '', trim($placeId)) ?? trim($placeId);
    }
    public static function buildSearchTextPayload(string $query, int $maxResults = 10): array
    {
        return ['textQuery' => $query, 'maxResultCount' => max(1, min(20, $maxResults))];
    }
    public static function defaultFieldMask(): string
    {
        return 'places.id,places.displayName,places.formattedAddress,places.location,places.rating,places.userRatingCount,places.websiteUri,places.nationalPhoneNumber';
    }
    public static function mapPlace(array $place): array
    {
        $name = $place['displayName']['text'] ?? ($place['displayName'] ?? '');
        if (is_array($name)) {
            $name = $name['text'] ?? '';
        }
        return [
            'id' => self::cleanPlaceId((string) ($place['id'] ?? ($place['name'] ?? ''))),
            'name' => (string) $name,
            'address' => $place['formattedAddress'] ?? '',
            'rating' => $place['rating'] ?? null,
            'reviewCount' => $place['userRatingCount'] ?? null,
            'phone' => $place['nationalPhoneNumber'] ?? '',
            'url' => $place['websiteUri'] ?? '',
            'lat' => $place['location']['latitude'] ?? null,
            'lng' => $place['location']['longitude'] ?? null,
        ];
    }
}
