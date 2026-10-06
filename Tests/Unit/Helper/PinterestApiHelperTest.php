<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\PinterestApiHelper;
use PHPUnit\Framework\TestCase;
final class PinterestApiHelperTest extends TestCase
{
    public function testUrls(): void
    {
        self::assertSame('https://api.pinterest.com/v5/user_account', PinterestApiHelper::userAccountUrl());
        self::assertSame('https://api.pinterest.com/v5/pins', PinterestApiHelper::pinsUrl());
        self::assertSame('maker', PinterestApiHelper::cleanUsername('@maker'));
    }
}
