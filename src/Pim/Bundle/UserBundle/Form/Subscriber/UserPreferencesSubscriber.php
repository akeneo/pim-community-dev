<?php

namespace Pim\Bundle\UserBundle\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Subscriber to override additional user fields with regular entity fields and use custom query builders
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
    }

    /**
     * Override catalogLocale, catalogScope and defautTree fields
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSetData(FormEvent $event)
    {
        if (null === $event->getData()) {
            return;
        }

        $form = $event->getForm();

        if (!$form->has('additional')) {
            return;
        }

        $subForm = $form->get('additional');

        if ($subForm->has('catalogLocale')) {
            $subForm->add(
                'catalogLocale',
                'entity',
                array(
                    'class'         => 'PimCatalogBundle:Locale',
                    'property'      => 'code',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getActivatedLocalesQB();
                    }
                )
            );
        };

        if ($subForm->has('catalogScope')) {
            $subForm->add(
                'catalogScope',
                'entity',
                array(
                    'class'    => 'PimCatalogBundle:Channel',
                    'property' => 'label'
                )
            );
        };

        if ($subForm->has('defaultTree')) {
            $subForm->add(
                'defaultTree',
                'entity',
                array(
                    'class'         => 'PimCatalogBundle:Category',
                    'property'      => 'label',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getTreesQB();
                    }
                )
            );
        };
    }
}
