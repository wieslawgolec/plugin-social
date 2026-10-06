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

    public static function searchTextUrl(): string
    {
        return self::url('places:searchText');
    }

    public static function placeDetailsUrl(string $placeId): string
    {
        $placeId = str_starts_with($placeId, 'places/') ? $placeId : 'places/'.$placeId;

        return self::url($placeId);
    }

    public static function cleanPlaceId(string $placeId): string
    {
        return preg_replace('#^places/#', '', trim($placeId)) ?? trim($placeId);
    }
}
