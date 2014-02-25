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

        $this->updateCatalogLocale($form);
        $this->updateCatalogScope($form);
        $this->updateDefaultTree($form);

    }

    /**
     * @param Form $form
     *
     * @return null
     */
    protected function updateCatalogLocale(Form $form)
    {
        $form->add(
            'catalogLocale',
            'entity',
            array(
                'class'         => 'PimCatalogBundle:Locale',
                'property'      => 'code',
                'select2'       => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getActivatedLocalesQB();
                }
            )
        );
    }

    /**
     * @param Form $form
     *
     * @return null
     */
    protected function updateCatalogScope(Form $form)
    {
        $form->add(
            'catalogScope',
            'entity',
            array(
                'class'    => 'PimCatalogBundle:Channel',
                'property' => 'label',
                'select2'  => true
            )
        );
    }

    /**
     * @param Form $form
     *
     * @return null
     */
    protected function updateDefaultTree(Form $form)
    {
        $form->add(
            'defaultTree',
            'entity',
            array(
                'class'         => 'PimCatalogBundle:Category',
                'property'      => 'label',
                'select2'       => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getTreesQB();
                }
            )
        );
    }
}
