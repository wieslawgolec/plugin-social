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
3. **Info notes** — each screen shows **capabilities and limits** (authorization + features), for example:
   - WhatsApp 24-hour session window
   - WeChat 48-hour customer-service window
   - X API tier / rate limits
   - Yelp = places only, no posting

### Typical key fields by type

| Type | Networks | What to enter |
|------|----------|----------------|
| **OAuth2** | X, Facebook, Instagram, LinkedIn, Reddit, YouTube, Pinterest, TikTok, Twitch, Mastodon | Client ID/secret (or instance URL for Mastodon) → **Authorize** → tokens stored encrypted |
| **API key / token** | Yelp, Google Places, Telegram, Discord, WhatsApp, Bluesky, WeChat, WeCom, Rumble | Provider-specific keys (see notes on each form) |

### OAuth callback

Copy the **callback URL** shown on the plugin form into the developer console of X, Meta, LinkedIn, Reddit, Google, TikTok, Twitch, Pinterest, or your Mastodon instance. After Authorize, Mautic stores tokens encrypted (same pattern as other Mautic integrations).

### Feature toggles

Where a network supports share/login features, use the **Features** section. Feature notes explain what is actually implemented versus what the remote API could do in theory.

---

## Campaign builder actions

Campaign actions appear only if the matching integration is **published**.

| Action key | Label | Sends as | Recipient / target |
|------------|-------|----------|--------------------|
| `twitter.tweet` | Send Tweet (X) | Authorized X app user | Contact must have Twitter handle; uses a **Tweet** entity |
| `telegram.send` | Send Telegram message | Bot | Chat id: form override → contact `telegram` → default chat id |
| `discord.send` | Send Discord message | Webhook or bot | Channel id override → else webhook URL |
| `mastodon.post` | Post to Mastodon | Authorized Mastodon account | Public (or visibility in API); **not** per-contact |
| `bluesky.post` | Post to Bluesky | Configured handle | **not** per-contact |
| `reddit.submit` | Submit to Reddit | Authorized Reddit user | **Channel target = subreddit** (no `r/` prefix) |
| `whatsapp.send` | Send WhatsApp message | WABA phone number | Phone: override → `whatsapp` / `mobile` / `phone` |
| `linkedin.post` | Post to LinkedIn | Authorized member | Member feed; **not** a DM |
| `wechat.send` | Send WeChat message | Official Account | `openid` / `wechat` or override |
| `wecom.send` | Send WeCom message | Corp app | `userid` / `wecom` or override |
| `twitch.chat` | Send Twitch chat message | Authorized Twitch user | Optional broadcaster id override |
| `rumble.publish` | Publish to Rumble | Partner API | Metadata title/description; needs API key |

### How to add an action

1. Open a campaign → **Add event** → **Actions**.
2. Choose the social action (names above).
3. Fill **Message** and optional **Channel / target override**.
4. Ensure contacts have the required fields (phone, openid, twitter handle, etc.).

Lead tokens such as `{contactfield=firstname}` are expanded at send time.

---

## Campaign form fields

Shared form type: `SocialMessageSendType` (all message-style actions except X Tweet, which uses the Tweet entity picker).

| Field | UI label | Purpose |
|-------|----------|---------|
| `message` | **Message** | Body text. Tokens supported. For Reddit, the first ~100 characters are also used as the post **title**. |
| `channelTarget` | **Channel / target override** | Optional override when the contact field or integration default is not enough (see table below). |

### `channelTarget` meaning by network

| Network | Meaning of override |
|---------|---------------------|
| Telegram | Chat ID (user, group, or channel) |
| Discord | Channel snowflake ID (uses bot token); if empty → webhook |
| Reddit | Subreddit name without `r/` |
| WhatsApp | E.164 phone digits |
| WeChat | Recipient `openid` |
| WeCom | Member `userid` (comma or `\|` for multiple) |
| Twitch | Broadcaster user id |
| Mastodon / Bluesky / LinkedIn | Usually unused (account-level post) |
| Rumble | Unused for meta publish (channel comes from plugin config) |

### X (Twitter) campaign form

Uses **TweetSendType**: pick an existing **Tweet** record (Channels → Tweets). The contact’s Twitter handle is required for send registration / timeline behaviour.

---

## Monitoring (CLI & cron)

### Generic command

```bash
php bin/console mautic:social:monitor --network=NETWORK --query="QUERY" [--limit=20]
```

| `--network` | `--query` examples | What it does |
|-------------|-------------------|--------------|
| `x` or `twitter` | `#mautic` or free text | X recent search (hashtags get `-is:retweet` style helpers) |
| `mastodon` | `mautic` or `#mautic` | Tag timeline on the configured instance |
| `bluesky` | `mautic` | `app.bsky.feed.searchPosts` |
| `reddit` | `php` or `r/php` | New posts in subreddit |
| `youtube` | `mautic marketing` | Video search |
| `yelp` | `coffee\|Berlin` | Business search (`term\|location`) |
| `twitch` | `fps` | Channel search (live flag when present) |
| `tiktok` | any (ignored by API) | Lists **authorized account** videos |

### Examples

```bash
# X hashtag
php bin/console mautic:social:monitor --network=x --query="#mautic" --limit=25

# Mastodon tag on your instance
php bin/console mautic:social:monitor --network=mastodon --query=opensource --limit=30

# Bluesky keyword
php bin/console mautic:social:monitor --network=bluesky --query="marketing automation"

# Reddit subreddit
php bin/console mautic:social:monitor --network=reddit --query=marketing

# YouTube
php bin/console mautic:social:monitor --network=youtube --query="mautic tutorial" --limit=10

# Yelp (term|city)
php bin/console mautic:social:monitor --network=yelp --query="pizza|New York" --limit=15

# Twitch live-oriented channel search
php bin/console mautic:social:monitor --network=twitch --query=justchatting --limit=20

# TikTok (own videos)
php bin/console mautic:social:monitor --network=tiktok --query=account --limit=10
```

### Cron suggestions

```cron
# Every 15 minutes — brand hashtag on X
*/15 * * * * www-data cd /path/to/mautic && php bin/console mautic:social:monitor --network=x --query="#YourBrand" --limit=50 >> /var/log/mautic-social-x.log 2>&1

# Hourly Reddit + Mastodon
0 * * * * www-data cd /path/to/mautic && php bin/console mautic:social:monitor --network=reddit --query=YourSubreddit --limit=25
5 * * * * www-data cd /path/to/mautic && php bin/console mautic:social:monitor --network=mastodon --query=YourTag --limit=25
```

### Legacy Twitter monitoring UI

The plugin still ships **Monitoring** entities and commands such as hashtag/mention monitors under Channels → Social monitoring. Configure monitors in the UI where available; the generic `mautic:social:monitor` command covers multi-network CLI usage above.

Ensure the integration is **published** and authorized before running cron.

---

## Architecture

```
plugins/MauticSocialBundle/
├── Integration/          # One class per network (auth, post/search/profile)
├── Helper/               # Pure API helpers (URLs, payloads, mappers) — unit-tested
├── Command/              # mautic:social:monitor + legacy Twitter monitors
├── EventListener/        # CampaignSubscriber (registers actions)
├── Form/Type/            # Tweet + SocialMessageSendType
├── Translations/en_US/   # UI notes and campaign labels
└── Tests/Unit/Helper/    # PHPUnit 11.x
```

- **Helpers** do not boot Mautic core; safe for CI.
- **Integrations** call `makeRequest()` / OAuth via Mautic’s plugin layer.
- **CampaignEventHelper** expands lead tokens and dispatches per-network send methods.

---

## Development & tests

```bash
git clone https://github.com/wieslawgolec/plugin-social.git
cd plugin-social
composer update --ignore-platform-reqs
vendor/bin/phpunit --configuration phpunit.xml.dist
```

GitHub Actions matrix: **PHP 8.2, 8.5, 8.6** with `composer update --ignore-platform-reqs` (needed while 8.6 is not fully platform-tagged).

PHPUnit **11.5** tests cover helpers (payloads, URL cleaning, response mapping), not live HTTP.

---

## Limitations & notes by network

| Network | Operator should know |
|---------|----------------------|
| **X** | Paid API tiers; OAuth2 PKCE-style app setup; campaign uses Tweet entities |
| **WhatsApp** | Opt-in; free-form text only in 24h window; else templates |
| **WeChat** | CS text in 48h window; else templates; China endpoints |
| **WeCom** | Corp members only (`userid`) |
| **Telegram / Discord** | No public firehose search in this plugin |
| **Mastodon / Bluesky / LinkedIn** | Campaign posts as the **connected account**, not as each lead |
| **TikTok** | Display/list only until Content Posting is approved |
| **Rumble** | Partner API for publish; slug for profile |
| **Yelp / Google Places** | Directory/search only |
| **YouTube** | Read/search by default (`youtube.readonly`) |

Full wording is also embedded in the Mautic UI via `Translations/en_US/messages.ini` (`mautic.social.*.notes.*` and campaign `*_desc` keys).

---

## Support & contributing

- Issues and PRs: [github.com/wieslawgolec/plugin-social](https://github.com/wieslawgolec/plugin-social)
- Upstream reference: [mautic/plugin-social](https://github.com/mautic/plugin-social)

## License

GPL-3.0-or-later (same family as Mautic).
