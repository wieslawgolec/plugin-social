<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Security\Permissions;

use Mautic\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Role permissions for Social Monitoring, Tweets, and categories.
 * Permission name is {@see getName()} → checks use mauticSocial:level:permission
 * (e.g. mauticSocial:monitoring:view, mauticSocial:tweets:viewown).
 */
final class MauticSocialPermissions extends AbstractPermissions
{
    /**
     * @param mixed[] $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->addStandardPermissions('categories');
        $this->addStandardPermissions('monitoring');
        $this->addExtendedPermissions('tweets');
    }

    public function getName(): string
    {
        return 'mauticSocial';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addStandardFormFields('mauticSocial', 'categories', $builder, $data);
        $this->addStandardFormFields('mauticSocial', 'monitoring', $builder, $data);
        $this->addExtendedFormFields('mauticSocial', 'tweets', $builder, $data);
    }
}
