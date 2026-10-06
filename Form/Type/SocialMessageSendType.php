<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class SocialMessageSendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('message', TextareaType::class, [
            'label' => 'mautic.social.campaign.message',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => 4,
                'tooltip' => 'mautic.social.campaign.message.tooltip',
            ],
            'constraints' => [new NotBlank(['message' => 'mautic.core.value.required'])],
        ]);

        $builder->add('channelTarget', TextType::class, [
            'label' => 'mautic.social.campaign.channel_target',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
                'tooltip' => 'mautic.social.campaign.channel_target.tooltip',
                'placeholder' => 'chat id / channel id / optional',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['label' => false]);
    }
}
