<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\TwitchApiHelper;
use PHPUnit\Framework\TestCase;
final class TwitchApiHelperTest extends TestCase
{
    public function testUrlsAndCleanLogin(): void
    {
        self::assertSame('https://api.twitch.tv/helix/users', TwitchApiHelper::usersUrl());
        self::assertSame('shroud', TwitchApiHelper::cleanLogin('@Shroud'));
        self::assertSame('shroud', TwitchApiHelper::cleanLogin('https://www.twitch.tv/shroud'));
    }
    public function testPayloadsAndMap(): void
    {
        $q = TwitchApiHelper::buildUsersQuery('Shroud');
        self::assertSame('shroud', $q['login']);
        $s = TwitchApiHelper::buildSearchChannelsQuery('fps', 5);
        self::assertSame(5, $s['first']);
        $c = TwitchApiHelper::buildChatMessagePayload('1', '2', 'Hi');
        self::assertSame('Hi', $c['message']);
        $m = TwitchApiHelper::mapUser(['id' => '1', 'login' => 'x', 'display_name' => 'X', 'description' => 'd']);
        self::assertSame('x', $m['profileHandle']);
        self::assertSame('https://www.twitch.tv/x', $m['url']);
    }
}
