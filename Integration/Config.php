<?php

namespace MauticPlugin\MauticSocialBundle\Integration;

use Mautic\PluginBundle\Helper\IntegrationHelper;

class Config
{
    public function __construct(
        private IntegrationHelper $integrationHelper
    ) {
    }

    public function isPublished(): bool
    {
        $integration = $this->integrationHelper->getIntegrationObject('Twitter');

        return $integration && $integration->getIntegrationSettings()->getIsPublished();
    }
}
