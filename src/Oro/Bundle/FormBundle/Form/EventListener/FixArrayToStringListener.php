<?php

namespace Oro\Bundle\FormBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixArrayToStringListener implements EventSubscriberInterface
{
    private $delimiter;

    /**
     * @param string $delimiter
     */
    public function __construct($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function preBind(FormEvent $event)
    {
        $value = $event->getData();
        if (is_array($value)) {
            $event->setData(implode($this->delimiter, $value));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }
}
