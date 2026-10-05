<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Helper;
final class FacebookGraphHelper
{
    public const VERSION = 'v26.0';
    public const GRAPH_BASE = 'https://graph.facebook.com';
    public static function url(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        if (!str_starts_with($endpoint, 'v')) {
            $endpoint = self::VERSION.'/'.$endpoint;
        }
        return self::GRAPH_BASE.'/'.$endpoint;
    }
}
