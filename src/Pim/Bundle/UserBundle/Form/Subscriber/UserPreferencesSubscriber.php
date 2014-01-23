<?php

namespace Pim\Bundle\UserBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
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
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
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
        $this->updateCatalogLocale($subForm);
        $this->updateCatalogScope($subForm);
        $this->updateDefaultTree($subForm);

    }

    /**
     * @param Form $subForm
     *
     * @return null
     */
    protected function updateCatalogLocale(Form $subForm)
    {
        if ($subForm->has('catalogLocale')) {
            $subForm->add(
                'catalogLocale',
                'entity',
                [
                    'class'         => 'PimCatalogBundle:Locale',
                    'property'      => 'code',
                    'select2'       => true,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getActivatedLocalesQB();
                    }
                ]
            );
        };
    }

    /**
     * @param Form $subForm
     *
     * @return null
     */
    protected function updateCatalogScope(Form $subForm)
    {
        if ($subForm->has('catalogScope')) {
            $subForm->add(
                'catalogScope',
                'entity',
                [
                    'class'    => 'PimCatalogBundle:Channel',
                    'property' => 'label',
                    'select2'  => true
                ]
            );
        };
    }

    /**
     * @param Form $subForm
     *
     * @return null
     */
    protected function updateDefaultTree(Form $subForm)
    {
        if ($subForm->has('defaultTree')) {
            $subForm->add(
                'defaultTree',
                'entity',
                [
                    'class'         => 'PimCatalogBundle:Category',
                    'property'      => 'label',
                    'select2'       => true,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getTreesQB();
                    }
                ]
            );
        };
    }
}
