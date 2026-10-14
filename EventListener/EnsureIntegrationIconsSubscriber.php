<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\EventListener;

use MauticPlugin\MauticSocialBundle\Helper\IntegrationIcons;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Writes missing integration PNG icons (128x128) into Assets/img on first request.
 * Does not overwrite existing icons (facebook, twitter, instagram, linkedin, foursquare).
 */
final class EnsureIntegrationIconsSubscriber implements EventSubscriberInterface
{
    private static bool $done = false;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 255],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (self::$done || !$event->isMainRequest()) {
            return;
        }
        self::$done = true;

        $imgDir = dirname(__DIR__).'/Assets/img';
        try {
            IntegrationIcons::installMissing($imgDir);
        } catch (\Throwable) {
            // non-fatal if filesystem is read-only
        }
    }
}
