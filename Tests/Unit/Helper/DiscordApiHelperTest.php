<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\DiscordApiHelper;
use PHPUnit\Framework\TestCase;
final class DiscordApiHelperTest extends TestCase
{
    public function testWebhookUrl(): void
    {
        self::assertSame('https://discord.com/api/webhooks/1/abc', DiscordApiHelper::webhookUrl('1', 'abc'));
    }
    public function testParseWebhookUrl(): void
    {
        $parsed = DiscordApiHelper::parseWebhookUrl('https://discord.com/api/webhooks/99/token_here');
        self::assertSame('99', $parsed['id']);
        self::assertSame('token_here', $parsed['token']);
        self::assertNull(DiscordApiHelper::parseWebhookUrl('https://example.com'));
    }
    public function testChannelMessageUrl(): void
    {
        self::assertSame('https://discord.com/api/v10/channels/55/messages', DiscordApiHelper::channelMessageUrl('55'));
    }
}
