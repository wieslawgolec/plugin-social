<?php

namespace MauticPlugin\MauticSocialBundle\Integration;

use Mautic\CoreBundle\Translation\Translator;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class SocialIntegration extends AbstractIntegration
{
    protected $persistNewLead = false;

    /**
     * @var Translator
     */
    protected TranslatorInterface $translator;

    protected IntegrationHelper $integrationHelper;

    #[Required]
    public function autowireSocialIntegration(
        IntegrationHelper $integrationHelper,
    ): void {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * Prefer PNG under Assets/img/{name}.png; fall back to SVG for new networks.
     */
    public function getIcon(): string
    {
        $systemPath = $this->pathsHelper->getSystemPath('root');
        $pluginPath = $this->pathsHelper->getSystemPath('plugins');
        $bundlePath = $this->pathsHelper->getSystemPath('bundles');
        $genericIcon = $bundlePath.'/PluginBundle/Assets/img/generic.png';

        $name = strtolower($this->getName());
        $bundle = $this->settings->getPlugin()->getBundle();
        $base = $pluginPath.'/'.$bundle.'/Assets/img/'.$name;

        foreach (['.png', '.svg'] as $ext) {
            $icon = $base.$ext;
            if (file_exists($systemPath.'/'.$icon)) {
                return $icon;
            }
        }

        return parent::getIcon() ?: $genericIcon;
    }

    /**
     * @param Form|FormBuilder $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('features' == $formArea) {
            $name     = strtolower($this->getName());
            $formType = $this->getFormType();
            if ($formType) {
                $builder->add('shareButton', $formType, [
                    'label'    => 'mautic.integration.form.sharebutton',
                    'required' => false,
                    'data'     => $data['shareButton'] ?? [],
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return array
     */
    public function getFormLeadFields(array $settings = [])
    {
        static $fields = [];

        if (empty($fields)) {
            $s         = $this->getName();
            $available = $this->getAvailableLeadFields($settings);
            if ([] === $available) {
                return [];
            }
            $socialProfileUrls = $this->integrationHelper->getSocialProfileUrlRegex();

            foreach ($available as $field => $details) {
                $label = (!empty($details['label'])) ? $details['label'] : false;
                $fn    = $this->matchFieldName($field);
                switch ($details['type']) {
                    case 'string':
                    case 'boolean':
                        $fields[$fn] = (!$label)
                            ? $this->translator->transConditional("mautic.integration.common.{$fn}", "mautic.integration.{$s}.{$fn}")
                            : $label;
                        break;
                    case 'object':
                        if (isset($details['fields'])) {
                            foreach ($details['fields'] as $f) {
                                $fn          = $this->matchFieldName($field, $f);
                                $fields[$fn] = (!$label)
                                    ? $this->translator->transConditional("mautic.integration.common.{$fn}", "mautic.integration.{$s}.{$fn}")
                                    : $label;
                            }
                        } else {
                            $fields[$field] = (!$label)
                                ? $this->translator->transConditional("mautic.integration.common.{$fn}", "mautic.integration.{$s}.{$fn}")
                                : $label;
                        }
                        break;
                    case 'array_object':
                        if ('urls' == $field || 'url' == $field) {
                            foreach ($socialProfileUrls as $p => $d) {
                                $fields["{$p}ProfileHandle"] = (!$label)
                                    ? $this->translator->transConditional("mautic.integration.common.{$p}ProfileHandle", "mautic.integration.{$s}.{$p}ProfileHandle")
                                    : $label;
                            }
                            foreach ($details['fields'] as $f) {
                                $fields["{$p}Urls"] = (!$label)
                                    ? $this->translator->transConditional("mautic.integration.common.{$f}Urls", "mautic.integration.{$s}.{$f}Urls")
                                    : $label;
                            }
                        } elseif (isset($details['fields'])) {
                            foreach ($details['fields'] as $f) {
                                $fn          = $this->matchFieldName($field, $f);
                                $fields[$fn] = (!$label)
                                    ? $this->translator->transConditional("mautic.integration.common.{$fn}", "mautic.integration.{$s}.{$fn}")
                                    : $label;
                            }
                        } else {
                            $fields[$fn] = (!$label)
                                ? $this->translator->transConditional("mautic.integration.common.{$fn}", "mautic.integration.{$s}.{$fn}")
                                : $label;
                        }
                        break;
                }
            }
            if ($this->sortFieldsAlphabetically()) {
                uasort($fields, strnatcmp(...));
            }
        }

        return $fields;
    }

    public function getFormCompanyFields($settings = [])
    {
        $settings['feature_settings']['objects'] = ['Company'];

        return [];
    }

    public function getAuthenticationType()
    {
        return 'oauth2';
    }

    public function getRequiredKeyFields()
    {
        return [
            'client_id'     => 'mautic.integration.keyfield.clientid',
            'client_secret' => 'mautic.integration.keyfield.clientsecret',
        ];
    }

    public function getClientIdKey()
    {
        return 'client_id';
    }

    public function getClientSecretKey()
    {
        return 'client_secret';
    }

    public function parseCallbackResponse($data, $postAuthorization = false)
    {
        if ($postAuthorization) {
            return json_decode($data, true);
        }

        return json_decode($data);
    }

    public function getFormNotes($section)
    {
        return ['', 'info'];
    }

    public function getSocialProfileTemplate()
    {
        return "MauticSocialBundle:Integration/{$this->getName()}/Profile:view.html.twig";
    }

    protected function getContactAccessToken(array &$socialCache)
    {
        if (!$this->requestStack->getCurrentRequest()->hasSession()) {
            return null;
        }

        if (!$this->requestStack->getSession()->isStarted()) {
            return (isset($socialCache['accessToken'])) ? $this->decryptApiKeys($socialCache['accessToken']) : null;
        }

        $accessToken = $this->requestStack->getSession()->get($this->getName().'_tokenResponse', []);
        if (!isset($accessToken[$this->getAuthTokenKey()])) {
            if (isset($socialCache['accessToken'])) {
                $accessToken = $this->decryptApiKeys($socialCache['accessToken']);
            } else {
                return null;
            }
        } else {
            $this->requestStack->getSession()->remove($this->getName().'_tokenResponse');
            $socialCache['accessToken'] = $this->encryptApiKeys($accessToken);

            $this->persistNewLead = true;
        }

        return $accessToken;
    }

    abstract public function getFormType();
}
