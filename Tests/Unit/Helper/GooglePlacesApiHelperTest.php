<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\GooglePlacesApiHelper;
use PHPUnit\Framework\TestCase;
final class GooglePlacesApiHelperTest extends TestCase
{
    public function testUrlsAndSearchPayload(): void
    {
        self::assertSame('https://places.googleapis.com/v1/places:searchText', GooglePlacesApiHelper::searchTextUrl());
        self::assertSame('ChIJabc', GooglePlacesApiHelper::cleanPlaceId('places/ChIJabc'));
        $q = GooglePlacesApiHelper::buildSearchTextPayload('pizza', 5);
        self::assertSame('pizza', $q['textQuery']);
        self::assertSame(5, $q['maxResultCount']);
    }
    public function testMapPlace(): void
    {
        $m = GooglePlacesApiHelper::mapPlace([
            'id' => 'places/X', 'displayName' => ['text' => 'Cafe'], 'formattedAddress' => '1 Main',
            'rating' => 4.5, 'userRatingCount' => 12, 'location' => ['latitude' => 1.0, 'longitude' => 2.0],
        ]);
        self::assertSame('X', $m['id']);
        self::assertSame('Cafe', $m['name']);
        self::assertSame(4.5, $m['rating']);
        self::assertSame(1.0, $m['lat']);
    }
}
