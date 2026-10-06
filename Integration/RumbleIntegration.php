<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\RumbleApiHelper;
final class RumbleIntegration extends SocialIntegration
{
    public function getName(): string { return 'Rumble'; }
    public function getIdentifierFields(): array { return ['rumble']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'api_key' => 'mautic.integration.keyfield.apitoken',
            'channel_slug' => 'mautic.integration.keyfield.channel_slug',
        ];
    }
    public function getApiUrl($endpoint): string { return RumbleApiHelper::apiUrl((string) $endpoint); }
    public function getUserData($identifier, &$socialCache): void
    {
        $slug = '' !== (string) $identifier
            ? RumbleApiHelper::cleanChannelSlug((string) $identifier)
            : RumbleApiHelper::cleanChannelSlug($this->keys['channel_slug'] ?? '');
        if ('' === $slug) {
            return;
        }
        $headers = [];
        if (!empty($this->keys['api_key'])) {
            $headers['Authorization'] = 'Bearer '.$this->keys['api_key'];
        }
        $response = $this->makeRequest(
            RumbleApiHelper::apiUrl('channel/'.rawurlencode($slug)),
            [],
            'GET',
            ['headers' => $headers, 'authorize' => false]
        );
        if (is_array($response) && (isset($response['id']) || isset($response['slug']) || isset($response['username']))) {
            $socialCache['profile'] = RumbleApiHelper::mapChannel($response);
            $socialCache['id'] = $socialCache['profile']['id'] ?? $slug;
            return;
        }
        $socialCache['profile'] = RumbleApiHelper::mapChannel([
            'slug' => $slug, 'username' => $slug, 'name' => $slug,
            'url' => RumbleApiHelper::channelPageUrl($slug),
        ]);
        $socialCache['id'] = $slug;
    }
    public function publishMeta(string $title, string $description = '', string $visibility = 'public'): array|false
    {
        if (empty($this->keys['api_key'])) {
            return ['error' => 'Rumble API key required'];
        }
        $response = $this->makeRequest(
            RumbleApiHelper::apiUrl('video/publish'),
            RumbleApiHelper::buildVideoMetaPayload($title, $description, $visibility, $this->keys['channel_slug'] ?? null),
            'POST',
            [
                'encode_parameters' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->keys['api_key'],
                ],
                'authorize' => false,
            ]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return ['Rumble partner API is invite-based. Store API key + channel slug.', 'info'];
        }
        return parent::getFormNotes($section);
    }
}
