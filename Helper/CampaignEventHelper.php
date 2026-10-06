<?php

namespace MauticPlugin\MauticSocialBundle\Helper;

use Mautic\AssetBundle\Helper\TokenHelper as AssetTokenHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;
use Mautic\PageBundle\Entity\Trackable;
use Mautic\PageBundle\Helper\TokenHelper as PageTokenHelper;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticSocialBundle\Model\TweetModel;

final class CampaignEventHelper
{
    private array $clickthrough = [];

    public function __construct(
        private readonly IntegrationHelper $integrationHelper,
        private readonly TrackableModel $trackableModel,
        private readonly PageTokenHelper $pageTokenHelper,
        private readonly AssetTokenHelper $assetTokenHelper,
        private readonly TweetModel $tweetModel,
    ) {
    }

    public function sendTweetAction(Lead $lead, array $event): array|false
    {
        $tweetEntity = $this->tweetModel->getEntity($event['channelId'] ?? null);
        if (!$tweetEntity) {
            return ['failed' => 1, 'response' => 'Tweet entity not found'];
        }
        $twitterIntegration = $this->integrationHelper->getIntegrationObject('Twitter');
        if (!$twitterIntegration) {
            return ['failed' => 1, 'response' => 'Twitter integration unavailable'];
        }
        $this->clickthrough = ['source' => ['campaign', $event['campaign']['id'] ?? 0]];
        $leadArray = $lead->getProfileFields();
        if (empty($leadArray['twitter'])) {
            return false;
        }
        $tweetText = $this->parseLeadText($tweetEntity->getText(), $leadArray, $tweetEntity->getId());
        $sendResponse = $twitterIntegration->postTweet($tweetText);
        if (is_array($sendResponse) && (array_key_exists('id_str', $sendResponse) || isset($sendResponse['data']['id']))) {
            $this->tweetModel->registerSend($tweetEntity, $lead, $sendResponse, 'campaign.event', $event['id'] ?? null);
            return ['timeline' => $tweetText, 'response' => $sendResponse];
        }
        $response = ['failed' => 1, 'response' => $sendResponse];
        if (is_array($sendResponse) && !empty($sendResponse['error']['message'])) {
            $response['reason'] = $sendResponse['error']['message'];
        }
        return $response;
    }

    public function sendTelegramAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('Telegram');
        if (!$integration || !method_exists($integration, 'sendMessage')) {
            return ['failed' => 1, 'response' => 'Telegram integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $chatId = $props['channelTarget'] ?? $leadArray['telegram'] ?? null;
        $result = $integration->sendMessage($message, $chatId);
        if (is_array($result) && (isset($result['message_id']) || isset($result['ok']))) {
            return ['timeline' => $message, 'response' => $result];
        }
        return ['failed' => 1, 'response' => $result];
    }

    public function sendDiscordAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('Discord');
        if (!$integration) {
            return ['failed' => 1, 'response' => 'Discord integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $target = (string) ($props['channelTarget'] ?? '');
        if ('' !== $target && method_exists($integration, 'sendChannelMessage') && !str_contains($target, 'webhook')) {
            $result = $integration->sendChannelMessage($target, $message);
        } elseif (method_exists($integration, 'sendWebhookMessage')) {
            $result = $integration->sendWebhookMessage($message);
        } else {
            return ['failed' => 1, 'response' => 'Discord send method missing'];
        }
        if (false === $result) {
            return ['failed' => 1, 'response' => 'Discord send failed'];
        }
        return ['timeline' => $message, 'response' => $result];
    }

    public function sendMastodonAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('Mastodon');
        if (!$integration || !method_exists($integration, 'postStatus')) {
            return ['failed' => 1, 'response' => 'Mastodon integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $result = $integration->postStatus($message);
        if (is_array($result) && isset($result['id'])) {
            return ['timeline' => $message, 'response' => $result];
        }
        return ['failed' => 1, 'response' => $result];
    }

    public function sendBlueskyAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('Bluesky');
        if (!$integration || !method_exists($integration, 'postText')) {
            return ['failed' => 1, 'response' => 'Bluesky integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $result = $integration->postText($message);
        if (is_array($result) && (isset($result['uri']) || isset($result['cid']))) {
            return ['timeline' => $message, 'response' => $result];
        }
        return is_array($result) ? ['failed' => 1, 'response' => $result] : ['failed' => 1, 'response' => 'Bluesky post failed'];
    }

    public function sendRedditAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('Reddit');
        if (!$integration || !method_exists($integration, 'submitPost')) {
            return ['failed' => 1, 'response' => 'Reddit integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        $subreddit = (string) ($props['channelTarget'] ?? 'test');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $title = mb_substr($message, 0, 100);
        $result = $integration->submitPost($subreddit, $title, $message, 'self');
        if (is_array($result)) {
            return ['timeline' => $message, 'response' => $result];
        }
        return ['failed' => 1, 'response' => 'Reddit submit failed'];
    }

    public function sendWhatsAppAction(Lead $lead, array $event): array|false
    {
        $integration = $this->integrationHelper->getIntegrationObject('WhatsApp');
        if (!$integration || !method_exists($integration, 'sendText')) {
            return ['failed' => 1, 'response' => 'WhatsApp integration unavailable'];
        }
        $props = $event['properties'] ?? $event;
        $message = (string) ($props['message'] ?? '');
        if ('' === trim($message)) {
            return ['failed' => 1, 'response' => 'Empty message'];
        }
        $leadArray = $lead->getProfileFields();
        $message = $this->parseLeadText($message, $leadArray);
        $phone = (string) ($props['channelTarget'] ?? $leadArray['whatsapp'] ?? $leadArray['mobile'] ?? $leadArray['phone'] ?? '');
        if ('' === $phone) {
            return false;
        }
        $result = $integration->sendText($phone, $message);
        if (is_array($result) && isset($result['message_id'])) {
            return ['timeline' => $message, 'response' => $result];
        }
        return is_array($result) ? ['failed' => 1, 'response' => $result] : ['failed' => 1, 'response' => 'WhatsApp send failed'];
    }

    private function parseLeadText(string $text, array $lead, ?int $channelId = -1): string
    {
        $tokens = TokenHelper::findLeadTokens($text, $lead);
        $tokens = array_merge(
            $tokens,
            $this->pageTokenHelper->findPageTokens($text, $this->clickthrough),
            $this->assetTokenHelper->findAssetTokens($text, $this->clickthrough)
        );
        [$text, $trackables] = $this->trackableModel->parseContentForTrackables(
            $text, $tokens, 'social_message', $channelId ?? -1
        );
        foreach ($trackables as $token => $trackable) {
            $tokens[$token] = $this->trackableModel->generateTrackableUrl(
                $trackable,
                array_merge($this->clickthrough, ['lead' => $lead['id'] ?? 0])
            );
        }
        return str_replace(array_keys($tokens), array_values($tokens), $text);
    }
}
