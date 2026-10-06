# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

## Capability matrix

| Platform | Post | Search | Profile | Campaign | Monitoring |
|----------|:----:|:------:|:-------:|:--------:|:----------:|
| **X (Twitter)** | ✓ | ✓ | ✓ | ✓ tweet | ✓ |
| **Mastodon** | ✓ | tag timeline | ✓ | ✓ post | ✓ |
| **Bluesky** | ✓ | ✓ | ✓ | ✓ post | ✓ |
| **Telegram** | ✓ send | — | bot | ✓ send | — |
| **Discord** | ✓ webhook/channel | — | — | ✓ send | — |
| **Reddit** | ✓ submit | ✓ | ✓ | ✓ submit | ✓ |
| **WhatsApp** | ✓ text/template | — | — | ✓ send | — |
| **Facebook** | Graph v26 | — | ✓ | — | — |
| **Instagram** | Business Graph | hashtags | business | — | — |
| **YouTube** | — | ✓ videos | ✓ channel | — | ✓ |
| **Pinterest** | ✓ pin | — | ✓ | — | — |
| **Yelp** | — | ✓ businesses | ✓ place | — | ✓ |
| **Google Places** | — | ✓ | ✓ place | — | — |

**Removed:** Foursquare.

## Campaign actions

- Send Tweet (X)
- Send Telegram / Discord / WhatsApp message
- Post to Mastodon / Bluesky
- Submit to Reddit (`channelTarget` = subreddit)

## Monitoring

```bash
php bin/console mautic:social:monitor --network=x --query="#mautic"
php bin/console mautic:social:monitor --network=mastodon --query=mautic
php bin/console mautic:social:monitor --network=bluesky --query=mautic
php bin/console mautic:social:monitor --network=reddit --query=php
php bin/console mautic:social:monitor --network=youtube --query=mautic
php bin/console mautic:social:monitor --network=yelp --query="coffee|Berlin"
```

## WhatsApp Cloud API

Keys: access token, Phone number ID, optional WABA ID. Opt-in required. 24h session or approved templates.

## Tests & CI

```bash
composer update --ignore-platform-reqs
vendor/bin/phpunit
```

PHP **8.2 / 8.5 / 8.6**, PHPUnit **11.5**.

## License

GPL-3.0-or-later
