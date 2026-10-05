<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Tests\Unit\Integration;
use PHPUnit\Framework\TestCase;
final class FoursquareRemovedTest extends TestCase
{
    public function testFoursquareFileRemoved(): void
    {
        $path = dirname(__DIR__, 3).'/Integration/FoursquareIntegration.php';
        self::assertFileDoesNotExist($path);
    }
}
