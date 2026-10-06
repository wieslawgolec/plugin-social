# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

Official core plans to **remove** this bundle in Mautic 8.0. This fork modernizes legacy APIs and adds additional networks.

## Platforms

| Platform | API / auth | Status |
|----------|------------|--------|
| **X (Twitter)** | API v2 + OAuth 2.0 | Full rewrite |
| **Facebook** | Graph **v26.0** | Updated |
| **Instagram** | Business Graph v26 | Skeleton |
| **Yelp** | Fusion API (key) | Replaces Foursquare |
| **Mastodon** | Instance REST + OAuth2 | Skeleton |
| **Bluesky** | AT Protocol / XRPC | Skeleton |
| **Google Places** | Places API (New) | Skeleton |
| **Reddit** | OAuth2 + oauth.reddit.com | Skeleton |
| **Telegram** | Bot API | Skeleton |
| **YouTube** | Data API v3 | Skeleton |
| **Pinterest** | API **v5** | Skeleton |
| **Discord** | Webhooks + Bot API v10 | Skeleton |

**Removed:** Foursquare (consumer API shut down).

## Requirements

- Mautic **7.2+** when installed as a plugin
- PHP **8.2+**
- Credentials per platform (see setup notes below)

## Installation

```bash
# Copy into plugins/MauticSocialBundle or require this package
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

## Platform setup (short)

| Platform | What you need |
|----------|----------------|
| X | Developer app, OAuth 2.0, scopes `tweet.read tweet.write users.read offline.access`, billing |
| Facebook / Instagram | Meta app, Graph v26, Login / Instagram Business |
| Yelp | Fusion API key |
| Mastodon | Instance URL + OAuth app on that instance |
| Bluesky | Handle + app password (optional custom PDS) |
| Google Places | Google Cloud API key (Places API New) |
| Reddit | Reddit app (script/web), OAuth2 |
| Telegram | Bot token from @BotFather, chat id |
| YouTube | Google OAuth client, YouTube Data API enabled |
| Pinterest | Pinterest app, API v5 OAuth |
| Discord | Incoming webhook URL and/or bot token |

## Architecture notes

- **Pure helpers** under `Helper/*ApiHelper.php` build URLs and normalize identifiers without Mautic core — covered by PHPUnit.
- **Integration classes** under `Integration/` wire into Mautic’s plugin framework (`SocialIntegration` / OAuth keys).
- Campaign “Send Tweet” uses X API v2 via `TwitterIntegration::postTweet()`.

## Tests & CI

```bash
composer install
vendor/bin/phpunit
```

GitHub Actions matrix: **PHP 8.2, 8.5, 8.6** · **PHPUnit 11.x**

- `composer update --ignore-platform-reqs` so PHP 8.6 (pre-stable) can install packages.
- Unit tests target API helpers (URL building, parsing, auth endpoints) so CI does not need a full Mautic stack.

## Version

Plugin config version **2.0.0** (modernized fork).

## License

GPL-3.0-or-later (same as Mautic).
