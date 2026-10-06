<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\RedditApiHelper;
use PHPUnit\Framework\TestCase;
final class RedditApiHelperTest extends TestCase
{
    public function testCleanUsernameAndSubreddit(): void
    {
        self::assertSame('spez', RedditApiHelper::cleanUsername('u/spez'));
        self::assertSame('php', RedditApiHelper::cleanSubreddit('r/php'));
        self::assertSame('https://oauth.reddit.com/user/spez/about', RedditApiHelper::apiUrl(RedditApiHelper::userAboutEndpoint('u/spez')));
    }
    public function testAuthEndpoints(): void
    {
        self::assertStringContainsString('reddit.com', RedditApiHelper::AUTH_URL);
        self::assertStringContainsString('access_token', RedditApiHelper::TOKEN_URL);
    }
}
