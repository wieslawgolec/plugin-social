<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\BlueskyApiHelper;
use PHPUnit\Framework\TestCase;
final class BlueskyApiHelperTest extends TestCase
{
    public function testUrlsAndHandle(): void
    {
        self::assertSame('https://bsky.social/xrpc/com.atproto.server.createSession', BlueskyApiHelper::createSessionUrl());
        self::assertSame('user.bsky.social', BlueskyApiHelper::cleanHandle('@User.Bsky.Social'));
        self::assertSame('app.bsky.feed.post', BlueskyApiHelper::postRecordType());
    }
    public function testSessionAndPostPayloads(): void
    {
        $s = BlueskyApiHelper::buildSessionPayload('@me.bsky.social', 'pass');
        self::assertSame('me.bsky.social', $s['identifier']);
        $post = BlueskyApiHelper::buildPostRecord('did:plc:abc', 'Hello Bluesky', '2026-01-01T00:00:00.000Z');
        self::assertSame('did:plc:abc', $post['repo']);
        self::assertSame('Hello Bluesky', $post['record']['text']);
        self::assertSame('app.bsky.feed.post', $post['record']['$type']);
    }
    public function testMapProfile(): void
    {
        $p = BlueskyApiHelper::mapProfile(['did' => 'did:1', 'handle' => 'a.bsky.social', 'displayName' => 'A', 'followersCount' => 10]);
        self::assertSame('a.bsky.social', $p['profileHandle']);
        self::assertSame(10, $p['followers']);
    }
}
