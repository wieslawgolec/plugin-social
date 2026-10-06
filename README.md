# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

## Platforms

| Platform | API / auth | Capabilities |
|----------|------------|--------------|
| **X (Twitter)** | API v2 + OAuth 2.0 | Post, search, profile, campaign, monitoring |
| **Facebook** | Graph v26.0 | Login, profile |
| **Instagram** | Business Graph v26 | Business discovery, hashtags |
| **Yelp** | Fusion API | Place search / details |
| **Mastodon** | Instance OAuth2 | Post status, campaign action, tag monitor |
| **Bluesky** | AT Protocol | Post, profile, search monitor |
| **Google Places** | Places API (New) | Text search, place details |
| **Reddit** | OAuth2 | Submit, user about, subreddit monitor |
| **Telegram** | Bot API | sendMessage, campaign action |
| **YouTube** | Data API v3 | Channel lookup |
| **Pinterest** | API v5 | Pins, user account |
| **Discord** | Webhooks + Bot v10 | Webhook/channel send, campaign action |

**Removed:** Foursquare.

## Campaign actions

When the integration is published, Campaign Builder shows:

- **Send Tweet** (X) — existing tweet entity picker
- **Send Telegram message** — free-text + optional chat id
- **Send Discord message** — free-text; optional channel id (else webhook)
- **Post to Mastodon** — free-text status

Lead tokens (`{contactfield=...}`) are supported in message bodies.

## Monitoring cron

```bash
php bin/console mautic:social:monitor --network=x --query="#mautic" --limit=20
php bin/console mautic:social:monitor --network=mastodon --query=mautic
php bin/console mautic:social:monitor --network=bluesky --query=mautic
php bin/console mautic:social:monitor --network=reddit --query=php
```

Schedule via system cron as needed. Legacy Twitter hashtag/mention commands remain available.

## OAuth / tokens

Authorization screens include provider-specific callback and scope hints. Access tokens are stored **encrypted** on the integration settings (Mautic core encryptApiKeys), same as other plugins.

## Tests & CI

```bash
composer update --ignore-platform-reqs
vendor/bin/phpunit
```

Matrix: **PHP 8.2 / 8.5 / 8.6**, PHPUnit **11.5**, `--ignore-platform-reqs` for PHP 8.6.

## License

GPL-3.0-or-later
