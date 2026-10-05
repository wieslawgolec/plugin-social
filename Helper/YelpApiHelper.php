<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Helper;
final class YelpApiHelper
{
    public const API_BASE = 'https://api.yelp.com/v3';
    public static function url(string $endpoint): string
    {
        return self::API_BASE.'/'.ltrim($endpoint, '/');
    }
}
