# Mautic Social Bundle (modernized)

Community modernization of the official [mautic/plugin-social](https://github.com/mautic/plugin-social) for **Mautic 7.2+**.

APIs are updated (X API v2, Facebook Graph v26, and many new networks). Foursquare is removed. The upstream plugin is effectively unmaintained and planned for retirement around Mautic 8; this fork keeps social features usable on 7.x.

| | |
|--|--|
| **Version** | 2.1.x |
| **PHP** | 8.2+ (CI: 8.2, 8.5, 8.6) |
| **Mautic** | 7.2+ |
| **License** | GPL-3.0-or-later |

---

## Table of contents

1. [Capability matrix](#capability-matrix)
2. [Install in Mautic](#install-in-mautic)
3. [Plugin configuration](#plugin-configuration)
4. [Campaign builder actions](#campaign-builder-actions)
5. [Campaign form fields](#campaign-form-fields)
6. [Monitoring (CLI & cron)](#monitoring-cli--cron)
7. [Architecture](#architecture)
8. [Development & tests](#development--tests)
9. [Limitations & notes by network](#limitations--notes-by-network)
10. [Support the project](#support-the-project)

---

## Capability matrix

| Platform | Post / send | Search | Profile | Campaign | Monitoring |
|----------|:-----------:|:------:|:-------:|:--------:|:----------:|
| **X (Twitter)** | ✓ tweet | ✓ recent | ✓ | ✓ Send Tweet | ✓ hashtag / mention / CLI |
| **Mastodon** | ✓ status | tag timeline | ✓ | ✓ Post | ✓ tag CLI |
| **Bluesky** | ✓ post | ✓ | ✓ | ✓ Post | ✓ search CLI |
| **Reddit** | ✓ submit | ✓ | ✓ | ✓ Submit | ✓ subreddit CLI |
| **Telegram** | ✓ sendMessage | — | bot | ✓ Send | — |
| **Discord** | ✓ webhook / channel | — | — | ✓ Send | — |
| **WhatsApp** | ✓ text / template | — | — | ✓ Send | — |
| **LinkedIn** | ✓ Posts API | — | ✓ | ✓ Post | — |
| **WeChat OA** | ✓ CS / template | — | ✓ openid | ✓ Send | — |
| **WeCom** | ✓ app message | — | ✓ userid | ✓ Send | — |
| **Twitch** | ✓ chat | ✓ channels | ✓ | ✓ Chat | ✓ search CLI |
| **TikTok** | upload* | own videos | ✓ | — | ✓ list CLI |
| **Rumble** | meta* | — | ✓ channel | ✓ Publish | — |
| **YouTube** | — | ✓ videos | ✓ channel | — | ✓ search CLI |
| **Pinterest** | ✓ pin | — | ✓ | — | — |
| **Yelp** | — | ✓ businesses | ✓ place | — | ✓ `term\|location` CLI |
| **Google Places** | — | ✓ | ✓ place | — | — |
| **Facebook** | Graph v26 | — | ✓ | — | — |
| **Instagram** | Business Graph | hashtags | business | — | — |

\*TikTok full video upload needs Content Posting API app review. Rumble metadata publish needs a partner API key.

**Removed:** Foursquare (replaced conceptually by Yelp / Google Places).

---

## Install in Mautic

### Requirements

- Mautic **7.2+**
- PHP **8.2+** with extensions used by Mautic (`json`, `mbstring`, `curl`, etc.)
- Composer (for dependency resolution inside the Mautic project)

### Option A — Clone into `plugins/`

From your Mautic root:

```bash
cd /path/to/mautic
git clone https://github.com/wieslawgolec/plugin-social.git plugins/MauticSocialBundle
```

The directory name must be **`MauticSocialBundle`** so Mautic discovers the bundle.

### Option B — Composer path / VCS repository

If you manage plugins via Composer, add a VCS repository pointing at this GitHub repo and require it into `plugins/` (exact package name depends on how you register it in the Mautic project). Ensure the installed path is `plugins/MauticSocialBundle`.

### Enable the plugin

```bash
php bin/console cache:clear
php bin/console mautic:plugins:reload
# or: Settings → Plugins → Install/Upgrade Plugins in the UI
```

Then open **Settings → Plugins**, find **Social Media**, and open each network you need.

### Permissions

Grant roles access under Mautic permissions for social monitoring / tweets if you use those menu items (Channels → Social monitoring, Tweets).

### Upgrade from stock `plugin-social`

1. Back up the database and `plugins/MauticSocialBundle`.
2. Replace the plugin directory with this fork.
3. Clear cache and reload plugins.
4. Re-authorize OAuth integrations (X, LinkedIn, Reddit, etc.); API versions and token shapes may differ.
5. Re-check campaign actions and monitoring cron entries.

---

## Plugin configuration

For each network: **Settings → Plugins → Social Media → [Network]**.

### What you will see

1. **Published** — only published integrations appear as campaign actions and are used by CLI monitors.
2. **Authorization / API keys** — OAuth “Authorize” or key fields (bot token, API key, etc.).
3. **Info notes** — each screen shows **capabilities and limits** (authorization + features).

### OAuth callback

Copy the **callback URL** shown on the plugin form into the developer console of the provider. After Authorize, Mautic stores tokens encrypted.

---

## Campaign builder actions

Campaign actions appear only if the matching integration is **published**.

| Action key | Label | Sends as | Recipient / target |
|------------|-------|----------|--------------------|
| `twitter.tweet` | Send Tweet (X) | Authorized X app user | Contact Twitter handle; Tweet entity |
| `telegram.send` | Send Telegram message | Bot | Chat id override → contact → default |
| `discord.send` | Send Discord message | Webhook or bot | Channel id → else webhook |
| `mastodon.post` | Post to Mastodon | Authorized account | Account-level (not per-contact) |
| `bluesky.post` | Post to Bluesky | Configured handle | Account-level |
| `reddit.submit` | Submit to Reddit | Authorized user | Channel target = subreddit |
| `whatsapp.send` | Send WhatsApp message | WABA phone | Phone fields or override |
| `linkedin.post` | Post to LinkedIn | Authorized member | Member feed |
| `wechat.send` | Send WeChat message | Official Account | openid / wechat |
| `wecom.send` | Send WeCom message | Corp app | userid |
| `twitch.chat` | Send Twitch chat message | Authorized user | Optional broadcaster id |
| `rumble.publish` | Publish to Rumble | Partner API | Metadata; needs API key |

---

## Campaign form fields

| Field | UI label | Purpose |
|-------|----------|---------|
| `message` | **Message** | Body text; lead tokens supported. Reddit uses ~100 chars as title. |
| `channelTarget` | **Channel / target override** | Telegram chat id, Discord channel id, Reddit subreddit, phone/openid/userid, Twitch broadcaster id. |

X Tweet campaigns use the Tweet entity picker instead.

---

## Monitoring (CLI & cron)

```bash
php bin/console mautic:social:monitor --network=NETWORK --query="QUERY" [--limit=20]
```

| `--network` | `--query` examples |
|-------------|-------------------|
| `x` / `twitter` | `#mautic` |
| `mastodon` | `opensource` |
| `bluesky` | `marketing automation` |
| `reddit` | `marketing` |
| `youtube` | `mautic tutorial` |
| `yelp` | `pizza\|New York` |
| `twitch` | `justchatting` |
| `tiktok` | any (lists own videos) |

```bash
php bin/console mautic:social:monitor --network=x --query="#YourBrand" --limit=25
php bin/console mautic:social:monitor --network=yelp --query="coffee|Berlin" --limit=15
```

Cron example:

```cron
*/15 * * * * www-data cd /path/to/mautic && php bin/console mautic:social:monitor --network=x --query="#YourBrand" --limit=50
```

---

## Architecture

```
plugins/MauticSocialBundle/
├── Integration/          # One class per network
├── Helper/               # Pure API helpers (unit-tested)
├── Command/              # mautic:social:monitor
├── EventListener/        # CampaignSubscriber
├── Form/Type/
├── Translations/en_US/
└── Tests/Unit/Helper/
```

---

## Development & tests

```bash
composer update --ignore-platform-reqs
vendor/bin/phpunit --configuration phpunit.xml.dist
```

CI: PHP **8.2 / 8.5 / 8.6**, PHPUnit **11.5**.

---

## Limitations & notes by network

See in-app notes on each plugin form (`mautic.social.*.notes.*`). Highlights: WhatsApp 24h window, WeChat 48h CS window, Mastodon/Bluesky/LinkedIn post as connected account, TikTok Display until Content Posting is approved, Yelp/Places search-only.

---

## Support the project

If this plugin saves you time, you can support development:

- **GitHub Sponsors:** [github.com/sponsors/wieslawgolec](https://github.com/sponsors/wieslawgolec)
- **Buy Me a Coffee:** [buymeacoffee.com/wieslawgolec](https://buymeacoffee.com/wieslawgolec)

Use the **Sponsor** button on this repository for the same links.

## License

GPL-3.0-or-later (same family as Mautic).
