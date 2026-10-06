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
| **LinkedIn** | ✓ Posts API | — | ✓ | ✓ post | — |
| **WeChat OA** | ✓ CS/template | — | ✓ openid | ✓ send | — |
| **WeCom** | ✓ app msg | — | ✓ userid | ✓ send | — |
| **Facebook** | Graph v26 | — | ✓ | — | — |
| **Instagram** | Business Graph | hashtags | business | — | — |
| **YouTube** | — | ✓ videos | ✓ channel | — | ✓ |
| **Pinterest** | ✓ pin | — | ✓ | — | — |
| **Yelp** | — | ✓ businesses | ✓ place | — | ✓ |
| **Google Places** | — | ✓ | ✓ place | — | — |

**Removed:** Foursquare.

## Campaign actions

- Send Tweet (X)
- Send Telegram / Discord / WhatsApp / WeChat / WeCom message
- Post to Mastodon / Bluesky / LinkedIn
- Submit to Reddit (`channelTarget` = subreddit)

## Monitoring

```bash
php bin/console mautic:social:monitor --network=x|mastodon|bluesky|reddit|youtube|yelp --query="..."
```

## Notes

- **LinkedIn**: OAuth2 + Posts API (`w_member_social`); author URN from profile after authorize.
- **WeChat OA**: AppID/Secret; CS text only within 48h of user message, else templates.
- **WeCom**: Corp ID + Secret + Agent ID; `touser` = member userid.

## Tests & CI

```bash
composer update --ignore-platform-reqs
vendor/bin/phpunit
```

PHP **8.2 / 8.5 / 8.6**, PHPUnit **11.5**.

## License

GPL-3.0-or-later
