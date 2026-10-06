<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Helper;

final class BlueskyApiHelper
{
    public const DEFAULT_PDS = 'https://bsky.social';
    public const PUBLIC_API = 'https://public.api.bsky.app';

    public static function xrpcUrl(string $pdsBase, string $nsid): string
    {
        $base = rtrim($pdsBase !== '' ? $pdsBase : self::DEFAULT_PDS, '/');

        return $base.'/xrpc/'.$nsid;
    }

    public static function createSessionUrl(string $pdsBase = ''): string
    {
        return self::xrpcUrl($pdsBase, 'com.atproto.server.createSession');
    }

    public static function createRecordUrl(string $pdsBase = ''): string
    {
        return self::xrpcUrl($pdsBase, 'com.atproto.repo.createRecord');
    }

    public static function getProfileUrl(string $pdsBase = ''): string
    {
        return self::xrpcUrl($pdsBase !== '' ? $pdsBase : self::PUBLIC_API, 'app.bsky.actor.getProfile');
    }

    public static function cleanHandle(string $handle): string
    {
        $handle = trim($handle);
        if (str_starts_with($handle, '@')) {
            $handle = substr($handle, 1);
        }

        return strtolower($handle);
    }

    public static function postRecordType(): string
    {
        return 'app.bsky.feed.post';
    }
}
