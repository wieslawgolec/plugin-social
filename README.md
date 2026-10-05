# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

Official core plans to **remove** this bundle in Mautic 8.0 because the old APIs were dead. This fork brings the integrations up to date so they remain usable on 7.2.

## What changed

| Area | Before | After |
|------|--------|--------|
| **X (Twitter)** | API v1.1 + OAuth 1.0a | **X API v2** + OAuth 2.0, `POST /2/tweets`, recent search, user lookup |
| **Facebook** | Graph ~v2.8 | **Graph API v26.0** |
| **Instagram** | Dead consumer API | **Instagram Graph** skeleton for Business/Creator accounts |
| **Foursquare** | Present but unusable | **Removed** |
| **Yelp** | — | **New** Places/Fusion skeleton (API key auth) |
| **Tests** | None | PHPUnit 11 unit tests |
| **CI** | Close-PRs only | PHPUnit matrix PHP **8.2 / 8.5 / 8.6** |

## Requirements

- Mautic **7.2+** (`mautic/core-lib: ^7.0`)
- PHP **8.2+**
- For X: paid / pay-per-use X developer project (no free tier for new apps as of 2026)
- For Facebook/Instagram: Meta app with appropriate products
- For Yelp: [Yelp Fusion API key](https://www.yelp.com/developers)

## Installation

```bash
composer require wieslawgolec/plugin-social
# Or copy into plugins/MauticSocialBundle
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

## X (Twitter) setup

1. Create an app in the [X Developer Portal](https://developer.x.com/) inside a Project.
2. Enable **OAuth 2.0**.
3. Set the callback URL to the one shown in Mautic plugin settings.
4. Scopes: `tweet.read tweet.write users.read offline.access`.
5. Paste Client ID / Client Secret and authorize.
6. Enable billing (pay-per-use).

Campaign “Send Tweet” uses `POST /2/tweets`. Monitoring uses `GET /2/tweets/search/recent` (last 7 days).

## Facebook setup

Graph **v26.0**. Enable Facebook Login, set OAuth redirect URI, request `email,public_profile`.

## Instagram setup

Business/Creator accounts only. Hashtag search: max **30 unique hashtags / 7 days**. Helpers: `businessDiscovery()`, `searchHashtag()`.

## Yelp setup

Enter a Yelp Fusion API key. Replaces Foursquare for local business / place data.

## Development / tests

```bash
composer install
vendor/bin/phpunit
```

GitHub Actions runs PHPUnit on PHP 8.2, 8.5, and 8.6.

## License

GPL-3.0-or-later (same as Mautic).
