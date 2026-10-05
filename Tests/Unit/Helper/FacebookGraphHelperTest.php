<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\FacebookGraphHelper;
use PHPUnit\Framework\TestCase;
final class FacebookGraphHelperTest extends TestCase
{
    public function testVersionIsV26(): void
    {
        self::assertSame('v26.0', FacebookGraphHelper::VERSION);
    }
    public function testUrl(): void
    {
        self::assertSame('https://graph.facebook.com/v26.0/me', FacebookGraphHelper::url('me'));
    }
}
