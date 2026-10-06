<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Helper;
use MauticPlugin\MauticSocialBundle\Helper\LinkedInApiHelper;
use PHPUnit\Framework\TestCase;
final class LinkedInApiHelperTest extends TestCase
{
    public function testUrlsAndUrn(): void
    {
        self::assertSame('https://api.linkedin.com/rest/posts', LinkedInApiHelper::postsUrl());
        self::assertSame('urn:li:person:abc', LinkedInApiHelper::cleanPersonUrn('abc'));
        self::assertSame('urn:li:person:abc', LinkedInApiHelper::cleanPersonUrn('urn:li:person:abc'));
        self::assertSame('urn:li:organization:1', LinkedInApiHelper::cleanPersonUrn('urn:li:organization:1'));
    }
    public function testPostPayloadAndProfile(): void
    {
        $p = LinkedInApiHelper::buildTextPostPayload('abc', 'Hello LI');
        self::assertSame('urn:li:person:abc', $p['author']);
        self::assertSame('Hello LI', $p['commentary']);
        self::assertSame('PUBLISHED', $p['lifecycleState']);
        $m = LinkedInApiHelper::mapProfile(['sub' => 'x', 'given_name' => 'A', 'family_name' => 'B', 'email' => 'a@b.c']);
        self::assertSame('A B', $m['name']);
        self::assertSame('x', $m['id']);
    }
}
