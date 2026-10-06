<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\MastodonApiHelper;
use PHPUnit\Framework\TestCase;
final class MastodonApiHelperTest extends TestCase
{
    public function testApiAndOauthUrls(): void
    {
        self::assertSame('https://mastodon.social/api/v1/statuses', MastodonApiHelper::apiUrl('https://mastodon.social', 'statuses'));
        self::assertSame('https://example.social/api/v1/accounts/verify_credentials', MastodonApiHelper::apiUrl('example.social', 'accounts/verify_credentials'));
        self::assertSame('https://mastodon.social/oauth/authorize', MastodonApiHelper::oauthAuthorizeUrl('mastodon.social'));
        self::assertSame('https://mastodon.social/oauth/token', MastodonApiHelper::oauthTokenUrl('https://mastodon.social/'));
    }
    public function testHandleAndAcct(): void
    {
        self::assertSame('alice', MastodonApiHelper::cleanHandle('@alice'));
        self::assertSame(['alice', 'example.social'], MastodonApiHelper::parseAcct('@alice@example.social'));
        self::assertSame(['bob', null], MastodonApiHelper::parseAcct('bob'));
    }
    public function testStatusPayloadAndProfileMap(): void
    {
        $p = MastodonApiHelper::buildStatusPayload('Hello', 'unlisted');
        self::assertSame('Hello', $p['status']);
        self::assertSame('unlisted', $p['visibility']);
        $profile = MastodonApiHelper::mapAccountToProfile([
            'id' => '1', 'acct' => 'alice', 'display_name' => 'Alice', 'note' => '<p>Hi</p>',
            'url' => 'https://x', 'avatar' => 'https://img', 'followers_count' => 3, 'following_count' => 2,
        ]);
        self::assertSame('alice', $profile['profileHandle']);
        self::assertSame('Hi', $profile['description']);
        self::assertSame(3, $profile['followers']);
    }
}
