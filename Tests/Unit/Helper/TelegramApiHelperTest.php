<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\TelegramApiHelper;
use PHPUnit\Framework\TestCase;
final class TelegramApiHelperTest extends TestCase
{
    public function testMethodUrl(): void
    {
        self::assertSame('https://api.telegram.org/bot123:ABC/sendMessage', TelegramApiHelper::sendMessageUrl('123:ABC'));
        self::assertSame('https://api.telegram.org/bot123:ABC/getMe', TelegramApiHelper::getMeUrl('123:ABC'));
        self::assertSame('-1001', TelegramApiHelper::cleanChatId(-1001));
    }
}
