# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

Official core plans to **remove** this bundle in Mautic 8.0 because the old APIs were dead. This fork brings the integrations up to date so they remain usable on 7.2.

## What changed

| Area | Before | After |
|------|--------|--------|
| **X (Twitter)** | API v1.1 + OAuth 1.0a | **X API v2** + OAuth 2.0 (PKCE-ready), `POST /2/tweets`, recent search, user lookup |
| **Facebook** | Graph ~v2.8 | **Graph API v26.0** |
| **Instagram** | Dead consumer API | **Instagram Graph** skeleton for Business/Creator accounts (hashtag search, business discovery) |
| **Foursquare** | Present but unusable | **Removed** |
| **Yelp** | — | **New** Places/Fusion skeleton (API key auth) |
| **Tests** | None usable standalone | PHPUnit 11 unit tests |
| **CI** | Close-PRs only | PHPUnit matrix PHP **8.2 / 8.5 / 8.6** |

## Requirements

- Mautic **7.2+** (`mautic/core-lib: ^7.0`)
- PHP **8.2+**
- For X: paid / pay-per-use X developer project (no free tier for new apps as of 2026)
- For Facebook/Instagram: Meta app with appropriate products and App Review where required
- For Yelp: [Yelp Fusion API key](https://www.yelp.com/developers)

## Installation

```bash
# Composer-based Mautic
composer require wieslawgolec/plugin-social

# Or copy this repository into plugins/MauticSocialBundle
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

## X (Twitter) setup

1. Create an app in the [X Developer Portal](https://developer.x.com/) inside a Project.
2. Enable **OAuth 2.0**.
3. Set the callback URL to the one shown in Mautic plugin settings.
4. Request scopes: `tweet.read tweet.write users.read offline.access`.
5. Paste Client ID / Client Secret into the Mautic X integration and authorize.
6. Ensure billing is enabled (pay-per-use).

Campaign “Send Tweet” actions use `POST /2/tweets`. Monitoring uses `GET /2/tweets/search/recent` (last 7 days).

## Facebook setup

Uses Graph **v26.0**. Enable Facebook Login, set the OAuth redirect URI, request `email,public_profile`. Public arbitrary profile scraping is not supported by Meta; enrichment works for users who authenticated via Login.

## Instagram setup

Business/Creator accounts only (linked Facebook Page). Hashtag search is capped at **30 unique hashtags / 7 days** per account. Full monitoring UX wiring can be extended later from the provided API helpers (`businessDiscovery`, `searchHashtag`).

## Yelp setup

Enter a Yelp Fusion API key. Use `searchBusinesses()` / `getBusiness()` for place data (replacement for Foursquare check-in style data).

## Development / tests

```bash
composer install
vendor/bin/phpunit
```

GitHub Actions runs PHPUnit on PHP 8.2, 8.5, and 8.6.

## License

GPL-3.0-or-later (same as Mautic).

## Upstream

Based on `mautic/plugin-social` 7.x. Prefer contributing API modernization upstream when possible; this fork exists because core intends to drop the bundle in 8.0.
