<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Group subscriber used to disable some fields
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData'
        );
    }

    /**
     * Post set data event
     * Disable axis fields
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $group = $event->getData();
        if (null === $group) {
            return;
        }

        $form = $event->getForm();

        if ($group->getId()) {
            $form->add(
                'attributes',
                'entity',
                array(
                    'disabled' => true,
                    'class'    => 'Pim\Bundle\CatalogBundle\Model\ProductAttribute',
                    'multiple' => true,
                    'label'    => 'Axis',
                    'help'     => 'pim_catalog.group.axis.help'
                )
            );
        }

        if ($group->getType()) {
            $form->add(
                'type',
                'entity',
                array(
                    'disabled' => true,
                    'class' => 'PimCatalogBundle:GroupType',
                    'multiple' => false,
                    'expanded' => false
                )
            );
        }
    }
}
