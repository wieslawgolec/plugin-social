# Mautic Social Bundle (modernized)

Community modernization of [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

## Capability matrix

| Platform | Post | Search | Profile | Campaign | Monitoring |
|----------|:----:|:------:|:-------:|:--------:|:----------:|
| **X (Twitter)** | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Mastodon / Bluesky / Reddit** | ✓ | ✓/tag | ✓ | ✓ | ✓ |
| **Telegram / Discord / WhatsApp** | ✓ | — | limited | ✓ | — |
| **LinkedIn / WeChat / WeCom** | ✓ | — | ✓ | ✓ | — |
| **TikTok** | upload* | video list | ✓ | — | ✓ list |
| **Twitch** | chat | channels | ✓ | ✓ chat | ✓ |
| **Rumble** | meta* | — | ✓ | ✓ | — |
| **YouTube / Pinterest / Yelp / Places** | pin/— | ✓ | ✓ | — | YT/Yelp |
| **Facebook / Instagram** | Graph | limited | ✓ | — | — |

\*TikTok Content Posting API needs app review. Rumble needs partner API key.

**Removed:** Foursquare.

## Monitoring

```bash
php bin/console mautic:social:monitor --network=twitch --query=fps
php bin/console mautic:social:monitor --network=tiktok --query=n/a
```

## Tests & CI

PHP **8.2 / 8.5 / 8.6**, PHPUnit **11.5**, `--ignore-platform-reqs`.

## License

GPL-3.0-or-later
