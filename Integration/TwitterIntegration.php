<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Form\Type\TwitterType;

/**
 * X (Twitter) integration using X API v2 + OAuth 2.0 Authorization Code with PKCE.
 *
 * Replaces legacy v1.1 + OAuth 1.0a implementation.
 */
final class TwitterIntegration extends SocialIntegration
{
    public const NAME = 'Twitter';
    public const API_BASE = 'https://api.x.com/2';
    public const AUTH_URL = 'https://x.com/i/oauth2/authorize';
    public const TOKEN_URL = 'https://api.x.com/2/oauth2/token';
    public const DEFAULT_SCOPES = 'tweet.read tweet.write users.read offline.access';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDisplayName(): string
    {
        return 'X (Twitter)';
    }

    public function getPriority(): int
    {
        return 5000;
    }

    public function getIdentifierFields(): string
    {
        return 'twitter';
    }

    public function getSupportedFeatures(): array
    {
        return [
            'public_profile',
            'public_activity',
            'share_button',
            'login_button',
        ];
    }

    public function getAuthenticationType(): string
    {
        return 'oauth2';
    }

    public function getAuthenticationUrl(): string
    {
        return self::AUTH_URL;
    }

    public function getAccessTokenUrl(): string
    {
        return self::TOKEN_URL;
    }

    public function getAuthScope(): string
    {
        return self::DEFAULT_SCOPES;
    }

    public function getClientIdKey(): string
    {
        return 'client_id';
    }

    public function getClientSecretKey(): string
    {
        return 'client_secret';
    }

    public function getAuthTokenKey(): string
    {
        return 'access_token';
    }

    public function getApiUrl($endpoint): string
    {
        $endpoint = ltrim((string) $endpoint, '/');

        if ('statuses/update' === $endpoint || '1.1/statuses/update.json' === $endpoint) {
            return self::API_BASE.'/tweets';
        }

        if (str_starts_with($endpoint, '1.1/') || str_starts_with($endpoint, '2/')) {
            $endpoint = preg_replace('#^(1\.1/|2/)#', '', $endpoint);
            $endpoint = preg_replace('#\.json$#', '', (string) $endpoint);
        }

        return self::API_BASE.'/'.$endpoint;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function postTweet(string $text): array|false
    {
        $url      = $this->getApiUrl('tweets');
        $payload  = ['text' => $text];
        $response = $this->makeRequest(
            $url,
            $payload,
            'POST',
            [
                'encode_parameters' => false,
                'headers'          => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        if (is_array($response) && isset($response['data']['id'])) {
            $response['id_str'] = $response['data']['id'];
            $response['id']     = $response['data']['id'];
            $response['text']   = $response['data']['text'] ?? $text;

            return $response;
        }

        return is_array($response) ? $response : false;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function searchRecent(string $query, array $params = []): array
    {
        $params = array_merge([
            'query'        => $query,
            'max_results'  => 100,
            'tweet.fields' => 'created_at,author_id,entities,public_metrics,lang',
            'expansions'   => 'author_id',
            'user.fields'  => 'username,name,description,location,url,public_metrics,profile_image_url',
        ], $params);

        $response = $this->makeRequest(
            $this->getApiUrl('tweets/search/recent'),
            $params,
            'GET',
            ['authorize_session' => false]
        );

        return is_array($response) ? $response : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUserByUsername(string $username): ?array
    {
        $username = $this->cleanIdentifier($username);
        $response = $this->makeRequest(
            $this->getApiUrl('users/by/username/'.$username),
            [
                'user.fields' => 'username,name,description,location,url,public_metrics,profile_image_url,created_at',
            ],
            'GET'
        );

        if (is_array($response) && isset($response['data'])) {
            return $response['data'];
        }

        return null;
    }

    public function getUserData($identifier, &$socialCache): void
    {
        $identifier = $this->cleanIdentifier($identifier);
        $user       = $this->getUserByUsername($identifier);

        if (!$user) {
            return;
        }

        $info = [
            'profileHandle' => $user['username'] ?? $identifier,
            'name'          => $user['name'] ?? '',
            'location'      => $user['location'] ?? '',
            'description'   => $user['description'] ?? '',
            'url'           => $user['url'] ?? '',
            'profileImage'  => $user['profile_image_url'] ?? '',
        ];

        if (isset($user['public_metrics'])) {
            $info['followers']  = $user['public_metrics']['followers_count'] ?? 0;
            $info['following']  = $user['public_metrics']['following_count'] ?? 0;
            $info['tweetCount'] = $user['public_metrics']['tweet_count'] ?? 0;
        }

        $socialCache['profile']     = $info;
        $socialCache['id']          = $user['id'] ?? null;
        $socialCache['lastRefresh'] = (new \DateTime())->format('c');
    }

    public function getPublicActivity($identifier, &$socialCache): void
    {
        $identifier = $this->cleanIdentifier($identifier);
        $user       = $this->getUserByUsername($identifier);

        if (!$user || empty($user['id'])) {
            return;
        }

        $response = $this->makeRequest(
            $this->getApiUrl('users/'.$user['id'].'/tweets'),
            [
                'max_results'  => 20,
                'tweet.fields' => 'created_at,entities,public_metrics',
            ],
            'GET'
        );

        $socialCache['activity'] = [
            'tweets' => [],
            'photos' => [],
            'tags'   => [],
        ];

        if (!is_array($response) || empty($response['data'])) {
            return;
        }

        foreach ($response['data'] as $tweet) {
            $socialCache['activity']['tweets'][] = [
                'id'   => $tweet['id'] ?? '',
                'text' => $tweet['text'] ?? '',
                'url'  => 'https://x.com/'.$identifier.'/status/'.($tweet['id'] ?? ''),
                'date' => $tweet['created_at'] ?? '',
            ];

            if (!empty($tweet['entities']['hashtags'])) {
                foreach ($tweet['entities']['hashtags'] as $h) {
                    $tag = $h['tag'] ?? '';
                    if ('' === $tag) {
                        continue;
                    }
                    if (isset($socialCache['activity']['tags'][$tag])) {
                        ++$socialCache['activity']['tags'][$tag]['count'];
                    } else {
                        $socialCache['activity']['tags'][$tag] = [
                            'count' => 1,
                            'url'   => 'https://x.com/search?q=%23'.rawurlencode($tag),
                        ];
                    }
                }
            }
        }
    }

    public function getAvailableLeadFields(array $settings = []): array
    {
        return [
            'profileHandle' => ['type' => 'string'],
            'name'          => ['type' => 'string'],
            'location'      => ['type' => 'string'],
            'description'   => ['type' => 'string'],
            'url'           => ['type' => 'string'],
            'profileImage'  => ['type' => 'string'],
        ];
    }

    public function cleanIdentifier($identifier): string
    {
        $identifier = (string) $identifier;

        if (preg_match('#https?://(www\.)?(twitter\.com|x\.com)/(.*?)(/.*?|$)#i', $identifier, $match)) {
            $identifier = $match[3];
        } elseif (str_starts_with($identifier, '@')) {
            $identifier = substr($identifier, 1);
        }

        return rawurlencode(trim($identifier));
    }

    public function parseCallbackResponse($data, $postAuthorization = false)
    {
        $values = json_decode($data, true);

        if (!is_array($values)) {
            parse_str($data, $values);
        }

        if ($postAuthorization && is_array($values) && isset($values['access_token'])) {
            if ($this->requestStack->getCurrentRequest()?->hasSession()) {
                $this->requestStack->getSession()->set($this->getName().'_tokenResponse', $values);
            }
        }

        return $values;
    }

    public function getFormType(): string
    {
        return TwitterType::class;
    }

    public function getFormNotes($section)
    {
        if ('authorization' === $section) {
            return [
                'Requires X API v2 with OAuth 2.0 PKCE. Enable OAuth 2.0 in the X Developer Portal, set the callback URL, and use scopes: tweet.read tweet.write users.read offline.access. Pay-per-use billing is required for new apps (2026).',
                'info',
            ];
        }

        return parent::getFormNotes($section);
    }
}
