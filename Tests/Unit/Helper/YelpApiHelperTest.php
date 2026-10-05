<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\YelpApiHelper;
use PHPUnit\Framework\TestCase;
final class YelpApiHelperTest extends TestCase
{
    public function testUrl(): void
    {
        self::assertSame('https://api.yelp.com/v3/businesses/search', YelpApiHelper::url('businesses/search'));
    }
}
