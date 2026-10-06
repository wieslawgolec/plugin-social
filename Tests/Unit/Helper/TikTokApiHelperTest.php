<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\TikTokApiHelper;
use PHPUnit\Framework\TestCase;
final class TikTokApiHelperTest extends TestCase
{
    public function testUrlsAndQueries(): void
    {
        self::assertSame('https://open.tiktokapis.com/v2/user/info/', TikTokApiHelper::userInfoUrl());
        $q = TikTokApiHelper::buildUserInfoQuery(['open_id', 'display_name']);
        self::assertSame('open_id,display_name', $q['fields']);
        $v = TikTokApiHelper::buildVideoListPayload(10, 5);
        self::assertSame(10, $v['max_count']);
        self::assertSame(5, $v['cursor']);
    }
    public function testMapUser(): void
    {
        $m = TikTokApiHelper::mapUser(['data' => ['user' => [
            'open_id' => 'oid', 'display_name' => 'Creator', 'follower_count' => 9, 'is_verified' => true,
        ]]]);
        self::assertSame('oid', $m['id']);
        self::assertSame('Creator', $m['name']);
        self::assertSame(9, $m['followers']);
        self::assertTrue($m['verified']);
    }
}
