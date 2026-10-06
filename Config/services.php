<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Helper/CampaignEventHelper.php',
        'Helper/TwitterCommandHelper.php',
    ];

    $services->load('MauticPlugin\\MauticSocialBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MauticPlugin\\MauticSocialBundle\\Entity\\', '../Entity/*Repository.php');

    $services->set('mautic.social.helper.campaign', MauticPlugin\MauticSocialBundle\Helper\CampaignEventHelper::class);
    $services->set('mautic.social.helper.twitter_command', MauticPlugin\MauticSocialBundle\Helper\TwitterCommandHelper::class);

    $services->set('mautic.integration.facebook', MauticPlugin\MauticSocialBundle\Integration\FacebookIntegration::class);
    $services->set('mautic.integration.instagram', MauticPlugin\MauticSocialBundle\Integration\InstagramIntegration::class);
    $services->set('mautic.integration.twitter', MauticPlugin\MauticSocialBundle\Integration\TwitterIntegration::class);
    $services->set('mautic.integration.yelp', MauticPlugin\MauticSocialBundle\Integration\YelpIntegration::class);
    $services->set('mautic.integration.mastodon', MauticPlugin\MauticSocialBundle\Integration\MastodonIntegration::class);
    $services->set('mautic.integration.bluesky', MauticPlugin\MauticSocialBundle\Integration\BlueskyIntegration::class);
    $services->set('mautic.integration.googleplaces', MauticPlugin\MauticSocialBundle\Integration\GooglePlacesIntegration::class);
    $services->set('mautic.integration.reddit', MauticPlugin\MauticSocialBundle\Integration\RedditIntegration::class);
    $services->set('mautic.integration.telegram', MauticPlugin\MauticSocialBundle\Integration\TelegramIntegration::class);
    $services->set('mautic.integration.youtube', MauticPlugin\MauticSocialBundle\Integration\YouTubeIntegration::class);
    $services->set('mautic.integration.pinterest', MauticPlugin\MauticSocialBundle\Integration\PinterestIntegration::class);
    $services->set('mautic.integration.discord', MauticPlugin\MauticSocialBundle\Integration\DiscordIntegration::class);
    $services->set('mautic.integration.whatsapp', MauticPlugin\MauticSocialBundle\Integration\WhatsAppIntegration::class);
    $services->set('mautic.integration.linkedin', MauticPlugin\MauticSocialBundle\Integration\LinkedInIntegration::class);
    $services->set('mautic.integration.wechat', MauticPlugin\MauticSocialBundle\Integration\WeChatIntegration::class);
    $services->set('mautic.integration.wecom', MauticPlugin\MauticSocialBundle\Integration\WeComIntegration::class);

    $services->alias('mautic.social.repository.lead', MauticPlugin\MauticSocialBundle\Entity\LeadRepository::class);
    $services->alias('mautic.social.model.monitoring', MauticPlugin\MauticSocialBundle\Model\MonitoringModel::class);
    $services->alias('mautic.social.model.postcount', MauticPlugin\MauticSocialBundle\Model\PostCountModel::class);
    $services->alias('mautic.social.model.tweet', MauticPlugin\MauticSocialBundle\Model\TweetModel::class);
};
