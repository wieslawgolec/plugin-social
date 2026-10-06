<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\YouTubeApiHelper;
use PHPUnit\Framework\TestCase;
final class YouTubeApiHelperTest extends TestCase
{
    public function testUrlsAndClean(): void
    {
        self::assertSame('https://www.googleapis.com/youtube/v3/channels', YouTubeApiHelper::channelsUrl());
        self::assertSame('UCabc', YouTubeApiHelper::cleanChannelId('https://www.youtube.com/channel/UCabc'));
        self::assertSame('MyHandle', YouTubeApiHelper::cleanChannelId('https://www.youtube.com/@MyHandle'));
    }
    public function testChannelQueryAndMap(): void
    {
        $q = YouTubeApiHelper::buildChannelsQuery('UCxxxxxxxxxxxxxxxxxxxxxx');
        self::assertArrayHasKey('id', $q);
        $h = YouTubeApiHelper::buildChannelsQuery('@SomeHandle');
        self::assertSame('SomeHandle', $h['forHandle']);
        $m = YouTubeApiHelper::mapChannel(['id' => 'UC1', 'snippet' => ['title' => 'T', 'description' => 'D', 'thumbnails' => ['default' => ['url' => 'u']]], 'statistics' => ['subscriberCount' => '10', 'videoCount' => '2', 'viewCount' => '100']]);
        self::assertSame(10, $m['subscribers']);
        self::assertSame('T', $m['name']);
    }
}
