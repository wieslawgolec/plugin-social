<?php
declare(strict_types=1);
namespace MauticPlugin\MauticSocialBundle\Integration;
use MauticPlugin\MauticSocialBundle\Helper\BlueskyApiHelper;
final class BlueskyIntegration extends SocialIntegration
{
    public function getName(): string { return 'Bluesky'; }
    public function getIdentifierFields(): array { return ['bluesky']; }
    public function getSupportedFeatures(): array { return ['public_profile', 'share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'handle' => 'mautic.integration.keyfield.handle',
            'app_password' => 'mautic.integration.keyfield.app_password',
            'pds' => 'mautic.integration.keyfield.pds',
        ];
    }
    public function getApiUrl($endpoint): string
    {
        return BlueskyApiHelper::xrpcUrl($this->keys['pds'] ?? BlueskyApiHelper::DEFAULT_PDS, (string) $endpoint);
    }
    public function postText(string $text): array|false
    {
        $pds = $this->keys['pds'] ?? BlueskyApiHelper::DEFAULT_PDS;
        $session = $this->makeRequest(
            BlueskyApiHelper::createSessionUrl($pds),
            BlueskyApiHelper::buildSessionPayload($this->keys['handle'] ?? '', $this->keys['app_password'] ?? ''),
            'POST',
            ['encode_parameters' => false, 'headers' => ['Content-Type' => 'application/json']]
        );
        if (!is_array($session) || empty($session['did']) || empty($session['accessJwt'])) {
            return is_array($session) ? $session : false;
        }
        $response = $this->makeRequest(
            BlueskyApiHelper::createRecordUrl($pds),
            BlueskyApiHelper::buildPostRecord($session['did'], $text),
            'POST',
            ['encode_parameters' => false, 'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$session['accessJwt'],
            ]]
        );
        return is_array($response) ? $response : false;
    }
    public function getUserData($identifier, &$socialCache): void
    {
        $handle = BlueskyApiHelper::cleanHandle((string) $identifier);
        $response = $this->makeRequest(BlueskyApiHelper::getProfileUrl(), ['actor' => $handle], 'GET');
        if (is_array($response) && isset($response['did'])) {
            $socialCache['profile'] = BlueskyApiHelper::mapProfile($response);
            $socialCache['id'] = $response['did'];
        }
    }
    public function getFormType() { return null; }
}
