<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\YouTubeApiHelper;
use PHPUnit\Framework\TestCase;
final class YouTubeApiHelperTest extends TestCase
{
    public function testUrls(): void
    {
        self::assertSame('https://www.googleapis.com/youtube/v3/channels', YouTubeApiHelper::channelsUrl());
        self::assertSame('https://www.googleapis.com/youtube/v3/search', YouTubeApiHelper::searchUrl());
    }
    public function testCleanChannelId(): void
    {
        self::assertSame('UCabc', YouTubeApiHelper::cleanChannelId('https://www.youtube.com/channel/UCabc'));
        self::assertSame('MyHandle', YouTubeApiHelper::cleanChannelId('https://www.youtube.com/@MyHandle'));
    }
}
