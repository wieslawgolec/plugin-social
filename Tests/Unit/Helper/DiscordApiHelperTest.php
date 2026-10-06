<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\DiscordApiHelper;
use PHPUnit\Framework\TestCase;
final class DiscordApiHelperTest extends TestCase
{
    public function testWebhookParseAndUrls(): void
    {
        self::assertSame('https://discord.com/api/webhooks/1/abc', DiscordApiHelper::webhookUrl('1', 'abc'));
        $parsed = DiscordApiHelper::parseWebhookUrl('https://discord.com/api/webhooks/99/token_here');
        self::assertSame(['id' => '99', 'token' => 'token_here'], $parsed);
        self::assertNull(DiscordApiHelper::parseWebhookUrl('https://example.com'));
        self::assertSame('https://discord.com/api/v10/channels/55/messages', DiscordApiHelper::channelMessageUrl('55'));
    }
    public function testPayloads(): void
    {
        $w = DiscordApiHelper::buildWebhookPayload('Hello', 'Bot', 'Title', 'Body');
        self::assertSame('Hello', $w['content']);
        self::assertSame('Bot', $w['username']);
        self::assertSame('Title', $w['embeds'][0]['title']);
        $c = DiscordApiHelper::buildChannelMessagePayload('msg');
        self::assertSame('msg', $c['content']);
    }
}
