<?php

declare(strict_types=1);

return [
    'name'        => 'Social Media',
    'description' => 'Modernized social integrations for Mautic 7.2+. Each plugin configuration screen and campaign action shows capabilities and limits for the operator.',
    'version'     => '2.1.0',
    'author'      => 'Mautic / community modernization',

    'routes' => [
        'main' => [
            'mautic_social_index' => [
                'path'       => '/monitoring/{page}',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\MonitoringController::indexAction',
            ],
            'mautic_social_action' => [
                'path'       => '/monitoring/{objectAction}/{objectId}',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\MonitoringController::executeAction',
            ],
            'mautic_social_contacts' => [
                'path'       => '/monitoring/view/{objectId}/contacts/{page}',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\MonitoringController::contactsAction',
            ],
            'mautic_tweet_index' => [
                'path'       => '/tweets/{page}',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\TweetController::indexAction',
            ],
            'mautic_tweet_action' => [
                'path'       => '/tweets/{objectAction}/{objectId}',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\TweetController::executeAction',
            ],
        ],
        'api' => [
            'mautic_api_tweetsstandard' => [
                'standard_entity' => true,
                'name'            => 'tweets',
                'path'            => '/tweets',
                'controller'      => MauticPlugin\MauticSocialBundle\Controller\Api\TweetApiController::class,
            ],
        ],
        'public' => [
            'mautic_social_js_generate' => [
                'path'       => '/social/generate/{formName}.js',
                'controller' => 'MauticPlugin\MauticSocialBundle\Controller\JsController::generateAction',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'mautic.social.monitoring' => [
                'route'     => 'mautic_social_index',
                'parent'    => 'mautic.core.channels',
                'access'    => 'mautic.social:monitoring:view',
                'priority'  => 100,
            ],
            'mautic.social.tweets' => [
                'route'     => 'mautic_tweet_index',
                'parent'    => 'mautic.core.channels',
                'access'    => 'mautic.social:tweets:view',
                'priority'  => 90,
            ],
        ],
    ],
];
