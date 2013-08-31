<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Form subscriber for Channel
 *
 * Disables the code field in update mode
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'addCodeField'
        );
    }

    /**
     * Adds the code field to the form
     *
     * @param FormEvent $event
     *
     * @return type
     */
    public function addCodeField(FormEvent $event)
    {
        $channel = $event->getData();
        if (null === $channel) {
            return;
        }

        $form = $event->getForm();
        $form->add(
            'code',
            'text',
            array('disabled' => (bool) $channel->getId())
        );
    }
}
