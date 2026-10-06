<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\TelegramApiHelper;
use PHPUnit\Framework\TestCase;
final class TelegramApiHelperTest extends TestCase
{
    public function testUrlsAndPayload(): void
    {
        self::assertSame('https://api.telegram.org/bot123:ABC/sendMessage', TelegramApiHelper::sendMessageUrl('123:ABC'));
        $p = TelegramApiHelper::buildSendMessagePayload(-1001, 'Hi', 'HTML');
        self::assertSame('-1001', $p['chat_id']);
        self::assertSame('HTML', $p['parse_mode']);
    }
    public function testResponseMaps(): void
    {
        $sent = TelegramApiHelper::mapSendMessageResult(['ok' => true, 'result' => ['message_id' => 9, 'chat' => ['id' => 1], 'text' => 'Hi', 'date' => 1]]);
        self::assertSame(9, $sent['message_id']);
        self::assertNull(TelegramApiHelper::mapSendMessageResult(['ok' => false]));
        $bot = TelegramApiHelper::mapBotInfo(['ok' => true, 'result' => ['id' => 1, 'username' => 'mybot', 'first_name' => 'Bot', 'is_bot' => true]]);
        self::assertSame('mybot', $bot['username']);
    }
}
