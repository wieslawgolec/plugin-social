<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\WeChatApiHelper;
use PHPUnit\Framework\TestCase;
final class WeChatApiHelperTest extends TestCase
{
    public function testTokenAndMessagePayloads(): void
    {
        $t = WeChatApiHelper::buildTokenQuery('id', 'sec');
        self::assertSame('client_credential', $t['grant_type']);
        $m = WeChatApiHelper::buildTextMessagePayload('openid1', 'Hi');
        self::assertSame('text', $m['msgtype']);
        self::assertSame('Hi', $m['text']['content']);
        $tpl = WeChatApiHelper::buildTemplatePayload('o1', 'tpl', ['first' => ['value' => 'v']], 'https://x');
        self::assertSame('tpl', $tpl['template_id']);
        self::assertSame('https://x', $tpl['url']);
    }
    public function testMapAndSuccess(): void
    {
        $u = WeChatApiHelper::mapUser(['openid' => 'o', 'nickname' => 'N', 'headimgurl' => 'h']);
        self::assertSame('N', $u['name']);
        self::assertTrue(WeChatApiHelper::isSuccess(['errcode' => 0]));
        self::assertFalse(WeChatApiHelper::isSuccess(['errcode' => 40001]));
    }
}
