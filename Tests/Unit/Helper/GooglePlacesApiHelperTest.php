<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\GooglePlacesApiHelper;
use PHPUnit\Framework\TestCase;
final class GooglePlacesApiHelperTest extends TestCase
{
    public function testUrls(): void
    {
        self::assertSame('https://places.googleapis.com/v1/places:searchText', GooglePlacesApiHelper::searchTextUrl());
        self::assertSame('https://places.googleapis.com/v1/places/ChIJabc', GooglePlacesApiHelper::placeDetailsUrl('ChIJabc'));
        self::assertSame('ChIJabc', GooglePlacesApiHelper::cleanPlaceId('places/ChIJabc'));
    }
}
