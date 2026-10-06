<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\RumbleApiHelper;
use PHPUnit\Framework\TestCase;
final class RumbleApiHelperTest extends TestCase
{
    public function testSlugAndUrls(): void
    {
        self::assertSame('mychannel', RumbleApiHelper::cleanChannelSlug('https://rumble.com/c-mychannel'));
        self::assertSame('mychannel', RumbleApiHelper::cleanChannelSlug('c-mychannel'));
        self::assertStringContainsString('c-mychannel', RumbleApiHelper::channelPageUrl('mychannel'));
    }
    public function testMetaAndMap(): void
    {
        $p = RumbleApiHelper::buildVideoMetaPayload('Title', 'Desc', 'public', 'chan');
        self::assertSame('Title', $p['title']);
        self::assertSame('chan', $p['channel']);
        $m = RumbleApiHelper::mapChannel(['id' => '1', 'slug' => 's', 'title' => 'T', 'subscribers' => 3]);
        self::assertSame('T', $m['name']);
        self::assertSame(3, $m['followers']);
    }
}
