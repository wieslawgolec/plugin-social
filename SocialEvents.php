<?php

namespace MauticPlugin\MauticSocialBundle;

final class SocialEvents
{
    public const MONITOR_POST_SAVE = 'mautic.monitor_post_save';
    public const MONITOR_POST_DELETE = 'mautic.monitor_post_delete';
    public const TWEET_POST_SAVE = 'mautic.tweet_post_save';
    public const TWEET_POST_DELETE = 'mautic.tweet_post_delete';
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mautic.social_on_campaign_trigger_action';
    public const MONITORING_ON_LOOKUP = 'mautic.social_monitoring_on_lookup';
}
