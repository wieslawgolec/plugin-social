<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\WeComApiHelper;
use PHPUnit\Framework\TestCase;
final class WeComApiHelperTest extends TestCase
{
    public function testTokenAndMessage(): void
    {
        $t = WeComApiHelper::buildTokenQuery('corp', 'secret');
        self::assertSame('corp', $t['corpid']);
        $m = WeComApiHelper::buildTextMessagePayload('user1,user2', 100, 'Hello');
        self::assertSame('user1|user2', $m['touser']);
        self::assertSame(100, $m['agentid']);
        self::assertSame('Hello', $m['text']['content']);
        $md = WeComApiHelper::buildMarkdownPayload('u1', 1, '**x**');
        self::assertSame('markdown', $md['msgtype']);
    }
    public function testMapAndSuccess(): void
    {
        $u = WeComApiHelper::mapUser(['userid' => 'u', 'name' => 'Name', 'mobile' => '1']);
        self::assertSame('Name', $u['name']);
        self::assertTrue(WeComApiHelper::isSuccess(['errcode' => 0]));
        self::assertFalse(WeComApiHelper::isSuccess(['errcode' => 60011]));
    }
}
