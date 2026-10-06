<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Integration;

use MauticPlugin\MauticSocialBundle\Helper\TelegramApiHelper;

final class TelegramIntegration extends SocialIntegration
{
    public function getName(): string { return 'Telegram'; }
    public function getIdentifierFields(): array { return ['telegram']; }
    public function getSupportedFeatures(): array { return ['share_button']; }
    public function getAuthenticationType(): string { return 'key'; }
    public function getRequiredKeyFields(): array
    {
        return [
            'bot_token' => 'mautic.integration.keyfield.bot_token',
            'default_chat_id' => 'mautic.integration.keyfield.chat_id',
        ];
    }
    public function getApiUrl($endpoint): string
    {
        return TelegramApiHelper::methodUrl($this->keys['bot_token'] ?? '', (string) $endpoint);
    }
    public function getFormType() { return null; }
}
