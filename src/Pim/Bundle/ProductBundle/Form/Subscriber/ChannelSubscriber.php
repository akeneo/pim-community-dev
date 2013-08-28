<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Form subscriber for Channel
 * 
 * Disables the code field in update mode
 * @author Antoine Guigan <antoine@akeneo.com>
 */
class ChannelSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA=>'disableCode'  
        );
    }
    /**
     * Adds the code field to the form
     * 
     * @param \Symfony\Component\Form\FormEvent $event
     * @return type
     */
    public function disableCode(FormEvent $event)
    {
        $channel = $event->getData();
        if (null === $channel) return;
        
        $form = $event->getForm();
        $form->add('code', 'text', array(
            'disabled' => (bool)$channel->getId()
        ));
    }
}
