<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Form\EventListener;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\LightEntityType;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class UserPreferencesSubscriber implements EventSubscriberInterface
{
    /** @var TranslatedLabelsProviderInterface */
    private $categoryProvider;

    /**
     * @param TranslatedLabelsProviderInterface $categoryProvider
     */
    public function __construct(TranslatedLabelsProviderInterface $categoryProvider)
    {
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'addFieldToForm'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function addFieldToForm(FormEvent $event): void
    {
        $form = $event->getForm();

        $form->add(
            'emailNotifications',
            CheckboxType::class,
            [
                'label'    => 'user.email.notifications',
                'required' => false,
            ]
        );

        $form->add(
            'assetDelayReminder',
            IntegerType::class,
            [
                'label'    => 'user.asset_delay_reminder',
                'required' => true,
            ]
        );

        $form->add(
            'defaultAssetTree',
            LightEntityType::class,
            [
                'select2'    => true,
                'repository' => $this->categoryProvider
            ]
        );
    }
}
