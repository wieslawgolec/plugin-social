<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\BlueskyApiHelper;
use PHPUnit\Framework\TestCase;
final class BlueskyApiHelperTest extends TestCase
{
    public function testXrpcUrl(): void
    {
        self::assertSame('https://bsky.social/xrpc/com.atproto.server.createSession', BlueskyApiHelper::createSessionUrl());
        self::assertStringContainsString('app.bsky.feed.post', BlueskyApiHelper::postRecordType());
    }
    public function testCleanHandle(): void
    {
        self::assertSame('user.bsky.social', BlueskyApiHelper::cleanHandle('@User.Bsky.Social'));
    }
}
