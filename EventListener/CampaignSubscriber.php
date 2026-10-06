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

        foreach ([
            ['Telegram', 'telegram.send', 'mautic.social.telegram.send', 'social.telegram'],
            ['Discord', 'discord.send', 'mautic.social.discord.send', 'social.discord'],
            ['Mastodon', 'mastodon.post', 'mautic.social.mastodon.post', 'social.mastodon'],
            ['Bluesky', 'bluesky.post', 'mautic.social.bluesky.post', 'social.bluesky'],
            ['Reddit', 'reddit.submit', 'mautic.social.reddit.submit', 'social.reddit'],
            ['WhatsApp', 'whatsapp.send', 'mautic.social.whatsapp.send', 'social.whatsapp'],
            ['LinkedIn', 'linkedin.post', 'mautic.social.linkedin.post', 'social.linkedin'],
            ['WeChat', 'wechat.send', 'mautic.social.wechat.send', 'social.wechat'],
            ['WeCom', 'wecom.send', 'mautic.social.wecom.send', 'social.wecom'],
        ] as [$name, $key, $label, $channel]) {
            $integration = $this->integrationHelper->getIntegrationObject($name);
            if (!$integration || !$integration->getIntegrationSettings()->isPublished()) {
                continue;
            }
            $event->addAction($key, [
                'label' => $label,
                'description' => $label.'_desc',
                'eventName' => SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType' => SocialMessageSendType::class,
                'channel' => $channel,
            ]);
        }
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
            'bluesky.post' => $this->campaignEventHelper->sendBlueskyAction($lead, $ev),
            'reddit.submit' => $this->campaignEventHelper->sendRedditAction($lead, $ev),
            'whatsapp.send' => $this->campaignEventHelper->sendWhatsAppAction($lead, $ev),
            'linkedin.post' => $this->campaignEventHelper->sendLinkedInAction($lead, $ev),
            'wechat.send' => $this->campaignEventHelper->sendWeChatAction($lead, $ev),
            'wecom.send' => $this->campaignEventHelper->sendWeComAction($lead, $ev),
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
            'bluesky.post' => 'social.bluesky',
            'reddit.submit' => 'social.reddit',
            'whatsapp.send' => 'social.whatsapp',
            'linkedin.post' => 'social.linkedin',
            'wechat.send' => 'social.wechat',
            'wecom.send' => 'social.wecom',
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
