<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to disable entity code modification after the entity has been created
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableCodeFieldSubscriber implements EventSubscriberInterface
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
     * Disable the code field
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $entity = $event->getData();
        if (null === $entity || !$entity->getId()) {
            return;
        }

        $form = $event->getForm();

        $form->add(
            'code',
            'text',
            array(
                'disabled'  => true,
                'read_only' => true
            )
        );
    }
}
