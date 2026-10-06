<?php

namespace MauticPlugin\MauticSocialBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticSocialBundle\Form\Type\SocialMessageSendType;
use MauticPlugin\MauticSocialBundle\Form\Type\TweetSendType;
use MauticPlugin\MauticSocialBundle\Helper\CampaignEventHelper;
use MauticPlugin\MauticSocialBundle\SocialEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CampaignEventHelper $campaignEventHelper,
        private IntegrationHelper $integrationHelper,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $twitter = $this->integrationHelper->getIntegrationObject('Twitter');
        if ($twitter && $twitter->getIntegrationSettings()->isPublished()) {
            $event->addAction('twitter.tweet', [
                'label' => 'mautic.social.twitter.tweet.event.open',
                'description' => 'mautic.social.twitter.tweet.event.open_desc',
                'eventName' => SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_channelId'],
                'formType' => TweetSendType::class,
                'channel' => 'social.tweet',
                'channelIdField' => 'channelId',
            ]);
        }

        $this->addMessageAction($event, 'Telegram', 'telegram.send', 'mautic.social.telegram.send', 'social.telegram');
        $this->addMessageAction($event, 'Discord', 'discord.send', 'mautic.social.discord.send', 'social.discord');
        $this->addMessageAction($event, 'Mastodon', 'mastodon.post', 'mautic.social.mastodon.post', 'social.mastodon');
    }

    private function addMessageAction(CampaignBuilderEvent $event, string $integrationName, string $actionKey, string $labelKey, string $channel): void
    {
        $integration = $this->integrationHelper->getIntegrationObject($integrationName);
        if (!$integration || !$integration->getIntegrationSettings()->isPublished()) {
            return;
        }

        $event->addAction($actionKey, [
            'label' => $labelKey,
            'description' => $labelKey.'_desc',
            'eventName' => SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'formType' => SocialMessageSendType::class,
            'channel' => $channel,
        ]);
    }

    public function onCampaignAction(CampaignExecutionEvent $event): void
    {
        $type = $event->getEvent()['type'] ?? '';
        $lead = $event->getLead();
        $ev = $event->getEvent();

        $result = match ($type) {
            'twitter.tweet' => $this->campaignEventHelper->sendTweetAction($lead, $ev),
            'telegram.send' => $this->campaignEventHelper->sendTelegramAction($lead, $ev),
            'discord.send' => $this->campaignEventHelper->sendDiscordAction($lead, $ev),
            'mastodon.post' => $this->campaignEventHelper->sendMastodonAction($lead, $ev),
            default => null,
        };

        if (null === $result) {
            return;
        }

        $channel = match ($type) {
            'twitter.tweet' => 'social.twitter',
            'telegram.send' => 'social.telegram',
            'discord.send' => 'social.discord',
            'mastodon.post' => 'social.mastodon',
            default => 'social',
        };
        $event->setChannel($channel);

        if (is_array($result) && empty($result['failed'])) {
            $event->setResult($result);

            return;
        }

        if (false === $result) {
            $event->setFailed($this->translator->trans('mautic.social.campaign.error.no_handle'));

            return;
        }

        $reason = is_array($result) ? ($result['reason'] ?? $result['response'] ?? 'failed') : 'failed';
        $event->setFailed(is_string($reason) ? $reason : json_encode($reason));
    }
}
