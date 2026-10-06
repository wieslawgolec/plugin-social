<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\RedditApiHelper;
use PHPUnit\Framework\TestCase;
final class RedditApiHelperTest extends TestCase
{
    public function testCleanersAndEndpoints(): void
    {
        self::assertSame('spez', RedditApiHelper::cleanUsername('u/spez'));
        self::assertSame('php', RedditApiHelper::cleanSubreddit('r/php'));
        self::assertSame('https://oauth.reddit.com/user/spez/about', RedditApiHelper::apiUrl(RedditApiHelper::userAboutEndpoint('u/spez')));
        self::assertStringContainsString('access_token', RedditApiHelper::TOKEN_URL);
    }
    public function testSubmitPayloadAndUserMap(): void
    {
        $p = RedditApiHelper::buildSubmitPayload('r/test', 'Title', 'self', 'Body');
        self::assertSame('test', $p['sr']);
        self::assertSame('Body', $p['text']);
        $link = RedditApiHelper::buildSubmitPayload('news', 'T', 'link', 'https://example.com');
        self::assertSame('https://example.com', $link['url']);
        $u = RedditApiHelper::mapUserAbout(['data' => ['id' => 't2_1', 'name' => 'spez', 'link_karma' => 1, 'comment_karma' => 2, 'icon_img' => 'x']]);
        self::assertSame(3, $u['karma']);
        self::assertSame('spez', $u['profileHandle']);
    }
}
