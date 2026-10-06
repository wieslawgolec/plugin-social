<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\TwitchApiHelper;
final class TwitchIntegration extends SocialIntegration
{
    public function getName(): string { return 'Twitch'; }
    public function getIdentifierFields(): array { return ['twitch']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'public_activity', 'share_button']; }
    public function getAuthenticationType(): string { return 'oauth2'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'client_id' => 'mautic.integration.keyfield.clientid',
            'client_secret' => 'mautic.integration.keyfield.clientsecret',
        ];
    }
    public function getAuthenticationUrl(): string { return TwitchApiHelper::AUTH_URL; }
    public function getAccessTokenUrl(): string { return TwitchApiHelper::TOKEN_URL; }
    public function getAuthScope(): string { return 'user:read:email user:write:chat moderator:manage:announcements'; }
    public function getApiUrl($endpoint): string { return TwitchApiHelper::url((string) $endpoint); }
    private function helixHeaders(): array
    {
        return [
            'Client-Id' => $this->keys['client_id'] ?? ($this->keys['clientId'] ?? ''),
            'Content-Type' => 'application/json',
        ];
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $login = '' !== (string) $identifier ? TwitchApiHelper::cleanLogin((string) $identifier) : null;
        $response = $this->makeRequest(TwitchApiHelper::usersUrl(), TwitchApiHelper::buildUsersQuery($login), 'GET', ['headers' => $this->helixHeaders()]);
        $user = is_array($response) ? ($response['data'][0] ?? null) : null;
        if (is_array($user)) {
            $socialCache['profile'] = TwitchApiHelper::mapUser($user);
            $socialCache['id'] = $user['id'] ?? '';
            if (!empty($user['id'])) {
                $this->keys['user_id'] = $user['id'];
            }
        }
    }
    public function searchChannels(string $query, int $first = 20): array|false
    {
        $response = $this->makeRequest(TwitchApiHelper::searchChannelsUrl(), TwitchApiHelper::buildSearchChannelsQuery($query, $first), 'GET', ['headers' => $this->helixHeaders()]);
        return is_array($response) ? $response : false;
    }
    public function getStreams(?string $userLogin = null, int $first = 20): array|false
    {
        $query = ['first' => max(1, min(100, $first))];
        if (null !== $userLogin && '' !== $userLogin) {
            $query['user_login'] = TwitchApiHelper::cleanLogin($userLogin);
        }
        $response = $this->makeRequest(TwitchApiHelper::streamsUrl(), $query, 'GET', ['headers' => $this->helixHeaders()]);
        return is_array($response) ? $response : false;
    }
    public function sendChatMessage(string $message, ?string $broadcasterId = null, ?string $senderId = null): array|false
    {
        $broadcasterId = $broadcasterId ?? ($this->keys['broadcaster_id'] ?? $this->keys['user_id'] ?? '');
        $senderId = $senderId ?? ($this->keys['user_id'] ?? '');
        if ('' === $broadcasterId || '' === $senderId) {
            return ['error' => 'Missing broadcaster_id or sender user_id'];
        }
        $response = $this->makeRequest(
            TwitchApiHelper::chatMessagesUrl(),
            TwitchApiHelper::buildChatMessagePayload($broadcasterId, $senderId, $message),
            'POST',
            ['encode_parameters' => false, 'headers' => $this->helixHeaders()]
        );
        return is_array($response) ? $response : false;
    }
    public function getFormType() { return null; }
    public function getFormNotes($section)
    {
        $transKey = 'mautic.social.twitch.notes.'.$section;
        $text = $this->translator->trans($transKey);
        if ($text !== $transKey) {
            return [$text, 'info'];
        }
        if ('authorization' === $section) {
            return [$this->translator->trans('mautic.social.oauth.callback_hint'), 'info'];
        }
        return parent::getFormNotes($section);
    }
}
