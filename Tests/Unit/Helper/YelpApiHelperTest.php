<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\YelpApiHelper;
use PHPUnit\Framework\TestCase;
final class YelpApiHelperTest extends TestCase
{
    public function testUrlsSearchAndMap(): void
    {
        self::assertSame('https://api.yelp.com/v3/businesses/search', YelpApiHelper::searchUrl());
        self::assertSame('https://api.yelp.com/v3/businesses/abc', YelpApiHelper::businessUrl('abc'));
        $q = YelpApiHelper::buildSearchQuery('coffee', 'Berlin', 5);
        self::assertSame(5, $q['limit']);
        $m = YelpApiHelper::mapBusiness(['id' => '1', 'name' => 'Cafe', 'rating' => 4.0, 'categories' => [['title' => 'Coffee']], 'location' => ['display_address' => ['x']]]);
        self::assertSame('Cafe', $m['name']);
        self::assertSame(['Coffee'], $m['categories']);
    }
}
