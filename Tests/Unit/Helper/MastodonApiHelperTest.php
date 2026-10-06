<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\MastodonApiHelper;
use PHPUnit\Framework\TestCase;
final class MastodonApiHelperTest extends TestCase
{
    public function testApiUrl(): void
    {
        self::assertSame('https://mastodon.social/api/v1/statuses', MastodonApiHelper::apiUrl('https://mastodon.social', 'statuses'));
        self::assertSame('https://example.social/api/v1/accounts/verify_credentials', MastodonApiHelper::apiUrl('example.social', 'accounts/verify_credentials'));
    }
    public function testCleanHandle(): void
    {
        self::assertSame('alice', MastodonApiHelper::cleanHandle('@alice'));
        self::assertSame('alice@example.social', MastodonApiHelper::cleanHandle('@alice@example.social'));
    }
    public function testParseAcct(): void
    {
        self::assertSame(['alice', 'example.social'], MastodonApiHelper::parseAcct('@alice@example.social'));
        self::assertSame(['bob', null], MastodonApiHelper::parseAcct('bob'));
    }
}
