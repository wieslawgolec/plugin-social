# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

## Platforms

| Platform | API / auth | Capabilities |
|----------|------------|--------------|
| **X (Twitter)** | API v2 + OAuth 2.0 | Post, search, profile, monitoring |
| **Facebook** | Graph v26.0 | Login, profile |
| **Instagram** | Business Graph v26 | Business discovery, hashtags |
| **Yelp** | Fusion API | Place search / details |
| **Mastodon** | Instance OAuth2 | Post status, account lookup |
| **Bluesky** | AT Protocol | Session + post, profile |
| **Google Places** | Places API (New) | Text search, place details |
| **Reddit** | OAuth2 | Submit, user about |
| **Telegram** | Bot API | sendMessage |
| **YouTube** | Data API v3 | Channel lookup |
| **Pinterest** | API v5 | Pins, user account |
| **Discord** | Webhooks + Bot v10 | Webhook post, channel message |

**Removed:** Foursquare.

## Architecture

- `Helper/*ApiHelper.php` — pure URL builders, payloads, response mappers (PHPUnit-covered, no Mautic core).
- `Integration/*Integration.php` — Mautic plugin wiring + `post*` / `getUserData` / send methods using helpers.
- Campaign tweet send uses `TwitterIntegration::postTweet()` (X API v2).

## Tests & CI

```bash
composer update --ignore-platform-reqs
vendor/bin/phpunit
```

Matrix: **PHP 8.2 / 8.5 / 8.6**, **PHPUnit 11.5**, with `--ignore-platform-reqs` for PHP 8.6.

## License

GPL-3.0-or-later
