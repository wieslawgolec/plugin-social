<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\XApiV2Client;
use PHPUnit\Framework\TestCase;
final class XApiV2ClientTest extends TestCase
{
    public function testApiUrlMapsLegacyStatusesUpdate(): void
    {
        self::assertSame('https://api.x.com/2/tweets', XApiV2Client::apiUrl('statuses/update'));
        self::assertSame('https://api.x.com/2/tweets', XApiV2Client::apiUrl('tweets'));
        self::assertSame('https://api.x.com/2/tweets/search/recent', XApiV2Client::apiUrl('tweets/search/recent'));
    }
    public function testCleanIdentifier(): void
    {
        self::assertSame('mautic', XApiV2Client::cleanIdentifier('@mautic'));
        self::assertSame('mautic', XApiV2Client::cleanIdentifier('https://x.com/mautic'));
        self::assertSame('mautic', XApiV2Client::cleanIdentifier('https://twitter.com/mautic/status/1'));
    }
    public function testSearchQueries(): void
    {
        self::assertSame('#mautic -is:retweet', XApiV2Client::buildSearchQueryForHashtag('mautic'));
        self::assertSame('@mautic -is:retweet', XApiV2Client::buildSearchQueryForMention('@mautic'));
    }
    public function testConstants(): void
    {
        self::assertSame('https://api.x.com/2', XApiV2Client::API_BASE);
        self::assertStringContainsString('tweet.write', XApiV2Client::DEFAULT_SCOPES);
    }
}
