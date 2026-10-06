<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\PinterestApiHelper;
use PHPUnit\Framework\TestCase;
final class PinterestApiHelperTest extends TestCase
{
    public function testUrlsAndPinPayload(): void
    {
        self::assertSame('https://api.pinterest.com/v5/pins', PinterestApiHelper::pinsUrl());
        $p = PinterestApiHelper::buildCreatePinPayload('b1', 'https://img', 'Title', 'Desc');
        self::assertSame('b1', $p['board_id']);
        self::assertSame('image_url', $p['media_source']['source_type']);
        self::assertSame('Title', $p['title']);
    }
    public function testMapUser(): void
    {
        $u = PinterestApiHelper::mapUserAccount(['id' => '1', 'username' => 'maker', 'about' => 'x', 'follower_count' => 5]);
        self::assertSame('maker', $u['profileHandle']);
        self::assertSame(5, $u['followers']);
    }
}
