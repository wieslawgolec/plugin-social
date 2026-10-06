<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\WhatsAppApiHelper;
use PHPUnit\Framework\TestCase;
final class WhatsAppApiHelperTest extends TestCase
{
    public function testUrlsAndPhoneClean(): void
    {
        self::assertSame('https://graph.facebook.com/v26.0/123/messages', WhatsAppApiHelper::messagesUrl('123'));
        self::assertSame('15551234567', WhatsAppApiHelper::cleanPhone('+1 (555) 123-4567'));
    }
    public function testTextPayload(): void
    {
        $p = WhatsAppApiHelper::buildTextMessagePayload('+15551234567', 'Hello');
        self::assertSame('whatsapp', $p['messaging_product']);
        self::assertSame('15551234567', $p['to']);
        self::assertSame('text', $p['type']);
        self::assertSame('Hello', $p['text']['body']);
    }
    public function testTemplatePayloadAndMap(): void
    {
        $p = WhatsAppApiHelper::buildTemplatePayload('1555', 'hello_world', 'en_US', ['Alice']);
        self::assertSame('template', $p['type']);
        self::assertSame('hello_world', $p['template']['name']);
        self::assertSame('Alice', $p['template']['components'][0]['parameters'][0]['text']);
        $mapped = WhatsAppApiHelper::mapSendResult(['messages' => [['id' => 'wamid.xxx']], 'contacts' => [['wa_id' => '1555']]]);
        self::assertSame('wamid.xxx', $mapped['message_id']);
        self::assertNull(WhatsAppApiHelper::mapSendResult([]));
    }
}
