<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Form\Type\FacebookType;
use Psr\Http\Message\ResponseInterface;

/**
 * Facebook integration using Graph API v26.0.
 */
final class FacebookIntegration extends SocialIntegration
{
    public const GRAPH_VERSION = 'v26.0';

    public function getName(): string
    {
        return 'Facebook';
    }

    public function getIdentifierFields(): array
    {
        return ['facebook'];
    }

    public function getSupportedFeatures(): array
    {
        return [
            'share_button',
            'login_button',
            'public_profile',
        ];
    }

    public function getAuthenticationUrl(): string
    {
        return 'https://www.facebook.com/'.self::GRAPH_VERSION.'/dialog/oauth';
    }

    public function getAccessTokenUrl(): string
    {
        return 'https://graph.facebook.com/'.self::GRAPH_VERSION.'/oauth/access_token';
    }

    public function getAuthScope(): string
    {
        return 'email,public_profile';
    }

    public function parseCallbackResponse($data, $postAuthorization = false)
    {
        $values = parent::parseCallbackResponse($data, $postAuthorization);

        if (null === $values) {
            parse_str($data, $values);
            if ($this->requestStack->getCurrentRequest()?->hasSession()) {
                $this->requestStack->getSession()->set($this->getName().'_tokenResponse', $values);
            }
        }

        return $values;
    }

    public function getApiUrl($endpoint): string
    {
        $endpoint = ltrim((string) $endpoint, '/');
        if (!str_starts_with($endpoint, 'v')) {
            $endpoint = self::GRAPH_VERSION.'/'.$endpoint;
        }

        return 'https://graph.facebook.com/'.$endpoint;
    }

    public function getUserData($identifier, &$socialCache): ?ResponseInterface
    {
        $this->persistNewLead = false;
        $accessToken          = $this->getContactAccessToken($socialCache);

        if (!isset($accessToken['access_token'])) {
            return null;
        }

        $fields = array_keys($this->getAvailableLeadFields());
        $url    = $this->getApiUrl('me');
        $data   = $this->makeRequest($url, [
            'access_token' => $accessToken['access_token'],
            'fields'       => implode(',', $fields),
        ], 'GET', ['auth_type' => 'rest']);

        if (is_object($data) && isset($data->id)) {
            $info = $this->matchUpData($data);
            if (isset($data->username)) {
                $info['profileHandle'] = $data->username;
            } elseif (isset($data->link)) {
                $info['profileHandle'] = basename(parse_url($data->link, PHP_URL_PATH) ?: '');
            }
            $info['profileImage'] = $this->getApiUrl($data->id.'/picture').'?type=large';
            $socialCache['profile'] = $info;
            $socialCache['id'] = $data->id;
            $this->persistNewLead = true;
        }

        return null;
    }

    public function getAvailableLeadFields(array $settings = []): array
    {
        return [
            'id'         => ['type' => 'string'],
            'first_name' => ['type' => 'string'],
            'last_name'  => ['type' => 'string'],
            'name'       => ['type' => 'string'],
            'email'      => ['type' => 'string'],
            'link'       => ['type' => 'string'],
            'locale'     => ['type' => 'string'],
        ];
    }

    public function getFormType(): string
    {
        return FacebookType::class;
    }

    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return [
                'Uses Facebook Graph API '.self::GRAPH_VERSION.'. Create a Meta app, enable Facebook Login, and set the OAuth redirect URI to the Mautic callback URL.',
                'info',
            ];
        }

        return parent::getFormNotes($section);
    }
}
